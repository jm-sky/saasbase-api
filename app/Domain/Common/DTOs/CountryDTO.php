<?php

namespace App\Domain\Common\DTOs;

use App\Domain\Common\Models\Country;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
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
 * @property ?Carbon $createdAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $updatedAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $deletedAt Internally Carbon, accepts/serializes ISO 8601
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
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $createdAt = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $updatedAt = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $deletedAt = null,
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
            emojiU: $model->emojiU,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
        );
    }
}
