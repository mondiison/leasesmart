<?php

namespace App\Support\Activity;

use Illuminate\Support\Str;

class ActivityPresenter
{
    public static function label(string $action): string
    {
        return match ($action) {
            'invoice_created' => 'Invoice issued',
            'payment_submitted' => 'Payment proof submitted',
            'payment_reviewed' => 'Payment reviewed',
            'maintenance_request_created' => 'Maintenance request opened',
            'maintenance_request_updated' => 'Maintenance updated',
            'tenancy_created' => 'Tenancy created',
            'tenancy_updated' => 'Tenancy updated',
            'tenancy_documents_uploaded' => 'Tenancy documents uploaded',
            'tenancy_document_renamed' => 'Tenancy document renamed',
            'tenancy_document_deleted' => 'Tenancy document deleted',
            'lease_expiry_alert_sent' => 'Lease alert sent',
            'rental_application_submitted' => 'Application submitted',
            'rental_application_updated' => 'Application reviewed',
            'inspection_requested' => 'Inspection requested',
            'inspection_updated' => 'Inspection updated',
            'property_created' => 'Property created',
            'property_updated' => 'Property updated',
            'property_unit_created' => 'Unit created',
            'property_unit_updated' => 'Unit updated',
            'property_publish_status_updated' => 'Publication updated',
            'user_created' => 'User created',
            'user_updated' => 'User updated',
            'role_assigned' => 'Role assigned',
            'password_reset_link_sent' => 'Password reset link sent',
            'profile_updated' => 'Profile updated',
            'weekly_report_digest_sent' => 'Weekly report digest sent',
            default => Str::of($action)->replace('_', ' ')->headline()->toString(),
        };
    }
}
