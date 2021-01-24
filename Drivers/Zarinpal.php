<?php


namespace Sohbatiha\AriaPayment\Drivers;


use Sohbatiha\AriaPayment\Invoice;

class Zarinpal extends Driver
{

    public function purchase(Invoice $invoice, callable $callback, string $redirect_url)
    {
        // TODO: Implement purchase() method.
    }

    public function verify(string $res_id, callable $callback)
    {
        // TODO: Implement verify() method.
    }

    public function execute()
    {
        // TODO: Implement execute() method.
    }

    public function mapBankStatusToDescription(): array
    {
        // TODO: Implement mapBankStatusToDescription() method.
    }

    public function mapBankStatusToAriaPaymentStatus(): array
    {
        // TODO: Implement mapBankStatusToAriaPaymentStatus() method.
    }
}
