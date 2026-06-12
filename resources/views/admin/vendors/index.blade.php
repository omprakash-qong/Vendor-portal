@extends('layouts.admin')
@section('title', 'Vendor Applications')

@section('content')
<div class="page-title">Vendor Applications</div>
<div class="page-sub">Review and manage all incoming vendor applications.</div>

<div class="filter-bar">
    @foreach(['all' => 'All', 'pending_review' => 'Pending Review', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $val => $label)
        <a href="{{ route('admin.vendors.index', ['status' => $val]) }}"
           class="filter-pill {{ $status === $val ? 'active' : '' }}">
            {{ $label }}
            @if($val !== 'all')
                <span style="opacity:0.6;">({{ \App\Models\VendorProfile::where('submission_status', $val)->count() }})</span>
            @endif
        </a>
    @endforeach
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Company</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Submitted</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vendors as $vendor)
            <tr>
                <td style="color:#5a5a80;">{{ $vendor->id }}</td>
                <td>
                    <div style="font-weight:600;color:#f1f0ff;">{{ $vendor->legal_company_name ?? '—' }}</div>
                    @if($vendor->trade_name)
                        <div style="font-size:11px;color:#5a5a80;">{{ $vendor->trade_name }}</div>
                    @endif
                </td>
                <td>{{ $vendor->primary_name ?? '—' }}</td>
                <td>{{ $vendor->primary_email ?? '—' }}</td>
                <td>{{ $vendor->submitted_at?->format('d M Y') ?? '—' }}</td>
                <td>
                    @php $s = $vendor->submission_status ?? 'draft'; @endphp
                    <span class="badge badge-{{ in_array($s,['pending_review','pending']) ? 'pending' : $s }}">
                        {{ $s === 'pending_review' ? 'Pending Review' : ucfirst($s) }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('admin.vendors.show', $vendor->id) }}" class="btn btn-ghost btn-sm">View →</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center;color:#5a5a80;padding:32px;">No vendor applications found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($vendors->hasPages())
    <div style="margin-top:16px;">{{ $vendors->links() }}</div>
@endif
@endsection
