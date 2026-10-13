<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Blade mis-compiles a file that mixes inline `@php(...)` with block
 * `@php ... @endphp`: the block regex swallows the inline one and variables
 * defined in the block vanish. It broke admin Settings with "Undefined
 * variable $default" on 2026-10-13. Use the block form only.
 */
class BladeInlinePhpTest extends TestCase
{
    #[Test]
    public function no_view_mixes_inline_and_block_php(): void
    {
        $offenders = [];
        $views = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/../../resources/views'));
        foreach ($views as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }
            $source = file_get_contents($file->getPathname());
            if (preg_match('/@php\s*\(/', $source) && str_contains($source, '@endphp')) {
                $offenders[] = $file->getPathname();
            }
        }

        $this->assertSame([], $offenders, 'Inline @php(...) next to @php…@endphp — use the block form');
    }
}
