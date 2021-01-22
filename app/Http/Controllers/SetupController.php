<?php


namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

use App\Http\Requests;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class SetupController extends BaseController
{

    public function setup(){

        //php artisan cache:clear  
        //php artisan config:clear  
        //php artisan migrate  
        //php artisan passport:install
        //php artisan db:seed --class=UserTypeTableSeeder
        //php artisan db:seed --class=UserTableSeeder  
        $migration = new Process("php artisan migrate");
        $migration->setWorkingDirectory(base_path());


        $migration = new Process("git pull origin develop_bi");
        $migration->setWorkingDirectory(base_path());

        $migration->run();

        if($migration->isSuccessful()){
            //...
        } else {
            throw new ProcessFailedException($migration);
        }
    }
}