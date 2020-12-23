<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->delete();

        User::create(array(
            "firstname" => "Moustapha S.",
            "lastname" => "Dieme",
            "email" => "ndieme16@hotmail.com",
            "password" => bcrypt("passer123"),
            "id_user_type" => 1,
            "phone" => "221774294171", 
            "address" => "Nord Foire" 
        ));
    }
}

