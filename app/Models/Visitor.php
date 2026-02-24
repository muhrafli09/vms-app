<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Visitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'name',
        'email',
        'company',
    ];

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }
}
