<?php


namespace Sohbatiha\AriaPayment;


use Sohbatiha\AriaPayment\Models\InvoiceItem;
use Sohbatiha\AriaPayment\Models\Invoice as InvoiceModel;

class Invoice
{

    private $invoice_data = [
        "amount" => 0
    ];

    private $items;

    private $amount;

    public function items($items): Invoice
    {
        $total_amount = 0;
        $invoice_items = [];

        if (is_array(reset($items))) {

            foreach ($items as $item) {
                $total_amount += (int)$item["amount"];
                $invoice_items[] = new InvoiceItem($item);
            }

        } else {

            $total_amount = $items["amount"];
            $invoice_items[] = new InvoiceItem($items);

        }

        $this->amount = $total_amount;

        $invoice = InvoiceModel::create([
            "amount" => $total_amount,
            "user_id" => auth()->user()->id ?? null,
        ]);

        $invoice->items()->saveMany($invoice_items);

        $this->items = $invoice->items;

        return $this;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function getAmount()
    {
        return $this->amount;
    }

}
