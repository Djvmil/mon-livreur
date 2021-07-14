<?php

namespace App\Http\Services;

use Closure;
use Illuminate\Http\Request;

class SmsService
{ 
    public function sendMessage($receiver, $message)
    {

        try {
                
            $basic  = new \Nexmo\Client\Credentials\Basic('88142513', 'nr3wZYZjuGpqWTW7');
            $client = new \Nexmo\Client($basic);

            $message = $client->message()->send([
                'to' => $receiver,
                'from' => 'MON LIVREUR APP',
                'text' => $message
            ]);

           return $message;
            
        } catch (\Throwable $th) {
            //throw $th;
        }
 
    }
}
