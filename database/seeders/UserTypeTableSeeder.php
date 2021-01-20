<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserType;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
            [
                "id" => 1,
                "name" => "ADMINISTRATEUR", 
                "created_at" => Carbon::now(),
                "updated_at" =>  Carbon::now()
            ],
            [
                "id" => 2,
                "name" => "CLIENT", 
                "created_at" => Carbon::now(),
                "updated_at" =>  Carbon::now()
            ], 
            [
                "id" => 3,
                "name" => "PRESTATAIRE", 
                "created_at" => Carbon::now(),
                "updated_at" =>  Carbon::now()
            ], 
        ];
        
        UserType::insert($data);
    }
}
