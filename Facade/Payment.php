<?php


namespace Sohbatiha\AriaPayment\Facade;


use Illuminate\Support\Facades\Facade;

class Payment extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return "aria_payment";
    }

}
