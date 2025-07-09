<?php

namespace App\Domain\Contractors\Controllers;

use App\Domain\Contractors\Models\Contractor;
use App\Domain\Contractors\Models\ContractorPreferences;
use App\Domain\Contractors\Requests\UpdateContractorPreferencesRequest;
use App\Domain\Contractors\Resources\ContractorPreferencesResource;
use App\Http\Controllers\Controller;

class ContractorPreferencesController extends Controller
{
    public function show(Contractor $contractor)
    {
        $preferences = $contractor->preferences;

        if (!$preferences) {
            $preferences = ContractorPreferences::create([
                'tenant_id'                 => $contractor->tenant_id,
                'contractor_id'             => $contractor->id,
                'default_payment_method_id' => null,
                'default_currency'          => null,
                'default_language'          => null,
                'default_payment_days'      => null,
                'default_tags'              => null,
            ]);
        }

        return new ContractorPreferencesResource($preferences);
    }

    public function update(UpdateContractorPreferencesRequest $request, Contractor $contractor)
    {
        $data        = $request->validated();
        $preferences = $contractor->preferences;

        if (!$preferences) {
            $preferences = ContractorPreferences::create([
                'tenant_id'    => $contractor->tenant_id,
                'contractor_id'=> $contractor->id,
            ]);
        }
        $preferences->update($data);

        return new ContractorPreferencesResource($preferences->fresh(['defaultPaymentMethod']));
    }
}
