<?php

namespace App\Domain\Subscription\Controllers;

use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Subscription\Models\SubscriptionPlan;
use App\Domain\Subscription\Resources\SubscriptionPlanResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Spatie\QueryBuilder\AllowedFilter;

class SubscriptionPlanController extends Controller
{
    use HasIndexQuery;

    protected int $defaultPerPage = 15;

    public function __construct()
    {
        $this->modelClass  = SubscriptionPlan::class;
        $this->defaultWith = ['features.feature', 'prices', 'subscriptions.billable'];

        $this->filters = [
            AllowedFilter::custom('search', new ComboSearchFilter(['name', 'description'])),
            AllowedFilter::custom('id', new AdvancedFilter()),
            AllowedFilter::custom('name', new AdvancedFilter()),
            AllowedFilter::custom('description', new AdvancedFilter()),
            AllowedFilter::custom('isActive', new AdvancedFilter(['is_active' => 'boolean']), 'is_active'),
            AllowedFilter::custom('billingPeriod', new AdvancedFilter(), 'billing_period'),
            AllowedFilter::custom('createdAt', new AdvancedFilter(), 'created_at'),
            AllowedFilter::custom('updatedAt', new AdvancedFilter(), 'updated_at'),
        ];

        $this->sorts = [
            'name',
            'isActive'  => 'is_active',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];

        $this->defaultSort = '-created_at';
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $request->merge([
            'filter' => [
                'isActive' => $request->has('isActive') ? $request->boolean('isActive') : true,
            ],
        ]);

        $plans = $this->getIndexPaginator($request);

        return SubscriptionPlanResource::collection($plans['data'])
            ->additional(['meta' => $plans['meta']])
        ;
    }

    public function show(string $id)
    {
        $plan = SubscriptionPlan::with(['features.feature', 'prices', 'subscriptions.billable'])
            ->findOrFail($id)
        ;

        return new SubscriptionPlanResource($plan);
    }
}
