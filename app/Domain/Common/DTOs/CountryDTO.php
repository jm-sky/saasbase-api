<?php

namespace App\Domain\Common\DTOs;

use App\Domain\Common\Models\Country;
use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $name
 * @property string $code
 * @property string $code3
 * @property string $numericCode
 * @property string $phoneCode
 * @property ?string $capital
 * @property ?string $currency
 * @property ?string $currencyCode
 * @property ?string $currencySymbol
 * @property ?string $tld
 * @property ?string $native
 * @property ?string $region
 * @property ?string $subregion
 * @property ?string $emoji
 * @property ?string $emojiU
 * @property ?string $createdAt
 * @property ?string $updatedAt
 * @property ?string $deletedAt
 */
class CountryDTO extends Data
{
    public function __construct(
        public string $name,
        public string $code,
        public string $code3,
        public string $numericCode,
        public string $phoneCode,
        public ?string $id = null,
        public ?string $capital = null,
        public ?string $currency = null,
        public ?string $currencyCode = null,
        public ?string $currencySymbol = null,
        public ?string $tld = null,
        public ?string $native = null,
        public ?string $region = null,
        public ?string $subregion = null,
        public ?string $emoji = null,
        public ?string $emojiU = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
    ) {}

    public static function fromModel(Country $model): self
    {
        return new self(
            name: $model->name,
            code: $model->code,
            code3: $model->code3,
            numericCode: $model->numeric_code,
            phoneCode: $model->phone_code,
            id: $model->id,
            capital: $model->capital,
            currency: $model->currency,
            currencyCode: $model->currency_code,
            currencySymbol: $model->currency_symbol,
            tld: $model->tld,
            native: $model->native,
            region: $model->region,
            subregion: $model->subregion,
            emoji: $model->emoji,
            emojiU: $model->emoji_u,
            createdAt: $model->created_at?->format('Y-m-d H:i:s'),
            updatedAt: $model->updated_at?->format('Y-m-d H:i:s'),
            deletedAt: $model->deleted_at?->format('Y-m-d H:i:s'),
        );
    }
}
