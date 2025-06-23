<?php

namespace App\Services\NBP\Enums;

enum NBPTableEnum: string
{
    case A = 'A'; // Average foreign currency exchange rates
    case B = 'B'; // Average exchange rates of inconvertible currencies
    case C = 'C'; // Purchase and sale exchange rates
    case H = 'H'; // Exchange rates of units of account

    public function getDescription(): string
    {
        return match ($this) {
            self::A => 'Average foreign currency exchange rates',
            self::B => 'Average exchange rates of inconvertible currencies',
            self::C => 'Purchase and sale exchange rates',
            self::H => 'Exchange rates of units of account',
        };
    }

    public static function fromString(string $table): self
    {
        return match (strtoupper($table)) {
            'A'     => self::A,
            'B'     => self::B,
            'C'     => self::C,
            'H'     => self::H,
            default => throw new \InvalidArgumentException("Invalid NBP table: {$table}"),
        };
    }
}
