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
        'source_type',
        'action',
        'status',
        'message',
        'file_hash',
        'log_file_path',
        'screenshot_path',
        'external_source_id'
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }
}
?>