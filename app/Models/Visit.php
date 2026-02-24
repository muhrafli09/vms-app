<?php

namespace App\Models;

use App\Traits\UuidScopeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visit extends Model
{
    use HasFactory;
    use UuidScopeTrait;
    use SoftDeletes;

    protected $fillable = [
        'visitor_id',
        'employee_id',
        'purpose',
        'photo',
        'arrival',
        'departure',
        'uuid',
        'status',
        'scheduled_time',
        'created_by',
        'visitor',
        'visitor_email',
        'visitor_phone',
        'visitor_company',
    ];

    protected $casts = [
        'arrival' => 'datetime',
        'departure' => 'datetime',
        'scheduled_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }
    
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }
    
    public function canCheckIn(): bool
    {
        return $this->status === 'scheduled' && $this->scheduled_time;
    }
}
