<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\StoreDatasheetRequest;
use App\Models\Datasheet;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DatasheetController extends Controller
{
    public function index()
    {
        $vendor = auth()->user()->vendorProfile;
        if (!$vendor) {
            abort(403, 'Vendor profile not found.');
        }

        $datasheets = Datasheet::where('vendor_profile_id', $vendor->id)
            ->with('product')
            ->latest()
            ->paginate(10);

        $products = Product::where('vendor_profile_id', $vendor->id)->get();

        return view('vendor.datasheets.index', compact('datasheets', 'products'));
    }

    public function store(StoreDatasheetRequest $request)
    {
        $vendor = auth()->user()->vendorProfile;
        if (!$vendor) {
            abort(403, 'Vendor profile not found.');
        }

        $data = $request->validated();

        // Verify the product belongs to this vendor
        $product = Product::where('vendor_profile_id', $vendor->id)->where('id', $data['product_id'])->first();
        if (!$product) {
            return back()->with('error', 'Invalid product selected.');
        }

        if ($request->hasFile('pdf')) {
            $data['pdf_path'] = $request->file('pdf')->store('datasheets/pdfs', 'public');
        }

        $vendor->datasheets()->create($data);

        return redirect()->route('vendor.datasheets.index')->with('success', 'Datasheet uploaded successfully.');
    }

    public function replace(StoreDatasheetRequest $request, Datasheet $datasheet)
    {
        $vendor = auth()->user()->vendorProfile;
        if ($datasheet->vendor_profile_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this datasheet.');
        }

        $data = $request->validated();

        // Verify the product belongs to this vendor
        $product = Product::where('vendor_profile_id', $vendor->id)->where('id', $data['product_id'])->first();
        if (!$product) {
            return back()->with('error', 'Invalid product selected.');
        }

        if ($request->hasFile('pdf')) {
            // Delete old file
            if ($datasheet->pdf_path) {
                Storage::disk('public')->delete($datasheet->pdf_path);
            }
            $data['pdf_path'] = $request->file('pdf')->store('datasheets/pdfs', 'public');
        }

        $datasheet->update($data);

        return redirect()->route('vendor.datasheets.index')->with('success', 'Datasheet replaced successfully.');
    }

    public function destroy(Datasheet $datasheet)
    {
        $vendor = auth()->user()->vendorProfile;
        if ($datasheet->vendor_profile_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this datasheet.');
        }

        $datasheet->delete();

        return redirect()->route('vendor.datasheets.index')->with('success', 'Datasheet deleted successfully.');
    }
}
