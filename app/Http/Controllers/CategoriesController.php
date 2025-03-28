<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $dataCategory = DB::table('categories as c')
                                ->join('users as u','u.id','c.iduser')
                                ->whereNull('c.deleted_at')
                ->select(
                    'c.id',
                    'c.name',
                    'u.name as username',
                    'c.created_at'
                );

            return DataTables::of($dataCategory)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';

                    if (auth()->user()->can('Categories-modifier')) {
                        // Edit button
                        $btn .= '<a href="#" class="btn btn-sm bg-primary-subtle me-1 editCategory"
                                    data-id="' . $row->id . '">
                                    <i class="fa-solid fa-pen-to-square text-primary"></i>
                                </a>';
                    }

                    if (auth()->user()->can('Categories-supprimer')) {
                        // Delete button
                        $btn .= '<a href="#" class="btn btn-sm bg-danger-subtle deleteCategory"
                                    data-id="' . $row->id . '" data-bs-toggle="tooltip" 
                                    title="Supprimer Catégorie">
                                    <i class="fa-solid fa-trash text-danger"></i>
                                </a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
             
        return view('categories.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if user has permission to add categories
        if (!auth()->user()->can('Categories-ajoute')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission d\'ajouter des catégories'
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
    
        // Vérification insensible à la casse et en supprimant les espaces
        $cleanedName = strtolower(trim($request->name));
        $exists = Category::whereRaw('LOWER(TRIM(name)) = ?', [$cleanedName])->count();
            
        if ($exists > 0) {
            return response()->json([
                'status' => 422, // Utilisation de 422 pour être cohérent avec les autres fonctions
                'message' => 'Cette catégorie existe déjà',
            ], 422);
        }
    
        $category = Category::create([
            'name' => trim($request->name), // Suppression des espaces
            'iduser' => Auth::user()->id,
        ]);
    
        if($category) {
            return response()->json([
                'status' => 200,
                'message' => 'Catégorie créée avec succès',
            ]);
        } else { 
            return response()->json([
                'status' => 500,
                'message' => 'Une erreur est survenue. Veuillez réessayer.'
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Check if user has permission to modify categories
        if (!auth()->user()->can('Categories-modifier')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission de modifier des catégories'
            ], 403);
        }

        $category = Category::find($id);
        
        if (!$category) {
            return response()->json([
                'status' => 404,
                'message' => 'Catégorie non trouvée'
            ], 404);
        }
        
        return response()->json($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        // Check if user has permission to modify categories
        if (!auth()->user()->can('Categories-modifier')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission de modifier des catégories'
            ], 403);
        }
    
        $category = Category::find($request->id);
        
        if (!$category) {
            return response()->json([
                'status' => 404,
                'message' => 'Catégorie non trouvée'
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
        
        // Vérification insensible à la casse et en supprimant les espaces
        $cleanedName = strtolower(trim($request->name));
        $exists = Category::whereRaw('LOWER(TRIM(name)) = ?', [$cleanedName])
            ->where('id', '!=', $request->id) // Exclure l'enregistrement actuel
            ->count();
            
        if ($exists > 0) {
            return response()->json([
                'status' => 422, // Utilisation de 422 pour être cohérent avec les autres fonctions
                'message' => 'Cette catégorie existe déjà',
            ], 422);
        }
    
        $category->name = trim($request->name); // Suppression des espaces
        $saved = $category->save();
        
        if ($saved) {
            return response()->json([
                'status' => 200,
                'message' => 'Catégorie mise à jour avec succès',
            ]);
        } else {
            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors de la mise à jour de la catégorie',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        // Check if user has permission to delete categories
        if (!auth()->user()->can('Categories-supprimer')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission de supprimer des catégories'
            ], 403);
        }

        $category = Category::find($request->id);

        if (!$category) {
            return response()->json([
                'status' => 404,
                'message' => 'Catégorie non trouvée'
            ], 404);
        }

        if ($category->delete()) {
            return response()->json([
                'status' => 200,
                'message' => 'Catégorie supprimée avec succès'
            ]);
        }

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la suppression'
        ], 500);
    }
}