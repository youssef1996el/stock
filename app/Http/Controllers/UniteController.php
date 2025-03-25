<?php

namespace App\Http\Controllers;

use App\Models\Unite;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UniteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $dataUnite = DB::table('unite as u')
                ->join('users as us', 'us.id', 'u.iduser')
                ->whereNull('u.deleted_at')
                ->select(
                    'u.id',
                    'u.name',
                    'us.name as username',
                    'u.created_at'
                );

            return DataTables::of($dataUnite)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';

                    if (auth()->user()->can('Unité-modifier')) {
                        // Edit button
                        $btn .= '<a href="#" class="btn btn-sm bg-primary-subtle me-1 editUnite"
                                    data-id="' . $row->id . '">
                                    <i class="fa-solid fa-pen-to-square text-primary"></i>
                                </a>';
                    }

                    if (auth()->user()->can('Unité-supprimer')) {
                        // Delete button
                        $btn .= '<a href="#" class="btn btn-sm bg-danger-subtle deleteUnite"
                                    data-id="' . $row->id . '" data-bs-toggle="tooltip" 
                                    title="Supprimer Unité">
                                    <i class="fa-solid fa-trash text-danger"></i>
                                </a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        
        return view('unite.index', [
            'unites' => Unite::latest('id')->paginate(10)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if user has permission to add unites
        if (!auth()->user()->can('Unité-ajoute')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission d\'ajouter des unités'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ], [
            'required' => 'Le champ :attribute est requis.',
        ], [
            'name' => 'nom',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ], 400);
        }

        // Check if unite already exists with the same name
        $exists = Unite::where('name', $request->name)->exists();
            
        if ($exists) {
            return response()->json([
                'status' => 409, // Conflict status code
                'message' => 'Cette unité existe déjà',
            ], 409);
        }

        $unite = Unite::create([
            'name' => $request->name,
            'iduser' => Auth::user()->id,
        ]);

        if($unite) {
            return response()->json([
                'status' => 200,
                'message' => 'Unité créée avec succès',
            ]);
        } else { 
            return response()->json([
                'status' => 500,
                'message' => 'Quelque chose ne va pas'
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Check if user has permission to modify unites
        if (!auth()->user()->can('Unité-modifier')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission de modifier des unités'
            ], 403);
        }

        $unite = Unite::find($id);
        
        if (!$unite) {
            return response()->json([
                'status' => 404,
                'message' => 'Unité non trouvée'
            ], 404);
        }
        
        return response()->json($unite);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        // Check if user has permission to modify unites
        if (!auth()->user()->can('Unité-modifier')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission de modifier des unités'
            ], 403);
        }

        $unite = Unite::find($request->id);
        
        if (!$unite) {
            return response()->json([
                'status' => 404,
                'message' => 'Unité non trouvée'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ], [
            'required' => 'Le champ :attribute est requis.',
        ], [
            'name' => 'nom',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ], 400);
        }
        
        // Check if another unite with the same name exists
        $exists = Unite::where('name', $request->name)
            ->where('id', '!=', $request->id)
            ->exists();
            
        if ($exists) {
            return response()->json([
                'status' => 409, // Conflict status code
                'message' => 'Cette unité existe déjà',
            ], 409);
        }

        $unite->name = $request->name;
        $saved = $unite->save();
        
        if ($saved) {
            return response()->json([
                'status' => 200,
                'message' => 'Unité mise à jour avec succès',
            ]);
        } else {
            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors de la mise à jour de l\'unité',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        // Check if user has permission to delete unites
        if (!auth()->user()->can('Unité-supprimer')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission de supprimer des unités'
            ], 403);
        }

        $unite = Unite::find($request->id);

        if (!$unite) {
            return response()->json([
                'status' => 404,
                'message' => 'Unité non trouvée'
            ], 404);
        }

        if ($unite->delete()) {
            return response()->json([
                'status' => 200,
                'message' => 'Unité supprimée avec succès'
            ]);
        }

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la suppression'
        ], 500);
    }
}