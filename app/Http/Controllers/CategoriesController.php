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

                    // Edit button
                    $btn .= '<a href="#" class="btn btn-sm bg-primary-subtle me-1 editCategory"
                                data-id="' . $row->id . '">
                                <i class="fa-solid fa-pen-to-square text-primary"></i>
                            </a>';

                    // Delete button
                    $btn .= '<a href="#" class="btn btn-sm bg-danger-subtle deleteCategory"
                                data-id="' . $row->id . '" data-bs-toggle="tooltip" 
                                title="Supprimer Catégorie">
                                <i class="fa-solid fa-trash text-danger"></i>
                            </a>';

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

        // Check if category already exists with the same name
        $exists = Category::where('name', $request->name)->exists();
            
        if ($exists) {
            return response()->json([
                'status' => 409, // Conflict status code
                'message' => 'Cette catégorie existe déjà',
            ], 409);
        }

        $category = Category::create([
            'name' => $request->name,
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
                'message' => 'Quelque chose ne va pas'
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
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
        
        // Check if another category with the same name exists
        $exists = Category::where('name', $request->name)
            ->where('id', '!=', $request->id) // Exclude current record
            ->exists();
            
        if ($exists) {
            return response()->json([
                'status' => 409, // Conflict status code
                'message' => 'Cette catégorie existe déjà',
            ], 409);
        }

        $category->name = $request->name;
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