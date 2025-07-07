<?php

namespace App\Domain\Invoice\Enums;

enum InvoiceActivityType: string
{
    case Created               = 'invoice.created';
    case Updated               = 'invoice.updated';
    case Deleted               = 'invoice.deleted';
    case CommentCreated        = 'invoice.comment.created';
    case CommentUpdated        = 'invoice.comment.updated';
    case CommentDeleted        = 'invoice.comment.deleted';
    case AttachmentCreated     = 'invoice.attachment.created';
    case AttachmentUpdated     = 'invoice.attachment.updated';
    case AttachmentDeleted     = 'invoice.attachment.deleted';

    public function label(): string
    {
        return match ($this) {
            self::Created               => 'invoice created',
            self::Updated               => 'invoice updated',
            self::Deleted               => 'invoice deleted',
            self::CommentCreated        => 'invoice comment created',
            self::CommentUpdated        => 'invoice comment updated',
            self::CommentDeleted        => 'invoice comment deleted',
            self::AttachmentCreated     => 'invoice attachment created',
            self::AttachmentUpdated     => 'invoice attachment updated',
            self::AttachmentDeleted     => 'invoice attachment deleted',
        };
    }
}
