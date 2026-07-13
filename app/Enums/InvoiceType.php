<?php

namespace App\Enums;

enum InvoiceType: string
{
    case Rent = 'rent';
    case ServiceCharge = 'service_charge';
    case CautionFee = 'caution_fee';
    case InspectionFee = 'inspection_fee';
    case Miscellaneous = 'miscellaneous';

    public function label(): string
    {
        return match ($this) {
            self::Rent => 'Rent',
            self::ServiceCharge => 'Service Charge',
            self::CautionFee => 'Caution Fee',
            self::InspectionFee => 'Inspection Fee',
            self::Miscellaneous => 'Miscellaneous',
        };
    }
}
