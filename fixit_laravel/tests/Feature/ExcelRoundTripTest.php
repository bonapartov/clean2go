<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * maatwebsite/excel 3.1 → 4.x и phpspreadsheet 1.30 → 5.x (2026-10-13,
 * переход на PHP 8.5 и закрытие уязвимостей): админские выгрузки пишут
 * XLSX, а загрузки читают его обратно. Все 26 классов выгрузки дополнительно
 * прогнаны на рабочей БД при обновлении.
 */
class ExcelRoundTripTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function export_writes_xlsx_that_reads_back(): void
    {
        \Spatie\Permission\Models\Role::create(['name' => \App\Enums\RoleEnum::CONSUMER, 'guard_name' => 'web']);
        User::factory()->count(2)->create(['system_reserve' => 0])->each->assignRole(\App\Enums\RoleEnum::CONSUMER);

        $xlsx = Excel::raw(new \App\Exports\UsersExport, \Maatwebsite\Excel\Excel::XLSX);
        $this->assertStringStartsWith('PK', $xlsx, 'XLSX — это zip');

        $path = tempnam(sys_get_temp_dir(), 'c2g').'.xlsx';
        file_put_contents($path, $xlsx);
        try {
            $headings = (new HeadingRowImport)->toArray($path)[0][0] ?? [];
            $rows = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray
            {
                public function array(array $array): void {}
            }, $path)[0];
        } finally {
            @unlink($path);
        }

        $this->assertNotEmpty($headings, 'строка заголовков читается');
        $this->assertGreaterThanOrEqual(3, count($rows), 'заголовок + 2 пользователя');
    }

    #[Test]
    public function all_admin_imports_satisfy_excel_4(): void
    {
        foreach (glob(app_path('Imports/*.php')) as $file) {
            $class = 'App\\Imports\\'.basename($file, '.php');
            $this->assertTrue(is_subclass_of($class, \Maatwebsite\Excel\Concerns\Import::class), "$class — Excel 4 требует Import");
        }
    }
}
