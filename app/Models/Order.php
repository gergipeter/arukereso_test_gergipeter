<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['start_address_id', 'end_address_id', 'status_id', 'start_date', 'end_date'];

    public function status()
    {
        return $this->belongsTo(OrderStatus::class);
    }
}
