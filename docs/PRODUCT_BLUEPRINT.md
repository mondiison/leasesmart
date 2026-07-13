LeaseSmart Premium - Product Blueprint

# Product Vision

LeaseSmart Premium is a modern property rental and operations platform for landlords, caretakers, tenants, and internal administrators.

The product combines:

- a public rental marketplace
- back-office property operations
- tenant self-service workflows
- financial tracking and billing
- maintenance management
- an API-ready backend for future mobile applications

# Product Goals

- Build a premium SaaS web application that is production-minded from day one
- Support both public listing discovery and internal property operations
- Keep the backend reusable for future mobile clients
- Model the rental business with clear, auditable workflow boundaries
- Favor long-term maintainability over fast throwaway CRUD decisions

# Current Build Status

LeaseSmart currently includes the planned Phase 0 through Phase 11 roadmap slices in working code.

Delivered platform areas:

- users and access administration
- property and unit management
- public marketplace and listing detail pages
- inspection booking and management
- rental application intake and review
- tenancy lifecycle management
- billing, payment verification, and receipts
- maintenance workflows
- role-aware dashboards and reporting
- Sanctum-backed API endpoints for mobile-ready account access
- baseline production hardening with security headers, rate limiting, and health checks

# Core User Types

- Admin
- Landlord
- Caretaker / Property Manager
- Tenant
- Public Visitor

# Core Product Modules

## 1. Users and Access

Handles authentication, authorization, profiles, auditability, and notifications as one internal administration domain.

Key capabilities:

- authentication
- role and permission management
- admin user management
- profile management
- role-specific profile records
- account activation and deactivation
- password reset workflows
- audit logs
- notifications

## 2. Property Management

Manages the rentable inventory model.

Core entities:

- properties
- property units
- amenities
- property media

Key capabilities:

- property creation and editing
- unit management
- media galleries
- publish and unpublish workflows
- landlord ownership mapping
- caretaker assignment

## 3. Public Marketplace

Handles the public-facing rental discovery experience.

Key capabilities:

- property search
- filters
- property detail pages
- unit availability display
- inspection booking
- rental application intake

## 4. Inspection Management

Tracks viewing requests and scheduling workflows.

Workflow:

Requested -> Confirmed -> Rescheduled -> Completed -> Cancelled

Key capabilities:

- booking form
- schedule management
- internal handling notes
- notifications

## 5. Rental Applications

Handles application intake, review, and decisioning.

Workflow:

Draft -> Submitted -> Under Review -> Approved -> Rejected -> Converted

Key capabilities:

- application forms
- supporting document uploads
- internal review queue
- approval and rejection handling
- conversion into tenancy

## 6. Tenancy Lifecycle

Tracks active and past occupancy relationships.

Lifecycle:

Pending Activation -> Active -> Renewal Pending -> Ending -> Ended

Key capabilities:

- lease tracking
- tenant-to-unit assignment
- occupancy tracking
- tenancy documents

## 7. Billing and Payments

Provides the financial layer for charges and payment reconciliation.

Core entities:

- invoices
- invoice items
- payments
- payment allocations
- receipts

Key capabilities:

- rent and service charge invoicing
- payment proof upload
- payment verification
- partial payment allocation
- outstanding balance tracking

## 8. Maintenance Management

Supports issue reporting, assignment, progress tracking, and resolution.

Workflow:

Open -> Assigned -> In Progress -> Awaiting Confirmation -> Resolved -> Closed

Key capabilities:

- issue reporting
- priority management
- attachments
- update timeline
- assignment and closure tracking

## 9. Reporting and Dashboards

Provides role-aware operational visibility.

Primary dashboard audiences:

- admin
- landlord
- caretaker
- tenant

Example metrics:

- occupancy rate
- outstanding rent
- maintenance workload
- application pipeline

## 10. API Layer

Prepares the backend for future mobile and partner integrations.

Current API responsibilities:

- authenticated account access
- tenancy data
- invoices
- payments
- maintenance requests
- token issuance and revocation

Authentication strategy:

- Laravel Sanctum

Reference:

- `docs/MOBILE_API_GUIDE.md`

## 11. Production Hardening

Provides the baseline safety and operability layer for deployment readiness.

Current hardening capabilities:

- baseline security headers
- API and authentication rate limiting
- operational health-check command
- inactive-account JSON handling for API consumers

# Product Architecture

LeaseSmart uses a modular monolith architecture.

Planned domains:

- UsersAccess
- Properties
- Listings
- Inspections
- Applications
- Tenancies
- Billing
- Maintenance
- Notifications
- Reporting
- Api

UI delivery strategy:

- prefer Livewire-first screens for internal back-office modules
- keep controllers thin and limited to simple request-response pages, public routes, and endpoints that do not benefit from stateful UI
- keep business logic in actions, policies, enums, and domain-focused services so both Livewire and future APIs reuse the same rules

This structure allows fast delivery while preserving clear business boundaries and future API reuse.

# Data Modeling Principles

- Separate properties from property units
- Separate rental applications from tenancies
- Separate invoices from payments
- Keep user authentication separate from role-specific profile records
- Use explicit workflow states instead of ad hoc status strings
- Preserve auditability for critical business actions

# Phase Sequence

Recommended build order:

1. Foundation and architecture
2. Users and access hardening
3. Property and unit management
4. Public marketplace
5. Inspection bookings
6. Rental applications
7. Tenancies
8. Billing and payments
9. Maintenance
10. Dashboards and reporting
11. API and mobile readiness
12. Production hardening

# Current Release Priorities

Now that the planned roadmap is delivered, the next priorities are:

- release checklist execution
- deployment environment validation
- backup and restore strategy confirmation
- queue and scheduler rollout verification
- API documentation for mobile consumers
- ongoing UX and seed-data refinement

# Supporting Reference

The detailed schema and Phase 0 implementation baseline live in `docs/DATABASE_ERD_SPEC.md`.
