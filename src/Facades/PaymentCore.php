<?php

namespace Avaztek\PaymentCore\Facades;

use Illuminate\Support\Facades\Facade;

class PaymentCore extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'payment-core';
    }
}
