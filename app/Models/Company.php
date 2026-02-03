<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Tenancy\TenantScoped;

class Company extends Model
{
    use HasFactory, TenantScoped;

    protected $table = 'company';

    protected $fillable = [
        'name',
    ];
}
