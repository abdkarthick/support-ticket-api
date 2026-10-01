<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Base query with role filter
        $query = Ticket::query();
        if ($user->role === 'agent') {
            $query->where('assigned_to', $user->id);
        } elseif ($user->role === 'user') {
            $query->where('user_id', $user->id);
        }

        $stats = [
            'total' => (clone $query)->count(),
            'open' => (clone $query)->where('status', 'open')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'resolved' => (clone $query)->where('status', 'resolved')->count(),
            'closed' => (clone $query)->where('status', 'closed')->count(),
            'by_priority' => (clone $query)->select('priority', DB::raw('count(*) as count'))->groupBy('priority')->pluck('count', 'priority'),
        ];

        // Only include by_category if column and relationship exists
        if (Schema::hasColumn('tickets', 'category_id')) {
            try {
                $stats['by_category'] = (clone $query)->select('category_id', DB::raw('count(*) as count'))
                    ->groupBy('category_id')->with('category')
                    ->get()->mapWithKeys(fn ($item) => [$item->category->name ?? $item->category_id => $item->count]);
            } catch (\Exception $e) {
                $stats['by_category'] = [];
            }
        } else {
            $stats['by_category'] = [];
        }

        return response()->json($stats);
    }

    public function notifications(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->paginate(15);

        return response()->json($notifications);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['message' => 'Marked as read']);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All marked as read']);
    }
}
