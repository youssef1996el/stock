<?php

namespace App\Http\Controllers;

use App\Models\Achat;
use App\Models\Product;
use App\Models\Fournisseur;
use App\Models\TempAchat;
use App\Models\LigneAchat;
use App\Models\Category;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class TempAchatController extends Controller
{
    /**
     * Display the achat interface.
     */
    public function index()
    {
        try {
            // Get products for dropdowns with specific columns
            $products = Product::select('id', 'name', 'price_achat')->get();
            
            // Get fournisseurs with specific columns
            $fournisseurs = Fournisseur::select('id', 'entreprise')->get();
            
            // Get categories with specific columns
            $categories = Category::select('id', 'name')->get();
            
            // Get temporary achat items for the current user
            $tempAchats = TempAchat::with(['product', 'user'])
                ->where('id_user', Auth::id())
                ->get();
            
            return view('tempachat.index', compact('products', 'fournisseurs', 'categories', 'tempAchats'));
        } catch (\Exception $e) {
            Log::error('Error in TempAchatController@index: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors du chargement de la page.');
        }
    }

    /**
     * Get products for a category.
     */
    public function getProductsByCategory($categoryId)
    {
        try {
            $products = Product::where('id_categorie', $categoryId)
                ->select('id', 'name', 'price_achat')
                ->orderBy('name')
                ->get();
                
            return response()->json([
                'status' => 200,
                'products' => $products
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getProductsByCategory: ' . $e->getMessage(), [
                'category_id' => $categoryId
            ]);
            
            return response()->json([
                'status' => 500,
                'message' => 'Une erreur est survenue',
                'products' => []
            ], 500);
        }
    }

    /**
     * Add a product to temp_achat.
     */
    public function addTempAchat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_produit' => 'required|exists:products,id',
            'id_fournisseur' => 'required|exists:fournisseurs,id',
            'qte' => 'required|numeric|min:1',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ], 400);
        }

        try {
            DB::beginTransaction();
            
            // Check if product already exists in temp_achat for this user and supplier
            $existingTempAchat = TempAchat::where('id_user', Auth::id())
                ->where('idproduit', $request->id_produit)
                ->where('id_fournisseur', $request->id_fournisseur)
                ->first();
            
            if ($existingTempAchat) {
                // Update quantity if product already exists
                $existingTempAchat->qte += $request->qte;
                $existingTempAchat->save();
                
                Log::info('Updated existing temp achat', [
                    'id' => $existingTempAchat->id,
                    'product' => $request->id_produit,
                    'new_quantity' => $existingTempAchat->qte
                ]);
            } else {
                // Create new temp_achat entry
                $newTempAchat = TempAchat::create([
                    'id_user' => Auth::id(),
                    'idproduit' => $request->id_produit,
                    'id_fournisseur' => $request->id_fournisseur,
                    'qte' => $request->qte,
                ]);
                
                Log::info('Created new temp achat', [
                    'id' => $newTempAchat->id,
                    'product' => $request->id_produit,
                    'quantity' => $request->qte
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'status' => 200,
                'message' => 'Produit ajouté avec succès',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error in TempAchatController@addTempAchat: ' . $e->getMessage(), [
                'product_id' => $request->id_produit ?? null,
                'fournisseur_id' => $request->id_fournisseur ?? null,
            ]);
            
            return response()->json([
                'status' => 500,
                'message' => 'Une erreur est survenue: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get temp achat items as DataTable.
     */
    public function getTempAchats(Request $request)
    {
        if ($request->ajax()) {
            $tempAchats = DB::table('temp_achat as ta')
                ->join('products as p', 'ta.idproduit', '=', 'p.id')
                ->join('users as u', 'ta.id_user', '=', 'u.id')
                ->leftJoin('fournisseurs as f', 'ta.id_fournisseur', '=', 'f.id')
                ->leftJoin('stock as s', 'p.id', '=', 's.id_product')
                ->leftJoin('tvas as t', 's.id_tva', '=', 't.id')
                ->select(
                    'ta.id',
                    'u.name as nom_user',
                    DB::raw("CONCAT('A-', LPAD(ta.id, 3, '0')) as id_achat"), 
                    'p.name as nom_produit',
                    'ta.qte',
                    'p.price_achat',
                    'f.entreprise as fournisseur',
                    'ta.idproduit',
                    't.value as tva_rate',
                    DB::raw('p.price_achat * ta.qte as subtotal'),
                    DB::raw('CASE WHEN t.value IS NOT NULL THEN p.price_achat * ta.qte * (1 + t.value/100) ELSE p.price_achat * ta.qte END as total')
                )
                ->where('ta.id_user', Auth::id());

            return DataTables::of($tempAchats)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a href="#" class="btn btn-sm bg-primary-subtle me-1 editTempAchat" data-id="'.$row->id.'">
                            <i class="fa-solid fa-pen-to-square text-primary"></i></a>';
                    $btn .= '<a href="#" class="btn btn-sm bg-danger-subtle deleteTempAchat" data-id="'.$row->id.'">
                            <i class="fa-solid fa-trash text-danger"></i></a>';
                    return $btn;
                })
                ->editColumn('price_achat', function ($row) {
                    return number_format($row->price_achat, 2) . ' €';
                })
                ->editColumn('subtotal', function ($row) {
                    return number_format($row->subtotal, 2) . ' €';
                })
                ->editColumn('total', function ($row) {
                    return number_format($row->total, 2) . ' €';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        
        // Return JSON response for non-ajax requests
        $tempAchats = DB::table('temp_achat as ta')
            ->join('products as p', 'ta.idproduit', '=', 'p.id')
            ->join('users as u', 'ta.id_user', '=', 'u.id')
            ->leftJoin('fournisseurs as f', 'ta.id_fournisseur', '=', 'f.id')
            ->select(
                'ta.id',
                'u.name as nom_user',
                DB::raw("CONCAT('A-', LPAD(ta.id, 3, '0')) as id_achat"),
                'p.name as nom_produit',
                'ta.qte',
                'p.price_achat',
                'f.entreprise as fournisseur',
                'ta.idproduit'
            )
            ->where('ta.id_user', Auth::id())
            ->get();

        return response()->json($tempAchats);
    }

    /**
     * Increase product quantity in temp_achat.
     */
    public function increaseTempAchat(Request $request)
    {
        try {
            $tempAchat = TempAchat::find($request->id);
            
            if (!$tempAchat) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Article non trouvé',
                ], 404);
            }
            
            $tempAchat->qte += 1;
            $tempAchat->save();
            
            return response()->json([
                'status' => 200,
                'message' => 'Quantité augmentée',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in TempAchatController@increaseTempAchat: ' . $e->getMessage(), [
                'temp_achat_id' => $request->id ?? null
            ]);
            
            return response()->json([
                'status' => 500,
                'message' => 'Une erreur est survenue: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Decrease product quantity in temp_achat.
     */
    public function decreaseTempAchat(Request $request)
    {
        try {
            $tempAchat = TempAchat::find($request->id);
            
            if (!$tempAchat) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Article non trouvé',
                ], 404);
            }
            
            if ($tempAchat->qte > 1) {
                $tempAchat->qte -= 1;
                $tempAchat->save();
                
                return response()->json([
                    'status' => 200,
                    'message' => 'Quantité diminuée',
                ]);
            } else {
                return response()->json([
                    'status' => 400,
                    'message' => 'La quantité ne peut pas être inférieure à 1',
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error in TempAchatController@decreaseTempAchat: ' . $e->getMessage(), [
                'temp_achat_id' => $request->id ?? null
            ]);
            
            return response()->json([
                'status' => 500,
                'message' => 'Une erreur est survenue: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove a product from temp_achat.
     */
    public function deleteTempAchat(Request $request)
    {
        try {
            $tempAchat = TempAchat::find($request->id);
            
            if (!$tempAchat) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Article non trouvé',
                ], 404);
            }
            
            $tempAchat->delete();
            
            return response()->json([
                'status' => 200,
                'message' => 'Article supprimé avec succès',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in TempAchatController@deleteTempAchat: ' . $e->getMessage(), [
                'temp_achat_id' => $request->id ?? null
            ]);
            
            return response()->json([
                'status' => 500,
                'message' => 'Une erreur est survenue: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get a specific temp achat item for editing.
     */
    public function edit($id)
    {
        try {
            // Log the edit request for debugging
            Log::info('Edit TempAchat requested', ['id' => $id]);
            
            $tempAchat = TempAchat::with(['product', 'fournisseur'])->find($id);
            
            if (!$tempAchat) {
                Log::warning('TempAchat not found', ['id' => $id]);
                return response()->json([
                    'status' => 404,
                    'message' => 'Article non trouvé',
                ], 404);
            }
            
            Log::info('TempAchat found', [
                'id' => $tempAchat->id,
                'product_id' => $tempAchat->idproduit,
                'fournisseur_id' => $tempAchat->id_fournisseur
            ]);
            
            return response()->json($tempAchat);
            
        } catch (\Exception $e) {
            Log::error('Error in TempAchatController@edit: ' . $e->getMessage(), [
                'temp_achat_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 500,
                'message' => 'Une erreur est survenue: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a temp achat item.
     */
    public function update(Request $request)
    {
        try {
            // Log update request for debugging
            Log::info('Update TempAchat requested', ['request' => $request->all()]);
            
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:temp_achat,id',
                'qte' => 'required|numeric|min:1',
            ]);
            
            if ($validator->fails()) {
                Log::warning('TempAchat update validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'status' => 400,
                    'errors' => $validator->messages(),
                ], 400);
            }
            
            DB::beginTransaction();
            
            $tempAchat = TempAchat::find($request->id);
            
            if (!$tempAchat) {
                Log::warning('TempAchat not found for update', ['id' => $request->id]);
                return response()->json([
                    'status' => 404,
                    'message' => 'Article non trouvé',
                ], 404);
            }
            
            // Update quantity
            $tempAchat->qte = $request->qte;
            $tempAchat->save();
            
            Log::info('TempAchat updated successfully', [
                'id' => $tempAchat->id,
                'new_quantity' => $tempAchat->qte
            ]);
            
            DB::commit();
            
            return response()->json([
                'status' => 200,
                'message' => 'Article mis à jour avec succès',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error in TempAchatController@update: ' . $e->getMessage(), [
                'temp_achat_id' => $request->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 500,
                'message' => 'Une erreur est survenue: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get the product ID for a temp achat entry.
     */
    public function getProductId($id)
    {
        try {
            $tempAchat = TempAchat::find($id);
            
            if (!$tempAchat) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Article non trouvé',
                ], 404);
            }
            
            return response()->json([
                'status' => 200,
                'id_produit' => $tempAchat->idproduit,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in TempAchatController@getProductId: ' . $e->getMessage(), [
                'temp_achat_id' => $id ?? null
            ]);
            
            return response()->json([
                'status' => 500,
                'message' => 'Une erreur est survenue: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate the total for all temp items for a supplier.
     */
    private function calculateTotal($fournisseurId)
    {
        $total = DB::table('temp_achat as ta')
            ->join('products as p', 'ta.idproduit', '=', 'p.id')
            ->leftJoin('stock as s', 'p.id', '=', 's.id_product')
            ->leftJoin('tvas as t', 's.id_tva', '=', 't.id')
            ->where('ta.id_user', Auth::id())
            ->where('ta.id_fournisseur', $fournisseurId)
            ->select(DB::raw('SUM(CASE WHEN t.value IS NOT NULL THEN p.price_achat * ta.qte * (1 + t.value/100) ELSE p.price_achat * ta.qte END) as total'))
            ->first()
            ->total;
        
        return $total ? round($total, 2) : 0;
    }

    /**
     * Store a newly created achat and clear temp_achat.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_fournisseur' => 'required|exists:fournisseurs,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ], 400);
        }

        try {
            DB::beginTransaction();
            
            // Get all temp_achat items for current user and the selected supplier
            $tempAchats = TempAchat::with('product')
                ->where('id_user', Auth::id())
                ->where('id_fournisseur', $request->id_fournisseur)
                ->get();
            
            if ($tempAchats->isEmpty()) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Aucun article à commander',
                ], 400);
            }
            
            // Calculate total
            $total = $this->calculateTotal($request->id_fournisseur);
            
            // Create new achat
            $achat = Achat::create([
                'total' => $total,
                'status' => 'En cours de traitement',
                'id_Fournisseur' => $request->id_fournisseur,
                'id_user' => Auth::id(),
            ]);
            
            Log::info('Created new achat', [
                'achat_id' => $achat->id,
                'fournisseur_id' => $request->id_fournisseur,
                'total' => $total
            ]);
            
            // Create ligne_achat entries for each temp_achat item
            foreach ($tempAchats as $item) {
                if ($item->product) {
                    $stock = Stock::where('id_product', $item->product->id)->first();
                    
                    LigneAchat::create([
                        'id_user' => Auth::id(),
                        'idachat' => $achat->id,
                        'idproduit' => $item->idproduit,
                        'idstock' => $stock ? $stock->id : null,
                        'qte' => $item->qte
                    ]);
                    
                    Log::info('Created new ligne achat', [
                        'achat_id' => $achat->id,
                        'product_id' => $item->idproduit,
                        'quantity' => $item->qte
                    ]);
                }
            }
            
            // Clear temp_achat for current user and the selected supplier
            TempAchat::where('id_user', Auth::id())
                ->where('id_fournisseur', $request->id_fournisseur)
                ->delete();
            
            DB::commit();
            
            return response()->json([
                'status' => 200,
                'message' => 'Achat créé avec succès',
                'achat_id' => $achat->id
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error in TempAchatController@store: ' . $e->getMessage(), [
                'fournisseur_id' => $request->id_fournisseur ?? null
            ]);
            
            return response()->json([
                'status' => 500,
                'message' => 'Une erreur est survenue: ' . $e->getMessage(),
            ], 500);
        }
    }
}