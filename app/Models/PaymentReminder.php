<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Tenancy\TenantScoped;

class PaymentReminder extends Model
{
    use HasFactory, TenantScoped;
    protected $fillable = [
        'customer_id', // Renombrado de 'client_id' para consistencia
        'scheduled_date',
        'body',
        'sent'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
