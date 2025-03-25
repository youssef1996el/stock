<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(request $request)
    {
        // Check if user has permission to view users
        if (!auth()->user()->can('utilisateur')) {
            return redirect()->back()->with('error', 'Vous n\'avez pas la permission d\'accéder aux utilisateurs');
        }
       
        if ($request->ajax()) {
            $dataUser = DB::table('users')
                ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->whereNull('users.deleted_at')
                ->select(
                    'users.id',
                    'users.name',
                    'users.email',
                    'users.password',
                    
                    'users.created_at',
                    DB::raw("GROUP_CONCAT(roles.name SEPARATOR ', ') as roles")
                )
                ->groupBy('users.id', 'users.name', 'users.email', 'users.password', 'users.created_at');
    
            return DataTables::of($dataUser)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';
    
                    if (auth()->user()->can('utilisateur-modifier')) {
                        // Edit button
                        $btn .= '<a href="#" class="btn btn-sm bg-primary-subtle me-1 editUser"
                                    data-id="' . $row->id . '"    title="modifier roles"
                                    >
                                    <i class="fa-solid fa-pen-to-square text-primary"></i>
                                </a>';
                    }
    
                    if (auth()->user()->can('utilisateur-supprimer')) {
                        // Delete button
                        $btn .= '<a href="#" class="btn btn-sm bg-danger-subtle deleteuser"
                                    data-id="' . $row->id . '" data-bs-toggle="tooltip" 
                                    title="Supprimer roles">
                                    <i class="fa-solid fa-trash text-danger"></i>
                                </a>';
                    }
    
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
                 
                 
        return view('users.index', [
            'users' => User::latest('id')->paginate(3),
            'roles' => Role::pluck('name')->all()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        // Check if user has permission to add users
        if (!auth()->user()->can('utilisateur-ajoute')) {
            abort(403, 'Vous n\'avez pas la permission d\'ajouter des utilisateurs');
        }

        return view('users.create', [
            'roles' => Role::pluck('name')->all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        // Check if user has permission to add users
        if (!auth()->user()->can('utilisateur-ajoute')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission d\'ajouter des utilisateurs'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ], [
            'required' => 'Le champ :attribute est requis.',
            'email.email' => 'Le champ mail doit être une adresse valide.',
            'email.unique' => 'Cet email est déjà utilisé, veuillez en choisir un autre.',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
        ], [
            'name' => 'nom complet',
            'email' => 'mail',
            'password' => 'mot de passe',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ], 400);
        }
        
        $input = $request->all();
        $input['password'] = Hash::make($request->password);

        $user = User::create($input);
        if($user)
        {
            $user->assignRole($request->roles);
            return response()->json([
                'status' => 200,
                'message' => 'user créée avec succès',
                
            ]);
        }
        else
        { 
            
            return response()->json([
                'status' => 500,
                'message' => 'Quelque chose ne va pas'
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): RedirectResponse
    {
        return redirect()->route('users.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        // Check if user has permission to modify users
        if (!auth()->user()->can('utilisateur-modifier')) {
            abort(403, 'Vous n\'avez pas la permission de modifier des utilisateurs');
        }

        // Check Only Super Admin can update his own Profile
        if ($user->hasRole('Super Admin')){
            if($user->id != auth()->user()->id){
                abort(403, 'USER DOES NOT HAVE THE RIGHT PERMISSIONS');
            }
        }

        return view('users.edit', [
            'user' => $user,
            'roles' => Role::pluck('name')->all(),
            'userRoles' => $user->roles->pluck('name')->all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        // Check if user has permission to modify users
        if (!auth()->user()->can('utilisateur-modifier')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission de modifier des utilisateurs'
            ], 403);
        }
       
        $user = User::find($request->id);

        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

        // Check Super Admin protection
        if ($user->hasRole('Super Admin') && $user->id != auth()->user()->id) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas le droit de modifier le profile Super Admin'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'password' => 'nullable|min:6',
        ], [
            'required' => 'Le champ :attribute est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
        ], [
            'name' => 'nom complet',
            'email' => 'mail',
            'password' => 'mot de passe',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ], 400);
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->filled('password') ? Hash::make($request->password) : $user->password,
        ]);

        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Utilisateur mis à jour avec succès',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user, Request $request)
    {
        // Check if user has permission to delete users
        if (!auth()->user()->can('utilisateur-supprimer')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission de supprimer des utilisateurs'
            ], 403);
        }

        $user = User::find($request->id);

        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

        // Check if user is trying to delete Super Admin or themselves
        if ($user->hasRole('Super Admin') || $user->id == auth()->user()->id) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous ne pouvez pas supprimer le Super Admin ou votre propre compte'
            ], 403);
        }

        // Delete the user
        if ($user->delete()) {
            return response()->json([
                'status' => 200,
                'message' => 'Utilisateur supprimé avec succès'
            ]);
        }

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la suppression'
        ], 500);
    }
}