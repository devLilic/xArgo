<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Licensing\ActivateLicenseAction;
use App\Actions\Licensing\RecordHeartbeatAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ActivateLicenseRequest;
use App\Http\Requests\Api\V1\LicenseHeartbeatRequest;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

class LicenseActivationController extends Controller
{
    public function store(
        ActivateLicenseRequest $request,
        ActivateLicenseAction $activateLicense,
    ): JsonResponse {
        $result = $activateLicense->execute($request->validated());

        return ApiResponse::success([
            'activationId' => $result->activationId,
            'activationToken' => $result->activationToken,
            'licenseStatus' => $result->licenseStatus,
            'graceUntil' => $result->graceUntil,
            'entitlements' => $result->entitlements,
            'reasonCode' => $result->reasonCode,
        ]);
    }

    public function heartbeat(
        LicenseHeartbeatRequest $request,
        RecordHeartbeatAction $recordHeartbeat,
    ): JsonResponse {
        $result = $recordHeartbeat->execute([
            ...$request->validated(),
            'ipAddress' => $request->ip(),
        ]);

        return ApiResponse::success([
            'accepted' => $result->accepted,
            'activationId' => $result->activationId,
            'licenseStatus' => $result->licenseStatus,
            'activationStatus' => $result->activationStatus,
            'graceUntil' => $result->graceUntil,
            'reasonCode' => $result->reasonCode,
        ]);
    }
}
