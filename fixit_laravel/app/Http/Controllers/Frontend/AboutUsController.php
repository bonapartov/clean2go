<?php

namespace App\Http\Controllers\Frontend;

use App\Models\SeoSetting;
use App\Http\Controllers\Controller;

class AboutUsController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Get SEO settings for about-us page
        $seoSetting = SeoSetting::where('page_slug', 'about-us')
            ->where('is_active', true)
            ->first();

        return view('frontend.about-us.index', [
            'seoSetting' => $seoSetting
        ]);
    }
}
