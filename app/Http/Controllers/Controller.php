<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public static function sendResponse($data = null, $userMessage = null, $debugMessage = null, $codeHttp = JsonResponse::HTTP_OK) {
	    return response()->json ( [ 
				'userMessage' => $userMessage,
				'debugMessage' => $debugMessage,
				'data' 	  => $data
		], $codeHttp );
    }
     
}
