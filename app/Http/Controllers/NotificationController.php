<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NotificationController extends Controller
{
    /** Sepuluh notifikasi terbaru milik pengguna yang sedang masuk. */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'unread' => $user->unreadNotifications()->count(),
            'items' => $user->notifications()->latest()->limit(10)->get()->map(fn ($n) => [
                'id' => $n->id,
                'message' => (string) ($n->data['message'] ?? ''),
                'read' => $n->read_at !== null,
                'ago' => $n->created_at->diffForHumans(),
                'href' => route('notifications.show', $n->id, absolute: false),
            ])->all(),
        ]);
    }

    /** Tandai dibaca, lalu buka tujuannya. Selalu lewat relasi milik pengguna, jadi milik orang lain 404. */
    public function show(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()->notifications()->findOrFail($notification);

        $item->markAsRead();

        $url = $item->data['url'] ?? null;
        $internal = is_string($url) && str_starts_with($url, '/') && ! str_starts_with($url, '//');

        return redirect($internal ? $url : route('dashboard'));
    }

    public function readAll(Request $request): Response
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->noContent();
    }
}
