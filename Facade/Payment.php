<?php


namespace Sohbatiha\AriaPayment\Facade;


use Illuminate\Support\Facades\Facade;
use Sohbatiha\AriaPayment\Drivers\Driver;
use Sohbatiha\AriaPayment\Models\Transaction;

class Payment extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return "aria_payment";
    }

    public static function verify()
    {
        $transaction = Transaction::where("id", request()->transaction_id)->with(['invoice', 'invoice.items'])->first();

        if ($transaction->status == Driver::SUCCESSFUL) {
            throw new \Exception("این تراکنش قبلا وریفای و تایید شده است .", null);
        }

        $driver = $transaction->data['driver'] ?? null;

        if (!$driver) {
            throw new \Exception("درایور مرتبط با این تراکنش پیدا نشد .", null);
        }

        $driver_class = resolve('aria_payment')->driver($driver);

        $driver_class->setTransaction($transaction);
        $driver_class->setInvoice($transaction->invoice);

        $driver_class->verify();

    }

}
