LeaseSmart Premium - Database ERD Spec and Phase 0 Reference

# Purpose

This document is the canonical implementation-oriented reference for:

- the Phase 0 database direction
- LeaseSmart's initial domain boundaries
- stateful workflow modeling
- the recommended Phase 0 Codex execution prompt

# Design Principles

- Separate property-level records from unit-level records
- Separate application lifecycle from tenancy lifecycle
- Separate billing from payment capture
- Support multiple roles with flexible permissions
- Keep the backend mobile-ready
- Preserve auditability and operational history
- Build for a modular monolith, not a toy CRUD app

# Core Domain Areas

- Users and Access
- People Profiles
- Property Inventory
- Public Listings
- Inspections
- Rental Applications
- Tenancy Lifecycle
- Billing and Payments
- Maintenance
- Notifications and Audit
- System Settings

# Users and Access

## users

Primary authentication table.

Suggested columns:

- id
- name
- email
- email_verified_at
- password
- phone nullable
- avatar_path nullable
- is_active boolean default true
- last_login_at nullable
- remember_token nullable
- created_at
- updated_at

Indexes:

- unique(email)
- index(is_active)
- index(last_login_at)

Notes:

- do not store a single rigid role enum here
- use Spatie Permission tables for authorization

## roles / permissions / model_has_roles / model_has_permissions / role_has_permissions

Handled by Spatie Laravel Permission.

## activity_logs

Tracks critical domain actions.

Suggested columns:

- id
- user_id nullable
- loggable_type nullable
- loggable_id nullable
- action
- description nullable
- metadata json nullable
- ip_address nullable
- user_agent nullable
- created_at
- updated_at

Indexes:

- index(user_id)
- index(loggable_type, loggable_id)
- index(action)
- index(created_at)

# People Profiles

These tables keep role-specific information outside the `users` table.

## landlords

- id
- user_id unique
- company_name nullable
- address nullable
- notes nullable
- created_at
- updated_at

## caretakers

- id
- user_id unique
- employee_code nullable
- notes nullable
- created_at
- updated_at

## tenants

- id
- user_id unique nullable
- full_name
- email nullable
- phone nullable
- date_of_birth nullable
- gender nullable
- id_type nullable
- id_number nullable
- address nullable
- emergency_contact_name nullable
- emergency_contact_phone nullable
- employment_status nullable
- employer_name nullable
- monthly_income nullable
- notes nullable
- created_at
- updated_at

Notes:

- `user_id` may be nullable before a tenant becomes an authenticated user

# Property Inventory

## properties

Represents the building, compound, or parent rentable asset.

Suggested columns:

- id
- landlord_id nullable
- caretaker_id nullable
- title
- slug unique
- property_code unique nullable
- property_type
- description text nullable
- address_line_1
- address_line_2 nullable
- city
- state nullable
- country default 'Nigeria'
- postal_code nullable
- latitude decimal nullable
- longitude decimal nullable
- year_built nullable
- publish_status default 'draft'
- is_featured boolean default false
- published_at nullable
- created_by nullable
- updated_by nullable
- created_at
- updated_at

Recommended values:

- property_type: apartment_building, duplex, bungalow, detached_house, terrace, self_contain, commercial, mixed_use, land
- publish_status: draft, under_review, published, unpublished, archived

## property_units

Represents actual rentable units.

Suggested columns:

- id
- property_id
- unit_code unique nullable
- unit_name
- unit_type nullable
- floor_label nullable
- bedrooms nullable
- bathrooms nullable
- toilets nullable
- size_sqm nullable
- occupancy_status default 'vacant'
- rent_amount decimal
- billing_cycle default 'yearly'
- service_charge_amount decimal default 0
- caution_fee_amount decimal default 0
- inspection_fee_amount decimal default 0
- available_from nullable
- description text nullable
- is_listed boolean default true
- created_at
- updated_at

Recommended values:

- occupancy_status: vacant, reserved, occupied, unavailable, under_maintenance
- billing_cycle: monthly, quarterly, biannual, yearly

## property_amenities

Master amenity definitions.

## amenity_property

Property-level amenities pivot.

## amenity_property_unit

Unit-level amenities pivot.

## property_media

Optional logical media layer if Spatie Media Library metadata is not used directly.

# Inspections

## inspection_bookings

Suggested columns include:

- property_id
- property_unit_id nullable
- applicant_name
- applicant_email nullable
- applicant_phone
- preferred_date nullable
- preferred_time nullable
- scheduled_at nullable
- status default 'requested'
- notes nullable
- internal_notes nullable
- handled_by nullable

Recommended statuses:

- requested
- confirmed
- rescheduled
- completed
- cancelled
- no_show

# Rental Applications

## rental_applications

Suggested columns include:

- property_id
- property_unit_id
- tenant_id nullable
- submitted_by_user_id nullable
- application_reference unique
- full_name
- email nullable
- phone
- date_of_birth nullable
- marital_status nullable
- current_address nullable
- employment_status nullable
- employer_name nullable
- monthly_income nullable
- desired_move_in_date nullable
- status default 'draft'
- submitted_at nullable
- reviewed_at nullable
- reviewed_by nullable
- review_notes nullable
- rejection_reason nullable
- converted_to_tenancy_id nullable

Recommended statuses:

- draft
- submitted
- under_review
- approved
- rejected
- withdrawn
- converted

## application_documents

Suggested document types:

- id_card
- passport_photo
- proof_of_income
- bank_statement
- guarantor_letter
- other

# Tenancy Lifecycle

## tenancies

Suggested columns include:

- property_id
- property_unit_id
- tenant_id
- rental_application_id nullable
- tenancy_reference unique
- lease_start_date
- lease_end_date
- rent_amount decimal
- billing_cycle
- service_charge_amount decimal default 0
- caution_fee_amount decimal default 0
- status default 'pending_activation'
- activated_at nullable
- ended_at nullable
- end_reason nullable
- notes nullable
- created_by nullable
- updated_by nullable

Recommended statuses:

- pending_activation
- active
- renewal_pending
- renewed
- ending
- ended
- cancelled

Important constraint:

- only one active tenancy per unit at a time, enforced in domain logic and optionally at the database layer where supported

## tenancy_documents

Stores uploaded tenancy records and lease artifacts.

# Billing and Payments

## invoices

Suggested columns include:

- tenancy_id
- invoice_number unique
- invoice_type default 'rent'
- issue_date
- due_date
- subtotal_amount decimal
- discount_amount decimal default 0
- total_amount decimal
- balance_amount decimal
- status default 'draft'
- notes nullable
- issued_by nullable

Recommended statuses:

- draft
- issued
- partially_paid
- paid
- overdue
- cancelled

Suggested invoice types:

- rent
- service_charge
- caution_fee
- inspection_fee
- miscellaneous

## invoice_items

Line items for invoices.

## payments

Suggested columns include:

- tenancy_id nullable
- tenant_id nullable
- payment_reference unique
- payment_method nullable
- amount decimal
- paid_at nullable
- status default 'pending_verification'
- proof_path nullable
- external_transaction_id nullable
- notes nullable
- verified_by nullable
- verified_at nullable
- rejection_reason nullable

Recommended statuses:

- pending_verification
- verified
- rejected
- refunded

Suggested payment methods:

- bank_transfer
- cash
- card
- ussd
- wallet
- other

## payment_allocations

Supports partial payments and multi-invoice allocation.

## receipts

Optional formal receipt tracking.

# Maintenance

## maintenance_requests

Suggested columns include:

- property_id
- property_unit_id nullable
- tenancy_id nullable
- tenant_id nullable
- title
- description text
- category nullable
- priority default 'medium'
- status default 'open'
- reported_at nullable
- assigned_to nullable
- resolved_at nullable
- closed_at nullable
- created_by nullable
- updated_by nullable

Recommended priorities:

- low
- medium
- high
- urgent

Recommended statuses:

- open
- assigned
- in_progress
- awaiting_confirmation
- resolved
- closed
- cancelled

## maintenance_updates

Timeline updates and status transitions.

## maintenance_attachments

Files attached to requests or updates.

# Settings and Support

## settings

Lightweight key-value configuration storage.

## charge_templates

Optional predefined billing templates.

# Relationship Summary

- a user may have one landlord profile
- a user may have one caretaker profile
- a user may map to one tenant profile
- a landlord has many properties
- a caretaker may manage many properties
- a property has many units
- properties and units may have amenities and media
- properties and units may have inspection bookings
- a unit may have many rental applications
- an approved application may convert into one tenancy
- a tenancy belongs to one tenant and one unit
- a tenancy has many invoices
- an invoice has many invoice items
- a tenant or tenancy may have many payments
- a payment may have many allocations
- tenancies, units, and properties may have maintenance requests

# Suggested Enums

Create enums for:

- PropertyPublishStatus
- UnitOccupancyStatus
- BillingCycle
- InspectionStatus
- ApplicationStatus
- TenancyStatus
- InvoiceStatus
- PaymentStatus
- MaintenanceStatus
- MaintenancePriority

# Recommended Migration Order

1. users
2. permission tables
3. landlords / caretakers / tenants
4. properties
5. property_units
6. property_amenities and pivots
7. inspection_bookings
8. rental_applications
9. application_documents
10. tenancies
11. tenancy_documents
12. invoices
13. invoice_items
14. payments
15. payment_allocations
16. receipts
17. maintenance_requests
18. maintenance_updates
19. maintenance_attachments
20. activity_logs
21. settings
22. charge_templates

# Build Constraints

## Multi-tenancy Strategy

For v1, prefer a single-application multi-account model over database-per-tenant isolation.

Prefer:

- ownership scoping
- strong authorization boundaries
- audit logs

## API-first Domain Logic

Even if Livewire drives the first UI, core business workflows should live in actions and services so future mobile clients can reuse the same domain rules.

## Media Strategy

Use S3-compatible storage in production for:

- property images
- application documents
- tenancy documents
- payment proofs
- maintenance attachments

## Background Jobs

Use queues for:

- notifications
- media conversions
- recurring invoice generation
- report generation

## Observability

Adopt:

- application logs
- activity logs
- queue failure visibility
- safe production error reporting

## Security

- use policies across modules
- validate uploads aggressively
- rate limit public forms
- protect admin routes carefully
- use signed URLs for sensitive downloads
- never expose hidden units or internal notes on public endpoints

# Phase 0 Implementation Prompt

Use the following prompt when starting foundation work:

```text
You are my senior Laravel SaaS engineer.
We are building LeaseSmart Premium, a premium property rental and property operations platform.

Current stage:
Phase 0 - Foundation and Architecture Setup

Project goals:
- Build a premium SaaS web application
- Use Laravel 12, Livewire 4, Flux Pro, Tailwind CSS, MySQL, Sanctum, Spatie Permission, Spatie Media Library, Pest
- Keep architecture mobile-ready from day one
- Use modular monolith design
- Keep UI thin and domain logic thick
- Prepare for public marketplace + internal operations + future mobile app

Architecture constraints:
- users must not rely on a single hardcoded role enum
- properties and property_units are separate entities
- rental_applications and tenancies are separate entities
- invoices and payments are separate entities
- workflow states must be explicit and controlled
- major actions should be auditable

Engineering rules:
- Livewire components should handle UI concerns only
- business logic belongs in Actions, Services, Policies, Events, and domain-focused classes
- use Form Requests or equivalent validation patterns where appropriate
- use transactions for multi-step workflow operations
- tests must be included for critical scaffolding and authorization
- code must be clean, maintainable, and production-minded

Your task in this phase:
1. Set up the application foundation.
2. Establish a clean folder structure for long-term scale.
3. Install/configure foundational packages.
4. Build the authenticated application shell and role-aware navigation.
5. Prepare seeders, middleware, and policies for future modules.
6. Leave the app in a runnable, coherent, premium-looking state.
```

# Recommended Next Build Order

1. Users and Access hardening
2. Property and property units
3. Public listings
4. Inspection bookings
5. Rental applications
6. Tenancies
7. Billing and payments
8. Maintenance
9. Dashboards
10. API and mobile readiness
