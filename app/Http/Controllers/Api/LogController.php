<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FrontendLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'action'   => 'required|string|max:255',
            'metadata' => 'nullable|array',
        ]);

        FrontendLog::create([
            'meeting_code' => $request->input('meeting_code'),
            'user_id'      => $request->input('user_id'),
            'action'       => $request->input('action'),
            'metadata'     => $request->input('metadata'),
        ]);

        return response()->json(['success' => true]);
    }
}
