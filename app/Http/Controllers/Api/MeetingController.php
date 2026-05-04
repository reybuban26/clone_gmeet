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

    public function storeChat(Request $request, string $code)
    {
        try {
            $chat = new MeetingChat();
            $chat->meeting_code = $code;
            $chat->sender = $request->input('sender');
            $messageText = $request->input('message');

            // DAGDAG ITO: I-check kung may file na ipinasa ang Vue
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                
                // Gumawa ng unique na pangalan para sa file
                $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                
                // I-save sa storage/app/public/chat_files
                $path = $file->storeAs('chat_files', $filename, 'public');
                
                // Kunin ang public URL link ng na-save na file
                $fileUrl = asset('storage/' . $path);
                
                // I-format ang ise-save sa database (Dito babasahin ng Filament yung link)
                $messageText = "Attached File: " . $fileUrl;
            }

            $chat->message = $messageText;
            $chat->save(); 

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function storeRecording(Request $request, string $code)
    {
        try {
            if ($request->hasFile('audio')) {
                $file = $request->file('audio');
                $path = $file->store('recordings', 'public');
                
                $recording = new MeetingRecording();
                $recording->meeting_code = $code;
                $recording->speaker = $request->input('speaker', 'Unknown');
                $recording->file_size = $file->getSize();
                $recording->file_path = $path; 
                $recording->save();

                return response()->json(['status' => 'success', 'path' => $path]);
            }
            return response()->json(['error' => 'No audio file provided'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
