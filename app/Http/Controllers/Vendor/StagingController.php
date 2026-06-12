<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ProductStaging;
use App\Services\Import\StagingPublishService;
use Illuminate\Http\Request;

class StagingController extends Controller
{
    public function index(Request $request)
    {
        $vendor = auth()->user()->vendorProfile;
        if (!$vendor) abort(403);

        $query = ProductStaging::forVendor($vendor->id)
            ->with('importJob')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'draft'); // default: show drafts
        }

        if ($request->filled('source') && $request->source !== 'all') {
            $query->whereHas('importJob', fn($q) => $q->where('source_type', $request->source));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('model_number', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $items      = $query->paginate(20)->withQueryString();
        $draftCount = ProductStaging::forVendor($vendor->id)->where('status', 'draft')->count();

        return view('vendor.staging.index', compact('items', 'draftCount'));
    }

    public function edit(ProductStaging $staging)
    {
        $this->authorize($staging);
        return view('vendor.staging.edit', compact('staging'));
    }

    public function update(Request $request, ProductStaging $staging)
    {
        $this->authorize($staging);

        $data = $request->validate([
            'product_name'      => 'required|string|max:255',
            'model_number'      => 'nullable|string|max:100',
            'brand'             => 'nullable|string|max:150',
            'category'          => 'nullable|string|max:100',
            'sku'               => 'nullable|string|max:100',
            'short_description' => 'nullable|string',
            'long_description'  => 'nullable|string',
            'datasheet_url'     => 'nullable|url|max:500',
        ]);

        // Handle inline spec edits passed as JSON string
        if ($request->filled('specifications_json')) {
            $decoded = json_decode($request->specifications_json, true);
            if (is_array($decoded)) {
                $data['specifications_json'] = $decoded;
            }
        }

        $staging->update($data);

        return redirect()->route('vendor.staging.index')
            ->with('success', 'Product updated.');
    }

    public function approve(ProductStaging $staging, StagingPublishService $publisher)
    {
        $this->authorize($staging);

        if ($staging->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft products can be approved.']);
        }

        $publisher->publish($staging);

        return back()->with('success', "'{$staging->product_name}' published successfully.");
    }

    public function approveBulk(Request $request, StagingPublishService $publisher)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        $vendor  = auth()->user()->vendorProfile;
        $items   = ProductStaging::whereIn('id', $request->ids)
            ->where('vendor_profile_id', $vendor->id)
            ->where('status', 'draft')
            ->get();

        $count = 0;
        foreach ($items as $item) {
            $publisher->publish($item);
            $count++;
        }

        return back()->with('success', "{$count} product(s) published successfully.");
    }

    public function reject(Request $request, ProductStaging $staging)
    {
        $this->authorize($staging);
        $staging->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->input('reason'),
        ]);
        return back()->with('success', "'{$staging->product_name}' rejected.");
    }

    public function destroy(ProductStaging $staging)
    {
        $this->authorize($staging);
        $staging->delete();
        return back()->with('success', 'Staging product deleted.');
    }

    private function authorize(ProductStaging $staging): void
    {
        $vendor = auth()->user()->vendorProfile;
        if ($staging->vendor_profile_id !== $vendor?->id) abort(403);
    }
}
