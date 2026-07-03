<?php

namespace App\Exceptions;

use App\Traits\ResponseHelper\ResponseHelper;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use Illuminate\Support\Facades\Auth;


class Handler extends ExceptionHandler
{

    use ResponseHelper;
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        $lang = Auth::check() ? Auth::user()->preferred_language : 'en';

        if($exception instanceof AuthenticationException) {
            return $this->Error(
                [],
                'You are not authenticated.',
                401
            );
        }

        // Handle AuthorizationException
        if ($exception instanceof AuthorizationException) {
            return $this->Error(
                [],
                'You are not authorized to perform this action.',
                403
            );
        }

        // Handle NotFoundHttpException
        if ($exception instanceof NotFoundHttpException) {
            return $this->Error(
                [],
                'The requested resource was not found.',
                404
            );
        }



        // Default handling for other exceptions
        return $this->Error([], $exception->getMessage(), 500);
    }
}
