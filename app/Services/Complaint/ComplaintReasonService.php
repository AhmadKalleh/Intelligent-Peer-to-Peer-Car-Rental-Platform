<?php

namespace App\Services\Complaint;

class ComplaintReasonService
{
    // ─── اللائحة الثابتة للشكاوي اللبقة ───────────────────────────────────────
    // كل عنصر يحتوي على: id (رقم الشكوى) - key (مفتاح) - subject (عنوان الشكوى) - text (نص الشكوى)
    protected static array $reasons = [
        [
            'id'      => 1,
            'key'     => 'late_response',
            'subject' => 'تأخر في الرد أو التواصل',
            'text'    => 'لم يقم الطرف الآخر بالرد أو التواصل خلال مدة معقولة بعد تأكيد الحجز.',
        ],
        [
            'id'      => 2,
            'key'     => 'inappropriate_behavior',
            'subject' => 'سلوك غير لائق',
            'text'    => 'تعرضت لتعامل أو أسلوب غير لائق من الطرف الآخر أثناء التواصل أو الرحلة.',
        ],
        [
            'id'      => 3,
            'key'     => 'vehicle_mismatch',
            'subject' => 'عدم مطابقة السيارة للوصف',
            'text'    => 'السيارة المستلمة لا تطابق الوصف أو الصور الموجودة في الإعلان.',
        ],
        [
            'id'      => 4,
            'key'     => 'payment_issue',
            'subject' => 'مشكلة في الدفع أو الاسترداد',
            'text'    => 'واجهت مشكلة تتعلق بعملية الدفع أو استرداد مبلغ مالي مستحق.',
        ],
        [
            'id'      => 5,
            'key'     => 'cancellation_without_notice',
            'subject' => 'إلغاء الحجز دون إشعار مسبق',
            'text'    => 'قام الطرف الآخر بإلغاء الحجز دون إشعاري مسبقاً ودون سبب مقنع.',
        ],
        [
            'id'      => 6,
            'key'     => 'cleanliness_safety',
            'subject' => 'مشكلة في نظافة أو سلامة السيارة',
            'text'    => 'السيارة لم تكن نظيفة أو ظهرت مشكلة تتعلق بسلامتها الفنية.',
        ],
        [
            'id'      => 7,
            'key'     => 'pickup_delivery_delay',
            'subject' => 'عدم الالتزام بموعد التسليم أو الاستلام',
            'text'    => 'لم يلتزم الطرف الآخر بالموعد المتفق عليه لتسليم أو استلام السيارة.',
        ],
        [
            'id'      => 8,
            'key'     => 'other',
            'subject' => 'سبب آخر',
            'text'    => 'يوجد سبب آخر غير مذكور أعلاه، وسيتم توضيحه في الملاحظات الإضافية.',
        ],
    ];

    // ─── إرجاع كامل اللائحة ────────────────────────────────────────────────────

    public function getReasons(): array
    {
        return self::$reasons;
    }

    public static function all(): array
    {
        return self::$reasons;
    }

    // ─── إرجاع كل المعرّفات المسموح بها (تُستخدم في التحقق) ───────────────────

    public static function ids(): array
    {
        return array_column(self::$reasons, 'id');
    }

    // ─── مفتاح "سبب آخر" (يُستخدم لإلزام كتابة ملاحظات إضافية) ────────────────

    public static function otherId(): int
    {
        return 8;
    }

    // ─── البحث عن سبب محدد عبر الـ id ──────────────────────────────────────────

    public static function find(int $id): ?array
    {
        foreach (self::$reasons as $reason) {
            if ($reason['id'] === $id) {
                return $reason;
            }
        }

        return null;
    }
}
