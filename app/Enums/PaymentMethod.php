<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case BankTransfer = 'bank_transfer';
    case Cash = 'cash';
    case Card = 'card';
    case Ussd = 'ussd';
    case Wallet = 'wallet';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::BankTransfer => 'Bank Transfer',
            self::Cash => 'Cash',
            self::Card => 'Card',
            self::Ussd => 'USSD',
            self::Wallet => 'Wallet',
            self::Other => 'Other',
        };
    }
}
