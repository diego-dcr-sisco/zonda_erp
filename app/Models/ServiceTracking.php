<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Tenancy\TenantScoped;

class ServiceTracking extends Model
{
    use HasFactory, TenantScoped;

    protected $table = 'service_tracking';

    protected $fillable = [
        'id',
        'model_id',
        'model_type',
        'service_id',
        'tracking_date',
        'tracking_time',
    ];

    public function model()
    {
        return $this->morphTo();
    }

    public function service() {
        return $this->belongsTo(Service::class, 'service_id');
    }    
}
