<?php

namespace App\Domain\Financial\Enums;

enum PaymentMethodKey: string
{
    case BankTransfer       = 'bankTransfer';
    case Cash               = 'cash';
    case CreditCard         = 'creditCard';
    case PayPal             = 'payPal';
    case Voucher            = 'voucher';
    case Cheque             = 'cheque';
    case Compensation       = 'compensation';
    case Credit             = 'credit';
    case ElectronicPayment  = 'electronicPayment';
    case PostSalePayment    = 'postSalePayment';
    case InstallmentPayment = 'installmentPayment';
    case Prepayment         = 'prepayment';
    case MoneyOrder         = 'moneyOrder';
    case ExpenseSettlement  = 'expenseSettlement';
    case CashOnDelivery     = 'cashOnDelivery';
    case PaidByBankTransfer = 'paidByBankTransfer';
}
