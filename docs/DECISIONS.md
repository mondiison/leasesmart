Architectural Decisions Log

# Core Architecture

Decision: Use Modular Monolith
Reason: Simpler deployment and faster iteration than microservices.

# Authorization

Decision: Use Spatie Laravel Permission
Reason: Flexible role and permission control for SaaS environments.

# Property Model

Decision: Separate properties and property_units
Reason: Many buildings contain multiple rentable units.

# Application Model

Decision: Separate rental_applications and tenancies
Reason: Not all applicants become tenants.

# Financial Model

Decision: Separate invoices from payments
Reason: Allows partial payments and multiple invoice items.

# Storage

Decision: Use centralized media management
Reason: Properties, documents, and maintenance all require attachments.

# Backend Design

Decision: Use an API-ready architecture
Reason: Future mobile apps will reuse the same domain logic.

# UI Delivery

Decision: Prefer Livewire-first implementation for internal management modules
Reason: Back-office workflows are stateful and benefit from inline validation, richer interaction, and fewer controller-driven round trips.

# Users and Access Rollout

Decision: Phase 1 centers on one internal users-and-access module before property modules
Reason: Admin-managed users, role assignment, profile records, and access policies belong to the same operational domain and are prerequisites for later modules.

# Profile Modeling

Decision: Implement landlords, caretakers, and tenants as dedicated profile records
Reason: Role-specific data should remain separate from authentication concerns and must be integrated into account administration workflows.

# Policy Baseline

Decision: Establish UserPolicy and RolePolicy during Phase 1
Reason: Authorization rules should be explicit before the admin management UI grows.

# Auditability

Decision: Expand activity logs in Phase 1 to include user creation, role assignment, login, and profile updates
Reason: User and access operations are high-value audit events and should be traceable from the beginning.

# Reporting Layer

Decision: Build dashboards from live role-scoped queries instead of static status cards
Reason: Reporting should reflect the true operational state of each workspace and surface actionable next steps.

# API Delivery

Decision: Expose a Sanctum-backed API layer through dedicated API resources and controllers
Reason: Mobile-ready access should reuse the same domain visibility rules while returning a clean JSON contract.

# Production Hardening

Decision: Add baseline security headers, explicit API rate limits, and a built-in health check command before release work
Reason: Deployment readiness requires practical operational protections and a simple way to validate the environment.

# Release Readiness

Decision: Add a dedicated release checklist after roadmap completion
Reason: Final polish work should be repeatable and visible instead of spread across ad hoc notes.
