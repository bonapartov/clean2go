<?php

namespace Database\Seeders;

use App\Models\ThemeOption;
use Illuminate\Database\Seeder;

class ThemeOptionSeeder extends Seeder
{
    protected $baseName;

    public function __construct()
    {
        $this->baseName = config('app.name');
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $options = $this->getThemeOptions();
        ThemeOption::updateOrCreate(['options' => $options]);
    }

    public function getThemeOptions()
    {
        return [
            'general' => [
                'header_logo' => '/frontend/images/logo/dark-logo.png',
                'favicon_icon' => '/frontend/images/logo/favicon-icon.png',
                'footer_logo' => '/frontend/images/logo/dark-logo.png',
                'site_title' => $this->baseName,
                'site_tagline' => 'Бытовые услуги на дому в Санкт-Петербурге и Гатчине',
                'breadcrumb_description' => 'Выберите услугу из категорий ниже, которая подходит под вашу задачу. Более 10 категорий и 50 услуг от проверенных исполнителей.',
                'app_store_url' => 'https://www.apple.com/ru/app-store/',
                'google_play_store_url' => 'https://play.google.com/store/apps/'
            ],
            'header' => [
                'home' => true,
                'categories' => true,
                'services' => true,
                'booking' => true,
                'blogs' => true,
            ],
            'footer' => [
                'footer_copyright' => '©' . date('Y') . ' ' . $this->baseName . ' — Bonapartov V.A.',
                'useful_link' =>
                [
                    [

                        'slug' => '/',
                        'name' => 'Главная',
                    ],
                    [

                        'slug' => 'category',
                        'name' => 'Категории',
                    ],
                    [
                        'slug' => 'service',
                        'name' => 'Услуги',
                    ],
                    [

                        'slug' => 'providers',
                        'name' => 'Мастера',
                    ],
                ],
                'pages' =>
                [
                    [
                        'slug' => 'privacy-policy',
                        'name' => 'Политика конфиденциальности',
                    ],
                    [
                        'slug' => 'terms-conditions',
                        'name' => 'Условия использования',
                    ],
                    [
                        'slug' => 'contact-us',
                        'name' => 'Контакты',
                    ],
                    [
                        'slug' => 'about-us',
                        'name' => 'О нас',
                    ],
                ],
                'others' => [
                    [
                        'slug' => 'account/profile',
                        'name' => 'Личный кабинет',
                    ],
                    [

                        'slug' => 'wishlist',
                        'name' => 'Избранное',
                    ],
                    [

                        'slug' => 'booking',
                        'name' => 'Заказы',
                    ],
                    [

                        'slug' => 'providers',
                        'name' => 'Мастера',
                    ],
                    [

                        'slug' => 'service',
                        'name' => 'Услуги',
                    ],
                ],
                'become_a_provider' => [
                    'become_a_provider_enable' => true,
                    'description' => 'Зарабатывайте больше — станьте исполнителем и предлагайте свои услуги клиентам в Санкт-Петербурге и Гатчине.',
                ],
            ],
            'contact_us' => [
                'header_title' => 'Контакты',
                'title' => 'Свяжитесь с нами',
                'description' => 'Мы становимся лучше благодаря вашим идеям, вопросам и замечаниям. Мы готовы выслушать вас — есть ли у вас предложение, возникла проблема или вы просто хотите поделиться впечатлением. Используйте форму ниже или любой другой способ связи.',
                'email' => 'info@clean2go.ru',
                'contact' => '+78121234567',
                'location' => 'Санкт-Петербург, Невский проспект, 1',
                'google_map_embed_url' => 'https://www.google.com/maps?q=59.9342802,30.3350986&z=15&output=embed',
            ],
            'pagination' => [
                'provider_per_page' => 12,
                'blog_per_page' => 12,
                'service_per_page' => 12,
                'service_list_per_page' => 12,
                'service_package_per_page' => 12,
                'categories_per_page' => 12,
                'provider_list_per_page' => 12,
            ],
            'about_us' => [
                'status' => true,
                'left_bg_image_url' => '/frontend/images/categories/electrician/7.jpg',
                'right_bg_image_url' => '/frontend/images/categories/painter/6.jpg',
                'title' => 'Наша миссия',
                'description' => "$this->baseName — это не просто платформа бытовых услуг, а надёжный партнёр в решении домашних задач. Мы стремимся:",
                'sub_title1' => 'Работать качественно:',
                'description1' => 'Выполняем работу точно и аккуратно, стабильно оправдывая ожидания клиентов.',
                'sub_title2' => 'Заботиться о клиентах:',
                'description2' => 'Даём инструменты и подсказки, которые помогают решить задачу быстро и без лишних хлопот.',
                'sub_title3' => 'Развивать сервис:',
                'description3' => 'Постоянно улучшаем платформу, чтобы клиентам и исполнителям было удобнее.',
                'sub_title4' => 'Строить доверие:',
                'description4' => 'Относимся к клиентам и исполнителям как к партнёрам — через открытое общение и честные условия.',
                'sub_title5' => 'Быть полезными городу:',
                'description5' => 'Работаем в Санкт-Петербурге и Гатчине и считаем, что наш успех связан с благополучием тех, кому мы помогаем.',
                'provider_status' => true,
                'provider_title' => 'Лучшие исполнители по рейтингу',
                'provider_ids' => [],
                'testimonial_status' => true,
                'testimonial_title' => 'Что говорят о нас клиенты',
                'banner_status' => true,
                'banners' => [
                    [
                        'title' => 'Лет на рынке',
                        'count' => '3.5',
                    ],
                    [
                        'title' => 'Положительных отзывов',
                        'count' => '520',
                    ],
                    [
                        'title' => 'Довольных клиентов',
                        'count' => '10000',
                    ],
                    [
                        'title' => 'Исполнителей в команде',
                        'count' => '60',
                    ],
                ],

            ],
            'seo' => [
                'meta_tags' => $this->baseName . ' — бытовые услуги на дому в Санкт-Петербурге',
                'meta_title' => $this->baseName . ' — уборка, ремонт и бытовые услуги в СПб и Гатчине',
                'meta_description' => 'Закажите клининг, ремонт, сантехнику, электрику и другие бытовые услуги на дому в Санкт-Петербурге и Гатчине. Проверенные исполнители, прозрачные цены, быстрый вызов через ' . $this->baseName . '.',
                'og_title' => $this->baseName . ' — бытовые услуги на дому',
                'og_description' => 'Платформа бытовых услуг ' . $this->baseName . ': уборка, ремонт, сантехника, электрика и другие услуги от проверенных исполнителей в Санкт-Петербурге и Гатчине.',
                'og_image' => null,
            ],
            'authentication' => [
                'header_logo' => '/frontend/images/logo/light-logo.png',
                'auth_images' => '/frontend/images/auth/girl.png',
                'title' => 'Добро пожаловать в ' . $this->baseName,
                'description' => 'Выбирайте нужную услугу — исполнитель приедет к вам домой или в офис.',
                'app_store_url' => 'https://www.apple.com/ru/app-store/',
                'google_play_store_url' => 'https://play.google.com/store/apps/',
            ],
            'privacy_policy'=> [
                'banners' => [

                ],
            ],
            'terms_and_conditions' =>[
                'banners' => [

                ],
            ],
        ];
    }
}
