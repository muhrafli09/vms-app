<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Kiosk extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'location',
        'token',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($kiosk) {
            if (empty($kiosk->token)) {
                $kiosk->token = Str::random(32);
            }
        });
    }
}
