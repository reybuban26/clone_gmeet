<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AudioController extends Controller
{
    public function serve($path)
    {
        $disk = Storage::disk('public');
        
        if (!$disk->exists($path)) {
            abort(404);
        }

        $fullPath = $disk->path($path);
        $size = filesize($fullPath);
        $mime = mime_content_type($fullPath) ?: 'audio/webm';
        $name = basename($path);

        $request = request();
        $rangeHeader = $request->header('Range');

        if ($rangeHeader) {
            // Parse Range header: bytes=0-100
            list($unit, $range) = explode('=', $rangeHeader, 2);
            if ($unit !== 'bytes') {
                abort(416);
            }

            list($start, $end) = explode('-', $range, 2);
            $start = $start === '' ? 0 : (int)$start;
            $end = $end === '' ? $size - 1 : (int)$end;
            $length = $end - $start + 1;

            if ($start >= $size || $end >= $size) {
                abort(416);
            }

            $headers = [
                'Content-Type' => $mime,
                'Content-Length' => $length,
                'Content-Range' => "bytes {$start}-{$end}/{$size}",
                'Accept-Ranges' => 'bytes',
                'Content-Disposition' => "inline; filename=\"{$name}\"",
            ];

            return new StreamedResponse(function () use ($fullPath, $start, $length) {
                $stream = fopen($fullPath, 'rb');
                fseek($stream, $start);
                echo fread($stream, $length);
                fclose($stream);
            }, 206, $headers);
        }

        // Full file response
        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Accept-Ranges' => 'bytes',
            'Content-Disposition' => "inline; filename=\"{$name}\"",
        ]);
    }
}