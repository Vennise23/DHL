<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\IncidentAttachment;
use App\Models\IncidentStatusHistory;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'source',
        'category',
        'assigned_to',
        'created_by',
        'updated_by'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    
    public function histories()
    {
        return $this->hasMany(IncidentStatusHistory::class);
    }

    public function rpaLogs()
    {
        return $this->hasMany(RpaLog::class);
    }

    public function attachments()
    {
        return $this->hasMany(IncidentAttachment::class);
    }

    public function aiProcessing()
    {
        return $this->hasOne(AiProcessing::class);
    }
}
