<?php

namespace App\Http\Controllers;

use App\Models\ContactClick;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

/**
 * endpoint خفيف بيسجّل ضغطة زر تواصل (اتصال/واتساب) — بيتنده من
 * navigator.sendBeacon عند الضغط. بيرجّع 204 دايماً ومايكسرش حاجة.
 */
class TrackClickController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $type = (string) ($request->query('type') ?? $request->input('type'));

        if (in_array($type, ContactClick::TYPES, true)) {
            try {
                if (Schema::hasTable('contact_clicks')) {
                    ContactClick::create([
                        'type' => $type,
                        'path' => mb_substr((string) $request->query('path', ''), 0, 512) ?: null,
                        'ip'   => (string) $request->ip(),
                    ]);
                }
            } catch (\Throwable $e) {
                // تجاهُل — التتبّع مش حرج.
            }
        }

        return response()->noContent();
    }
}
