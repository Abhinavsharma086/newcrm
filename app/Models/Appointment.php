<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Appointment extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'leads';

    const STATUS_SCHEDULED   = 'scheduled';
    const STATUS_CONFIRMED   = 'confirmed';
    const STATUS_VISITED     = 'visited';
    const STATUS_CONVERTED   = 'converted';
    const STATUS_DENIED      = 'denied';
    const STATUS_RESCHEDULED = 'rescheduled';

    protected $fillable = [
        'customer_id', 'burner_type', 'kitchen_burner_video', 'external_riser_video',
        'assigned_to', 'assigned_technician',
        'title', 'stage', 'appointment_status',
        'source', 'value', 'notes',
        'follow_up_date', 'follow_up_note', 'next_follow_up_date',
        'appointment_date', 'appointment_time',
        'society',
        'denial_reason', 'denied_by_person', 'denial_photo',
        'further_action', 'further_action_date',
        'converted_at', 'conversion_notes',
    ];

    protected function casts(): array
    {
        return [
            'follow_up_date'      => 'date',
            'next_follow_up_date' => 'date',
            'appointment_date'    => 'date',
            'further_action_date' => 'date',
            'converted_at'        => 'datetime',
            'value'               => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'assigned_technician');
    }

    public function isDenied(): bool
    {
        return $this->appointment_status === self::STATUS_DENIED;
    }

    public function isConverted(): bool
    {
        return $this->appointment_status === self::STATUS_CONVERTED;
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->appointment_status) {
            'converted'   => 'success',
            'denied'      => 'danger',
            'visited'     => 'info',
            'confirmed'   => 'primary',
            'rescheduled' => 'warning',
            default       => 'secondary',
        };
    }
}
