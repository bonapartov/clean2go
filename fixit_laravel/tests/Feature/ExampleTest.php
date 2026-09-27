<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Проверка, что главная страница отдаёт 200 — ей нужны реальные
     * category_type/ThemeOption/Currency/SystemLang записи (см. db:seed),
     * иначе HomeController падает на пустых таблицах.
     *
     * @return void
     */
    public function test_example()
    {
        $this->seed();

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
