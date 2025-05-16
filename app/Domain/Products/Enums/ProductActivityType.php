<?php

namespace App\Domain\Products\Enums;

enum ProductActivityType: string
{
    case Created           = 'product.created';
    case Updated           = 'product.updated';
    case Deleted           = 'product.deleted';
    case CategoryChanged   = 'product.category.changed';
    case PriceUpdated      = 'product.price.updated';
    case StockUpdated      = 'product.stock.updated';
    case LogoCreated       = 'product.logo.created';
    case LogoUpdated       = 'product.logo.updated';
    case LogoDeleted       = 'product.logo.deleted';
    case AttachmentCreated = 'product.attachment.created';
    case AttachmentUpdated = 'product.attachment.updated';
    case AttachmentDeleted = 'product.attachment.deleted';
    case CommentCreated    = 'product.comment.created';
    case CommentUpdated    = 'product.comment.updated';
    case CommentDeleted    = 'product.comment.deleted';
    case TagAdded          = 'product.tag.added';
    case TagRemoved        = 'product.tag.removed';
    case TagsSynced        = 'product.tags.synced';
}
