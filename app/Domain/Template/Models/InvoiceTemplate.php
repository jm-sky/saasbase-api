<?php

namespace App\Domain\Template\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Template\Casts\TemplatePreviewDataCast;
use App\Domain\Template\Casts\TemplateSettingsCast;
use App\Domain\Template\Enums\TemplateCategory;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string           $id
 * @property string           $tenant_id
 * @property ?string          $user_id
 * @property string           $name
 * @property ?string          $description
 * @property string           $content
 * @property TemplateCategory $category
 * @property array            $preview_data
 * @property array            $settings
 * @property bool             $is_active
 * @property bool             $is_default
 * @property ?\Carbon\Carbon  $created_at
 * @property ?\Carbon\Carbon  $updated_at
 */
class InvoiceTemplate extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'invoice_templates';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'description',
        'content',
        'category',
        'preview_data',
        'settings',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'category'     => TemplateCategory::class,
        'preview_data' => TemplatePreviewDataCast::class,
        'settings'     => TemplateSettingsCast::class,
        'is_active'    => 'boolean',
        'is_default'   => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByCategory($query, TemplateCategory $category)
    {
        return $query->where('category', $category);
    }
}
