<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'job_listing_id' => ['required', 'integer', 'exists:job_listings,id'],
            'application_id' => ['sometimes', 'nullable', 'integer', 'exists:applications,id'],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'contact_phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'platform' => ['sometimes', 'string', 'in:linkedin,colleague,referral,other'],
            'relationship' => ['sometimes', 'nullable', 'string', 'max:255'],
            'company' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ]);

        $referral = Referral::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'status' => Referral::STATUS_REQUESTED,
        ]);

        return back()->with('status', "Referral from {$referral->contact_name} added successfully.");
    }

    public function update(Request $request, Referral $referral): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:requested,submitted,acknowledged,ghosted,declined,hired'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ]);

        $statusTimestamps = match ($validated['status']) {
            Referral::STATUS_SUBMITTED => ['referred_at' => now()],
            Referral::STATUS_ACKNOWLEDGED, Referral::STATUS_GHOSTED, Referral::STATUS_DECLINED, Referral::STATUS_HIRED => ['responded_at' => now()],
            default => [],
        };

        $referral->update([
            ...$validated,
            ...$statusTimestamps,
        ]);

        return back()->with('status', "Referral status updated to {$referral->status_label}.");
    }

    public function destroy(Referral $referral): RedirectResponse
    {
        $referral->delete();

        return back()->with('status', 'Referral removed.');
    }
}
