<?php

namespace Webkul\Phonix\Console\Commands;

use Illuminate\Console\Command;

class SeedProducts extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'phonix:seed-products';

    /**
     * The console command description.
     */
    protected $description = 'Seed demo products for the Phonix electronics store';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Seeding Phonix products...');

        $this->call('db:seed', [
            '--class' => \Webkul\Phonix\Database\Seeders\PhonixProductSeeder::class,
        ]);

        $this->newLine();
        $this->info('Phonix demo products seeded successfully!');
        $this->info('You may need to run: php artisan bagisto:product:reindex');

        return self::SUCCESS;
    }
}
