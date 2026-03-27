<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Licensing\ConfirmLicenseRebindAction;
use App\Actions\Licensing\RequestLicenseRebindAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RebindLicenseRequest;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

class LicenseRebindController extends Controller
{
    public function request(
        RebindLicenseRequest $request,
        RequestLicenseRebindAction $requestLicenseRebind,
    ): JsonResponse {
        $result = $requestLicenseRebind->execute($request->validated());

        return ApiResponse::success([
            'requested' => $result->requested,
            'requiresManualReview' => $result->requiresManualReview,
            'activationId' => $result->activationId,
            'licenseStatus' => $result->licenseStatus,
            'graceUntil' => $result->graceUntil,
            'reasonCode' => $result->reasonCode,
        ]);
    }

    public function confirm(
        RebindLicenseRequest $request,
        ConfirmLicenseRebindAction $confirmLicenseRebind,
    ): JsonResponse {
        $result = $confirmLicenseRebind->execute($request->validated());

        return ApiResponse::success([
            'confirmed' => $result->confirmed,
            'activationId' => $result->activationId,
            'licenseStatus' => $result->licenseStatus,
            'graceUntil' => $result->graceUntil,
            'reasonCode' => $result->reasonCode,
        ]);
    }
}
