<?php

namespace App\Domain\Utils\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankInfoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'iban'       => $this['iban'],
            'bankName'   => $this['bankName'],
            'branchName' => $this['branchName'],
            'swift'      => $this['swift'],
            'currency'   => $this['currency'],
        ];
    }
}
