<?php
// app/Repositories/Review/Interfaces/ReviewGuestRepositoryInterface.php

namespace App\Repositories\Review\Interfaces;

interface ReviewGuestRepositoryInterface
{
    /**
     * الضيف يرسل تقييم لحجز انتهت رحلته (status = completed).
     */
    public function submit(array $data, int $userId): array;
}
