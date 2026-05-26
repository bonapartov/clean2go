<?php

namespace Database\Seeders;

use App\Models\SystemLang;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Session;

class SystemLangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $systemLangs = [
            [
                'name' => 'Russian',
                'locale' => 'ru',
                'app_locale' => 'ru_RU',
                'is_rtl' => 0,
                'system_reserve' => 1,
                'status' => 1,
            ],
        ];

        foreach ($systemLangs as $lang) {
            SystemLang::create($lang);
        }

        Session::put('locale', 'ru');
        app()->setLocale('ru');
    }
}
