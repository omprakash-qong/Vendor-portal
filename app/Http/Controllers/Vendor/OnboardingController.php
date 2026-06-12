<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\VendorOnboardingRequest;
use App\Models\VendorProfile;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    public function index()
    {
        return view('vendor.onboarding');
    }

    public function submit(VendorOnboardingRequest $request)
    {
        $data  = $request->validated();
        $email = $data['primary_email'];

        // Block active or approved applications
        $active = VendorProfile::where('primary_email', $email)
            ->whereIn('submission_status', ['pending_review', 'approved'])
            ->exists();

        if ($active) {
            return back()
                ->withErrors(['primary_email' => 'An application with this email is already under review or has been approved. Please contact procurement@qong.com if you believe this is an error.'])
                ->withInput();
        }

        // Use email slug as storage path (no user_id yet)
        $pathPrefix = 'vendor/applications/' . Str::slug($email);

        $fileFields = [
            'company_logo', 'msme_certificate', 'authorization_letter',
            'iso_certificate', 'company_brochure', 'incorporation_cert',
            'dealer_certificate', 'quality_certificate_file',
        ];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store("{$pathPrefix}/{$field}", 'public');
            }
        }

        if ($request->hasFile('additional_certs')) {
            $paths = [];
            foreach ($request->file('additional_certs') as $file) {
                $paths[] = $file->store("{$pathPrefix}/additional_certs", 'public');
            }
            $data['additional_certs'] = $paths;
        }

        $data['submission_status'] = 'pending_review';
        $data['submitted_at']      = now();
        // Clear previous rejection feedback on resubmission
        $data['admin_notes']       = null;
        $data['reviewed_at']       = null;

        // If vendor was previously rejected, update that record instead of inserting a duplicate
        // (the unique index on primary_email would prevent a second INSERT)
        $existing = VendorProfile::where('primary_email', $email)
            ->where('submission_status', 'rejected')
            ->first();

        if ($existing) {
            // Strip user_id from submitted data so we never overwrite it from form input
            unset($data['user_id']);
            $existing->update($data);
        } else {
            // New application — ensure user_id cannot be injected from the form
            unset($data['user_id']);
            VendorProfile::create($data);
        }

        return redirect()->route('vendor.apply.success');
    }

    public function approvalStatus()
    {
        $vendor = VendorProfile::where('user_id', auth()->id())->first();
        return view('vendor.approval', compact('vendor'));
    }
}
