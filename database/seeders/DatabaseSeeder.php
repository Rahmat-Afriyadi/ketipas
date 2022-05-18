<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Hash;

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
        DB::table('users')->insert([
            'name' => 'Epi Haryono',
            'email' => 'epiharyono@gmail.com',
            'password' => Hash::make('anambas'),
        ]);
        // php artisan db:seed
    }
}
