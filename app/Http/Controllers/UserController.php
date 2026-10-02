<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\AdminLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    

    /**
     * Get the current user's active login session
     */
    private function getActiveSession()
    {
        $user = Auth::user();
        if (!$user) return null;
        return \App\Models\LoginSession::where('user_id', $user->id)
            ->whereNull('logout_time')
            ->orderBy('login_time', 'desc')
            ->first();
    }

    /**
     * Check if current user has a specific permission
     */
    private function hasPermission($permissionName)
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        
        $permission = Permission::where('name', $permissionName)->first();
        if (!$permission) {
            return false;
        }
        
        return DB::table('role_permissions')
            ->where('role_id', $user->role_id)
            ->where('permission_id', $permission->id)
            ->exists();
    }


    /**
     * Get current user's permissions
     */
    public function permissions()
    {
        $user = Auth::user();
        
        // Get all permissions for the user's role
        $permissions = DB::table('role_permissions')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->where('role_permissions.role_id', $user->role_id)
            ->pluck('permissions.name')
            ->toArray();
        
        return response()->json([
            'permissions' => $permissions
        ]);
    }

    public function index(Request $request)
    {
        if (!$this->hasPermission('Security_read_all')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access. You do not have permission to view users.'
            ], 403);
        }
        
        $query = User::with('role');


        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'LIKE', "%{$search}%")
                  ->orWhere('full_name', 'LIKE', "%{$search}%");
            });
        }

        $users = $query->paginate(10);
        return response()->json($users);
    }

    public function store(Request $request)
    {
        if (!$this->hasPermission('Security_create')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access. You do not have permission to create users.'
            ], 403);
        }

        $authUser = Auth::user();



        // Build validation rules
        $rules = [
            'username'        => 'required|string|max:255|unique:users,username|regex:/^[^\s@]+@[^\s@]+\.[^\s@]+$/',
            'full_name'       => 'required|string|max:255|regex:/^[A-Za-z\s\.\-]+$/',
            'phone_no'        => 'nullable|string|max:20',
            'password'        => 'required|string|min:6|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'role_id'         => 'required|exists:roles,id',
           
        ];

        $messages = [
            'username.required'       => 'Username is required',
            'username.regex'          => 'Please enter a valid email address',
            'full_name.required'      => 'Full name is required',
            'full_name.regex'         => 'Full name can only contain letters, spaces, periods, and hyphens',
            'phone_no.max'            => 'Phone number must not exceed 20 characters',
            'password.required'       => 'Password is required',
            'password.min'            => 'Password must be at least 6 characters',
            'password.regex'          => 'Password must contain at least one uppercase letter, one lowercase letter, and one number',
            'role_id.required'        => 'Role is required',
            'nic.required'            => 'NIC is required',
            'nic.unique'              => 'This NIC is already registered',
            'nic.max'                 => 'NIC must not exceed 15 characters',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors'  => $validator->errors()
            ], 422);
        }

       

        $user = User::create([
            'username'        => $request->username,
            'full_name'       => $request->full_name,
            'phone_no'        => $request->phone_no ?? null,
            'nic'             => $request->nic ?? null,
            'password'        => Hash::make($request->password),
            'role_id'         => $request->role_id,
            'is_active'       => true
        ]);

        // Admin Log
        $currentUser = Auth::user();
        $session = $this->getActiveSession();
        if ($session) {
            AdminLog::create([
                'session_id'  => $session->id,
                'module'      => 'Security',
                'action_type' => 'User Created',
                'performed_by'=> $currentUser->full_name,
                'details'     => 'User ID U' . str_pad($user->id, 3, '0', STR_PAD_LEFT) . ' ' . $user->full_name . ' (' . $user->username . ') created.',
                'log'         => 'User Created',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data'    => $user->load('role')
        ], 201);
    }

    public function update(Request $request, $id)
    {
        if (!$this->hasPermission('Security_update')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access. You do not have permission to update users.'
            ], 403);
        }
        
        $user = User::findOrFail($id);
        $authUser = Auth::user();
        

        // Build validation rules
        $rules = [
            'username'        => 'required|string|max:255|regex:/^[^\s@]+@[^\s@]+\.[^\s@]+$/|unique:users,username,'.$id,
            'full_name'       => 'required|string|max:255|regex:/^[A-Za-z\s\.\-]+$/',
            'phone_no'        => 'nullable|string|max:20',
            'role_id'         => 'required|exists:roles,id',
            'password'        => 'nullable|string|min:6|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
        ];



        $messages = [
            'username.required'       => 'Username is required',
            'username.regex'          => 'Please enter a valid email address',
            'full_name.required'      => 'Full name is required',
            'full_name.regex'         => 'Full name can only contain letters, spaces, periods, and hyphens',
            'phone_no.max'            => 'Phone number must not exceed 20 characters',
            'role_id.required'        => 'Role is required',
            'password.min'            => 'Password must be at least 6 characters',
            'password.regex'          => 'Password must contain at least one uppercase letter, one lowercase letter, and one number',
            'nic.required'            => 'NIC is required',
            'nic.unique'              => 'This NIC is already registered',
            'nic.max'                 => 'NIC must not exceed 15 characters',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors'  => $validator->errors()
            ], 422);
        }



        $updateData = [
            'username'        => $request->username,
            'full_name'       => $request->full_name,
            'phone_no'        => $request->phone_no ?? null,
            'nic'             => $request->nic ?? null,
            'role_id'         => $request->role_id,
        ];

        // Only hash and update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        // Admin Log
        $currentUser = Auth::user();
        $session = $this->getActiveSession();
        if ($session) {
            AdminLog::create([
                'session_id'  => $session->id,
                'module'      => 'Security',
                'action_type' => 'User Updated',
                'performed_by'=> $currentUser->full_name,
                'details'     => 'User ID U' . str_pad($user->id, 3, '0', STR_PAD_LEFT) . ' ' . $user->full_name . ' (' . $user->username . ') updated.',
                'log'         => 'User Updated',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data'    => $user->load('role')
        ]);
    }

    // Toggle user active/inactive status
    public function toggleStatus($id)
    {
        if (!$this->hasPermission('Security_update')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access. You do not have permission to update user status.'
            ], 403);
        }

        $user = User::findOrFail($id);


        $user->is_active = !$user->is_active;
        $user->save();

        // Admin Log
        $currentUser = Auth::user();
        $session = $this->getActiveSession();
        if ($session) {
            $action = $user->is_active ? 'User Activated' : 'User Deactivated';
            AdminLog::create([
                'session_id'  => $session->id,
                'module'      => 'Security',
                'action_type' => $action,
                'performed_by'=> $currentUser->full_name,
                'details'     => 'User ID U' . str_pad($user->id, 3, '0', STR_PAD_LEFT) . ' ' . $user->full_name . ' (' . $user->username . ') ' . ($user->is_active ? 'activated.' : 'deactivated.'),
                'log'         => $action,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully',
            'data'    => $user->load('role')
        ]);
    }

    // Get all roles for dropdown
    public function getRoles()
    {
        if (!$this->hasPermission('Security_read_all')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access. You do not have permission to view roles.'
            ], 403);
        }
        
        $roles = Role::all();
        return response()->json($roles);
    }

    // Reset password for logged-in user
    public function resetPassword(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please login first.'
            ], 401);
        }

        // Validate inputs
        $validator = Validator::make($request->all(), [
            'old_password'     => 'required|string',
            'new_password'     => 'required|string|min:6|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'confirm_password' => 'required|string|same:new_password'
        ], [
            'old_password.required'     => 'Current password is required',
            'new_password.required'     => 'New password is required',
            'new_password.min'          => 'New password must be at least 6 characters',
            'new_password.regex'        => 'New password must contain at least one uppercase letter, one lowercase letter, and one number',
            'confirm_password.required' => 'Password confirmation is required',
            'confirm_password.same'     => 'Password confirmation does not match the new password'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Verify old password
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 422);
        }

        // Check that new password is different from old password
        if (Hash::check($request->new_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'New password must be different from current password'
            ], 422);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        // Admin Log
        $session = $this->getActiveSession();
        if ($session) {
            AdminLog::create([
                'session_id'  => $session->id,
                'module'      => 'Security',
                'action_type' => 'Password Reset',
                'performed_by'=> $user->full_name,
                'details'     => 'User ID U' . str_pad($user->id, 3, '0', STR_PAD_LEFT) . ' ' . $user->full_name . ' (' . $user->username . ') changed their password.',
                'log'         => 'Password Reset',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }
}