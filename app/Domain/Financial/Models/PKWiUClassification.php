<?php

namespace App\Domain\Financial\Models;

use App\Domain\Products\Models\Product;
use Carbon\Carbon;
use Database\Factories\PKWiUClassificationFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string                          $code
 * @property ?string                         $parent_code
 * @property string                          $name
 * @property ?string                         $description
 * @property int                             $level
 * @property bool                            $is_active
 * @property Carbon                          $created_at
 * @property Carbon                          $updated_at
 * @property PKWiUClassification             $parent
 * @property Collection<PKWiUClassification> $children
 * @property Collection<Product>             $products
 */
class PKWiUClassification extends Model
{
    use HasFactory;

    protected $table = 'pkwiu_classifications';

    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'parent_code',
        'name',
        'description',
        'level',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'level'      => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Hierarchical relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_code', 'code');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_code', 'code');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'pkwiu_code', 'code');
    }

    // Scopes for filtering
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByLevel($query, int $level)
    {
        return $query->where('level', $level);
    }

    public function scopeRootCategories($query)
    {
        return $query->whereNull('parent_code');
    }

    // Helper methods
    public function getFullHierarchyPath(): string
    {
        $path    = [$this->name];
        $current = $this->parent;

        while ($current) {
            array_unshift($path, $current->name);
            $current = $current->parent;
        }

        return implode(' > ', $path);
    }

    public function isLeafNode(): bool
    {
        return 0 === $this->children()->count();
    }

    public function getAncestors(): Collection
    {
        $ancestors = new Collection();
        $current   = $this->parent;

        while ($current) {
            $ancestors->push($current);
            $current = $current->parent;
        }

        return $ancestors;
    }

    public function getDescendants(): Collection
    {
        $descendants = new Collection();

        foreach ($this->children as $child) {
            $descendants->push($child);

            if ($child instanceof self) {
                $descendants = $descendants->merge($child->getDescendants());
            }
        }

        return $descendants;
    }

    protected static function newFactory()
    {
        return PKWiUClassificationFactory::new();
    }
}
