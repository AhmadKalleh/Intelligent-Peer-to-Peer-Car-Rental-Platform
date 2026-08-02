<?php

namespace App\Services\AiChat;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * الأداة (Tool) التي يستدعيها نموذج الذكاء الاصطناعي فعلياً للبحث
 * عن سيارات حقيقية في قاعدة البيانات، بدل تأليف نتائج وهمية.
 * تُطبَّق نفس شروط الظهور المستخدمة في باقي التطبيق:
 * السيارة مُدرجة (listed) ومعتمدة إدارياً (approved).
 */
class VehicleSearchTool
{
    public const MAX_RESULTS = 8;

    /**
     * تعبير SQL الخاص بحساب السعر الحالي للسيارة (سعر مخصص فعّال حالياً
     * إن وُجد، وإلا السعر الأساسي). نفس التعبير المستخدم بـ
     * Vehicle::scopeWithCurrentPrice()، لكن هون بنعيد استخدامه كنص خام
     * جوا havingRaw() بدل الاعتماد على alias "current_price" — لأن
     * الاعتماد على alias معرّف بـ subquery مترابط جوا HAVING كان يسبب
     * QueryException عند فلترة السعر (min_price / max_price).
     */
    protected const CURRENT_PRICE_SQL = <<<SQL
        COALESCE(
            (SELECT cp.price_per_day FROM vehicle_custom_pricings cp
             WHERE cp.vehicle_id = vehicles.id
             AND NOW() BETWEEN cp.date_from AND cp.date_to
             ORDER BY cp.date_from DESC LIMIT 1),
            vehicles.base_price_per_day
        )
    SQL;

    /**
     * أسماء المدن السورية بالعربي مقابل الاسم المخزّن بقاعدة البيانات (إنجليزي).
     * المستخدم بيكتب "دمشق" لكن العمود city مخزّن فيه "Damascus" — بدون هالتطبيع
     * أي بحث بالعربي كان رح يرجّع صفر نتائج دايماً.
     */
    protected const CITY_MAP = [
        'دمشق'        => 'Damascus',
        'حلب'         => 'Aleppo',
        'حمص'         => 'Homs',
        'حماة'        => 'Hama',
        'اللاذقية'    => 'Latakia',
        'طرطوس'       => 'Tartus',
        'دير الزور'   => 'Deir ez-Zor',
        'الحسكة'      => 'Hasakah',
        'الرقة'       => 'Raqqa',
        'درعا'        => 'Daraa',
        'السويداء'    => 'Sweida',
        'إدلب'        => 'Idlib',
        'ادلب'        => 'Idlib',
        'القنيطرة'    => 'Quneitra',
    ];

    /**
     * يحوّل اسم مدينة مكتوب بالعربي لمقابله الإنجليزي المخزّن بقاعدة البيانات.
     * إذا الاسم مش موجود بالخريطة (أو مكتوب أصلاً إنجليزي) بيرجّعه متل ما هو،
     * وبيبقى فلتر LIKE يشتغل عادي.
     */
    protected function normalizeCity(string $city): string
    {
        $trimmed = trim($city);

        return self::CITY_MAP[$trimmed] ?? $trimmed;
    }

    /**
     * تعريف الأداة بصيغة JSON Schema المتوافقة مع Groq / OpenAI tool-calling.
     */
    public static function definition(): array
    {
        return [
            'type'     => 'function',
            'function' => [
                'name'        => 'search_vehicles',
                'description' => 'يبحث عن سيارات متاحة للإيجار ضمن التطبيق بناءً على الفلاتر المُعطاة. استخدمها دائماً عندما يطلب المستخدم اقتراح أو إيجاد سيارة.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'city' => [
                            'type'        => 'string',
                            'description' => 'المدينة المطلوبة (اختياري)',
                        ],
                        'make' => [
                            'type'        => 'string',
                            'description' => 'الشركة المصنّعة، مثل Toyota أو Kia (اختياري)',
                        ],
                        'model' => [
                            'type'        => 'string',
                            'description' => 'موديل السيارة (اختياري)',
                        ],
                        'min_price' => [
                            'type'        => 'number',
                            'description' => 'أقل سعر يومي مقبول بالدولار (اختياري)',
                        ],
                        'max_price' => [
                            'type'        => 'number',
                            'description' => 'أعلى سعر يومي مقبول بالدولار (اختياري)',
                        ],
                        'min_seats' => [
                            'type'        => 'integer',
                            'description' => 'أقل عدد مقاعد مطلوب (اختياري)',
                        ],
                        'transmission' => [
                            'type'        => 'string',
                            'enum'        => ['automatic', 'manual'],
                            'description' => 'نوع ناقل الحركة (اختياري)',
                        ],
                        'fuel_type' => [
                            'type'        => 'string',
                            'description' => 'نوع الوقود، مثل benzine أو diesel أو electric (اختياري)',
                        ],
                        'delivery_available' => [
                            'type'        => 'boolean',
                            'description' => 'أرسل true فقط إذا كان توفر التوصيل شرطاً إلزامياً صريحاً من المستخدم (مثل "لازم/بشرط يكون في توصيل"). لا ترسل هذا الحقل إطلاقاً إذا كانت العبارة مجرد تفضيل أو أفضلية اختيارية (مثل "يفضل"، "لو أمكن"، "بحبذا"، "أحسن")، حتى لا يتم استبعاد سيارات مناسبة بدون سبب.',
                        ],
                    ],
                    'required' => [],
                ],
            ],
        ];
    }

    /**
     * تنفيذ البحث الفعلي وإرجاع نتيجة مبسطة تُرسَل كـ tool result للنموذج.
     * إذا لم تُعطِ الفلاتر الكاملة أي نتيجة، تُعاد المحاولة تلقائياً هنا
     * (بالكود، وليس بانتظار قرار من النموذج) بالاحتفاظ فقط بفلاتر الهوية
     * الأساسية (المدينة/الشركة المصنّعة/الموديل) وتجاهل الفلاتر الثانوية
     * (السعر، المقاعد، ناقل الحركة، نوع الوقود، التوصيل)، حتى نضمن دائماً
     * أفضل نتيجة متاحة فعلياً بدل رد فارغ.
     */
    public function search(array $filters): array
    {
        $vehicles = $this->runQuery($filters);

        if ($vehicles->isNotEmpty()) {
            return [
                'count'            => $vehicles->count(),
                'relaxed_filters'  => false,
                'vehicles'         => $vehicles->map(fn (Vehicle $vehicle) => $this->present($vehicle))->values()->all(),
            ];
        }

        $identityFilters = array_intersect_key($filters, array_flip(['city', 'make', 'model']));
        $droppedFilters   = array_keys(array_diff_key($filters, $identityFilters));

        // لو كل الفلاتر أصلاً كانت هوية بس (city/make/model)، معناها فعلاً ما في نتيجة، ما في داعي نعيد نفس الاستعلام
        if (empty($droppedFilters)) {
            return [
                'count'           => 0,
                'relaxed_filters' => false,
                'vehicles'        => [],
            ];
        }

        $relaxedVehicles = $this->runQuery($identityFilters);

        if ($relaxedVehicles->isEmpty()) {
            return [
                'count'           => 0,
                'relaxed_filters' => false,
                'vehicles'        => [],
            ];
        }

        return [
            'count'           => $relaxedVehicles->count(),
            'relaxed_filters' => true,
            'ignored_filters' => $droppedFilters,
            'note'            => 'لم يتم إيجاد سيارات تطابق كل المعايير المطلوبة، لذلك تم تجاهل المعايير التالية وعرض أقرب النتائج المتاحة فعلياً: ' . implode(', ', $droppedFilters) . '. أخبر المستخدم بصدق أن هذه النتائج لا تحقق كل شروطه بالكامل.',
            'vehicles'        => $relaxedVehicles->map(fn (Vehicle $vehicle) => $this->present($vehicle))->values()->all(),
        ];
    }

    protected function runQuery(array $filters): Collection
    {
        $query = Vehicle::query()
            ->select(['vehicles.*'])
            ->with('primaryImage')
            ->withCurrentPrice()
            ->where('listing_status', 'listed')
            ->where('admin_review_status', 'approved');

        if (! empty($filters['city'])) {
            $query->where('city', 'like', '%' . $this->normalizeCity($filters['city']) . '%');
        }

        if (! empty($filters['make'])) {
            $query->where('make', 'like', '%' . $filters['make'] . '%');
        }

        if (! empty($filters['model'])) {
            $query->where('model', 'like', '%' . $filters['model'] . '%');
        }

        if (! empty($filters['min_seats'])) {
            $query->where('seats', '>=', (int) $filters['min_seats']);
        }

        if (! empty($filters['transmission'])) {
            $query->where('transmission', $filters['transmission']);
        }

        if (! empty($filters['fuel_type'])) {
            $query->where('fuel_type', 'like', '%' . $filters['fuel_type'] . '%');
        }

        if (array_key_exists('delivery_available', $filters) && $filters['delivery_available'] !== null) {
            $query->where('delivery_available', (bool) $filters['delivery_available']);
        }

        // ── فلترة السعر ──────────────────────────────────────────
        // ملاحظة: نستخدم havingRaw() بنفس تعبير SQL الكامل لحساب السعر
        // الحالي، بدل having('current_price', ...) على الـ alias مباشرة.
        // الاعتماد على alias معرّف بـ subquery مترابط جوا HAVING كان
        // يسبب QueryException عند التنفيذ الفعلي (رغم إنو الاستعلام
        // كان يُبنى بدون خطأ ظاهري وقت toSql()).
        if (! empty($filters['min_price']) || ! empty($filters['max_price'])) {
            $query->havingRaw(
                self::CURRENT_PRICE_SQL . ' >= ?',
                [(float) ($filters['min_price'] ?? 0)]
            );

            if (! empty($filters['max_price'])) {
                $query->havingRaw(
                    self::CURRENT_PRICE_SQL . ' <= ?',
                    [(float) $filters['max_price']]
                );
            }
        }

        return $query
            ->orderByDesc('rating_avg')
            ->limit(self::MAX_RESULTS)
            ->get();
    }

    protected function present(Vehicle $vehicle): array
    {
        $image = $vehicle->primaryImage;

        return [
            'id'                  => $vehicle->id,
            'make'                => $vehicle->make,
            'model'               => $vehicle->model,
            'year'                => $vehicle->year,
            'city'                => $vehicle->city,
            'seats'               => $vehicle->seats,
            'transmission'        => $vehicle->transmission,
            'fuel_type'           => $vehicle->fuel_type,
            'price_per_day'       => (float) ($vehicle->current_price ?? $vehicle->base_price_per_day),
            'rating_avg'          => (float) ($vehicle->rating_avg ?? 0),
            'delivery_available'  => (bool) $vehicle->delivery_available,
            'image_url'           => $image ? url(Storage::url($image->path)) : null,
        ];
    }
}