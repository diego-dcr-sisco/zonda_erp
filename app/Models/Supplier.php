<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Tenancy\TenantScoped;

class Supplier extends Model
{
    use HasFactory, TenantScoped;
    protected $table = 'suppliers';
    protected  $fillable = [
        'category_id',
        'name',
        'rfc',
        'address',
        'phone',
        'email'
    ];

    public function category()
    {
        return $this->belongsTo(SupplierCategory::class, 'category_id');
    }
}

