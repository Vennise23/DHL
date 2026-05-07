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
        'created_count',
        'duplicate_count',
        'failed_count',
        'log_file_path',
        'created_at',
        'updated_at',
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }
}
?>