<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Tenancy\TenantScoped;

class AppearanceSetting extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'primary_color',
        'secondary_color',
        'logo_path',
        'watermark_path',
        'watermark_opacity'
    ];

    protected $attributes = [
        'primary_color' => '#012640',
        'secondary_color' => '#793775',
        'logo_path' => 'images/logo_reporte.png',
        'watermark_path' => 'images/watermark.png',
        'watermark_opacity' => 0.1
    ];
}
