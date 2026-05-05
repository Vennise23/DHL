<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Incident;

class RpaLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_id',
        'action',
        'status',
        'message'
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }
}
?>