<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Incident;

class AiProcessing extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_id',
        'input_data',
        'ai_result',
        'confidence_score'
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }
}
?>