<?php

namespace App\Domain\Tenant\Enums;

enum TenantActivityType: string
{
    case Created               = 'tenant.created';
    case Updated               = 'tenant.updated';
    case Deleted               = 'tenant.deleted';
    case LogoCreated           = 'tenant.logo.created';
    case LogoUpdated           = 'tenant.logo.updated';
    case LogoDeleted           = 'tenant.logo.deleted';
    case AttachmentCreated     = 'tenant.attachment.created';
    case AttachmentUpdated     = 'tenant.attachment.updated';
    case AttachmentDeleted     = 'tenant.attachment.deleted';
    case BankAccountCreated    = 'tenant.bank_account.created';
    case BankAccountUpdated    = 'tenant.bank_account.updated';
    case BankAccountDeleted    = 'tenant.bank_account.deleted';
    case BankAccountSetDefault = 'tenant.bank_account.set_default';
    case AddressCreated        = 'tenant.address.created';
    case AddressUpdated        = 'tenant.address.updated';
    case AddressDeleted        = 'tenant.address.deleted';
    case AddressSetDefault     = 'tenant.address.set_default';
    case InvitationSent        = 'tenant.invitation.sent';
    case InvitationAccepted    = 'tenant.invitation.accepted';
    case InvitationRejected    = 'tenant.invitation.rejected';
    case InvitationCanceled    = 'tenant.invitation.canceled';
    case InvitationResent      = 'tenant.invitation.resent';
}
