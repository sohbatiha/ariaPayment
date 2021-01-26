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

    abstract public function verify(string $res_id, callable $callback);

    abstract public function mapBankStatusToDescription(): array;

    abstract public function mapBankStatusToAriaPaymentStatus(): array;

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
}
