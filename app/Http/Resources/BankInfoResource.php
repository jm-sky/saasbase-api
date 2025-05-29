<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankInfoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'bankName'   => $this['bankName'],
            'branchName' => $this['branchName'],
            'swift'      => $this['swift'],
        ];
    }
}
