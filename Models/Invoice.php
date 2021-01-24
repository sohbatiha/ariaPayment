<?php


namespace Sohbatiha\AriaPayment\Models;


use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

}
