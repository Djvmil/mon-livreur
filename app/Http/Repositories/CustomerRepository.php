<?php
namespace App\Http\Repositories; 

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Customer;

class CustomerRepository extends BaseRepository
{
    function __construct(){
		$this->model = new Customer();
	}
}