<?php

namespace App\Services;

use App\Models\AiProcessing;

class AiProcessingService
{
    public function store($incidentId, $result, $rawPrompt, $rawResponse, $inputType = 'text')
{
    return AiProcessing::create([
        'incident_id' => $incidentId,

        'ai_summary' => $result['summary'] ?? null,
        'ai_tags' => json_encode($result['tags'] ?? []),
        'ai_suggestions' => $result['suggestions'] ?? null,

        'conflict_flag' => $result['conflict'] ?? false,
        'ai_confidence' => $result['confidence'] ?? null,
        'ai_input_type' => $inputType,
    ]);
}
}