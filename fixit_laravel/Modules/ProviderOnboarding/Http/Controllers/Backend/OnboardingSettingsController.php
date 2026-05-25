<?php

namespace Modules\ProviderOnboarding\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\ProviderOnboarding\Models\IntegrationSetting;

class OnboardingSettingsController extends Controller
{
    private array $groups = ['general', 'fns', 'npd', 'passport', 'fssp', 'contract'];

    public function index(): View
    {
        $settings = IntegrationSetting::orderBy('group')->orderBy('key')->get()
            ->groupBy('group');

        return view('backend.onboarding.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->except(['_token', '_method']);

        foreach ($data as $key => $value) {
            $setting = IntegrationSetting::where('key', $key)->first();
            if (!$setting) continue;

            // password-поля: пропускаем пустые (не перетираем существующий ключ)
            if ($setting->type === 'password' && empty($value)) continue;

            IntegrationSetting::set($key, (string) $value, auth()->id());
        }

        return redirect()->route('backend.onboarding.settings')
            ->with('success', 'Настройки сохранены.');
    }
}
