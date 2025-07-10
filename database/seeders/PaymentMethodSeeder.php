<?php

namespace Database\Seeders;

use App\Domain\Financial\Enums\PaymentMethodCode;
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
                'code'         => PaymentMethodCode::BankTransfer->value,
                'name'         => 'Bank Transfer',
                'payment_days' => 14,
                'tenant_id'    => null,
            ],
            [
                'code'         => PaymentMethodCode::Cash->value,
                'name'         => 'Cash',
                'payment_days' => 0,
                'tenant_id'    => null,
            ],
            [
                'code'         => PaymentMethodCode::CreditCard->value,
                'name'         => 'Credit card',
                'payment_days' => 7,
                'tenant_id'    => null,
            ],
            [
                'code'         => PaymentMethodCode::PayPal->value,
                'name'         => 'PayPal',
                'payment_days' => 7,
                'tenant_id'    => null,
            ],
            [
                'code'         => PaymentMethodCode::Voucher->value,
                'name'         => 'Voucher',
                'payment_days' => 0,
                'tenant_id'    => null,
            ],
            [
                'code'         => PaymentMethodCode::Cheque->value,
                'name'         => 'Cheque',
                'payment_days' => 0,
                'tenant_id'    => null,
            ],
            [
                'code'         => PaymentMethodCode::Compensation->value,
                'name'         => 'Compensation',
                'payment_days' => null,
                'tenant_id'    => null,
            ],
            [
                'code'         => PaymentMethodCode::Credit->value,
                'name'         => 'Credit',
                'payment_days' => 30,
                'tenant_id'    => null,
            ],
            [
                'code'         => PaymentMethodCode::ElectronicPayment->value,
                'name'         => 'Electronic Payment',
                'payment_days' => 7,
                'tenant_id'    => null,
            ],
            [
                'code'         => PaymentMethodCode::PostSalePayment->value,
                'name'         => 'Post-sale Payment',
                'payment_days' => 14,
                'tenant_id'    => null,
            ],
            [
                'code'         => PaymentMethodCode::InstallmentPayment->value,
                'name'         => 'Installment Payment',
                'payment_days' => null,
                'tenant_id'    => null,
            ],
            [
                'code'         => PaymentMethodCode::Prepayment->value,
                'name'         => 'Prepayment',
                'payment_days' => null,
                'tenant_id'    => null,
            ],
            [
                'code'         => PaymentMethodCode::MoneyOrder->value,
                'name'         => 'Money Order',
                'payment_days' => null,
                'tenant_id'    => null,
            ],
            [
                'code'         => PaymentMethodCode::ExpenseSettlement->value,
                'name'         => 'Expense Settlement',
                'payment_days' => null,
                'tenant_id'    => null,
            ],
            [
                'code'         => PaymentMethodCode::CashOnDelivery->value,
                'name'         => 'Cash on Delivery',
                'payment_days' => 0,
                'tenant_id'    => null,
            ],
            [
                'code'         => PaymentMethodCode::PaidByBankTransfer->value,
                'name'         => 'Paid by Bank Transfer',
                'payment_days' => 0,
                'tenant_id'    => null,
            ],
        ];

        Tenant::bypassTenant(Tenant::GLOBAL_TENANT_ID, function () use ($methods) {
            foreach ($methods as $method) {
                PaymentMethod::firstOrCreate([
                    'id'        => Ulid::deterministic([$method['code']]),
                    'name'      => $method['name'],
                    'code'      => $method['code'],
                    'tenant_id' => $method['tenant_id'],
                ], [
                    'payment_days' => $method['payment_days'],
                ]);
            }
        });
    }
}
