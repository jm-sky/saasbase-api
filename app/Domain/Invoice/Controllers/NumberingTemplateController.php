<?php

namespace App\Domain\Invoice\Controllers;

use App\Domain\Invoice\Models\NumberingTemplate;
use App\Domain\Invoice\Requests\PreviewNumberingTemplateRequest;
use App\Domain\Invoice\Requests\StoreNumberingTemplateRequest;
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

    public function store(StoreNumberingTemplateRequest $request): JsonResponse
    {
        $template = NumberingTemplate::create($request->validated());

        return response()->json([
            'data' => new NumberingTemplateResource($template),
        ], Response::HTTP_CREATED);
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
                ->where('invoice_type', $numberingTemplate->invoice_type)
                ->update(['is_default' => false])
            ;
            $numberingTemplate->is_default = true;
            $numberingTemplate->save();
        });

        return new NumberingTemplateResource($numberingTemplate);
    }

    public function preview(PreviewNumberingTemplateRequest $request): JsonResponse
    {
        $format     = $request->input('format');
        $nextNumber = $request->input('nextNumber');
        $prefix     = $request->input('prefix', '');
        $suffix     = $request->input('suffix', '');

        $now   = now();
        $year  = $now->format('Y');
        $month = $now->format('m');
        // Zero-pad nextNumber to 3 digits
        $number = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $preview = $format;
        $preview = str_replace('YYYY', $year, $preview);
        $preview = str_replace('MM', $month, $preview);
        $preview = str_replace('NNN', $number, $preview);

        if ($prefix) {
            $preview = $prefix . $preview;
        }

        if ($suffix) {
            $preview = $preview . $suffix;
        }

        return response()->json([
            'preview' => $preview,
        ]);
    }
}
