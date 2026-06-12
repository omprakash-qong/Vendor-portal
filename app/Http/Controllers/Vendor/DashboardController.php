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
            $vendor = auth()->user()->vendorProfile()->create([
                'legal_company_name' => (auth()->user()->name ?? 'Vendor') . ' Solutions',
                'trade_name' => auth()->user()->name ?? 'Vendor',
                'company_type' => 'Pvt Ltd',
                'primary_email' => auth()->user()->email,
                'submission_status' => 'approved',
                'terms_accepted' => true,
                'data_accurate' => true,
            ]);
        }

        $stats = [
            'total'        => \App\Models\Product::where('vendor_profile_id', $vendor->id)->count(),
            'needs_review' => \App\Models\Product::where('vendor_profile_id', $vendor->id)->where('status', 'inactive')->count(),
            'active'       => \App\Models\Product::where('vendor_profile_id', $vendor->id)->whereIn('status', ['active', 'published'])->count(),
        ];

        return view('vendor.dashboard', compact('vendor', 'stats'));
    }
}