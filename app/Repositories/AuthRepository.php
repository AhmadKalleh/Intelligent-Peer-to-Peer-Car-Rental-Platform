<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\AuthRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Google\Client as GoogleClient;

class AuthRepository implements AuthRepositoryInterface
{

    private function generateUniqueVerificationCode()
    {
        do {
            $code = random_int(100000, 999999);
        } while (User::where('verification_code', $code)->exists());


        return $code;
    }

    public function register(array $data): array
    {
        $verificationCode = $this->generateUniqueVerificationCode();

        $user = User::create([
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'verification_code' => $verificationCode,
            'auth_provider' => 'local',
            'verification_code_expires_at' => now()->addMinutes(5),
        ]);

        return [
            'user'  => $user,
        ];
    }

    public function verify_code(array $data): array
    {
        return DB::transaction(function () use ($data)
        {
            $user = User::where('email', $data['email'])
                ->lockForUpdate()
                ->first();

            if (now()->gt($user->verification_code_expires_at)) {
                return ['status' => 'expired'];
            }


            if ($user->verification_attempts >= 3) {
                return ['status' => 'max_attempts'];
            }

            if ($user->verification_code !== $data['code']) {
                $user->increment('verification_attempts');
                $remaining = 3 - ($user->verification_attempts + 1);
                // أو
                $user->refresh();
                $remaining = 3 - $user->verification_attempts;

                return [
                    'status'    => 'wrong_code',
                    'remaining' => $remaining,
                ];
            }


            $user->update([
                'verification_code'       => null,
                'verification_code_expires_at' => null,
                'verification_attempts'   => 0,
                'email_verified_at' => now()
            ]);

            $guestRole = Role::query()->where('name', '=', 'guest')->first();
            $user->assignRole($guestRole);

            $permissions = $guestRole->permissions()->pluck('name')->toArray();
            $user->givePermissionTo($permissions);

            $token = $user->createToken("api_token")->plainTextToken;


            return ['status' => 'verified', 'user' => $user->fresh(),'token' => $token];
        });

    }

    public function resend_code(array $data): array
    {
        $user = User::where('email', $data['email'])->first();

        $verificationCode = $this->generateUniqueVerificationCode();

        $user->update([
            'verification_code'           => $verificationCode,
            'verification_code_expire_at' => now()->addMinutes(5),
            'verification_attempts'       => 0,
        ]);

        return ['user' => $user];
    }

    public function login(array $data): array
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return [
                'status' => 'invalid_credentials',
            ];
        }

        if (!$user->email_verified_at) {
            return [
                'status' => 'unverified_email',
            ];
        }

        $token = $user->createToken("api_token")->plainTextToken;

        return [
            'status' => 'success',
            'user' => $user,
            'token' => $token,
        ];
    }

    public function login_with_google(string $idToken): array
    {
        $googleClient = new GoogleClient();
        $googleClient->setClientId(config('services.google.client_id'));

        $payload = $googleClient->verifyIdToken($idToken);

        if (!$payload) {
            return [
                'status' => 'invalid_token',
            ];
        }

        $userData = [
            'full_name' => $payload['name'] ?? 'Unknown',
            'email' => $payload['email'] ?? null,
            'google_id' => $payload['sub'],
        ];


        $user = $this->updateOrCreateByGoogle($userData);

        $guestRole = Role::query()->where('name', '=', 'guest')->first();
        $user->assignRole($guestRole);

        $permissions = $guestRole->permissions()->pluck('name')->toArray();
        $user->givePermissionTo($permissions);

        $token = $user->createToken("api_token")->plainTextToken;

        return[
            'status' => 'success',
            'user' => $user,
            'token' => $token,
        ];
    }

    public function updateOrCreateByGoogle(array $userData): User
    {
        // لو الـ user موجود بـ google_id حدّثه، لو لا أنشئه
        return User::updateOrCreate(
            [
                'google_id' => $userData['google_id'],
            ],
            [
                'full_name'     => $userData['full_name'],
                'email'         => $userData['email'],
                'auth_provider' => 'google',
                'status'        => 'active',
                'email_verified_at' => now(),
                'password'      => null,
            ]
        );
    }
    public function logout($user): array
    {

        if(!is_null($user))
        {
            $token = $user->currentAccessToken();
            /** @var PersonalAccessToken|null $token */
            $token?->delete();

            return [
                'status' => 'success',
            ];
        }
        else
        {
            return [
                'status' => 'not_authenticated',
            ];
        }
    }








}
