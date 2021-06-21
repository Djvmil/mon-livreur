<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;
//https://laravel-news.com/laravel-enum-package-for-generating-enum-php-classes
/**
 * The StateAdvert enum.
 *
 * @method static self WAITING_FOR_TAKE()
 * @method static self TAKEN()
 * @method static self IN_PROGRESS()
 * @method static self DELIVERED()
 * @method static self BLOCKED()
 * @method static self DELETED()
 * @method static self CANCELED()
 * @method static self ACCEPTED()
 * @method static self REFUSED()
 * @method static self WAITING()
 */
class StateAdvert extends Enum
{
    const WAITING_FOR_TAKE = 1;
    const TAKEN = 2;
    const IN_PROGRESS = 3;
    const DELIVERED = 4;
    const BLOCKED = 5;
    const DELETED = 6;
    const CANCELED = 7;
    const ACCEPTED = 8;
    const REFUSED = 9;
    const WAITING = 10;
    const FINISH = 11;

    /**
     * Retrieve a map of enum keys and values.
     *
     * @return array
     */
    public static function map() : array
    {
        return [
            static::WAITING_FOR_TAKE => 'Waiting for take',
            static::TAKEN => 'Taken',
            static::IN_PROGRESS => 'In progress',
            static::DELIVERED => 'Delivered',
            static::BLOCKED => 'Blocked',
            static::DELETED => 'Deleted',
            static::CANCELED => 'Canceled',
            static::ACCEPTED => 'Accepted',
            static::REFUSED => 'Refused',
            static::WAITING => 'Waiting',
            static::FINISH => 'Finish',
        ];
    }
}
