<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\StoreQuotationRequest;
use App\Models\Rfq;
use App\Models\Quotation;
use Illuminate\Http\Request;

class RfqController extends Controller
{
    public function index()
    {
        $vendor = auth()->user()->vendorProfile;
        if (!$vendor) {
            abort(403, 'Vendor profile not found.');
        }

        $rfqs = Rfq::where('vendor_profile_id', $vendor->id)
            ->latest()
            ->paginate(10, ['*'], 'rfqs_page');

        $quotations = Quotation::where('vendor_profile_id', $vendor->id)
            ->with('rfq')
            ->latest()
            ->paginate(10, ['*'], 'quotations_page');

        return view('vendor.rfqs.index', compact('rfqs', 'quotations'));
    }

    public function show(Rfq $rfq)
    {
        $vendor = auth()->user()->vendorProfile;
        if ($rfq->vendor_profile_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this RFQ.');
        }

        $quotation = $rfq->quotation;

        return view('vendor.rfqs.show', compact('rfq', 'quotation'));
    }

    public function submitQuotation(StoreQuotationRequest $request, Rfq $rfq)
    {
        $vendor = auth()->user()->vendorProfile;
        if ($rfq->vendor_profile_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this RFQ.');
        }

        if ($rfq->quotation) {
            return back()->with('error', 'You have already submitted a quotation for this RFQ.');
        }

        $data = $request->validated();
        $data['rfq_id'] = $rfq->id;
        $data['vendor_profile_id'] = $vendor->id;

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('quotations/attachments', 'public');
        }

        Quotation::create($data);

        return redirect()->route('vendor.rfqs.show', $rfq->id)->with('success', 'Quotation submitted successfully.');
    }
}
