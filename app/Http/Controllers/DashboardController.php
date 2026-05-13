<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;
use App\Models\RpaLog;

class DashboardController
{
    public function index()
    {
        return response()->json([
            'stats' => [
                'total' => Incident::count(),
                'open' => Incident::where('status', 'draft')->count(),
                'resolved' => Incident::where('status', 'published')->count(),
                'critical' => Incident::where('priority', 'high')->count(),
            ],

            'recentIncidents' => Incident::latest()->take(10)->get(),

            'rpaLogs' => RpaLog::latest()->take(10)->get(),
        ]);
    }
}
