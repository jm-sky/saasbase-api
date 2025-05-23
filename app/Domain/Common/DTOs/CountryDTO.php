<?php

namespace App\Domain\Common\DTOs;

use App\Domain\Common\Models\Country;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<Country>
 *
 * @property string  $name
 * @property string  $code
 * @property string  $code3
 * @property string  $numericCode
 * @property string  $phoneCode
 * @property ?string $id             UUID
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
 * @property ?Carbon $createdAt      Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $updatedAt      Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $deletedAt      Internally Carbon, accepts/serializes ISO 8601
 */
class CountryDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $code,
        public readonly string $code3,
        public readonly string $numericCode,
        public readonly string $phoneCode,
        public readonly ?string $id = null,
        public readonly ?string $capital = null,
        public readonly ?string $currency = null,
        public readonly ?string $currencyCode = null,
        public readonly ?string $currencySymbol = null,
        public readonly ?string $tld = null,
        public readonly ?string $native = null,
        public readonly ?string $region = null,
        public readonly ?string $subregion = null,
        public readonly ?string $emoji = null,
        public readonly ?string $emojiU = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
        public ?Carbon $deletedAt = null,
    ) {
    }

    public static function fromModel(Model $model): static
    {
        /* @var Country $model */
        return new static(
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
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            name: $data['name'],
            code: $data['code'],
            code3: $data['code3'],
            numericCode: $data['numeric_code'],
            phoneCode: $data['phone_code'],
            id: $data['id'] ?? null,
            capital: $data['capital'] ?? null,
            currency: $data['currency'] ?? null,
            currencyCode: $data['currency_code'] ?? null,
            currencySymbol: $data['currency_symbol'] ?? null,
            tld: $data['tld'] ?? null,
            native: $data['native'] ?? null,
            region: $data['region'] ?? null,
            subregion: $data['subregion'] ?? null,
            emoji: $data['emoji'] ?? null,
            emojiU: $data['emoji_u'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
            deletedAt: isset($data['deleted_at']) ? Carbon::parse($data['deleted_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'code'           => $this->code,
            'code3'          => $this->code3,
            'numericCode'    => $this->numericCode,
            'phoneCode'      => $this->phoneCode,
            'capital'        => $this->capital,
            'currency'       => $this->currency,
            'currencyCode'   => $this->currencyCode,
            'currencySymbol' => $this->currencySymbol,
            'tld'            => $this->tld,
            'native'         => $this->native,
            'region'         => $this->region,
            'subregion'      => $this->subregion,
            'emoji'          => $this->emoji,
            'emojiU'         => $this->emojiU,
            'createdAt'      => $this->createdAt?->toIso8601String(),
            'updatedAt'      => $this->updatedAt?->toIso8601String(),
            'deletedAt'      => $this->deletedAt?->toIso8601String(),
        ];
    }
}
