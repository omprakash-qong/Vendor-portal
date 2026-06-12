<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QuotationController extends Controller
{
    private function vendor()
    {
        $vendor = auth()->user()->vendorProfile;
        if (!$vendor) {
            abort(403, 'Vendor profile not found.');
        }
        return $vendor;
    }

    public function index()
    {
        $vendor = $this->vendor();

        $quotations = Quotation::where('vendor_profile_id', $vendor->id)
            ->latest()
            ->paginate(15);

        return view('vendor.quotations.index', compact('quotations'));
    }

    public function create()
    {
        return view('vendor.quotations.create');
    }

    public function store(Request $request)
    {
        $vendor = $this->vendor();

        $data = $request->validate([
            'customer_name' => 'required|string|max:255',
            'subject'       => 'nullable|string|max:255',
            'remarks'       => 'nullable|string|max:2000',
            'attachment'    => 'required|file|mimes:pdf|max:20480',
        ], [
            'attachment.required' => 'Please attach the quotation PDF.',
            'attachment.mimes'    => 'The quotation must be a PDF file.',
        ]);

        $file = $request->file('attachment');

        Quotation::create([
            'vendor_profile_id' => $vendor->id,
            'customer_name'     => $data['customer_name'],
            'subject'           => $data['subject'] ?? null,
            'remarks'           => $data['remarks'] ?? null,
            'attachment_path'   => $file->store('quotations/attachments', 'public'),
            'original_filename' => $file->getClientOriginalName(),
            'status'            => 'draft',
        ]);

        return redirect()->route('vendor.quotations.index')
            ->with('success', 'Quotation saved. It is ready to send to your customer.');
    }

    public function download(Quotation $quotation)
    {
        $vendor = $this->vendor();
        if ($quotation->vendor_profile_id !== $vendor->id) {
            abort(403);
        }
        if (!$quotation->attachment_path || !Storage::disk('public')->exists($quotation->attachment_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download(
            $quotation->attachment_path,
            $quotation->original_filename ?? 'quotation.pdf'
        );
    }

    public function destroy(Quotation $quotation)
    {
        $vendor = $this->vendor();
        if ($quotation->vendor_profile_id !== $vendor->id) {
            abort(403);
        }

        if ($quotation->attachment_path) {
            Storage::disk('public')->delete($quotation->attachment_path);
        }
        $quotation->delete();

        return redirect()->route('vendor.quotations.index')
            ->with('success', 'Quotation deleted.');
    }
}
