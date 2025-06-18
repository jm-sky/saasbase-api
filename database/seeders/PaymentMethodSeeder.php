<?php

namespace Database\Seeders;

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
                'key'          => 'bankTransfer',
                'name'         => 'Bank Transfer',
                'payment_days' => 14,
                'tenant_id'    => null,
            ],
            [
                'key'          => 'cash',
                'name'         => 'Cash',
                'payment_days' => 0,
                'tenant_id'    => null,
            ],
            [
                'key'          => 'creditCard',
                'name'         => 'Credit card',
                'payment_days' => 7,
                'tenant_id'    => null,
            ],
            [
                'key'          => 'payPal',
                'name'         => 'PayPal',
                'payment_days' => 7,
                'tenant_id'    => null,
            ],
            [
                'key'          => 'voucher',
                'name'         => 'Voucher',
                'payment_days' => 0,
                'tenant_id'    => null,
            ],
            [
                'key'          => 'cheque',
                'name'         => 'Cheque',
                'payment_days' => 0,
                'tenant_id'    => null,
            ],
            [
                'key'          => 'compensation',
                'name'         => 'Compensation',
                'payment_days' => null,
                'tenant_id'    => null,
            ],
            [
                'key'          => 'credit',
                'name'         => 'Credit',
                'payment_days' => 30,
                'tenant_id'    => null,
            ],
            [
                'key'          => 'electronicPayment',
                'name'         => 'Electronic Payment',
                'payment_days' => 7,
                'tenant_id'    => null,
            ],
            [
                'key'          => 'postSalePayment',
                'name'         => 'Post-sale Payment',
                'payment_days' => 14,
                'tenant_id'    => null,
            ],
            [
                'key'          => 'installmentPayment',
                'name'         => 'Installment Payment',
                'payment_days' => null,
                'tenant_id'    => null,
            ],
            [
                'key'          => 'prepayment',
                'name'         => 'Prepayment',
                'payment_days' => null,
                'tenant_id'    => null,
            ],
            [
                'key'          => 'moneyOrder',
                'name'         => 'Money Order',
                'payment_days' => null,
                'tenant_id'    => null,
            ],
            [
                'key'          => 'expenseSettlement',
                'name'         => 'Expense Settlement',
                'payment_days' => null,
                'tenant_id'    => null,
            ],
            [
                'key'          => 'cashOnDelivery',
                'name'         => 'Cash on Delivery',
                'payment_days' => 0,
                'tenant_id'    => null,
            ],
            [
                'key'          => 'paidByBankTransfer',
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
