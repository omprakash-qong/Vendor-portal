@extends('layouts.admin')
@section('title', $vendor->legal_company_name ?? 'Vendor Detail')

@php use Illuminate\Support\Facades\Storage; @endphp

@push('styles')
<style>
.two-col   { display:grid; grid-template-columns:1fr 1fr; gap:24px; align-items:start; }
.sec-title { padding:16px 24px;
             border-bottom:1px solid rgba(168,85,247,0.12);
             box-shadow: inset 4px 0 0 #a855f7;
             font-size:15px; font-weight:700; color:#c084fc; letter-spacing:1.5px; text-transform:uppercase; }
.sec-title.green { color:#4ade80; border-bottom-color:rgba(74,222,128,0.12); box-shadow: inset 4px 0 0 #4ade80; }
.sec-title.red   { color:#f87171; border-bottom-color:rgba(248,113,113,0.12); box-shadow: inset 4px 0 0 #f87171; }

/* Override card-body for more breathing room */
.card > .card-body { padding:24px; }

/* Override detail-grid for more spacing */
.detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px 24px; }
.detail-item label { font-size:14px; font-weight:700; letter-spacing:1px; text-transform:uppercase;
                     color:#5a5a80; display:block; margin-bottom:6px; }
.detail-item span  { font-size:18px; color:#c8bfed; line-height:1.5; }

.tag  { display:inline-block; background:rgba(168,85,247,0.10); border:1px solid rgba(168,85,247,0.25);
        border-radius:6px; padding:5px 14px; font-size:14px; color:#c084fc; margin:3px; }
.tag-green { background:rgba(74,222,128,0.08); border-color:rgba(74,222,128,0.2); color:#4ade80; }
.sub-label { font-size:13px; color:#5a5a80; text-transform:uppercase; letter-spacing:1px;
             margin-bottom:8px; display:block; }
.divider   { border:none; border-top:1px solid rgba(168,85,247,0.08); margin:20px 0; }
.doc-row   { display:flex; justify-content:space-between; align-items:center;
             padding:12px 16px; background:rgba(255,255,255,0.02);
             border:1px solid rgba(168,85,247,0.08); border-radius:8px; margin-bottom:10px; }
.doc-row span { font-size:15px; color:#a0a0c0; }
.action-grid { display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-top:28px; }
.card.green-border { border-color:rgba(74,222,128,0.2); }
.card.red-border   { border-color:rgba(248,113,113,0.2); }
textarea.rej-textarea {
    width:100%; background:rgba(255,255,255,0.04); border:1px solid rgba(248,113,113,0.2);
    border-radius:8px; padding:12px 14px; color:#f1f0ff; font-size:13px;
    font-family:inherit; resize:vertical; margin-bottom:14px; box-sizing:border-box; outline:none;
    line-height:1.6;
}
textarea.rej-textarea:focus { border-color:rgba(248,113,113,0.5); }
</style>
@endpush

@section('content')
@php
function docLink($path, $label) {
    if (!$path) return '<span style="color:#5a5a80;">—</span>';
    $url  = Storage::url($path);
    $icon = strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf' ? '📄' : '🖼';
    return "<a href=\"{$url}\" target=\"_blank\" style=\"color:#c084fc;font-size:13px;\">{$icon} View ↗</a>";
}
$s = $vendor->submission_status ?? 'draft';
$statusLabel = $s === 'pending_review' ? 'Pending Review' : ucfirst($s);
$statusBadge = in_array($s, ['pending_review','pending']) ? 'pending' : $s;
@endphp

{{-- PAGE HEADER --}}
<div style="display:flex;align-items:center;gap:14px;margin-bottom:24px;flex-wrap:wrap;">
    <div style="flex:1;">
        <div class="page-title">{{ $vendor->legal_company_name ?? 'Unnamed Company' }}</div>
        <div class="page-sub">Application #{{ $vendor->id }} &nbsp;·&nbsp; {{ $vendor->submitted_at?->format('d M Y, g:i A') ?? 'Not submitted' }}</div>
    </div>
    <span class="badge badge-{{ $statusBadge }}" style="font-size:12px;padding:6px 18px;">{{ $statusLabel }}</span>
</div>

{{-- MAIN TWO-COLUMN LAYOUT --}}
<div class="two-col">

{{-- ══ LEFT COLUMN ══ --}}
<div>

    {{-- Company Info --}}
    <div class="card">
        <div class="sec-title">Company Information</div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-item"><label>Legal Name</label><span>{{ $vendor->legal_company_name ?? '—' }}</span></div>
                <div class="detail-item"><label>Trade Name</label><span>{{ $vendor->trade_name ?? '—' }}</span></div>
                <div class="detail-item"><label>Company Type</label><span>{{ $vendor->company_type ?? '—' }}</span></div>
                <div class="detail-item"><label>Website</label>
                    @if($vendor->company_website)
                        <a href="{{ $vendor->company_website }}" target="_blank" style="color:#c084fc;font-size:16px;">{{ $vendor->company_website }} ↗</a>
                    @else <span>—</span> @endif
                </div>
            </div>
            @if($vendor->company_logo)
            <div style="margin-top:14px;">
                <span class="sub-label">Company Logo</span>
                <img src="{{ Storage::url($vendor->company_logo) }}" alt="Logo" style="max-height:52px;margin-top:4px;border-radius:6px;border:1px solid rgba(168,85,247,0.2);">
            </div>
            @endif
        </div>
    </div>

    {{-- Address --}}
    <div class="card">
        <div class="sec-title">Address</div>
        <div class="card-body">
            <span class="sub-label">Registered Address</span>
            <div style="font-size:18px;color:#c8bfed;line-height:1.8;">
                {{ $vendor->reg_address_line1 ?? '—' }}<br>
                @if($vendor->reg_address_line2){{ $vendor->reg_address_line2 }}<br>@endif
                {{ implode(', ', array_filter([$vendor->reg_city, $vendor->reg_state, $vendor->reg_pincode])) }}<br>
                {{ $vendor->reg_country ?? '' }}
            </div>
            @if(!$vendor->same_as_registered && $vendor->op_address_line1)
            <hr class="divider">
            <span class="sub-label">Operational Address</span>
            <div style="font-size:18px;color:#c8bfed;line-height:1.8;">
                {{ $vendor->op_address_line1 }}<br>
                @if($vendor->op_address_line2){{ $vendor->op_address_line2 }}<br>@endif
                {{ implode(', ', array_filter([$vendor->op_city, $vendor->op_state, $vendor->op_pincode])) }}<br>
                {{ $vendor->op_country ?? '' }}
            </div>
            @endif
        </div>
    </div>

    {{-- Tax & Compliance --}}
    <div class="card">
        <div class="sec-title">Tax & Compliance</div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-item"><label>VAT / GST / Tax ID</label><span style="font-family:monospace;">{{ $vendor->gstin ?? '—' }}</span></div>
                <div class="detail-item"><label>Incorporation No.</label><span style="font-family:monospace;">{{ $vendor->incorporation_number ?? '—' }}</span></div>
                <div class="detail-item"><label>MSME No.</label><span>{{ $vendor->msme ?? '—' }}</span></div>
                <div class="detail-item"><label>Intl Tax ID</label><span>{{ $vendor->tax_id_intl ?? '—' }}</span></div>
                <div class="detail-item"><label>ISO Certified</label><span>{{ $vendor->iso_certified ? ucfirst($vendor->iso_certified) : '—' }}{{ $vendor->iso_number ? " · {$vendor->iso_number}" : '' }}</span></div>
                @if($vendor->quality_certificate_number)
                <div class="detail-item"><label>Quality Cert No.</label><span style="font-family:monospace;">{{ $vendor->quality_certificate_number }}</span></div>
                @endif
            </div>
        </div>
    </div>

    {{-- Vendor Categories --}}
    <div class="card">
        <div class="sec-title">Vendor Categories</div>
        <div class="card-body">
            <div style="margin-bottom:10px;">
                @forelse((array)($vendor->vendor_category ?? []) as $cat)
                    <span class="tag">{{ $cat }}</span>
                @empty <span style="color:#5a5a80;font-size:16px;">—</span>
                @endforelse
            </div>
            @if($vendor->vendor_category_other)
                <div style="font-size:15px;color:#a0a0c0;margin-top:8px;">
                    <span class="sub-label" style="display:inline;margin-right:6px;">Other:</span>{{ $vendor->vendor_category_other }}
                </div>
            @endif
        </div>
    </div>

    {{-- Vendor / Distributor Details --}}
    @if(!empty($vendor->vendor_category))
    @php
        $vCats      = (array)($vendor->vendor_category ?? []);
        $isReseller = (bool)array_intersect(['General Reseller','Authorized Distributor'], $vCats);
        $hasDetails = $vendor->authorized_brands || $vendor->distribution_region
                   || $vendor->inventory_capability || $vendor->warehouse_availability
                   || $vendor->dealer_certificate;
    @endphp
    <div class="card" style="{{ $isReseller ? 'border-color:rgba(74,222,128,0.18);' : '' }}">
        <div class="sec-title" style="{{ $isReseller ? 'color:#4ade80;border-color:rgba(74,222,128,0.12);' : '' }}">
            {{ $isReseller ? 'Distributor / Reseller Details' : 'Vendor Details' }}
        </div>
        <div class="card-body">
            @if($hasDetails)
            <div class="detail-grid">
                @if($vendor->authorized_brands)
                <div class="detail-item"><label>Authorized Brands</label><span>{{ $vendor->authorized_brands }}</span></div>
                @endif
                @if($vendor->distribution_region)
                <div class="detail-item"><label>Distribution Region</label><span>{{ $vendor->distribution_region }}</span></div>
                @endif
                @if($vendor->inventory_capability)
                <div class="detail-item"><label>Inventory Capability</label><span>{{ ucfirst($vendor->inventory_capability) }}</span></div>
                @endif
                @if($vendor->warehouse_availability)
                <div class="detail-item"><label>Warehouse Available</label><span>{{ ucfirst($vendor->warehouse_availability) }}</span></div>
                @endif
            </div>
            @if($vendor->dealer_certificate)
            <div style="margin-top:18px;">
                <span class="sub-label">Dealer Certificate</span>
                {!! docLink($vendor->dealer_certificate,'Dealer Certificate') !!}
            </div>
            @endif
            @else
            <div style="font-size:15px;color:#5a5a80;">No additional details provided.</div>
            @endif
        </div>
    </div>
    @endif

    {{-- Industry Focus --}}
    <div class="card">
        <div class="sec-title">Industry Focus</div>
        <div class="card-body">
            <div style="margin-bottom:10px;">
                @forelse((array)($vendor->industry_focus ?? []) as $f)
                    <span class="tag">{{ $f }}</span>
                @empty <span style="color:#5a5a80;font-size:16px;">—</span>
                @endforelse
            </div>
            @if($vendor->industry_focus_other)
                <div style="font-size:15px;color:#a0a0c0;margin-top:8px;">
                    <span class="sub-label" style="display:inline;margin-right:6px;">Other:</span>{{ $vendor->industry_focus_other }}
                </div>
            @endif
        </div>
    </div>

    {{-- Sub-domains --}}
    @php $hasSub = !empty(array_filter(array_merge(
        (array)($vendor->subdomain_pumps??[]),(array)($vendor->subdomain_compressors??[]),
        (array)($vendor->subdomain_instruments??[]),(array)($vendor->subdomain_valves??[]),
        (array)($vendor->subdomain_turbines??[]),(array)($vendor->subdomain_motors??[])
    ))); @endphp
    @if($hasSub)
    <div class="card">
        <div class="sec-title">Product Sub-domains</div>
        <div class="card-body">
            @foreach(['subdomain_pumps'=>'Pumps','subdomain_compressors'=>'Compressors','subdomain_instruments'=>'Instruments','subdomain_valves'=>'Valves','subdomain_turbines'=>'Turbines','subdomain_motors'=>'Motors'] as $f=>$gl)
                @if(!empty($vendor->$f))
                <div style="margin-bottom:12px;">
                    <span class="sub-label">{{ $gl }}</span>
                    <div>@foreach((array)$vendor->$f as $item)<span class="tag" style="font-size:14px;">{{ $item }}</span>@endforeach</div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- Industry Standards --}}
    @if(!empty($vendor->industry_standards) || $vendor->other_standards)
    <div class="card">
        <div class="sec-title">Industry Standards</div>
        <div class="card-body">
            <div style="margin-bottom:10px;">
                @foreach((array)($vendor->industry_standards ?? []) as $std)
                    <span class="tag tag-green">{{ $std }}</span>
                @endforeach
            </div>
            @if($vendor->other_standards)
                <div style="margin-top:14px;padding:14px 16px;background:rgba(168,85,247,0.04);border:1px solid rgba(168,85,247,0.1);border-radius:8px;font-size:13px;color:#c0b8e0;line-height:1.7;">
                    <span class="sub-label" style="margin-bottom:6px;">Other Standards</span>
                    {{ $vendor->other_standards }}
                </div>
            @endif
        </div>
    </div>
    @endif

</div>{{-- end left --}}

{{-- ══ RIGHT COLUMN ══ --}}
<div>

    {{-- Primary Contact --}}
    <div class="card">
        <div class="sec-title">Primary Contact</div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-item"><label>Name</label><span>{{ $vendor->primary_name ?? '—' }}</span></div>
                <div class="detail-item"><label>Designation</label><span>{{ $vendor->primary_designation ?? '—' }}</span></div>
                <div class="detail-item"><label>Email</label>
                    <a href="mailto:{{ $vendor->primary_email }}" style="color:#c084fc;font-size:16px;">{{ $vendor->primary_email ?? '—' }}</a>
                </div>
                <div class="detail-item"><label>Phone</label><span>{{ $vendor->primary_phone ?? '—' }}</span></div>
            </div>
        </div>
    </div>

    {{-- Secondary Contacts --}}
    @if(!empty($vendor->secondary))
    <div class="card">
        <div class="sec-title">Additional Contacts</div>
        <div class="card-body">
            @foreach((array)$vendor->secondary as $i => $poc)
            <div style="padding:12px 14px;background:rgba(168,85,247,0.04);border:1px solid rgba(168,85,247,0.1);border-radius:8px;{{ $i > 0 ? 'margin-top:10px;' : '' }}">
                <div style="font-size:14px;font-weight:700;color:#a0a0c0;margin-bottom:8px;">Contact {{ $i+1 }}</div>
                <div class="detail-grid">
                    <div class="detail-item"><label>Name</label><span>{{ $poc['name'] ?? '—' }}</span></div>
                    <div class="detail-item"><label>Designation</label><span>{{ $poc['designation'] ?? '—' }}</span></div>
                    <div class="detail-item"><label>Email</label><span>{{ $poc['email'] ?? '—' }}</span></div>
                    <div class="detail-item"><label>Phone</label><span>{{ $poc['phone'] ?? '—' }}</span></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Uploaded Documents --}}
    <div class="card">
        <div class="sec-title">Uploaded Documents</div>
        <div class="card-body">
            @php $docs = [
                'incorporation_cert'       => 'Incorporation Certificate',
                'msme_certificate'         => 'MSME Certificate',
                'authorization_letter'     => 'Authorization Letter',
                'iso_certificate'          => 'ISO Certificate',
                'quality_certificate_file' => 'Quality Certificate',
                'company_brochure'         => 'Company Brochure',
                'dealer_certificate'       => 'Dealer / Authorization Certificate',
            ]; @endphp
            @foreach($docs as $field => $label)
            <div class="doc-row">
                <span>{{ $label }}</span>
                {!! docLink($vendor->$field, $label) !!}
            </div>
            @endforeach
            @if(!empty($vendor->additional_certs))
            <div style="padding:10px 14px;background:rgba(255,255,255,0.02);border:1px solid rgba(168,85,247,0.08);border-radius:8px;margin-bottom:8px;">
                <div style="font-size:14px;color:#a0a0c0;margin-bottom:8px;">Additional Certificates</div>
                @foreach((array)$vendor->additional_certs as $i => $path)
                    <a href="{{ Storage::url($path) }}" target="_blank" style="display:block;color:#c084fc;font-size:13px;margin-bottom:4px;">📄 Certificate {{ $i+1 }} ↗</a>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Declarations --}}
    <div class="card">
        <div class="sec-title">Declarations</div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-item"><label>Terms Accepted</label>
                    <span style="color:{{ $vendor->terms_accepted ? '#4ade80' : '#f87171' }};">{{ $vendor->terms_accepted ? '✔ Yes' : '✖ No' }}</span>
                </div>
                <div class="detail-item"><label>Data Accuracy Certified</label>
                    <span style="color:{{ $vendor->data_accurate ? '#4ade80' : '#f87171' }};">{{ $vendor->data_accurate ? '✔ Yes' : '✖ No' }}</span>
                </div>
            </div>
        </div>
    </div>

</div>{{-- end right --}}
</div>{{-- end two-col --}}

{{-- ══ ACTION PANEL ══ --}}
@if(in_array($vendor->submission_status, ['pending_review','pending']))
<div class="action-grid">

    <div class="card green-border">
        <div class="sec-title green">Approve Vendor</div>
        <div class="card-body">
            <p style="font-size:13px;color:#9d8ec4;margin:0 0 16px;">
                Creates a login account and sends a secure access link to
                <strong style="color:#f1f0ff;">{{ $vendor->primary_email }}</strong>.
            </p>
            <form action="{{ route('admin.vendors.approve', $vendor->id) }}" method="POST"
                  onsubmit="return confirm('Approve and send access link to {{ $vendor->primary_email }}?');">
                @csrf
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">✓ Approve &amp; Send Access Link</button>
            </form>
        </div>
    </div>

    <div class="card red-border">
        <div class="sec-title red">Reject Application</div>
        <div class="card-body">
            <form action="{{ route('admin.vendors.reject', $vendor->id) }}" method="POST">
                @csrf
                <label style="font-size:14px;color:#5a5a80;letter-spacing:1px;text-transform:uppercase;display:block;margin-bottom:8px;">
                    Reason for Rejection <span style="color:#f87171;">(*)</span>
                </label>
                <textarea name="admin_notes" required rows="4" class="rej-textarea"
                    placeholder="Explain why this application is rejected — this will be emailed to the applicant."></textarea>
                @error('admin_notes')
                    <div style="color:#f87171;font-size:12px;margin-bottom:8px;">{{ $message }}</div>
                @enderror
                <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center;">✖ Reject &amp; Notify Applicant</button>
            </form>
        </div>
    </div>

</div>

@elseif($vendor->submission_status === 'rejected')
<div class="card" style="margin-top:24px;border-color:rgba(248,113,113,0.2);">
    <div class="sec-title red">Rejection Details</div>
    <div class="card-body">
        <div class="detail-grid">
            <div class="detail-item"><label>Rejected On</label><span>{{ $vendor->reviewed_at?->format('d M Y') ?? '—' }}</span></div>
            <div class="detail-item"><label>Reason</label><span>{{ $vendor->admin_notes ?? '—' }}</span></div>
        </div>
    </div>
</div>

@elseif($vendor->submission_status === 'approved')
<div class="card" style="margin-top:24px;border-color:rgba(74,222,128,0.2);">
    <div class="card-body" style="display:flex;align-items:center;gap:20px;">
        <div style="font-size:38px;">✅</div>
        <div>
            <div style="font-size:15px;font-weight:700;color:#4ade80;">Approved on {{ $vendor->reviewed_at?->format('d M Y') }}</div>
            <div style="font-size:15px;color:#5a5a80;margin-top:4px;">Access link was sent to {{ $vendor->primary_email }}</div>
        </div>
    </div>
</div>
@endif

{{-- Bottom back button --}}
<div style="margin-top:32px;padding-top:24px;border-top:1px solid rgba(168,85,247,0.1);">
    <a href="{{ route('admin.vendors.index') }}" class="btn btn-ghost btn-sm">← Back to Applications</a>
</div>

@endsection
