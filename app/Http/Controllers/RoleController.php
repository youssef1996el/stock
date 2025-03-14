<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use DB;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
class RoleController extends Controller
{
    /* public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-role|edit-role|delete-role', ['only' => ['index','show']]);
        $this->middleware('permission:create-role', ['only' => ['create','store']]);
        $this->middleware('permission:edit-role', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-role', ['only' => ['destroy']]);
    } */
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if($request->ajax())
        {
            $roles = Role::with('permissions')->select('id', 'name');
            return DataTables::of($roles)
            ->addIndexColumn() // Adds a column for row numbers
            ->addColumn('permissions', function ($role) {
                return $role->permissions->pluck('name')->implode(', '); // Convert permissions to a string
            })
            ->addColumn('actions', function ($role) {
                $btn = '';
    
                    // زر التعديل
                    $btn .= '<a href="#" class="btn btn-sm bg-primary-subtle me-1 editRole"
                                data-id="' . $role->id . '"  
                                >
                                <i class="fa-solid fa-pen-to-square text-primary"></i>
                            </a>';
    
                    // زر الحذف
                    $btn .= '<a href="#" class="btn btn-sm bg-danger-subtle deleterole"
                                data-id="' . $role->id . '" data-bs-toggle="tooltip" 
                                title="Supprimer company">
                                <i class="fa-solid fa-trash text-danger"></i>
                            </a>';
    
                    return $btn;
            })
            ->rawColumns(['permissions', 'actions']) // Allow HTML rendering
            ->make(true);
        }


        return view('roles.index', [
            'roles' => Role::with('permissions')->orderBy('id', 'DESC')->paginate(3),
            'permissions' => Permission::get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('roles.create', [
            'permissions' => Permission::get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'permissions' => 'required', // ✅ التحقق من البريد الإلكتروني الفريد
            
        ],[
            'name' => 'nom role',
            'permissions' => 'autorisations',
            
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ], 400); // تأكد من وضع كود الحالة HTTP هنا
        }
        $role = Role::create(['name' => $request->name]);

        $permissions = Permission::whereIn('id', $request->permissions)->get(['name'])->toArray();
        
        $role->syncPermissions($permissions);
        if($role)
        {
            return response()->json([
                'status'    => 200,
                'message'   => 'New role is added successfully.'
            ]);
        }
        else
        {
            return response()->json([
                'status'    => 500,
                'message'   => 'Une erreur est survenue, veuillez réessayer .'
            ]); 
        }

        /* return redirect()->route('roles.index')
                ->withSuccess('New role is added successfully.'); */
    }

    /**
     * Display the specified resource.
     */
    public function show(): RedirectResponse
    {
        return redirect()->route('roles.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role): View
    {
        if($role->name=='Super Admin'){
            abort(403, 'SUPER ADMIN ROLE CAN NOT BE EDITED');
        }

        $rolePermissions = DB::table("role_has_permissions")->where("role_id",$role->id)
            ->pluck('permission_id')
            ->all();

        return view('roles.edit', [
            'role' => $role,
            'permissions' => Permission::get(),
            'rolePermissions' => $rolePermissions
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        // ✅ Ensure role exists in the database
        $role = Role::find($request->id);

        if (!$role) {
            return response()->json([
                'status' => 404,
                'message' => 'Role not found!',
            ]);
        }

        // ✅ Validate request
        $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        if ($request->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $request->messages(),
            ], 400); // تأكد من وضع كود الحالة HTTP هنا
        }

        // ✅ Update role name
        $role->update(['name' => $request->name]);

        // ✅ Sync permissions
        $permissions = Permission::whereIn('id', $request->permissions)->pluck('name')->toArray();
        $role->syncPermissions($permissions);

        return response()->json([
            'status' => 200,
            'message' => 'Role updated successfully.',
        ]);
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Role $role)
    {
         // Validate the incoming request to ensure it contains an 'id' parameter
        $request->validate([
            'id' => 'required|exists:roles,id', // Validate that the role exists
        ]);

        // Retrieve the role using the provided ID
        $role = Role::findOrFail($request->id);

        // Check if the role is 'Super Admin'
        if ($role->name === 'Super Admin') {
            return response()->json([
                'status' => 403,
                'message' => 'SUPER ADMIN ROLE CANNOT BE DELETED'
            ], 403);
        }

        // Ensure the current user is not trying to delete their own role
        $user = Auth::user();

        if ($user && $user->hasRole($role->name)) {
            return response()->json([
                'status' => 403,
                'message' => 'CANNOT DELETE SELF-ASSIGNED ROLE'
            ], 403);
        }

        // Proceed with deleting the role
        $role->delete();

        // Return a success response
        return response()->json([
            'status' => 200,
            'message' => 'Role deleted successfully.'
        ]);
    }

}