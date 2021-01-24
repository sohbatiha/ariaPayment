<?php


namespace Sohbatiha\AriaPayment\Drivers;


use Illuminate\Support\Facades\Redirect;
use Sohbatiha\AriaPayment\Invoice;

abstract class Driver
{
    protected static $callbackUrl;

    protected static $submitMethod = "GET";

    protected static $submitData = [];

    public function callbackUrl($url)
    {
        static::$callbackUrl = $url;
    }

    abstract public function purchase(Invoice $invoice, callable $callback , string $redirect_url);

    abstract public function verify(string $res_id, callable $callback);

    abstract public function execute();

    abstract public function mapBankStatusToDescription(): array;

    abstract public function mapBankStatusToAriaPaymentStatus(): array;

    public function toJson()
    {
        $data = [
            "callbackUrl" => static::$callbackUrl,
            "submitMethod" => static::$submitMethod,
            "data" => static::$submitData,
        ];
        return response()->json($data);
    }

    public function redirect()
    {
        if (static::$submitMethod == "GET") {
            $query_string = "/?";
            foreach (static::$submitData as $key => $value) {
                $query_string .= $key . '=' . $value . '&';
            }
            $query_string = rtrim($query_string, '&');

            $url = rtrim(static::$callbackUrl, '/') . $query_string;

            return Redirect::to($url);
        }

        return view('redirect')->with(["data" => static::$submitData]);

    }

}
