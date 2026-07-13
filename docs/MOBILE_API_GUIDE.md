LeaseSmart Mobile API Guide

# Overview

This document describes the currently available mobile-facing API endpoints for LeaseSmart Premium.

Base URL pattern:

- local example: `http://localhost/api/v1`
- production example: `https://your-domain.com/api/v1`

Authentication strategy:

- Laravel Sanctum personal access tokens
- send tokens with `Authorization: Bearer {token}`

Content type:

- requests: `Content-Type: application/json`
- responses: JSON

# Current Endpoint Surface

Available endpoints:

- `POST /tokens`
- `DELETE /tokens/current`
- `GET /account`
- `GET /tenancies`
- `GET /invoices`
- `GET /payments`
- `GET /maintenance-requests`

Current audience focus:

- tenant mobile consumers
- authenticated account experiences
- tenancy, billing, payment, and maintenance visibility

The visibility rules are role-aware, but the current mobile-oriented implementation is especially suited to tenant-facing apps.

# Authentication Flow

## 1. Create Token

Endpoint:

- `POST /api/v1/tokens`

Request body:

```json
{
  "email": "tenant@example.com",
  "password": "password",
  "device_name": "iPhone 17 Pro"
}
```

Success response: `201 Created`

```json
{
  "token_type": "Bearer",
  "plain_text_token": "1|example-token-value",
  "user": {
    "id": 7,
    "name": "Tapi Tenant",
    "email": "tenant@example.com",
    "phone": "+2348000000000",
    "bio": null,
    "avatar_url": null,
    "role": "tenant",
    "role_label": "Tenant",
    "is_active": true,
    "last_login_at": null,
    "email_verified_at": "2026-03-24T08:00:00+00:00",
    "created_at": "2026-03-24T08:00:00+00:00"
  }
}
```

Validation rules:

- `email` is required and must be a valid email address
- `password` is required
- `device_name` is required and max length is 255 characters

Failure responses:

- `422 Unprocessable Entity` for invalid credentials or validation failure
- `403 Forbidden` if the user exists but the account is inactive
- `429 Too Many Requests` if auth throttling is exceeded

Example invalid-credentials response:

```json
{
  "message": "The provided credentials are incorrect.",
  "errors": {
    "email": [
      "The provided credentials are incorrect."
    ]
  }
}
```

Example inactive-account response:

```json
{
  "message": "Your account is currently inactive. Please contact support."
}
```

## 2. Use Token

Send the token with every authenticated request:

```http
Authorization: Bearer 1|example-token-value
Accept: application/json
```

## 3. Revoke Current Token

Endpoint:

- `DELETE /api/v1/tokens/current`

Success response: `200 OK`

```json
{
  "message": "Token revoked successfully."
}
```

# Common Response Patterns

## Resource Endpoint Pattern

Authenticated data endpoints return Laravel resource collections.

Typical structure:

```json
{
  "data": [
    {
      "id": 1
    }
  ],
  "links": {
    "first": "https://your-domain.com/api/v1/tenancies?page=1",
    "last": "https://your-domain.com/api/v1/tenancies?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "https://your-domain.com/api/v1/tenancies",
    "per_page": 15,
    "to": 1,
    "total": 1
  }
}
```

## Pagination

Supported query parameter:

- `per_page`

Example:

- `GET /api/v1/invoices?per_page=25`

If `per_page` is omitted, the current default is `15`.

# Endpoints

## Account

Endpoint:

- `GET /api/v1/account`

Description:

- returns the authenticated user profile for the current token

Success response: `200 OK`

```json
{
  "data": {
    "id": 7,
    "name": "Tapi Tenant",
    "email": "tenant@example.com",
    "phone": "+2348000000000",
    "bio": null,
    "avatar_url": "https://your-domain.com/storage/avatars/example.jpg",
    "role": "tenant",
    "role_label": "Tenant",
    "is_active": true,
    "last_login_at": "2026-03-24T08:30:00+00:00",
    "email_verified_at": "2026-03-24T08:00:00+00:00",
    "created_at": "2026-03-24T08:00:00+00:00"
  }
}
```

Field notes:

- `avatar_url` may be `null`
- `role` is the machine-friendly role key
- `role_label` is the display label

## Tenancies

Endpoint:

- `GET /api/v1/tenancies`

Description:

- returns tenancies visible to the authenticated user
- for tenant users, only that resident's tenancy records are returned

Example item:

```json
{
  "id": 12,
  "status": "active",
  "status_label": "Active",
  "tenant_name": "Scoped Tenant",
  "tenant_email": "scoped-tenant@example.com",
  "tenant_phone": "+2348000000200",
  "lease_start_date": "2026-02-24",
  "lease_end_date": "2027-02-24",
  "move_in_date": "2026-03-10",
  "rent_amount": 220000,
  "service_charge_amount": 30000,
  "billing_cycle": "yearly",
  "property": {
    "id": 3,
    "title": "Azure Gardens",
    "slug": "azure-gardens",
    "city": "Lagos",
    "state": "Lagos"
  },
  "unit": {
    "id": 8,
    "unit_name": "Suite C3",
    "unit_code": "UNIT-C3",
    "occupancy_status": "occupied"
  },
  "created_at": "2026-03-24T08:35:00+00:00",
  "updated_at": "2026-03-24T08:35:00+00:00"
}
```

## Invoices

Endpoint:

- `GET /api/v1/invoices`

Description:

- returns invoices visible to the authenticated user
- for tenant users, only that tenant's invoices are returned

Example item:

```json
{
  "id": 22,
  "invoice_number": "INV-API-1001",
  "invoice_type": "rent",
  "invoice_type_label": "Rent",
  "status": "issued",
  "status_label": "Issued",
  "issue_date": "2026-03-20",
  "due_date": "2026-04-03",
  "subtotal_amount": 220000,
  "discount_amount": 0,
  "total_amount": 220000,
  "balance_amount": 220000,
  "notes": null,
  "tenancy": {
    "id": 12,
    "status": "active",
    "tenant_name": "Scoped Tenant"
  },
  "property": {
    "id": 3,
    "title": "Azure Gardens",
    "slug": "azure-gardens"
  },
  "items": [
    {
      "id": 1,
      "description": "Annual rent",
      "quantity": 1,
      "unit_amount": 220000,
      "total_amount": 220000
    }
  ],
  "created_at": "2026-03-24T08:40:00+00:00",
  "updated_at": "2026-03-24T08:40:00+00:00"
}
```

Field notes:

- `balance_amount` is the current unpaid amount remaining on the invoice
- `items` is included in the current API implementation

## Payments

Endpoint:

- `GET /api/v1/payments`

Description:

- returns payments visible to the authenticated user
- for tenant users, only that tenant's payments are returned

Example item:

```json
{
  "id": 31,
  "payment_reference": "PAY-API-1001",
  "payment_method": "bank_transfer",
  "payment_method_label": "Bank Transfer",
  "status": "pending_verification",
  "status_label": "Pending Verification",
  "amount": 220000,
  "paid_at": "2026-03-23T08:45:00+00:00",
  "verified_at": null,
  "invoice": {
    "id": 22,
    "invoice_number": "INV-API-1001",
    "status": "issued",
    "balance_amount": 220000
  },
  "tenancy": {
    "id": 12,
    "status": "active",
    "tenant_name": "Scoped Tenant"
  },
  "receipt": null,
  "created_at": "2026-03-23T08:45:00+00:00",
  "updated_at": "2026-03-23T08:45:00+00:00"
}
```

Field notes:

- `receipt` is `null` until a receipt exists
- `verified_at` is `null` until the payment has been reviewed and verified

## Maintenance Requests

Endpoint:

- `GET /api/v1/maintenance-requests`

Description:

- returns maintenance requests visible to the authenticated user
- for tenant users, only that tenant's maintenance requests are returned

Example item:

```json
{
  "id": 15,
  "title": "Air conditioning service",
  "description": "Cooling is weak in the living room.",
  "category": "electrical",
  "priority": "medium",
  "priority_label": "Medium",
  "status": "assigned",
  "status_label": "Assigned",
  "reported_at": "2026-03-24T00:35:00+00:00",
  "resolved_at": null,
  "closed_at": null,
  "property": {
    "id": 3,
    "title": "Azure Gardens",
    "slug": "azure-gardens"
  },
  "unit": {
    "id": 8,
    "unit_name": "Suite C3",
    "unit_code": "UNIT-C3"
  },
  "assignee": {
    "id": 9,
    "name": "Api Caretaker"
  },
  "updates": [
    {
      "id": 2,
      "status": "assigned",
      "status_label": "Assigned",
      "message": "Caretaker assigned and follow-up started.",
      "created_at": "2026-03-24T01:00:00+00:00",
      "user": {
        "id": 9,
        "name": "Api Caretaker"
      }
    }
  ],
  "created_at": "2026-03-24T00:35:00+00:00",
  "updated_at": "2026-03-24T01:00:00+00:00"
}
```

Field notes:

- `unit` may be `null`
- `assignee` may be `null`
- `updates` may be an empty array

# Rate Limits

Current rate limits:

- `POST /tokens`: `5` requests per minute per email and IP combination
- authenticated API routes: `60` requests per minute per authenticated user, falling back to IP if needed

Mobile client recommendations:

- treat `429` as retryable
- respect `Retry-After` if present
- throttle repeated login retries in the client UI

# Error Handling

## 401 Unauthorized

Returned when:

- bearer token is missing
- bearer token is invalid
- bearer token was revoked

Example:

```json
{
  "message": "Unauthenticated."
}
```

## 403 Forbidden

Returned when:

- account is inactive

Example:

```json
{
  "message": "Your account is currently inactive. Please contact support."
}
```

## 422 Unprocessable Entity

Returned when:

- validation fails
- login credentials are wrong

Example:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The provided credentials are incorrect."
    ]
  }
}
```

## 429 Too Many Requests

Returned when:

- route rate limits are exceeded

Mobile recommendation:

- show a retry message
- avoid silent endless retries

# Suggested Mobile Integration Flow

1. Call `POST /tokens` after login.
2. Store the bearer token securely in the mobile platform's secure storage.
3. Call `GET /account` to hydrate the signed-in session.
4. Load tenancy, invoices, payments, and maintenance requests in parallel.
5. Use pagination metadata for long lists.
6. Call `DELETE /tokens/current` on logout and clear the local token.

# cURL Examples

## Login

```bash
curl -X POST "https://your-domain.com/api/v1/tokens" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "tenant@example.com",
    "password": "password",
    "device_name": "Android Pixel 12"
  }'
```

## Get Account

```bash
curl "https://your-domain.com/api/v1/account" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Get Invoices

```bash
curl "https://your-domain.com/api/v1/invoices?per_page=20" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Logout

```bash
curl -X DELETE "https://your-domain.com/api/v1/tokens/current" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

# Current Limitations

Current API surface is read-focused after authentication, except for token creation and revocation.

Not yet available in this version:

- write endpoints for inspections, applications, payments, or maintenance creation from mobile
- refresh-token flow separate from Sanctum personal access tokens
- versioned OpenAPI schema export
- webhook-based mobile sync events

# Source of Truth

The current implementation is defined by:

- `routes/api.php`
- `app/Http/Controllers/Api/V1/*`
- `app/Http/Resources/Api/V1/*`
- `tests/Feature/PhaseTen/ApiLayerTest.php`
