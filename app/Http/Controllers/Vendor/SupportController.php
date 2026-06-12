<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\StoreSupportTicketRequest;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index()
    {
        $vendor = auth()->user()->vendorProfile;
        if (!$vendor) {
            abort(403, 'Vendor profile not found.');
        }

        $tickets = SupportTicket::where('vendor_profile_id', $vendor->id)
            ->latest()
            ->paginate(10);

        return view('vendor.support.index', compact('tickets'));
    }

    public function store(StoreSupportTicketRequest $request)
    {
        $vendor = auth()->user()->vendorProfile;
        if (!$vendor) {
            abort(403, 'Vendor profile not found.');
        }

        $data = $request->validated();
        $data['status'] = 'open';

        $vendor->supportTickets()->create($data);

        return redirect()->route('vendor.support.index')->with('success', 'Support ticket raised successfully. Our team will get back to you shortly.');
    }
}
