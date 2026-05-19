<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SeoSetting;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $serviceCategories = Category::getDropdownOptions();

        // Get SEO settings for home page
        $seoSetting = SeoSetting::where('page_slug', 'home-page')
            ->where('is_active', true)
            ->first();

        return view('frontend.home.index', [
            'SEOData' => new SEOData(
                title: 'Awesome News - My Project',
                description: 'Lorem Ipsum',
            ),
            'serviceCategories' => $serviceCategories,
            'seoSetting' => $seoSetting
        ]);
    }
}
