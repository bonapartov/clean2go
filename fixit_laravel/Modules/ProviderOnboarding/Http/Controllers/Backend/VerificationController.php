<?php

namespace Modules\ProviderOnboarding\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\ProviderOnboarding\Models\OnboardingLog;
use Modules\ProviderOnboarding\Models\ProviderVerification;

class VerificationController extends Controller
{
    public function index(Request $request): View
    {
        $query = ProviderVerification::with('user')
            ->when($request->input('status'), fn($q, $v) => $q->where('onboarding_status', $v))
            ->when($request->input('type'), fn($q, $v) => $q->where('taxpayer_type', $v))
            ->latest();

        $verifications = $query->paginate(20);

        return view('backend.onboarding.verifications.index', compact('verifications'));
    }

    public function show(int $id): View
    {
        $verification = ProviderVerification::with(['user', 'reviewer'])->findOrFail($id);
        $logs = OnboardingLog::where('user_id', $verification->user_id)->latest('created_at')->get();

        return view('backend.onboarding.verifications.show', compact('verification', 'logs'));
    }

    public function approve(int $id): RedirectResponse
    {
        $verification = ProviderVerification::findOrFail($id);
        $verification->update([
            'onboarding_status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);

        $verification->user->update(['onboarding_completed' => true]);

        OnboardingLog::record($verification->user_id, 'admin_review', 'approved', [
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('backend.verifications.show', $id)
            ->with('success', 'Заявка одобрена.');
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'min:10']]);

        $verification = ProviderVerification::findOrFail($id);
        $verification->update([
            'onboarding_status' => 'rejected',
            'rejection_reason' => $request->input('reason'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $verification->user->update(['onboarding_blocked' => true, 'onboarding_blocked_reason' => $request->input('reason')]);

        OnboardingLog::record($verification->user_id, 'admin_review', 'rejected', [
            'reason' => $request->input('reason'),
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('backend.verifications.show', $id)
            ->with('success', 'Заявка отклонена.');
    }

    public function requestDocs(Request $request, int $id): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'min:10']]);

        $verification = ProviderVerification::findOrFail($id);
        $verification->update([
            'manual_review_reason' => $request->input('reason'),
            'onboarding_status' => 'pending_manual',
        ]);

        OnboardingLog::record($verification->user_id, 'admin_review', 'request_docs', [
            'reason' => $request->input('reason'),
        ]);

        return redirect()->route('backend.verifications.show', $id)
            ->with('success', 'Запрос на доп. документы отправлен.');
    }
}
