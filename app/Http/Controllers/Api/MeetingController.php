<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingChat;
use App\Models\MeetingRecording;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function create(Request $request): JsonResponse
    {
        $code = $this->generateUniqueCode();

        $meeting = Meeting::create([
            'meeting_code' => $code,
            'status'       => 'waiting',
        ]);

        return response()->json([
            'success'      => true,
            'meeting_code' => $meeting->meeting_code,
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate(['meeting_code' => 'required|string']);

        $meeting = Meeting::where('meeting_code', $request->meeting_code)
            ->whereIn('status', ['waiting', 'active'])
            ->first();

        if (! $meeting) {
            return response()->json([
                'valid'   => false,
                'message' => 'Meeting not found or has already ended.',
            ], 404);
        }

        return response()->json([
            'valid'   => true,
            'meeting' => $meeting,
        ]);
    }

    public function savePeerId(Request $request, string $code): JsonResponse
    {
        $request->validate(['peer_id' => 'required|string']);

        $meeting = Meeting::where('meeting_code', $code)->firstOrFail();
        $meeting->update([
            'host_peer_id' => $request->peer_id,
            'status'       => 'active',
        ]);

        return response()->json(['success' => true]);
    }

    public function getPeerId(string $code): JsonResponse
    {
        $meeting = Meeting::where('meeting_code', $code)->firstOrFail();

        return response()->json([
            'peer_id' => $meeting->host_peer_id,
            'status'  => $meeting->status,
        ]);
    }

    public function end(string $code): JsonResponse
    {
        $meeting = Meeting::where('meeting_code', $code)->firstOrFail();
        $meeting->update(['status' => 'ended']);

        return response()->json(['success' => true]);
    }

    public function storeChat(Request $request, string $code): JsonResponse
    {
        $validated = $request->validate([
            'sender'  => 'required|string|max:50',
            'message' => 'required|string|max:5000',
        ]);

        MeetingChat::create([
            'meeting_code' => $code,
            'sender'       => $validated['sender'],
            'message'      => $validated['message'],
        ]);

        return response()->json(['success' => true]);
    }

    public function storeRecording(Request $request, string $code): JsonResponse
    {
        $request->validate([
            'audio'   => 'required|file|max:204800', // 200 MB max
            'speaker' => 'required|string|max:50',
        ]);

        $file = $request->file('audio');
        $path = $file->store("recordings/{$code}", 'public');

        MeetingRecording::create([
            'meeting_code' => $code,
            'speaker'      => $request->input('speaker'),
            'file_path'    => $path,
            'file_size'    => $file->getSize(),
        ]);

        return response()->json(['success' => true]);
    }

    private function generateUniqueCode(): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz';

        do {
            $part1 = substr(str_shuffle($chars), 0, 3);
            $part2 = substr(str_shuffle($chars), 0, 4);
            $part3 = substr(str_shuffle($chars), 0, 3);
            $code  = "$part1-$part2-$part3";
        } while (Meeting::where('meeting_code', $code)->exists());

        return $code;
    }
}
