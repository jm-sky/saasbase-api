<?php

namespace App\Domain\Expense\Enums;

enum ExpenseActivityType: string
{
    case Created               = 'expense.created';
    case Updated               = 'expense.updated';
    case Deleted               = 'expense.deleted';
    case CommentCreated        = 'expense.comment.created';
    case CommentUpdated        = 'expense.comment.updated';
    case CommentDeleted        = 'expense.comment.deleted';
    case AttachmentCreated     = 'expense.attachment.created';
    case AttachmentUpdated     = 'expense.attachment.updated';
    case AttachmentDeleted     = 'expense.attachment.deleted';

    public function label(): string
    {
        return match ($this) {
            self::Created               => 'Expense created',
            self::Updated               => 'Expense updated',
            self::Deleted               => 'Expense deleted',
            self::CommentCreated        => 'Expense comment created',
            self::CommentUpdated        => 'Expense comment updated',
            self::CommentDeleted        => 'Expense comment deleted',
            self::AttachmentCreated     => 'Expense attachment created',
            self::AttachmentUpdated     => 'Expense attachment updated',
            self::AttachmentDeleted     => 'Expense attachment deleted',
        };
    }
}
