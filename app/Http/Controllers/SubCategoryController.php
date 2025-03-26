<?php

namespace App\Http\Controllers;

use App\Models\SubCategory;
use App\Models\Category;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $dataSubCategory = DB::table('sub_categories as sc')
                ->join('users as u', 'u.id', 'sc.iduser')
                ->join('categories as c', 'c.id', 'sc.id_categorie')
                ->whereNull('sc.deleted_at')
                ->select(
                    'sc.id',
                    'sc.name',
                    'c.name as category_name',
                    'u.name as username',
                    'sc.created_at'
                );

            return DataTables::of($dataSubCategory)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';

                    if (auth()->user()->can('Famille-modifier')) {
                        // Edit button
                        $btn .= '<a href="#" class="btn btn-sm bg-primary-subtle me-1 editSubCategory"
                                    data-id="' . $row->id . '">
                                    <i class="fa-solid fa-pen-to-square text-primary"></i>
                                </a>';
                    }

                    if (auth()->user()->can('Famille-supprimer')) {
                        // Delete button
                        $btn .= '<a href="#" class="btn btn-sm bg-danger-subtle deleteSubCategory"
                                    data-id="' . $row->id . '" data-bs-toggle="tooltip" 
                                    title="Supprimer Sous-catégorie">
                                    <i class="fa-solid fa-trash text-danger"></i>
                                </a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        
        $categories = Category::all();
        
        return view('subcategory.index', [
            'subcategories' => SubCategory::latest('id')->paginate(10),
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if user has permission to add subcategories
        if (!auth()->user()->can('Famille-ajoute')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission d\'ajouter des sous-catégories'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'id_categorie' => 'required|exists:categories,id',
        ], [
            'required' => 'Le champ :attribute est requis.',
            'exists' => 'La catégorie sélectionnée n\'existe pas.',
        ], [
            'name' => 'nom',
            'id_categorie' => 'catégorie',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ], 400);
        }

        // Check if subcategory already exists with the same name in the same category
        $exists = SubCategory::where('name', $request->name)
            ->where('id_categorie', $request->id_categorie)
            ->exists();
            
        if ($exists) {
            return response()->json([
                'status' => 409, // Conflict status code
                'message' => 'Cette sous-catégorie existe déjà pour cette catégorie',
            ], 409);
        }

        $subcategory = SubCategory::create([
            'name' => $request->name,
            'id_categorie' => $request->id_categorie,
            'iduser' => Auth::user()->id,
        ]);

        if($subcategory) {
            return response()->json([
                'status' => 200,
                'message' => 'Sous-catégorie créée avec succès',
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
        // Check if user has permission to modify subcategories
        if (!auth()->user()->can('Famille-modifier')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission de modifier des sous-catégories'
            ], 403);
        }

        $subcategory = SubCategory::find($id);
        
        if (!$subcategory) {
            return response()->json([
                'status' => 404,
                'message' => 'Sous-catégorie non trouvée'
            ], 404);
        }
        
        return response()->json($subcategory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        // Check if user has permission to modify subcategories
        if (!auth()->user()->can('Famille-modifier')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission de modifier des sous-catégories'
            ], 403);
        }

        $subcategory = SubCategory::find($request->id);
        
        if (!$subcategory) {
            return response()->json([
                'status' => 404,
                'message' => 'Sous-catégorie non trouvée'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'id_categorie' => 'required|exists:categories,id',
        ], [
            'required' => 'Le champ :attribute est requis.',
            'exists' => 'La catégorie sélectionnée n\'existe pas.',
        ], [
            'name' => 'nom',
            'id_categorie' => 'catégorie',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ], 400);
        }
        
        // Check if another subcategory with the same name exists in the same category
        $exists = SubCategory::where('name', $request->name)
            ->where('id_categorie', $request->id_categorie)
            ->where('id', '!=', $request->id) // Exclude current record
            ->exists();
            
        if ($exists) {
            return response()->json([
                'status' => 409, // Conflict status code
                'message' => 'Cette sous-catégorie existe déjà pour cette catégorie',
            ], 409);
        }

        $subcategory->name = $request->name;
        $subcategory->id_categorie = $request->id_categorie;
        $saved = $subcategory->save();
        
        if ($saved) {
            return response()->json([
                'status' => 200,
                'message' => 'Sous-catégorie mise à jour avec succès',
            ]);
        } else {
            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors de la mise à jour de la sous-catégorie',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        // Check if user has permission to delete subcategories
        if (!auth()->user()->can('Famille-supprimer')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission de supprimer des sous-catégories'
            ], 403);
        }

        $subcategory = SubCategory::find($request->id);

        if (!$subcategory) {
            return response()->json([
                'status' => 404,
                'message' => 'Sous-catégorie non trouvée'
            ], 404);
        }

        if ($subcategory->delete()) {
            return response()->json([
                'status' => 200,
                'message' => 'Sous-catégorie supprimée avec succès'
            ]);
        }

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la suppression'
        ], 500);
    }
}