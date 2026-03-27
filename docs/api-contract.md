# Licensing API V1 Contract

Base path:

```text
/api/v1/licenses
```

All endpoint responses use the same envelope:

```json
{
  "status": "success",
  "data": {},
  "error": null
}
```

Error envelope:

```json
{
  "status": "error",
  "data": null,
  "error": {
    "code": "validation_error",
    "reasonCode": "validation_failed",
    "message": "The license key field is required.",
    "details": {
      "licenseKey": [
        "The license key field is required."
      ]
    }
  }
}
```

## Status values

License status values:

- `active`
- `inactive`
- `suspended`
- `revoked`
- `expired`

Activation status values:

- `active`
- `blocked`
- `inactive`
- `stale`

Envelope status values:

- `success`
- `error`

## Reason codes

Common reason codes currently returned by the licensing API:

- `device_mismatch`
- `license_inactive`
- `license_suspended`
- `license_revoked`
- `license_expired`
- `rebind_pending_manual_confirmation`
- `validation_failed`
- `rate_limited`
- `internal_error`

## Entitlements shape

When included, entitlements use this array shape:

```json
[
  {
    "featureCode": "pro_export",
    "enabled": true
  }
]
```

## POST `/activate`

Create a first activation, reissue a token for the same bound device, or return mismatch context when another device attempts activation.

Request body:

```json
{
  "licenseKey": "XARGO-ACT-0001",
  "appId": "xargo.desktop",
  "appVersion": "2.0.0",
  "machineId": "machine-001",
  "installationId": "installation-001"
}
```

Success response:

```json
{
  "status": "success",
  "data": {
    "activationId": "4d3d7f3d-5a4f-4fc6-8b2f-3f2fe9d69f0e",
    "activationToken": "plain-text-token",
    "licenseStatus": "active",
    "graceUntil": null,
    "entitlements": [
      {
        "featureCode": "pro_export",
        "enabled": true
      }
    ],
    "reasonCode": null
  },
  "error": null
}
```

Mismatch response example:

```json
{
  "status": "success",
  "data": {
    "activationId": "existing-activation-id",
    "activationToken": null,
    "licenseStatus": "active",
    "graceUntil": "2026-03-27T18:05:00+00:00",
    "entitlements": [],
    "reasonCode": "device_mismatch"
  },
  "error": null
}
```

## POST `/validate`

Validate the current device against an existing activation token.

Request body:

```json
{
  "licenseKey": "XARGO-VAL-0001",
  "activationToken": "plain-text-token",
  "appId": "xargo.desktop",
  "appVersion": "2.1.0",
  "machineId": "machine-001",
  "installationId": "installation-001"
}
```

Success response:

```json
{
  "status": "success",
  "data": {
    "isValid": true,
    "activationId": "existing-activation-id",
    "licenseStatus": "active",
    "graceUntil": null,
    "entitlements": [
      {
        "featureCode": "cloud_sync",
        "enabled": true
      }
    ],
    "reasonCode": null
  },
  "error": null
}
```

Blocked mismatch example:

```json
{
  "status": "success",
  "data": {
    "isValid": false,
    "activationId": "existing-activation-id",
    "licenseStatus": "active",
    "graceUntil": "2026-03-27T17:59:00+00:00",
    "entitlements": [],
    "reasonCode": "device_mismatch"
  },
  "error": null
}
```

## POST `/heartbeat`

Record a heartbeat for an activation and return current acceptance state.

Request body:

```json
{
  "activationId": "existing-activation-id",
  "activationToken": "plain-text-token",
  "appId": "xargo.desktop",
  "appVersion": "2.2.0",
  "machineId": "machine-001",
  "installationId": "installation-001"
}
```

Success response:

```json
{
  "status": "success",
  "data": {
    "accepted": true,
    "activationId": "existing-activation-id",
    "licenseStatus": "active",
    "activationStatus": "active",
    "graceUntil": null,
    "reasonCode": null
  },
  "error": null
}
```

Mismatch example:

```json
{
  "status": "success",
  "data": {
    "accepted": true,
    "activationId": "existing-activation-id",
    "licenseStatus": "active",
    "activationStatus": "active",
    "graceUntil": "2026-03-27T20:30:00+00:00",
    "reasonCode": "device_mismatch"
  },
  "error": null
}
```

## POST `/rebind/request`

Explicitly request manual rebind review for a mismatched device. This does not reassign the activation automatically.

Request body:

```json
{
  "licenseKey": "XARGO-REBIND-001",
  "activationToken": "plain-text-token",
  "appId": "xargo.desktop",
  "appVersion": "2.3.0",
  "machineId": "machine-replacement",
  "installationId": "installation-replacement"
}
```

Success response:

```json
{
  "status": "success",
  "data": {
    "requested": true,
    "requiresManualReview": true,
    "activationId": "existing-activation-id",
    "licenseStatus": "active",
    "graceUntil": "2026-03-27T22:05:00+00:00",
    "reasonCode": "device_mismatch"
  },
  "error": null
}
```

## POST `/rebind/confirm`

Confirm that a manual rebind has already changed the bound device.

Request body:

```json
{
  "licenseKey": "XARGO-REBIND-003",
  "activationToken": "plain-text-token",
  "appId": "xargo.desktop",
  "appVersion": "2.3.0",
  "machineId": "machine-replacement",
  "installationId": "installation-replacement"
}
```

Success response:

```json
{
  "status": "success",
  "data": {
    "confirmed": true,
    "activationId": "existing-activation-id",
    "licenseStatus": "active",
    "graceUntil": null,
    "reasonCode": null
  },
  "error": null
}
```

Pending-manual-review example:

```json
{
  "status": "success",
  "data": {
    "confirmed": false,
    "activationId": "existing-activation-id",
    "licenseStatus": "active",
    "graceUntil": "2026-03-27T22:05:00+00:00",
    "reasonCode": "rebind_pending_manual_confirmation"
  },
  "error": null
}
```
