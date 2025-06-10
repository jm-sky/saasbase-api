<?php

namespace App\Domain\Invoice\Controllers;

use App\Domain\Invoice\Models\NumberingTemplate;
use App\Domain\Invoice\Requests\UpdateNumberingTemplateRequest;
use App\Domain\Invoice\Resources\NumberingTemplateResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class NumberingTemplateController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = NumberingTemplate::query();

        // Optionally filter by tenant/global here if needed
        return NumberingTemplateResource::collection($query->get());
    }

    public function update(UpdateNumberingTemplateRequest $request, NumberingTemplate $numberingTemplate): NumberingTemplateResource
    {
        $numberingTemplate->update($request->validated());

        return new NumberingTemplateResource($numberingTemplate);
    }

    public function destroy(NumberingTemplate $numberingTemplate): JsonResponse
    {
        $numberingTemplate->delete();

        return response()->json(['message' => 'Numbering template deleted successfully.'], Response::HTTP_NO_CONTENT);
    }

    public function setDefault(NumberingTemplate $numberingTemplate): NumberingTemplateResource
    {
        DB::transaction(function () use ($numberingTemplate) {
            NumberingTemplate::query()
                ->where('tenant_id', $numberingTemplate->tenant_id)
                ->update(['is_default' => false])
            ;
            $numberingTemplate->is_default = true;
            $numberingTemplate->save();
        });

        return new NumberingTemplateResource($numberingTemplate);
    }
}
