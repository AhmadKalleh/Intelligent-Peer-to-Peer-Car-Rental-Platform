<?php
// routes/channels.php

use App\Models\Conversation;
use App\Models\Host;
use App\Models\User;
use App\Models\Booking;

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

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// Conversation Private Channel  ← جديد
//
// private-conversation.{conversationId}
//
// يُصرَّح للمستخدم فقط إذا كان طرفاً في المحادثة
// (guest_user_id أو host_user_id)
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Broadcast::channel('conversation.{conversationId}', function (User $user, int $conversationId) {
    $conversation = Conversation::find($conversationId);

    if (! $conversation) {
        return false;
    }

    return (int) $user->id === (int) $conversation->guest_user_id
        || (int) $user->id === (int) $conversation->host_user_id;
});

Broadcast::channel('ai-chat.{userId}', function (User $user, int $userId) {
    return $user->hasRole('guest') && (int) $user->id === (int) $userId;
});
// routes/channels.php
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// Booking Tracking Private Channel  ← جديد
//
// private-booking-tracking.{bookingId}
//
// يُصرَّح للمستخدم فقط إذا كان طرفاً بهاد الحجز تحديدًا
// (المالك عبر host.user_id أو المستأجر عبر user_id)
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Broadcast::channel('booking-tracking.{bookingId}', function (User $user, int $bookingId) {
    $booking = Booking::with('host')->find($bookingId);

    if (! $booking) {
        return false;
    }

    return (int) $user->id === (int) $booking->user_id
        || (int) $user->id === (int) ($booking->host->user_id ?? 0);
});
