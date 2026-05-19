<?php

namespace Database\Seeders;

use App\Models\SeoSetting;
use Illuminate\Database\Seeder;

class SeoSettingSeeder extends Seeder
{
    public function run()
    {
        $pages = [
            [
                'page_name' => 'Home Page',
                'page_slug' => 'home-page',
            ],
            [
                'page_name' => 'Service List Page',
                'page_slug' => 'service-list',
            ],
            [
                'page_name' => 'Service Detail Page',
                'page_slug' => 'service-detail',
            ],
            [
                'page_name' => 'Category List Page',
                'page_slug' => 'category-list',
            ],
            [
                'page_name' => 'Blog List Page',
                'page_slug' => 'blog-list',
            ],
            [
                'page_name' => 'Blog Detail Page',
                'page_slug' => 'blog-detail',
            ],
            [
                'page_name' => 'Provider List Page',
                'page_slug' => 'provider-list',
            ],
            [
                'page_name' => 'Provider Detail Page',
                'page_slug' => 'provider-detail',
            ],
            [
                'page_name' => 'Service Package List Page',
                'page_slug' => 'service-package-list',
            ],
            [
                'page_name' => 'Service Package Detail Page',
                'page_slug' => 'service-package-detail',
            ],
            [
                'page_name' => 'Privacy Policy Page',
                'page_slug' => 'privacy-policy',
            ],
            [
                'page_name' => 'Terms & Conditions Page',
                'page_slug' => 'terms-conditions',
            ],
            [
                'page_name' => 'Contact Us Page',
                'page_slug' => 'contact-us',
            ],
            [
                'page_name' => 'About Us Page',
                'page_slug' => 'about-us',
            ],
            [
                'page_name' => 'Provider Sign Up',
                'page_slug' => 'become-provider',
            ],
        ];

        foreach ($pages as $page) {
            SeoSetting::firstOrCreate(
                ['page_slug' => $page['page_slug']],
                $page
            );
        }
    }
}
