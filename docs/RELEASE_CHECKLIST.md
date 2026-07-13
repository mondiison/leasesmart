LeaseSmart Release Checklist

# Goal

Use this checklist before any production or client-facing release.

# 1. Environment and Secrets

- confirm `APP_ENV=production`
- confirm `APP_DEBUG=false`
- confirm `APP_URL` uses the real HTTPS domain
- confirm `APP_KEY` is set
- confirm mail, queue, cache, and database credentials are correct
- confirm third-party keys and private package credentials are stored outside source control
- rotate exposed credentials if they were ever committed or shared during development

# 2. Database and Storage

- run migrations in a staging-like environment first
- confirm storage symlink exists and media URLs resolve correctly
- confirm seeders are not automatically populating demo data in production
- verify database backup destination and retention policy
- verify restore steps have been tested at least once

# 3. Queue, Scheduler, and Background Work

- confirm queue worker strategy for the host environment
- confirm scheduler is running every minute
- confirm notifications, media conversions, and other queued jobs complete successfully
- confirm failed jobs are visible and monitored

# 4. Security and Access

- run `php artisan app:health-check`
- confirm security headers are present in responses
- confirm API and auth rate limiting are active
- confirm admin accounts use real production credentials and verified emails
- review active roles and permissions for least-privilege access
- confirm inactive-user handling works for both web and API flows

# 5. Product Validation

- smoke test public marketplace listings and property details
- smoke test inspection requests and rental applications
- smoke test tenancy, billing, and maintenance workflows with realistic accounts
- smoke test dashboard visibility for admin, landlord, caretaker, and tenant roles
- smoke test API token issuance and authenticated mobile-ready endpoints

# 6. Performance and Observability

- run `php artisan config:cache`
- run `php artisan route:cache`
- run `php artisan view:cache`
- confirm logging destination and retention settings
- confirm application errors are captured and reviewable
- test representative pages with production-like data volume

# 7. Final Verification

- run `php artisan test`
- confirm release notes or deployment notes are updated
- confirm rollback steps are documented
- confirm the deployment owner and verification owner are clear

# Current Built-In Ops Command

- `php artisan app:health-check`
  Validates database, cache, queue, debug-mode, and app URL readiness signals.
