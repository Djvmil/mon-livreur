<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\UserType;
use App\Models\ProviderService;
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
        $admin = UserType::where('name', "ADMINISTRATEUR")->first();
        $customer = UserType::where('name', "CLIENT")->first();
        $provider = UserType::where('name', "PRESTATAIRE")->first();
        $data = [
            [ 
                "firstname" => "Moustapha S.",
                "lastname" => "Dieme",
                "name" => "Moustapha S. Dieme",
                "email" => "ndieme16@hotmail.com",
                "password" => bcrypt("passer123"),
                "id_user_type" => $admin->id,
                "phone" => "221774294171", 
                "address" => "Nord Foire" 
            ],
            [ 
                "firstname" => "Djibril",
                "lastname" => "Diop",
                "name" => "Djibril Diop",
                "email" => "djibi@yopmail.com",
                "password" => bcrypt("passer123"),
                "id_user_type" => $customer->id,
                "phone" => "221774294172", 
                "address" => "Nord Foire" 
            ], 
            [ 
                "firstname" => "Omar",
                "lastname" => "Sy",
                "name" => "Omar Sy",
                "email" => "omar@yopmail.com",
                "password" => bcrypt("passer123"),
                "id_user_type" => $provider->id,
                "phone" => "221774294173", 
                "address" => "Nord Foire" 
            ], 
        ]; 
        User::insert($data);

        DB::table('customers')->delete(); 
        DB::table('provider_services')->delete(); 
        DB::table('admins')->delete(); 
        
        $result = User::where('email', "ndieme16@hotmail.com")->first();
        Admin::create(array( 
            "id_user" => $result->id,
            "avis" => "RAS"
        ));
        
        $result = User::where('email', "djibi@yopmail.com")->first();
        Customer::create(array(
            "id_user" => $result->id,
            "avis" => "RAS"
        ));

        $result = User::where('email', "omar@yopmail.com")->first();
        ProviderService::create(array(
            "id_user" => $result->id,
            "avis" => "RAS"
        ));
 
    }
}

