<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        $this->call('UserTypeTableSeeder');
        $this->call('UserTableSeeder');

        $this->command->info('UserType and User  table seeded!');
    }
}
