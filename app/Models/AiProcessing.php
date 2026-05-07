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
        'ai_summary',
        'ai_tags',
        'ai_suggestions',
        'category',
        'priority',
        'conflict_flag',
        'ai_confidence',
        'ai_input_type',
        'raw_prompt',
        'raw_response',
    ];

    protected $casts = [
        'conflict_flag' => 'boolean',
        'ai_tags' => 'array',
    ];
    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }
}
?>