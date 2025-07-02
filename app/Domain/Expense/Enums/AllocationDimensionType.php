<?php

namespace App\Domain\Expense\Enums;

enum AllocationDimensionType: string
{
    case EMPLOYEES            = 'HA';          // Pracownicy
    case LOCATION             = 'LO';           // Lokalizacja
    case PRODUCTS             = 'PD';           // Produkty
    case PROJECT              = 'PR';            // Projekt
    case REVENUE_TYPE         = 'RS';       // Rodzaj przychodów
    case TRANSACTION_TYPE     = 'RTR';  // Rodzaj transakcji
    case COST_TYPE            = 'RY';          // Rodzaj kosztów
    case STRUCTURE            = 'ST';          // Struktura/Działy
    case RELATED_TRANSACTIONS = 'TP'; // Transakcje powiązane
    case CONTRACTS            = 'UM';          // Umowy
    case EQUIPMENT            = 'UR';          // Urządzenia

    public function label(): string
    {
        return match ($this) {
            self::EMPLOYEES            => 'Pracownicy',
            self::LOCATION             => 'Lokalizacja',
            self::PRODUCTS             => 'Produkty',
            self::PROJECT              => 'Projekt',
            self::REVENUE_TYPE         => 'Rodzaj przychodów',
            self::TRANSACTION_TYPE     => 'Rodzaj transakcji',
            self::COST_TYPE            => 'Rodzaj kosztów',
            self::STRUCTURE            => 'Struktura/Działy',
            self::RELATED_TRANSACTIONS => 'Transakcje powiązane',
            self::CONTRACTS            => 'Umowy',
            self::EQUIPMENT            => 'Urządzenia',
        };
    }

    public function labelEN(): string
    {
        return match ($this) {
            self::EMPLOYEES            => 'Employees',
            self::LOCATION             => 'Location',
            self::PRODUCTS             => 'Products',
            self::PROJECT              => 'Project',
            self::REVENUE_TYPE         => 'Revenue Type',
            self::TRANSACTION_TYPE     => 'Transaction Type',
            self::COST_TYPE            => 'Cost Type',
            self::STRUCTURE            => 'Structure/Departments',
            self::RELATED_TRANSACTIONS => 'Related Transactions',
            self::CONTRACTS            => 'Contracts',
            self::EQUIPMENT            => 'Equipment',
        };
    }

    public function getMorphClass(): string
    {
        return match ($this) {
            self::EMPLOYEES            => \App\Domain\Auth\Models\User::class,
            self::LOCATION             => \App\Domain\Common\Models\AllocationLocation::class,
            self::PRODUCTS             => \App\Domain\Products\Models\AllocationProductCategory::class,
            self::PROJECT              => \App\Domain\Projects\Models\Project::class,
            self::REVENUE_TYPE         => \App\Domain\Financial\Models\AllocationRevenueType::class,
            self::TRANSACTION_TYPE     => \App\Domain\Financial\Models\AllocationTransactionType::class,
            self::COST_TYPE            => \App\Domain\Financial\Models\AllocationCostType::class,
            self::STRUCTURE            => \App\Domain\Tenant\Models\OrganizationUnit::class,
            self::RELATED_TRANSACTIONS => \App\Domain\Financial\Models\AllocationRelatedTransactionCategory::class,
            self::CONTRACTS            => \App\Domain\Common\Models\AllocationContractType::class,
            self::EQUIPMENT            => \App\Domain\Common\Models\AllocationEquipmentType::class,
        };
    }

    public function isAlwaysVisible(): bool
    {
        return self::TRANSACTION_TYPE === $this;
    }

    public function isConfigurable(): bool
    {
        return !$this->isAlwaysVisible();
    }

    public function getDefaultDisplayOrder(): int
    {
        return match ($this) {
            self::TRANSACTION_TYPE     => 1,  // Always first
            self::PROJECT              => 2,
            self::EMPLOYEES            => 3,
            self::COST_TYPE            => 4,
            self::STRUCTURE            => 5,
            self::LOCATION             => 6,
            self::PRODUCTS             => 7,
            self::REVENUE_TYPE         => 8,
            self::RELATED_TRANSACTIONS => 9,
            self::CONTRACTS            => 10,
            self::EQUIPMENT            => 11,
        };
    }

    public function getDefaultEnabledState(): bool
    {
        // Enable common dimensions by default
        return match ($this) {
            self::TRANSACTION_TYPE => true, // Always enabled
            self::PROJECT          => true,
            self::EMPLOYEES        => true,
            self::COST_TYPE        => true,
            self::STRUCTURE        => true,
            default                => false,
        };
    }
}
