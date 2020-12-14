<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserType;
use Illuminate\Support\Facades\DB;

class UserTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_types')->delete(); 
        $data = [
            ["name" => "ADMINISTRATEUR"],
            ["name" => "CLIENT"], 
            ["name" => "PRESTATAIRE"], 
        ];
        
        UserType::insert(array(
            ["name" => "ADMINISTRATEUR"],
            ["name" => "CLIENT"], 
            ["name" => "PRESTATAIRE"] ));
    }
}
