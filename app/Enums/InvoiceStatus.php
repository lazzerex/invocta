<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Draft => 'Draft',
            self::Sent => 'Sent',
            self::Paid => 'Paid',
            self::Overdue => 'Overdue',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft => 'gray',
            self::Sent => 'blue',
            self::Paid => 'green',
            self::Overdue => 'red',
            self::Cancelled => 'gray',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function canEdit(): bool
    {
        return $this === self::Draft;
    }

    public function canSend(): bool
    {
        return in_array($this, [self::Draft, self::Sent]);
    }

    public function canMarkPaid(): bool
    {
        return in_array($this, [self::Sent, self::Overdue]);
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::Draft, self::Sent, self::Overdue]);
    }
}
