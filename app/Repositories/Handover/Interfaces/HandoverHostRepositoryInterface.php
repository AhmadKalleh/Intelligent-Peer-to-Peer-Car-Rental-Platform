<?php
// app/Repositories/Handover/Interfaces/HandoverHostRepositoryInterface.php

namespace App\Repositories\Handover\Interfaces;

interface HandoverHostRepositoryInterface
{
    /**
     * يولّد المالك كود/QR جديد لتسليم أو استلام السيارة حسب حالة الحجز الحالية.
     */
    public function generate(int $bookingId, int $hostId): array;
}
