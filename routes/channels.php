<?php
// routes/channels.php

use App\Models\Host;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// Admin Private Channel
// فقط المستخدمين الذين لديهم role admin
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Broadcast::channel('admin.notifications', function (User $user) {
    return $user->hasRole('admin');
});

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// Host Private Channel
// فقط الهوست نفسه يستمع لإشعاراته
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Broadcast::channel('host.{hostUserId}', function (User $user, int $hostUserId) {
    return (int) $user->id === (int) $hostUserId;
});

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// Vehicles Listing Presence Channel
// عام لكل المستخدمين المسجلين لتحديث الواجهة لحظياً
// عند إضافة سيارة جديدة أو تغيير حالتها
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Broadcast::channel('vehicles.listing', function (User $user) {
    // أي مستخدم مسجل يستطيع الاستماع
    return [
        'id'   => $user->id,
        'name' => $user->full_name,
    ];
});
