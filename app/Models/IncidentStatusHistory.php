<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Incident;
use App\Models\User;

class IncidentStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_id',
        'status',
        'changed_by',
        'note'
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
?>