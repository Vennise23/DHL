<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AIService;

class AIController
{
    public function process(Request $request)
    {
        $data = $request->validate([
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'incident_id' => 'nullable|integer',
        ]);

        $result = app(AIService::class)->analyze($data);

        return response()->json($result);
    }
}
