<?php

namespace App\Services\User\Admin;

use App\Repositories\User\Interfaces\UserAdminRepositoryInterface;
use App\Services\Notification\NotificationService;

class UserAdminService
{
    public function __construct(
        protected UserAdminRepositoryInterface $_userAdminRepository
    ) {}

    // ─── Add User ──────────────────────────────────────────────────────────────

    public function addUser(array $data): array
    {
        $result = $this->_userAdminRepository->addUser($data);

        return [
            'data'    => $result['user'],
            'message' => 'User created successfully.',
            'code'    => 201,
        ];
    }

    // ─── Promote Guest → Host ──────────────────────────────────────────────────

    public function promoteGuestToHost(int $userId): array
    {
        $result = $this->_userAdminRepository->promoteGuestToHost($userId);

        return match ($result['status']) {
            'not_guest' => [
                'data'    => [],
                'message' => 'User is not a guest.',
                'code'    => 422,
            ],

            'already_host' => [
                'data'    => [],
                'message' => 'User is already a host.',
                'code'    => 422,
            ],

            default => (function () use ($result) {
                // ✅ الإشعار
                app(NotificationService::class)->send(
                    userId         : $result['user']->id,
                    type           : 'role_upgraded',
                    title          : 'Account Upgraded!',
                    body           : 'Your account has been upgraded to Host. You can now list vehicles on the platform.',
                );

                return [
                    'data'    => $result['user'],
                    'message' => 'Guest promoted to host successfully.',
                    'code'    => 200,
                ];
            })(),
        };
    }

    // ─── Delete Guest ─────────────────────────────────────────────────────────

    public function deleteGuest(int $userId): array
    {
        $result = $this->_userAdminRepository->deleteGuest($userId);

        return match ($result['status']) {
            'not_guest'            => ['data' => [],  'message' => 'User is not a guest.',                         'code' => 422],
            'has_active_bookings'  => ['data' => ['bookings_count' => $result['bookings_count']], 'message' => $result['warning'], 'code' => 409],
            'has_booking_history'  => ['data' => [],  'message' => $result['warning'],                              'code' => 409], // ← جديد
            default                => ['data' => [],  'message' => 'Guest deleted successfully.',                   'code' => 200],
        };
    }

    // ─── Delete Host ──────────────────────────────────────────────────────────

    public function deleteHost(int $userId): array
    {
        $result = $this->_userAdminRepository->deleteHost($userId);

        return match ($result['status']) {
            'not_host'            => ['data' => [], 'message' => 'User is not a host.',                        'code' => 422],
            'has_vehicles'        => ['data' => ['vehicle_count' => $result['vehicle_count']], 'message' => $result['warning'], 'code' => 409],
            'has_booking_history' => ['data' => [], 'message' => $result['warning'],                            'code' => 409], // ← جديد
            default               => ['data' => [], 'message' => 'Host deleted successfully.',                 'code' => 200],
        };
    }

    // ─── Toggle Guest Status ──────────────────────────────────────────────────

    public function toggleGuestStatus(int $userId): array
    {
        $result = $this->_userAdminRepository->toggleGuestStatus($userId);

        if ($result['status'] === 'not_guest') {
            return ['data' => [], 'message' => 'User is not a guest.', 'code' => 422];
        }

        $action  = $result['new_status'] === 'banned' ? 'banned' : 'activated';
        $message = "Guest account {$action} successfully.";

        return [
            'data'    => $result['user'],
            'message' => $message,
            'code'    => 200,
        ];
    }

    // ─── Get All Users ────────────────────────────────────────────────────────

    public function getUsers(int $perPage = 15): array
    {
        $paginated = $this->_userAdminRepository->getUsers($perPage);

        return [
            'data'    => $paginated,
            'message' => 'Users retrieved successfully.',
            'code'    => 200,
        ];
    }
}
