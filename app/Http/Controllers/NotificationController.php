<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

// Controller untuk mengelola notifikasi admin (get, mark as read, delete)
class NotificationController extends Controller
{
    // Mengambil semua notifikasi dengan pagination dan filter (status, type)
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 20);
        $status = $request->input('status', 'all');
        $type = $request->input('type');

        $query = Notification::with('triggeredBy:id,name,email')
            ->orderBy('created_at', 'desc');

        // Filter berdasarkan status
        if ($status === 'unread') {
            $query->where('is_read', false);
        } elseif ($status === 'read') {
            $query->where('is_read', true);
        }

        // Filter berdasarkan tipe
        if ($type) {
            $query->where('type', $type);
        }

        $notifications = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'from' => $notifications->firstItem(),
                'to' => $notifications->lastItem(),
            ],
        ]);
    }

    // Menampilkan notifikasi berdasarkan ID dengan info user yang memicu
    public function show(Notification $notification): JsonResponse
    {
        $notification->load('triggeredBy:id,name,email');

        return response()->json([
            'status' => 'success',
            'data' => $notification,
        ]);
    }

    // Menandai notifikasi sebagai sudah dibaca
    public function markAsRead(Notification $notification): JsonResponse
    {
        $notification->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Notifikasi berhasil ditandai sebagai sudah dibaca',
            'data' => $notification,
        ]);
    }

    // Menandai semua notifikasi sebagai sudah dibaca
    public function markAllAsRead(): JsonResponse
    {
        $updated = Notification::where('is_read', false)->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => "Berhasil menandai {$updated} notifikasi sebagai sudah dibaca",
            'data' => [
                'updated_count' => $updated,
            ],
        ]);
    }

    // Mengambil jumlah notifikasi yang belum dibaca
    public function unreadCount(): JsonResponse
    {
        $count = Notification::where('is_read', false)->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'unread_count' => $count,
            ],
        ]);
    }

    // Menghapus notifikasi dari database
    public function destroy(Notification $notification): JsonResponse
    {
        $notification->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Notifikasi berhasil dihapus',
        ]);
    }
}
