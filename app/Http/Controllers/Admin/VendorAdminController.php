<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\VendorCredentialsMail;
use App\Mail\VendorRejectionMail;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class VendorAdminController extends Controller
{
    public function index(Request $request)
    {
        $status  = $request->query('status', 'all');
        $query   = VendorProfile::latest();

        if ($status !== 'all') {
            $query->where('submission_status', $status);
        }

        $vendors = $query->paginate(20)->withQueryString();

        return view('admin.vendors.index', compact('vendors', 'status'));
    }

    public function show($id)
    {
        $vendor = VendorProfile::findOrFail($id);
        return view('admin.vendors.show', compact('vendor'));
    }

    public function approve(Request $request, $id)
    {
        $vendor = VendorProfile::findOrFail($id);

        if ($vendor->submission_status === 'approved') {
            return back()->with('error', 'This vendor is already approved.');
        }

        // Guard against duplicate user accounts (e.g. re-approval attempt)
        if (User::where('email', $vendor->primary_email)->exists()) {
            return back()->with('error', 'A login account already exists for this email. Please contact support if this is unexpected.');
        }

        // Create vendor login account with a strong random password
        $user = User::create([
            'name'     => $vendor->primary_name,
            'email'    => $vendor->primary_email,
            'password' => Str::random(32), // placeholder — vendor will set own password via reset link
            'role'     => 'vendor',
        ]);

        $vendor->update([
            'user_id'           => $user->id,
            'submission_status' => 'approved',
            'reviewed_at'       => now(),
        ]);

        // Generate a one-time password reset link so vendor sets their own password
        // (never expose a plaintext password over email)
        $resetLink = $this->generatePasswordResetLink($user);

        Mail::to($vendor->primary_email)->send(new VendorCredentialsMail($user, $resetLink));

        return redirect()->route('admin.vendors.index')
            ->with('success', "Vendor approved. Login setup link sent to {$vendor->primary_email}.");
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['admin_notes' => 'required|string|max:1000']);

        $vendor = VendorProfile::findOrFail($id);

        if ($vendor->submission_status === 'rejected') {
            return back()->with('error', 'This application has already been rejected.');
        }

        $vendor->update([
            'submission_status' => 'rejected',
            'admin_notes'       => $request->admin_notes,
            'reviewed_at'       => now(),
        ]);

        Mail::to($vendor->primary_email)->send(new VendorRejectionMail($vendor));

        return redirect()->route('admin.vendors.index')
            ->with('success', 'Vendor application rejected. Notification sent to applicant.');
    }

    private function generatePasswordResetLink(User $user): string
    {
        $token = Password::broker()->createToken($user);
        return url(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ], false));
    }
}
