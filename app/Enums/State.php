<?php

namespace App\Enums;

use MadWeb\Enum\Enum;

/**
 * @method static State WAITING_FOR_TAKE()
 */
final class State extends Enum
{
    const __default = self::WAITING_FOR_TAKE;

    const WAITING_FOR_TAKE = '1';
}
