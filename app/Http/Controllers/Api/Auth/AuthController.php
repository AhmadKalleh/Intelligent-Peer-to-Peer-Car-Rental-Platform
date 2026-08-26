<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AuthRequests\FormRequestAuth;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;

use App\Traits\ResponseHelper\ResponseHelper;
use Throwable;

class AuthController extends Controller   // i add extends controller to use authorize method in register function
{
    use ResponseHelper;

    public function __construct(
        protected AuthService $_authService
    ) {}

    public function register(FormRequestAuth $request): JsonResponse
    {
        $data=[];

        try
        {

            $data = $this->_authService->register($request->validated());
            return $this->Success($data['data'],$data['message'],$data['code']);
        }
        catch(Throwable $e)
        {
            $message = $e->getMessage();
            return $this->Error($data,$message);
        }
    }

    public function verify_code(FormRequestAuth $request): JsonResponse
    {
        $data=[];

        try
        {
            $data = $this->_authService->verify_code($request->validated());
            return $this->Success($data['data'],$data['message'],$data['code']);
        }
        catch(Throwable $e)
        {
            $message = $e->getMessage();
            return $this->Error($data,$message);
        }
    }

    public function googleLogin(FormRequestAuth $request): JsonResponse
    {
        $data = [];

        try {
            $data = $this->_authService->googleLogin($request->validated()['id_token']);
            return $this->Success($data['data'], $data['message'], $data['code']);

        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage());
        }
    }

    public function resend_code(FormRequestAuth $request): JsonResponse
    {
        $data = [];

        try {
            $raw = $this->_authService->resend_code($request->validated());
            return $this->Success($raw['data'], $raw['message'], $raw['code']);

        } catch (Throwable $e) {
            return $this->Error($data, $e->getMessage());
        }
    }

    public function login(FormRequestAuth $request): JsonResponse
    {
        try {
            $data = $this->_authService->login($request->validated());
            return $this->Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $e) {
            return $this->Error([], $e->getMessage());
        }
    }

    public function logout(FormRequestAuth $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->_authService->logout($request->user());
            return $this->Success(
                $data['data'],
                $data['message'],
                $data['code']
            );
        } catch (Throwable $e) {

            return $this->Error(
                [],
                $e->getMessage()
            );
        }
    }
}
