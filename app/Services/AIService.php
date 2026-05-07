<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class AIService
{
    public function analyze($data)
    {
        $inputType = $this->detectInputType($data);

        $prompt = $this->buildPrompt($data, $inputType);

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4.1-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a DHL incident analysis AI.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
        ]);

        $content = $response->choices[0]->message->content;

        $result = $this->safeJsonDecode($content);

        // ✅ inject metadata
        $result['ai_input_type'] = $inputType;
        $result['ai_confidence'] = $this->calculateConfidence($result);

        return $result;
    }

    private function detectInputType($data)
    {
        if (!empty($data['images'])) return 'image';
        return 'text';
    }

    private function calculateConfidence($result)
    {
        // simple rule-based confidence (you can improve later)
        if (empty($result['category'])) return 'low';
        if (empty($result['tags'])) return 'low';

        return 'high';
    }

    private function buildPrompt($data)
{
    $title = $data['title'] ?? '';
    $description = $data['description'] ?? '';
    $inputType = $data['ai_input_type'] ?? 'text';

    return "
You are a DHL incident analysis AI.

Input type: {$inputType}

Return JSON ONLY:

{
  \"category\": \"Delivery Issue | Address Problem | Damaged Parcel | System Error | Customer Complaint | Other\",
  \"priority\": \"low | medium | high\",
  \"summary\": \"...\",
  \"tags\": [\"...\"],
  \"suggestions\": \"...\",
  \"conflict\": false,
  \"confidence\": \"0-100\"
}

Incident:
Title: {$title}
Description: {$description}
";
}
    private function safeJsonDecode($content)
    {
        $clean = preg_replace('/```json|```/', '', $content);
        return json_decode($clean, true) ?? [];
    }
}