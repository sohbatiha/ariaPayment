<?php


namespace Sohbatiha\AriaPayment\Drivers;


use Illuminate\Support\Facades\Redirect;
use phpDocumentor\Reflection\Types\This;
use Sohbatiha\AriaPayment\Invoice;

abstract class Driver
{
    protected $callbackUrl;
    protected $invoice;
    protected $submitMethod = "GET";
    protected $submitData = [];
    protected $transaction;

    const FAILED = -1;
    const WAITING = 0;
    const SUCCESSFUL = 1;


    public function callbackUrl($url)
    {
        $this->callbackUrl = $url;
        return $this;
    }

    public function pay(Invoice $invoice)
    {
        $this->invoice = $invoice;
        return $this;
    }

    public function callback(string $string)
    {
        $this->callbackUrl = $string;
        return $this;
    }

    abstract public function execute();

    abstract public function verify();

    abstract public function getDriverName(): string;

    public function getResponse()
    {
        $data = [
            "callbackUrl" => $this->callbackUrl,
            "submitMethod" => $this->submitMethod,
            "data" => $this->submitData,
        ];
        return response()->json($data);
    }

    public function redirectView()
    {

        if ($this->submitMethod == "GET") {
            $query_string = "/?";
            foreach ($this->submitData as $key => $value) {
                $query_string .= $key . '=' . $value . '&';
            }
            $query_string = rtrim($query_string, '&');

            $url = rtrim($this->callbackUrl, '/') . $query_string;

            return Redirect::to($url);
        }

        return view('ariaPayment::redirect')->with(["data" => $this->submitData]);
    }

    public function getResNumber()
    {
        return $this->resNum = time() . rand(1000, 2000);
    }

    public function generateCallbackUrl($transaction_id)
    {
        return rtrim($this->callbackUrl ?? env("APP_URL"), "/") . '/?_token=' . csrf_token() . '&transaction_id=' . $transaction_id;
    }

    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;
    }

    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;
    }

}
