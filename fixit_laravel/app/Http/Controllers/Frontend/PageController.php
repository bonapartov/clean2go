<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\SeoSetting;

class PageController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function privacy()
    {
        $seoSetting = SeoSetting::where('page_slug', 'privacy-policy')->where('is_active', true)->first();
        return view('frontend.page.privacy',[
            'seoSetting' => $seoSetting
        ]);
    }

    public function terms()
    {
        $seoSetting = SeoSetting::where('page_slug', 'terms-conditions')->where('is_active', true)->first();
        return view('frontend.page.terms',[
            'seoSetting' => $seoSetting
        ]);
    }

    public function details($slug)
    {
        $page = Page::where('slug', $slug)->whereNull('deleted_at')?->first();

        if(!$page) {
            abort(404);
        }
        return view('frontend.page.details', ['page' => $page]);
    }
}
