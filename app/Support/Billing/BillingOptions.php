<?php

namespace App\Support\Billing;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;

class BillingOptions
{
    public static function forForms(): array
    {
        return [
            'invoiceTypes' => InvoiceType::cases(),
            'invoiceStatuses' => InvoiceStatus::cases(),
            'paymentMethods' => PaymentMethod::cases(),
            'paymentStatuses' => PaymentStatus::cases(),
        ];
    }
}
