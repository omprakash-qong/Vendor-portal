<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $vendor = auth()->user()->vendorProfile;

        if (!$vendor) {
            abort(500, 'Vendor profile not found. Please contact support.');
        }

        $stats = [
            'total'        => \App\Models\Product::where('vendor_profile_id', $vendor->id)->count(),
            'needs_review' => \App\Models\Product::where('vendor_profile_id', $vendor->id)->where('status', 'inactive')->count(),
            'active'       => \App\Models\Product::where('vendor_profile_id', $vendor->id)->whereIn('status', ['active', 'published'])->count(),
        ];

        return view('vendor.dashboard', compact('vendor', 'stats'));
    }
}