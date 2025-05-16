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
}
