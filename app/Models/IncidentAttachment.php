<?php 

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Incident;

class IncidentAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_id',
        'file_path',
        'file_type'
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }
}
?>