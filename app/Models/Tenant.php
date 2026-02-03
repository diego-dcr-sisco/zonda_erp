<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Plan;

class Tenant extends Model
{
    protected $table = 'tenant';
    protected $fillable = [
        'company_name',
        'slug',
        'is_active',
        'plan_id',
        'subscription_start',
        'subscription_end',
        'company_name',
        'path',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'subscription_start' => 'date',
        'subscription_end' => 'date',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'tenant_id');
    }
}
