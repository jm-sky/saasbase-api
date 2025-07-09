<?php

namespace Database\Seeders;

use App\Domain\Financial\Enums\PaymentMethodKey;
use App\Domain\Financial\Models\PaymentMethod;
use App\Domain\Tenant\Models\Tenant;
use App\Helpers\Ulid;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'key'          => PaymentMethodKey::BankTransfer->value,
                'name'         => 'Bank Transfer',
                'payment_days' => 14,
                'tenant_id'    => null,
            ],
            [
                'key'          => PaymentMethodKey::Cash->value,
                'name'         => 'Cash',
                'payment_days' => 0,
                'tenant_id'    => null,
            ],
            [
                'key'          => PaymentMethodKey::CreditCard->value,
                'name'         => 'Credit card',
                'payment_days' => 7,
                'tenant_id'    => null,
            ],
            [
                'key'          => PaymentMethodKey::PayPal->value,
                'name'         => 'PayPal',
                'payment_days' => 7,
                'tenant_id'    => null,
            ],
            [
                'key'          => PaymentMethodKey::Voucher->value,
                'name'         => 'Voucher',
                'payment_days' => 0,
                'tenant_id'    => null,
            ],
            [
                'key'          => PaymentMethodKey::Cheque->value,
                'name'         => 'Cheque',
                'payment_days' => 0,
                'tenant_id'    => null,
            ],
            [
                'key'          => PaymentMethodKey::Compensation->value,
                'name'         => 'Compensation',
                'payment_days' => null,
                'tenant_id'    => null,
            ],
            [
                'key'          => PaymentMethodKey::Credit->value,
                'name'         => 'Credit',
                'payment_days' => 30,
                'tenant_id'    => null,
            ],
            [
                'key'          => PaymentMethodKey::ElectronicPayment->value,
                'name'         => 'Electronic Payment',
                'payment_days' => 7,
                'tenant_id'    => null,
            ],
            [
                'key'          => PaymentMethodKey::PostSalePayment->value,
                'name'         => 'Post-sale Payment',
                'payment_days' => 14,
                'tenant_id'    => null,
            ],
            [
                'key'          => PaymentMethodKey::InstallmentPayment->value,
                'name'         => 'Installment Payment',
                'payment_days' => null,
                'tenant_id'    => null,
            ],
            [
                'key'          => PaymentMethodKey::Prepayment->value,
                'name'         => 'Prepayment',
                'payment_days' => null,
                'tenant_id'    => null,
            ],
            [
                'key'          => PaymentMethodKey::MoneyOrder->value,
                'name'         => 'Money Order',
                'payment_days' => null,
                'tenant_id'    => null,
            ],
            [
                'key'          => PaymentMethodKey::ExpenseSettlement->value,
                'name'         => 'Expense Settlement',
                'payment_days' => null,
                'tenant_id'    => null,
            ],
            [
                'key'          => PaymentMethodKey::CashOnDelivery->value,
                'name'         => 'Cash on Delivery',
                'payment_days' => 0,
                'tenant_id'    => null,
            ],
            [
                'key'          => PaymentMethodKey::PaidByBankTransfer->value,
                'name'         => 'Paid by Bank Transfer',
                'payment_days' => 0,
                'tenant_id'    => null,
            ],
        ];

        Tenant::bypassTenant(Tenant::GLOBAL_TENANT_ID, function () use ($methods) {
            foreach ($methods as $method) {
                PaymentMethod::firstOrCreate([
                    'id'        => Ulid::deterministic([$method['key']]),
                    'name'      => $method['name'],
                    'key'       => $method['key'],
                    'tenant_id' => $method['tenant_id'],
                ], [
                    'payment_days' => $method['payment_days'],
                ]);
            }
        });
    }
}
