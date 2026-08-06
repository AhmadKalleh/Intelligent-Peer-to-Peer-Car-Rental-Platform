<?php
// app/Repositories/Handover/Interfaces/HandoverGuestRepositoryInterface.php

namespace App\Repositories\Handover\Interfaces;

interface HandoverGuestRepositoryInterface
{
    /**
     * يقوم المستأجر بتأكيد الكود الذي سكنه من عند المالك (QR).
     */
    public function confirm(int $bookingId, string $code, int $userId): array;
}
