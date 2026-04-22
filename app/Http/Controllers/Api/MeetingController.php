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

    public function storeChat(Request $request, $code)
    {
        // Hanapin ang meeting base sa code
        $meeting = Meeting::where('meeting_code', $code)->firstOrFail();

        // I-save ang chat
        $chat = new MeetingChat();
        $chat->meeting_id = $meeting->id;
        $chat->sender = $request->input('sender');
        $chat->message = $request->input('message');
        // Siguraduhing sinasalo mo ang 'time' galing sa Vue!
        $chat->time = $request->input('time'); 
        $chat->save();

        return response()->json(['status' => 'success']);
    }

    public function storeRecording(Request $request, $code)
    {
        $meeting = Meeting::where('meeting_code', $code)->firstOrFail();

        // I-check kung may ipinasang file na ang pangalan ay 'audio'
        if ($request->hasFile('audio')) {
            $file = $request->file('audio');
            // I-save ang file sa storage/app/public/recordings
            $path = $file->store('recordings', 'public');
            $size = $file->getSize();

            // I-save sa database (table: meeting_recordings)
            $recording = new MeetingRecording();
            $recording->meeting_id = $meeting->id;
            $recording->speaker = $request->input('speaker', 'Unknown');
            $recording->file_size = $size;
            $recording->audio = $path; // Ito yung column na nakikita mo sa Filament
            $recording->save();

            return response()->json(['status' => 'success', 'path' => $path]);
        }

        return response()->json(['error' => 'No audio file provided'], 400);
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
