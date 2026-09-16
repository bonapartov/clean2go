<?php

namespace App\Console\Commands;

use App\Enums\RoleEnum;
use App\Enums\UserTypeEnum;
use App\Helpers\Helpers;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillFreelancerShadowServicemen extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:backfill-freelancer-shadow-servicemen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a shadow serviceman for every existing freelancer provider that has none, so bookings can be assigned to them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $created = 0;
        $skipped = 0;

        User::role(RoleEnum::PROVIDER)
            ->where('type', UserTypeEnum::FREELANCER)
            ->whereDoesntHave('servicemans')
            ->chunkById(100, function ($providers) use (&$created, &$skipped) {
                foreach ($providers as $provider) {
                    // Belt-and-suspenders re-check per row (not just the
                    // query-level whereDoesntHave above), so this stays safe
                    // to run twice without creating duplicates.
                    $hasServiceman = $provider->servicemans()->doesntExist() === false;

                    if ($hasServiceman) {
                        $skipped++;

                        continue;
                    }

                    DB::transaction(function () use ($provider) {
                        Helpers::createShadowServiceman($provider);
                    });

                    $created++;
                }
            });

        $this->info("Backfill complete: created {$created} shadow serviceman record(s), skipped {$skipped} freelancer(s) that already had a serviceman.");
    }
}
