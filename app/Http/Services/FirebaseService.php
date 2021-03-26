<?php

namespace App\Services;
use Kreait\Firebase\Messaging;

class FirebaseService
{
    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
    }
}