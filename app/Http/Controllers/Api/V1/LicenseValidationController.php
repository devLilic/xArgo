<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Licensing\ValidateLicenseAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ValidateLicenseRequest;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

class LicenseValidationController extends Controller
{
    public function store(
        ValidateLicenseRequest $request,
        ValidateLicenseAction $validateLicense,
    ): JsonResponse {
        $result = $validateLicense->execute($request->validated());

        return ApiResponse::success([
            'isValid' => $result->isValid,
            'activationId' => $result->activationId,
            'licenseStatus' => $result->licenseStatus,
            'graceUntil' => $result->graceUntil,
            'entitlements' => $result->entitlements,
            'reasonCode' => $result->reasonCode,
        ]);
    }
}
