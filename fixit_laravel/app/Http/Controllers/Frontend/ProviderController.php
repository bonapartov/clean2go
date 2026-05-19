<?php

namespace App\Http\Controllers\Frontend;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\SeoSetting;
use App\Models\User;

class ProviderController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $providers = Helpers::getProviders();
        $providers = $providers->paginate(Helpers::getThemeOptions()['pagination']['provider_list_per_page']);

         // Get SEO settings for blog list page
        $locale = app()->getLocale();
        $seoSetting = SeoSetting::where('page_slug', 'provider-detail')->where('is_active', true)->first();
        return view('frontend.provider.index', [
            'providers' => $providers,
            'seoSetting' => $seoSetting
        ]);
    }

    public function details($slug)
    {
        $provider = User::where('slug', $slug)->with('media')->whereNull('deleted_at')?->first();
        $services = Helpers::getServiceByProviderId($provider?->id);
        $seoSetting = SeoSetting::where('page_slug', 'provider-detail')->where('is_active', true)->first();
        return view('frontend.provider.details', ['provider' => $provider, 'services' => $services,'seoSetting' => $seoSetting]);
    }
}