<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Database\Seeder;

class ComplaintSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================================
        // Get Users
        // ============================================================

        $raghad = User::where('email', 'raghad@carrental.sy')->firstOrFail();

        $yara = User::where('email', 'yara@carrental.sy')->firstOrFail();
        $jad = User::where('email', 'jad@carrental.sy')->firstOrFail();
        $ahmad = User::where('email', 'ahmad@carrental.sy')->firstOrFail();
        $haifa = User::where('email', 'haifa@carrental.sy')->firstOrFail();
        $salah = User::where('email', 'salah@carrental.sy')->firstOrFail();
        $ghassan = User::where('email', 'ghassan@carrental.sy')->firstOrFail();
        $saeed = User::where('email', 'saeed@carrental.sy')->firstOrFail();
        $adam = User::where('email', 'adam@carrental.sy')->firstOrFail();
        $ayla = User::where('email', 'ayla@carrental.sy')->firstOrFail();

        // ============================================================
        // 20 Complaints
        // ============================================================

        $complaints = [

            // 1 - Yara -> Jad
            [
                'user_id' => $yara->id,
                'reported_user_id' => $jad->id,
                'reason_key' => 'vehicle_condition',
                'reason_subject' => 'حالة السيارة لا تطابق الوصف',
                'reason_text' => 'السيارة التي استلمتها كانت بحالة مختلفة عن الصور والوصف الموجود في الإعلان.',
                'details' => 'وجدت خدوشاً واضحة على السيارة بالإضافة إلى أن السيارة لم تكن نظيفة عند الاستلام.',
                'status' => 'pending',
                'admin_reply' => null,
                'replied_by' => null,
                'replied_at' => null,
            ],

            // 2 - Ahmad -> Jad
            [
                'user_id' => $ahmad->id,
                'reported_user_id' => $jad->id,
                'reason_key' => 'late_handover',
                'reason_subject' => 'تأخير في تسليم السيارة',
                'reason_text' => 'المستخدم تأخر في تسليم السيارة في الموعد المتفق عليه.',
                'details' => 'انتظرت حوالي ساعة كاملة في مكان التسليم قبل أن يتم تسليمي السيارة.',
                'status' => 'answered',
                'admin_reply' => 'تمت مراجعة الشكوى والتواصل مع الطرفين، وتم تنبيه المستخدم بضرورة الالتزام بمواعيد التسليم.',
                'replied_by' => $raghad->id,
                'replied_at' => now()->subDays(3),
            ],

            // 3 - Salah -> Jad
            [
                'user_id' => $salah->id,
                'reported_user_id' => $jad->id,
                'reason_key' => 'vehicle_availability',
                'reason_subject' => 'السيارة غير متوفرة',
                'reason_text' => 'السيارة لم تكن متوفرة في الموعد الذي تم تأكيد الحجز فيه.',
                'details' => 'وصلت إلى مكان الاستلام ثم أبلغني المضيف أن السيارة غير متاحة.',
                'status' => 'answered',
                'admin_reply' => 'تمت مراجعة الحجز والتواصل مع المضيف، وتم تسجيل الملاحظة.',
                'replied_by' => $raghad->id,
                'replied_at' => now()->subDays(10),
            ],

            // 4 - Ayla -> Jad
            [
                'user_id' => $ayla->id,
                'reported_user_id' => $jad->id,
                'reason_key' => 'overall_experience',
                'reason_subject' => 'تجربة سيئة مع المضيف',
                'reason_text' => 'كانت تجربة الحجز والاستلام غير مرضية بشكل عام.',
                'details' => 'واجهت عدة مشاكل أثناء الحجز والتواصل والاستلام.',
                'status' => 'pending',
                'admin_reply' => null,
                'replied_by' => null,
                'replied_at' => null,
            ],

            // 5 - Salah -> Ahmad
            [
                'user_id' => $salah->id,
                'reported_user_id' => $ahmad->id,
                'reason_key' => 'communication',
                'reason_subject' => 'سوء التعامل والتواصل',
                'reason_text' => 'لم يكن المستخدم متعاوناً أثناء التواصل معه بخصوص الحجز.',
                'details' => 'حاولت التواصل معه عدة مرات ولكنه لم يرد إلا بعد وقت طويل.',
                'status' => 'pending',
                'admin_reply' => null,
                'replied_by' => null,
                'replied_at' => null,
            ],

            // 6 - Ghassan -> Ahmad
            [
                'user_id' => $ghassan->id,
                'reported_user_id' => $ahmad->id,
                'reason_key' => 'extra_payment',
                'reason_subject' => 'طلب مبلغ إضافي',
                'reason_text' => 'تم طلب مبلغ إضافي غير موجود ضمن تفاصيل الحجز.',
                'details' => 'طلب مني المستخدم دفع مبلغ إضافي عند استلام السيارة.',
                'status' => 'answered',
                'admin_reply' => 'تمت مراجعة تفاصيل الحجز وطلب توضيح المبلغ الإضافي.',
                'replied_by' => $raghad->id,
                'replied_at' => now()->subDays(5),
            ],

            // 7 - Jad -> Ahmad
            [
                'user_id' => $jad->id,
                'reported_user_id' => $ahmad->id,
                'reason_key' => 'price_issue',
                'reason_subject' => 'اختلاف في السعر',
                'reason_text' => 'السعر المطلوب عند الاستلام كان مختلفاً عن السعر الظاهر في الحجز.',
                'details' => 'ظهر سعر مختلف عند استلام السيارة عن السعر الذي تم تأكيده أثناء الحجز.',
                'status' => 'pending',
                'admin_reply' => null,
                'replied_by' => null,
                'replied_at' => null,
            ],

            // 8 - Saeed -> Ahmad
            [
                'user_id' => $saeed->id,
                'reported_user_id' => $ahmad->id,
                'reason_key' => 'communication',
                'reason_subject' => 'عدم الرد على الرسائل',
                'reason_text' => 'لم يقم المستخدم بالرد على رسائلي المتعلقة بالحجز.',
                'details' => 'أرسلت عدة رسائل ولم أحصل على رد حتى اقترب موعد الاستلام.',
                'status' => 'answered',
                'admin_reply' => 'تمت مراجعة المحادثة وتسجيل الملاحظة.',
                'replied_by' => $raghad->id,
                'replied_at' => now()->subDays(12),
            ],

            // 9 - Saeed -> Haifa
            [
                'user_id' => $saeed->id,
                'reported_user_id' => $haifa->id,
                'reason_key' => 'vehicle_problem',
                'reason_subject' => 'مشكلة ميكانيكية في السيارة',
                'reason_text' => 'ظهرت مشكلة ميكانيكية في السيارة بعد استلامها مباشرة.',
                'details' => 'ظهرت لمبة تحذير في لوحة القيادة بعد فترة قصيرة من استلام السيارة.',
                'status' => 'pending',
                'admin_reply' => null,
                'replied_by' => null,
                'replied_at' => null,
            ],

            // 10 - Adam -> Haifa
            [
                'user_id' => $adam->id,
                'reported_user_id' => $haifa->id,
                'reason_key' => 'booking_cancellation',
                'reason_subject' => 'إلغاء الحجز من قبل المضيف',
                'reason_text' => 'قام المضيف بإلغاء الحجز قبل موعد الاستلام بفترة قصيرة.',
                'details' => 'كنت قد رتبت رحلتي بناءً على الحجز ثم تم إلغاؤه في آخر لحظة.',
                'status' => 'answered',
                'admin_reply' => 'تمت مراجعة حالة الحجز وسيتم اتخاذ الإجراء المناسب وفق سياسة الإلغاء.',
                'replied_by' => $raghad->id,
                'replied_at' => now()->subDays(2),
            ],

            // 11 - Ayla -> Haifa
            [
                'user_id' => $ayla->id,
                'reported_user_id' => $haifa->id,
                'reason_key' => 'vehicle_cleanliness',
                'reason_subject' => 'السيارة غير نظيفة',
                'reason_text' => 'السيارة لم تكن نظيفة عند الاستلام.',
                'details' => 'وجدت بقايا طعام وأوساخاً داخل السيارة وكانت المقاعد بحاجة إلى تنظيف.',
                'status' => 'pending',
                'admin_reply' => null,
                'replied_by' => null,
                'replied_at' => null,
            ],

            // 12 - Yara -> Haifa
            [
                'user_id' => $yara->id,
                'reported_user_id' => $haifa->id,
                'reason_key' => 'fuel_level',
                'reason_subject' => 'مستوى الوقود غير مطابق',
                'reason_text' => 'تم تسليم السيارة بمستوى وقود أقل من المستوى المتفق عليه.',
                'details' => 'كان مستوى الوقود أقل بكثير من المستوى الموجود عند بداية الحجز.',
                'status' => 'answered',
                'admin_reply' => 'تمت مراجعة بيانات الحجز وصور التسليم وتمت معالجة الشكوى.',
                'replied_by' => $raghad->id,
                'replied_at' => now()->subDays(8),
            ],

            // 13 - Ghassan -> Haifa
            [
                'user_id' => $ghassan->id,
                'reported_user_id' => $haifa->id,
                'reason_key' => 'documents',
                'reason_subject' => 'مشكلة في أوراق السيارة',
                'reason_text' => 'كانت هناك مشكلة في بعض الأوراق المتعلقة بالسيارة.',
                'details' => 'لم تكن جميع المستندات المطلوبة متوفرة عند استلام السيارة.',
                'status' => 'pending',
                'admin_reply' => null,
                'replied_by' => null,
                'replied_at' => null,
            ],

            // 14 - Adam -> Haifa
            [
                'user_id' => $adam->id,
                'reported_user_id' => $haifa->id,
                'reason_key' => 'pickup_location',
                'reason_subject' => 'تغيير مكان الاستلام',
                'reason_text' => 'قام المستخدم بتغيير مكان استلام السيارة بعد تأكيد الحجز.',
                'details' => 'تم تغيير موقع الاستلام في آخر لحظة مما تسبب لي بتكاليف نقل إضافية.',
                'status' => 'answered',
                'admin_reply' => 'تم التواصل مع الطرفين وتسجيل الملاحظة على الحجز.',
                'replied_by' => $raghad->id,
                'replied_at' => now()->subDays(6),
            ],

            // 15 - Yara -> Salah
            [
                'user_id' => $yara->id,
                'reported_user_id' => $salah->id,
                'reason_key' => 'late_return',
                'reason_subject' => 'التأخر في إعادة السيارة',
                'reason_text' => 'تمت إعادة السيارة متأخرة عن الوقت المتفق عليه.',
                'details' => 'تسبب التأخير في مشكلة بالنسبة للحجز التالي واضطررت للانتظار.',
                'status' => 'answered',
                'admin_reply' => 'تم التواصل مع الطرفين وتسجيل الملاحظة على الحجز.',
                'replied_by' => $raghad->id,
                'replied_at' => now()->subDays(7),
            ],

            // 16 - Jad -> Salah
            [
                'user_id' => $jad->id,
                'reported_user_id' => $salah->id,
                'reason_key' => 'vehicle_damage',
                'reason_subject' => 'وجود أضرار على السيارة',
                'reason_text' => 'تمت إعادة السيارة مع وجود ضرر جديد لم يكن موجوداً قبل التسليم.',
                'details' => 'تم اكتشاف خدش على الباب الخلفي بعد إعادة السيارة.',
                'status' => 'pending',
                'admin_reply' => null,
                'replied_by' => null,
                'replied_at' => null,
            ],

            // 17 - Adam -> Salah
            [
                'user_id' => $adam->id,
                'reported_user_id' => $salah->id,
                'reason_key' => 'booking_rules',
                'reason_subject' => 'عدم الالتزام بشروط الحجز',
                'reason_text' => 'لم يتم الالتزام بالشروط المتفق عليها في الحجز.',
                'details' => 'حدث خلاف بين الطرفين بسبب عدم الالتزام بالتعليمات الخاصة بالحجز.',
                'status' => 'pending',
                'admin_reply' => null,
                'replied_by' => null,
                'replied_at' => null,
            ],

            // 18 - Saeed -> Ghassan
            [
                'user_id' => $saeed->id,
                'reported_user_id' => $ghassan->id,
                'reason_key' => 'wrong_information',
                'reason_subject' => 'معلومات غير صحيحة عن السيارة',
                'reason_text' => 'تفاصيل السيارة في الإعلان لم تكن مطابقة للسيارة التي استلمتها.',
                'details' => 'الإعلان ذكر وجود بعض الميزات التي لم أجدها في السيارة.',
                'status' => 'pending',
                'admin_reply' => null,
                'replied_by' => null,
                'replied_at' => null,
            ],

            // 19 - Adam -> Ghassan
            [
                'user_id' => $adam->id,
                'reported_user_id' => $ghassan->id,
                'reason_key' => 'host_behavior',
                'reason_subject' => 'تصرف غير مناسب من المضيف',
                'reason_text' => 'تعرضت لتعامل غير مناسب أثناء عملية استلام السيارة.',
                'details' => 'كان أسلوب التواصل غير محترم ولم يتم التعامل معي بطريقة مناسبة.',
                'status' => 'answered',
                'admin_reply' => 'تم التواصل مع المضيف وتسجيل ملاحظة رسمية على حسابه.',
                'replied_by' => $raghad->id,
                'replied_at' => now()->subDays(6),
            ],

            // 20 - Ayla -> Yara
            [
                'user_id' => $ayla->id,
                'reported_user_id' => $yara->id,
                'reason_key' => 'communication',
                'reason_subject' => 'مشكلة في التواصل',
                'reason_text' => 'واجهت صعوبة في التواصل مع المستخدم بخصوص تفاصيل الحجز.',
                'details' => 'لم يتم الرد على الرسائل في الوقت المناسب مما سبب تأخيراً في التنسيق.',
                'status' => 'pending',
                'admin_reply' => null,
                'replied_by' => null,
                'replied_at' => null,
            ],
        ];

        // ============================================================
        // Insert Complaints
        // ============================================================

        foreach ($complaints as $complaint) {
            Complaint::create($complaint);
        }

        $this->command->info('20 complaints seeded successfully.');
    }
}