<?php

namespace App\Domain\Subscription\Controllers;

use App\Domain\Subscription\Models\AddonPackage;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AddonPackageController extends Controller
{
    public function index(Request $request)
    {
        // TODO: Add filtering, pagination, etc.
        return AddonPackage::paginate();
    }

    public function show(string $id)
    {
        $addonPackage = AddonPackage::findOrFail($id);

        return response()->json($addonPackage);
    }
}
