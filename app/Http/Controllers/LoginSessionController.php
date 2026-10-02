<?php

namespace App\Http\Controllers;

use App\Models\LoginSession;
use Illuminate\Http\Request;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class LoginSessionController extends Controller
{
    private function hasPermission($permissionName)
    {
        $user = auth()->user();
        if (!$user) return false;

        $permission = Permission::where('name', $permissionName)->first();
        if (!$permission) return false;

        return DB::table('role_permissions')
            ->where('role_id', $user->role_id)
            ->where('permission_id', $permission->id)
            ->exists();
    }

   public function index(Request $request)
{
    if (!$this->hasPermission('Security_read_all')) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized access.'
        ], 403);
    }

    // Automatically close old active sessions (e.g. older than 8 hours)
    try {
        $oldSessions = LoginSession::whereNull('logout_time')
            ->where('login_time', '<', now()->subHours(8))
            ->get();
            
        foreach ($oldSessions as $session) {
            // Set logout_time to 2 hours after login as an estimate
            $session->logout_time = \Carbon\Carbon::parse($session->login_time)->addHours(2);
            $session->save();
        }
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::warning('Failed to auto-close old login sessions: ' . $e->getMessage());
    }

    $perPage = $request->get('per_page', 10);
    $search = trim($request->search ?? '');

    $query = LoginSession::with(['user.organization'])
        ->orderByDesc('id');

    // ✅ SEARCH FIXED
    if (!empty($search) && strlen($search) >= 3) {

        $query->where(function ($q) use ($search) {

            // login_sessions table
            $q->where('id', 'LIKE', "%{$search}%")
                ->orWhere('user_id', 'LIKE', "%{$search}%")
                ->orWhere('login_time', 'LIKE', "%{$search}%")
                ->orWhere('logout_time', 'LIKE', "%{$search}%")
                ->orWhere('ip_address', 'LIKE', "%{$search}%")

            // user relation
            ->orWhereHas('user', function ($u) use ($search) {
                $u->where('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('username', 'LIKE', "%{$search}%")


                  // organization
                  ->orWhereHas('organization', function ($org) use ($search) {
                      $org->where('name', 'LIKE', "%{$search}%");
                  });
            });
        });
    }

    $sessions = $query->paginate($perPage);

    $data = collect($sessions->items())->map(function ($session) {
        return [
            'id' => $session->id,
            'user_id' => $session->user_id,
            'user_name' => $session->user?->full_name,
            'organization_name' => $session->user?->organization?->name ?? '-',
            'login_time' => $session->login_time,
            'logout_time' => $session->logout_time,
            'ip_address' => $session->ip_address,
        ];
    });

    return response()->json([
        'data' => $data,
        'current_page' => $sessions->currentPage(),
        'last_page' => $sessions->lastPage(),
        'per_page' => $sessions->perPage(),
        'total' => $sessions->total(),
    ]);
}
}