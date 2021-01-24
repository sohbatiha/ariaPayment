<?php


namespace Sohbatiha\AriaPayment;


use Illuminate\Support\Manager;
use Sohbatiha\AriaPayment\Drivers\Saman;

class PaymentManager extends Manager
{

    public function getDefaultDriver(): string
    {
        return "saman";
    }

    public function createSamanDriver(){
        return new Saman();
    }
}
