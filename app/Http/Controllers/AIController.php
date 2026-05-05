<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Exceptions\RateLimitException;

class AIController extends Controller
{
    public function categorize(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $prompt = "
You are an incident classification AI for DHL support system.

Classify the incident into:
- Delivery Issue
- Address Problem
- Damaged Parcel
- System Error
- Customer Complaint
- Other

Also suggest priority: low, medium, high

Return JSON ONLY:
{
  \"category\": \"...\",
  \"priority\": \"...\"
}

Incident:
Title: {$request->title}
Description: {$request->description}
";
        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a strict JSON classifier.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.2,
            ]);

            $result = $response->choices[0]->message->content;

            return response()->json(json_decode($result));
        } catch (RateLimitException $e) {
            return response()->json([
                'category' => 'manual_review',
                'priority' => 'medium',
                'note' => 'AI busy, sent for manual classification'
            ]);
        }
    }
}
