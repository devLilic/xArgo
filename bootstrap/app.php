<?php

use App\Domain\Licensing\LicenseReasonCode;
use App\Support\Api\ApiResponse;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('licensing:prune-heartbeats')
            ->dailyAt('02:00');

        $schedule->command('licensing:send-notifications')
            ->dailyAt('03:00');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! $request->is('api/v1/*')) {
                return null;
            }

            $messages = $exception->errors();
            $firstMessage = collect($messages)
                ->flatten()
                ->first() ?? 'The given data was invalid.';

            return ApiResponse::error(
                code: 'validation_error',
                reasonCode: LicenseReasonCode::VALIDATION_FAILED,
                message: $firstMessage,
                details: $messages,
                statusCode: $exception->status,
            );
        });

        $exceptions->render(function (\Throwable $exception, Request $request) {
            if (! $request->is('api/v1/*')) {
                return null;
            }

            if ($exception instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
                return ApiResponse::error(
                    code: 'rate_limited',
                    reasonCode: 'rate_limited',
                    message: 'Too many licensing API requests. Please retry later.',
                    details: [
                        'retryAfter' => $exception->getHeaders()['Retry-After'] ?? null,
                    ],
                    statusCode: 429,
                );
            }

            return ApiResponse::error(
                code: 'internal_error',
                reasonCode: LicenseReasonCode::INTERNAL_ERROR,
                message: app()->hasDebugModeEnabled()
                    ? $exception->getMessage()
                    : 'An unexpected API error occurred.',
                details: null,
                statusCode: 500,
            );
        });
    })->create();
