<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stock;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Local;
use App\Models\Rayon;
use App\Models\Tva;
use App\Models\Unite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $products = DB::table('products as p')
                ->leftJoin('stock as s', 'p.id', '=', 's.id_product')
                ->leftJoin('categories as c', 'p.id_categorie', '=', 'c.id')
                ->leftJoin('sub_categories as sc', 'p.id_subcategorie', '=', 'sc.id')
                ->leftJoin('locals as l', 'p.id_local', '=', 'l.id')
                ->leftJoin('rayons as r', 'p.id_rayon', '=', 'r.id')
                ->leftJoin('tvas as t', 's.id_tva', '=', 't.id')
                ->leftJoin('unite as u', 's.id_unite', '=', 'u.id')
                ->leftJoin('users as us', 'p.id_user', '=', 'us.id')
                ->select(
                    'p.id',
                    'p.name',
                    'p.code_article',
                    'u.name as unite',
                    'c.name as categorie',
                    'sc.name as famille',
                    'p.emplacement',
                    's.quantite as stock',
                    'p.price_achat',
                    'p.price_vente',
                    't.value as taux_taxe',
                    's.seuil',
                    'p.code_barre',
                    'us.name as username',
                    'p.created_at'
                );

            return DataTables::of($products)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a href="#" class="btn btn-sm bg-primary-subtle me-1 editProduct" data-id="'.$row->id.'">
                            <i class="fa-solid fa-pen-to-square text-primary"></i></a>';
                    $btn .= '<a href="#" class="btn btn-sm bg-danger-subtle deleteProduct" data-id="'.$row->id.'">
                            <i class="fa-solid fa-trash text-danger"></i></a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        
        // Get required data for dropdowns
        $categories = Category::all();
        $subcategories = SubCategory::all();
        $locals = Local::all();
        $rayons = Rayon::all();
        $tvas = Tva::all();
        $unites = Unite::all();
        
        return view('products.index', compact('categories', 'subcategories', 'locals', 'rayons', 'tvas', 'unites'));
    }

    /**
     * Get subcategories for a category.
     */
    public function getSubcategories($categoryId)
    {
        try {
            // Validate the category ID
            $validator = Validator::make(
                ['category_id' => $categoryId],
                ['category_id' => 'required|integer|exists:categories,id']
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => 400,
                    'message' => 'ID de catégorie invalide',
                    'subcategories' => []
                ], 400);
            }

            // Retrieve subcategories with eager loading to improve performance
            $subcategories = SubCategory::where('id_categorie', $categoryId)
                ->select('id', 'name')
                ->orderBy('name', 'asc')
                ->get();
            
            // Log the retrieval for debugging
            Log::info('Subcategories retrieved', [
                'category_id' => $categoryId,
                'count' => $subcategories->count()
            ]);
            
            return response()->json([
                'status' => 200,
                'subcategories' => $subcategories
            ]);
        } catch (\Exception $e) {
            // Log the error with more context
            Log::error('Erreur lors de la récupération des sous-catégories', [
                'category_id' => $categoryId,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors de la récupération des sous-catégories',
                'subcategories' => []
            ], 500);
        }
    }

    /**
     * Get rayons for a local.
     */
    public function getRayons($localId)
    {
        try {
            // Validate the local ID
            $validator = Validator::make(
                ['local_id' => $localId],
                ['local_id' => 'required|integer|exists:locals,id']
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => 400,
                    'message' => 'ID de local invalide',
                    'rayons' => []
                ], 400);
            }

            // Retrieve rayons with eager loading to improve performance
            $rayons = Rayon::where('id_local', $localId)
                ->select('id', 'name')
                ->orderBy('name', 'asc')
                ->get();
            
            // Log the retrieval for debugging
            Log::info('Rayons retrieved', [
                'local_id' => $localId,
                'count' => $rayons->count()
            ]);
            
            return response()->json([
                'status' => 200,
                'rayons' => $rayons
            ]);
        } catch (\Exception $e) {
            // Log the error with more context
            Log::error('Erreur lors de la récupération des rayons', [
                'local_id' => $localId,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors de la récupération des rayons',
                'rayons' => []
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price_achat' => 'required|numeric',
            'price_vente' => 'required|numeric',
            'id_categorie' => 'required|exists:categories,id',
            'id_subcategorie' => 'required|exists:sub_categories,id',
            'id_local' => 'required|exists:locals,id',
            'id_rayon' => 'required|exists:rayons,id',
            'id_unite' => 'required|exists:unite,id',
            'code_barre' => 'nullable|string|max:255',
            'quantite' => 'required|numeric',
            'seuil' => 'required|numeric',
            'id_tva' => 'required|exists:tvas,id',
        ], [
            'required' => 'Le champ :attribute est requis.',
            'numeric' => 'Le champ :attribute doit être un nombre.',
            'exists' => 'La valeur sélectionnée pour :attribute est invalide.',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ], 400);
        }

        try {
            DB::beginTransaction();
            
            // Verify the relationship between category and subcategory
            $subcategory = SubCategory::find($request->id_subcategorie);
            if ($subcategory->id_categorie != $request->id_categorie) {
                return response()->json([
                    'status' => 400,
                    'message' => 'La famille sélectionnée n\'appartient pas à cette catégorie',
                ], 400);
            }
            
            // Verify the relationship between local and rayon
            $rayon = Rayon::find($request->id_rayon);
            if ($rayon->id_local != $request->id_local) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Le rayon sélectionné n\'appartient pas à ce local',
                ], 400);
            }
            
            // Get category and subcategory names for code generation
            $category = Category::find($request->id_categorie);
            
            // Generate code_article
            $code_article = Product::generateCodeArticle(
                $category->name, 
                $subcategory->name
            );
            
            // Create product
            $product = Product::create([
                'name' => $request->name,
                'code_article' => $code_article,
                'unite' => null, // We'll use the id_unite in stock table now
                'price_achat' => $request->price_achat,
                'price_vente' => $request->price_vente,
                'code_barre' => $request->code_barre,
                'id_categorie' => $request->id_categorie,
                'id_subcategorie' => $request->id_subcategorie,
                'id_local' => $request->id_local,
                'id_rayon' => $request->id_rayon,
                'id_user' => Auth::id(),
            ]);
            
            // Update emplacement after creating product
            $product->emplacement = $product->generateEmplacement();
            $product->save();
            
            // Create stock entry
            Stock::create([
                'id_product' => $product->id,
                'id_tva' => $request->id_tva,
                'id_unite' => $request->id_unite,
                'quantite' => $request->quantite,
                'seuil' => $request->seuil,
            ]);
            
            DB::commit();
            
            return response()->json([
                'status' => 200,
                'message' => 'Produit créé avec succès',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error creating product: ' . $e->getMessage(), [
                'request' => $request->all()
            ]);
            
            return response()->json([
                'status' => 500,
                'message' => 'Une erreur est survenue: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $product = Product::with(['stock', 'category', 'subcategory', 'local', 'rayon'])->find($id);
            
            if (!$product) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Produit non trouvé',
                ], 404);
            }
            
            return response()->json($product);
            
        } catch (\Exception $e) {
            Log::error('Error retrieving product for edit: ' . $e->getMessage(), [
                'id' => $id
            ]);
            
            return response()->json([
                'status' => 500,
                'message' => 'Une erreur est survenue: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'price_achat' => 'required|numeric',
            'price_vente' => 'required|numeric',
            'id_categorie' => 'required|exists:categories,id',
            'id_subcategorie' => 'required|exists:sub_categories,id',
            'id_local' => 'required|exists:locals,id',
            'id_rayon' => 'required|exists:rayons,id',
            'id_unite' => 'required|exists:unite,id',
            'code_barre' => 'nullable|string|max:255',
            'quantite' => 'required|numeric',
            'seuil' => 'required|numeric',
            'id_tva' => 'required|exists:tvas,id',
        ], [
            'required' => 'Le champ :attribute est requis.',
            'numeric' => 'Le champ :attribute doit être un nombre.',
            'exists' => 'La valeur sélectionnée pour :attribute est invalide.',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            $product = Product::find($request->id);
            
            if (!$product) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Produit non trouvé',
                ], 404);
            }
            
            // Verify the relationship between category and subcategory
            $subcategory = SubCategory::find($request->id_subcategorie);
            if ($subcategory->id_categorie != $request->id_categorie) {
                return response()->json([
                    'status' => 400,
                    'message' => 'La famille sélectionnée n\'appartient pas à cette catégorie',
                ], 400);
            }
            
            // Verify the relationship between local and rayon
            $rayon = Rayon::find($request->id_rayon);
            if ($rayon->id_local != $request->id_local) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Le rayon sélectionné n\'appartient pas à ce local',
                ], 400);
            }
            
            // Update product
            $product->update([
                'name' => $request->name,
                'price_achat' => $request->price_achat,
                'price_vente' => $request->price_vente,
                'code_barre' => $request->code_barre,
                'id_categorie' => $request->id_categorie,
                'id_subcategorie' => $request->id_subcategorie,
                'id_local' => $request->id_local,
                'id_rayon' => $request->id_rayon,
            ]);
            
            // Update emplacement after updating product
            $product->emplacement = $product->generateEmplacement();
            $product->save();
            
            // Update or create stock
            $stock = Stock::where('id_product', $product->id)->first();
            
            if ($stock) {
                $stock->update([
                    'id_tva' => $request->id_tva,
                    'id_unite' => $request->id_unite,
                    'quantite' => $request->quantite,
                    'seuil' => $request->seuil,
                ]);
            } else {
                Stock::create([
                    'id_product' => $product->id,
                    'id_tva' => $request->id_tva,
                    'id_unite' => $request->id_unite,
                    'quantite' => $request->quantite,
                    'seuil' => $request->seuil,
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'status' => 200,
                'message' => 'Produit mis à jour avec succès',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error updating product: ' . $e->getMessage(), [
                'id' => $request->id,
                'request' => $request->all()
            ]);
            
            return response()->json([
                'status' => 500,'message' => 'Une erreur est survenue: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $product = Product::find($request->id);
            
            if (!$product) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Produit non trouvé',
                ], 404);
            }
            
            $productName = $product->name;
            $productId = $product->id;
            
            // Delete stock first (foreign key constraint)
            Stock::where('id_product', $product->id)->delete();
            
            // Delete product
            $product->delete();
            
            DB::commit();
            
            return response()->json([
                'status' => 200,
                'message' => 'Produit supprimé avec succès',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error deleting product: ' . $e->getMessage(), [
                'id' => $request->id
            ]);
            
            return response()->json([
                'status' => 500,
                'message' => 'Une erreur est survenue: ' . $e->getMessage(),
            ], 500);
        }
    }
}