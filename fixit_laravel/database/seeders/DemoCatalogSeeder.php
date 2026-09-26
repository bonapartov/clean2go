<?php

namespace Database\Seeders;

use App\Enums\CategoryType;
use App\Enums\RoleEnum;
use App\Enums\ServiceTypeEnum;
use App\Enums\UserTypeEnum;
use App\Helpers\Helpers;
use App\Models\Category;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Role as RoleModel;
use App\Models\Service;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Демо-каталог для тестирования модели: 10 топовых по спросу категорий
 * бытовых услуг (клининг, ремонт, сантехника, электрика и т.д.) по 5 услуг
 * в каждой, плюс 12 демо-исполнителей (5 самозанятых, 5 ИП, 2 ООО) —
 * авторы этих услуг. Картинки — реальные тематические фото с открытой
 * лицензией (Openverse/Wikimedia), не постановочные фото конкретных людей;
 * аватары исполнителей и логотипы ООО — сгенерированы локально (GD,
 * инициалы на цветном фоне), а не чьи-то настоящие фотографии.
 *
 * Идемпотентен: повторный запуск пропускает уже существующих
 * провайдеров/категории/услуги (сверка по имени/названию).
 *
 * Создано 2026-09-26 по запросу пользователя.
 */
class DemoCatalogSeeder extends Seeder
{
    private string $img;
    private string $ava;

    public function run(): void
    {
        $this->img = __DIR__ . '/assets/catalog/categories';
        $this->ava = __DIR__ . '/assets/catalog/avatars';

        $providers = $this->seedProviders();
        $this->seedCatalog($providers);
    }

    /**
     * @return array<int, User>
     */
    private function seedProviders(): array
    {
        $providerRole = RoleModel::where('name', RoleEnum::PROVIDER)->first();
        $allZoneIds = Zone::pluck('id')->all();
        $phoneBase = 79001000000;
        $idx = 0;

        $freelancers = [
            ['Демо Иванов', 'avatar_demo_ivanov.jpg'],
            ['Демо Петров', 'avatar_demo_petrov.jpg'],
            ['Демо Сидоров', 'avatar_demo_sidorov.jpg'],
            ['Демо Кузнецова', 'avatar_demo_kuznecova.jpg'],
            ['Демо Смирнова', 'avatar_demo_smirnova.jpg'],
        ];

        $individualEntrepreneurs = [
            ['ИП Демо Волков', 'avatar_ip_volkov.jpg'],
            ['ИП Демо Морозов', 'avatar_ip_morozov.jpg'],
            ['ИП Демо Соколова', 'avatar_ip_sokolova.jpg'],
            ['ИП Демо Новикова', 'avatar_ip_novikova.jpg'],
            ['ИП Демо Лебедев', 'avatar_ip_lebedev.jpg'],
        ];

        $companies = [
            ['Демо Директор Кленов', 'avatar_ooo_klenov.jpg', 'ООО «Демо Клининг Сервис»', 'logo_ooo_kliningservis.jpg'],
            ['Демо Директор Рябов', 'avatar_ooo_ryabov.jpg', 'ООО «Демо РемонтГрупп»', 'logo_ooo_remontgrupp.jpg'],
        ];

        $providers = [];

        foreach ($freelancers as [$name, $avatar]) {
            $idx++;
            $existing = User::where('name', $name)->first();
            if ($existing) {
                $providers[] = $existing;
                continue;
            }
            $user = $this->makeProvider($name, UserTypeEnum::FREELANCER, null, $phoneBase + $idx);
            $user->assignRole($providerRole);
            $user->zones()->syncWithoutDetaching($allZoneIds);
            $user->addMedia("{$this->ava}/$avatar")->preservingOriginal()->toMediaCollection('image');
            Helpers::createShadowServiceman($user);
            $providers[] = $user;
        }

        foreach ($individualEntrepreneurs as [$name, $avatar]) {
            $idx++;
            $existing = User::where('name', $name)->first();
            if ($existing) {
                $providers[] = $existing;
                continue;
            }
            $user = $this->makeProvider($name, UserTypeEnum::COMPANY, null, $phoneBase + 100 + $idx);
            $user->assignRole($providerRole);
            $user->zones()->syncWithoutDetaching($allZoneIds);
            $user->addMedia("{$this->ava}/$avatar")->preservingOriginal()->toMediaCollection('image');
            $providers[] = $user;
        }

        foreach ($companies as [$personName, $avatar, $companyName, $logo]) {
            $existing = User::where('name', $personName)->first();
            if ($existing) {
                $providers[] = $existing;
                continue;
            }
            $idx++;
            $company = Company::create([
                'name' => $companyName,
                'email' => 'demo.' . Str::slug($companyName) . '@example.com',
                'phone' => (string) ($phoneBase + 200 + $idx),
                'code' => '+7',
                'description' => 'Демо-организация для тестирования модели.',
            ]);
            $company->addMedia("{$this->ava}/$logo")->preservingOriginal()->toMediaCollection('company_logo');

            $idx++;
            $user = $this->makeProvider($personName, UserTypeEnum::COMPANY, $company->id, $phoneBase + 200 + $idx);
            $user->assignRole($providerRole);
            $user->zones()->syncWithoutDetaching($allZoneIds);
            $user->addMedia("{$this->ava}/$avatar")->preservingOriginal()->toMediaCollection('image');
            $providers[] = $user;
        }

        return $providers;
    }

    private function makeProvider(string $name, string $type, ?int $companyId, int $phone): User
    {
        return User::create([
            'name' => $name,
            'email' => 'demo.' . Str::slug($name) . '@example.com',
            'phone' => (string) $phone,
            'code' => '+7',
            'type' => $type,
            'company_id' => $companyId,
            'password' => Hash::make(Str::random(40)),
            'status' => true,
            'system_reserve' => true,
            'experience_interval' => 'years',
            'experience_duration' => (string) random_int(1, 8),
            'referral_code' => Helpers::getReferralCodeByName($name, 6),
        ]);
    }

    /**
     * @param array<int, User> $providers
     */
    private function seedCatalog(array $providers): void
    {
        $adminId = User::where('email', 'admin@example.com')->value('id');

        $catalog = [
            'Уборка квартир и домов' => [
                'image' => 'cleaning.jpg',
                'services' => [
                    ['Генеральная уборка квартиры', 3500, 4],
                    ['Поддерживающая уборка (регулярная)', 1800, 2],
                    ['Уборка после ремонта', 5500, 6],
                    ['Уборка после стройки', 6500, 6],
                    ['Уборка перед сдачей/арендой жилья', 2800, 3],
                ],
            ],
            'Химчистка и чистка ковров' => [
                'image' => 'dry_cleaning.jpg',
                'services' => [
                    ['Чистка ковра (у мастера)', 1200, 1, ServiceTypeEnum::PROVIDER_SITE],
                    ['Химчистка дивана', 2500, 2],
                    ['Химчистка матраса', 2000, 1],
                    ['Чистка мягкой мебели на дому', 2200, 2],
                    ['Химчистка штор и текстиля', 1800, 1],
                ],
            ],
            'Ремонт и отделка помещений' => [
                'image' => 'renovation.jpg',
                'services' => [
                    ['Косметический ремонт квартиры', 45000, 24],
                    ['Поклейка обоев', 8000, 6],
                    ['Укладка плитки', 12000, 8],
                    ['Малярные работы', 7000, 6],
                    ['Выравнивание стен и потолков', 15000, 10],
                ],
            ],
            'Сантехнические работы' => [
                'image' => 'plumbing.jpg',
                'services' => [
                    ['Устранение засоров', 1500, 1],
                    ['Установка смесителя', 1800, 1],
                    ['Замена труб водоснабжения', 6000, 4],
                    ['Установка унитаза', 2500, 2],
                    ['Устранение протечек', 2000, 1],
                ],
            ],
            'Электромонтажные работы' => [
                'image' => 'electrical.jpg',
                'services' => [
                    ['Замена электропроводки', 15000, 8],
                    ['Установка розеток и выключателей', 1200, 1],
                    ['Монтаж люстр и светильников', 1500, 1],
                    ['Установка электрощита', 8000, 4],
                    ['Диагностика электропроводки', 2000, 2],
                ],
            ],
            'Ремонт бытовой техники' => [
                'image' => 'appliance_repair.jpg',
                'services' => [
                    ['Ремонт стиральных машин', 2500, 2],
                    ['Ремонт холодильников', 3000, 2],
                    ['Ремонт посудомоечных машин', 2800, 2],
                    ['Ремонт варочных панелей и духовых шкафов', 3200, 2],
                    ['Ремонт кондиционеров', 3500, 2],
                ],
            ],
            'Сборка и ремонт мебели' => [
                'image' => 'furniture.jpg',
                'services' => [
                    ['Сборка кухонного гарнитура', 6000, 4],
                    ['Сборка шкафа-купе', 4500, 3],
                    ['Сборка корпусной мебели IKEA', 2500, 2],
                    ['Ремонт и регулировка мебельной фурнитуры', 1800, 1],
                    ['Сборка детской мебели', 2200, 2],
                ],
            ],
            'Грузоперевозки и переезды' => [
                'image' => 'moving.jpg',
                'services' => [
                    ['Квартирный переезд', 8000, 4],
                    ['Офисный переезд', 15000, 6],
                    ['Грузчики без транспорта', 2500, 2],
                    ['Перевозка мебели и техники', 4000, 2],
                    ['Вывоз старой мебели и мусора', 3000, 2],
                ],
            ],
            'Мастер на час' => [
                'image' => 'handyman.jpg',
                'services' => [
                    ['Мелкий бытовой ремонт', 1500, 1],
                    ['Навеска карнизов и полок', 1200, 1],
                    ['Сборка/навеска зеркал и картин', 1000, 1],
                    ['Ремонт дверей и замков', 1800, 1],
                    ['Устранение мелких поломок по дому', 1500, 1],
                ],
            ],
            'Мойка окон и фасадов' => [
                'image' => 'windows.jpg',
                'services' => [
                    ['Мытьё окон в квартире', 1800, 2],
                    ['Мытьё окон в офисе', 2500, 2],
                    ['Мойка фасадов зданий', 12000, 6],
                    ['Мойка балконов и лоджий', 1500, 1],
                    ['Мойка витрин магазинов', 2000, 1],
                ],
            ],
        ];

        $existingCategoriesByName = [];
        foreach (Category::get() as $c) {
            $existingCategoriesByName[$c->getTranslation('title', 'ru')] = $c;
        }
        $existingServiceTitles = Service::withTrashed()->get()
            ->map(fn ($s) => $s->getTranslation('title', 'ru'))
            ->all();

        $providerCycle = 0;

        foreach ($catalog as $categoryName => $data) {
            $category = $existingCategoriesByName[$categoryName] ?? null;
            if (!$category) {
                $category = Category::create([
                    'title' => $categoryName,
                    'description' => $categoryName,
                    'category_type' => CategoryType::SERVICE,
                    'status' => true,
                    'commission' => 10,
                    'created_by' => $adminId,
                ]);
                $category->setTranslation('title', 'ru', $categoryName);
                $category->setTranslation('description', 'ru', $categoryName);
                $category->save();
                $category->addMedia("{$this->img}/{$data['image']}")
                    ->preservingOriginal()
                    ->withCustomProperties(['language' => 'ru'])
                    ->toMediaCollection('image');
            }

            foreach ($data['services'] as $svc) {
                [$title, $price, $duration] = $svc;
                $type = $svc[3] ?? ServiceTypeEnum::FIXED;

                if (in_array($title, $existingServiceTitles, true)) {
                    continue;
                }

                $provider = $providers[$providerCycle % count($providers)];
                $providerCycle++;

                $description = $title . '. Демо-услуга для тестирования модели.';

                $service = Service::create([
                    'title' => $title,
                    'description' => $description,
                    'price' => $price,
                    'service_rate' => $price,
                    'discount' => 0,
                    'status' => 1,
                    'duration' => (string) $duration,
                    'duration_unit' => 'hours',
                    'type' => $type,
                    'user_id' => $provider->id,
                    'required_servicemen' => 1,
                    // decimal(4,2) column caps at 99.99 despite validation allowing 100
                    'per_serviceman_commission' => 99.99,
                    'is_featured' => $svc === $data['services'][0],
                    'is_advance_payment_enabled' => false,
                ]);
                $service->setTranslation('title', 'ru', $title);
                $service->setTranslation('description', 'ru', $description);
                $service->save();

                $service->categories()->attach($category->id);

                $imagePath = "{$this->img}/{$data['image']}";
                foreach (['image', 'thumbnail', 'web_thumbnail', 'web_images'] as $collection) {
                    $service->addMedia($imagePath)
                        ->preservingOriginal()
                        ->withCustomProperties(['language' => 'ru'])
                        ->toMediaCollection($collection);
                }
            }
        }
    }
}
