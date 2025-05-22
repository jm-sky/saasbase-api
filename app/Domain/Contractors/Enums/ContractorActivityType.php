<?php

namespace App\Domain\Contractors\Enums;

enum ContractorActivityType: string
{
    case Created               = 'contractor.created';
    case Updated               = 'contractor.updated';
    case Deleted               = 'contractor.deleted';
    case AddressCreated        = 'contractor.address.created';
    case AddressUpdated        = 'contractor.address.updated';
    case AddressDeleted        = 'contractor.address.deleted';
    case AddressSetDefault     = 'contractor.address.set_default';
    case BankAccountCreated    = 'contractor.bank_account.created';
    case BankAccountUpdated    = 'contractor.bank_account.updated';
    case BankAccountDeleted    = 'contractor.bank_account.deleted';
    case BankAccountSetDefault = 'contractor.bank_account.set_default';
    case CommentCreated        = 'contractor.comment.created';
    case CommentUpdated        = 'contractor.comment.updated';
    case CommentDeleted        = 'contractor.comment.deleted';
    case ContactCreated        = 'contractor.contact.created';
    case ContactUpdated        = 'contractor.contact.updated';
    case ContactDeleted        = 'contractor.contact.deleted';
    case LogoCreated           = 'contractor.logo.created';
    case LogoUpdated           = 'contractor.logo.updated';
    case LogoDeleted           = 'contractor.logo.deleted';
    case AttachmentCreated     = 'contractor.attachment.created';
    case AttachmentUpdated     = 'contractor.attachment.updated';
    case AttachmentDeleted     = 'contractor.attachment.deleted';

    public function label(): string
    {
        return match ($this) {
            self::Created               => 'Contractor created',
            self::Updated               => 'Contractor updated',
            self::Deleted               => 'Contractor deleted',
            self::AddressCreated        => 'Contractor address created',
            self::AddressUpdated        => 'Contractor address updated',
            self::AddressDeleted        => 'Contractor address deleted',
            self::AddressSetDefault     => 'Contractor address set as default',
            self::BankAccountCreated    => 'Contractor bank account created',
            self::BankAccountUpdated    => 'Contractor bank account updated',
            self::BankAccountDeleted    => 'Contractor bank account deleted',
            self::BankAccountSetDefault => 'Contractor bank account set as default',
            self::CommentCreated        => 'Contractor comment created',
            self::CommentUpdated        => 'Contractor comment updated',
            self::CommentDeleted        => 'Contractor comment deleted',
            self::ContactCreated        => 'Contractor contact created',
            self::ContactUpdated        => 'Contractor contact updated',
            self::ContactDeleted        => 'Contractor contact deleted',
            self::LogoCreated           => 'Contractor logo created',
            self::LogoUpdated           => 'Contractor logo updated',
            self::LogoDeleted           => 'Contractor logo deleted',
            self::AttachmentCreated     => 'Contractor attachment created',
            self::AttachmentUpdated     => 'Contractor attachment updated',
            self::AttachmentDeleted     => 'Contractor attachment deleted',
        };
    }
}
