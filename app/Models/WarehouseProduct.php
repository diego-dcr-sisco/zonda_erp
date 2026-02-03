<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseProduct extends Model
{
    use HasFactory;

    protected $table = 'warehouse_product';

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'lot_id',
        'amount'
    ];

    public function product()
    {
        return $this->belongsTo(ProductCatalog::class, 'product_id');
    }

    public function lot()
    {
        return $this->belongsTo(Lot::class, 'lot_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
