<?php

namespace App\Http\Controllers;

use App\Models\Achat;
use App\Models\LigneAchat;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class LigneAchatController extends Controller
{
    /**
     * Get ligne achats for a specific achat.
     */
    public function getByAchat($achatId, Request $request)
    {
        $achat = Achat::find($achatId);
        
        if (!$achat) {
            return response()->json([
                'status' => 404,
                'message' => 'Achat non trouvé',
            ], 404);
        }
        
        if ($request->ajax()) {
            $ligneAchats = DB::table('ligne_Achat as la')
                ->join('products as p', 'la.idproduit', '=', 'p.id')
                ->leftJoin('stock as s', 'la.idstock', '=', 's.id')
                ->leftJoin('tvas as t', 's.id_tva', '=', 't.id')
                ->select(
                    'la.id',
                    'p.name as product_name',
                    'p.code_article',
                    'p.price_achat',
                    'la.qte',
                    't.value as tva_rate',
                    DB::raw('p.price_achat * la.qte as subtotal'),
                    DB::raw('CASE WHEN t.value IS NOT NULL THEN p.price_achat * la.qte * (1 + t.value/100) ELSE p.price_achat * la.qte END as total')
                )
                ->where('la.idachat', $achatId);

            return DataTables::of($ligneAchats)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<button type="button" class="btn btn-sm btn-danger deleteLigneAchat" data-id="'.$row->id.'">
                            <i class="fa-solid fa-trash"></i></button>';
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
        
        $ligneAchats = LigneAchat::with('product', 'stock')
            ->where('idachat', $achatId)
            ->get();
            
        return response()->json($ligneAchats);
    }

    /**
     * Add a new product to an existing achat.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idachat' => 'required|exists:achats,id',
            'idproduit' => 'required|exists:products,id',
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
            
            $achat = Achat::find($request->idachat);
            $product = Product::find($request->idproduit);
            $stock = Stock::where('id_product', $request->idproduit)->first();
            
            // Check if this product already exists in this achat
            $existingLigne = LigneAchat::where('idachat', $request->idachat)
                ->where('idproduit', $request->idproduit)
                ->first();
                
            if ($existingLigne) {
                // Update quantity if product already exists
                $existingLigne->qte += $request->qte;
                $existingLigne->save();
                
                Log::info('Updated existing ligne achat', [
                    'id' => $existingLigne->id,
                    'product' => $request->idproduit,
                    'new_quantity' => $existingLigne->qte
                ]);
            } else {
                // Create new ligne_achat
                $newLigne = LigneAchat::create([
                    'id_user' => Auth::id(),
                    'idachat' => $request->idachat,
                    'idproduit' => $request->idproduit,
                    'idstock' => $stock ? $stock->id : null,
                    'qte' => $request->qte
                ]);
                
                Log::info('Created new ligne achat', [
                    'id' => $newLigne->id,
                    'product' => $request->idproduit,
                    'quantity' => $request->qte
                ]);
            }
            
            // Recalculate achat total
            $this->recalculateAchatTotal($request->idachat);
            
            DB::commit();
            
            return response()->json([
                'status' => 200,
                'message' => 'Produit ajouté avec succès',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in LigneAchatController@store: ' . $e->getMessage(), [
                'achat_id' => $request->idachat ?? null,
                'product_id' => $request->idproduit ?? null,
            ]);
            
            return response()->json([
                'status' => 500,
                'message' => 'Une erreur est survenue: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a ligne achat quantity.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:ligne_Achat,id',
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
            
            $ligneAchat = LigneAchat::find($request->id);
            $ligneAchat->qte = $request->qte;
            $ligneAchat->save();
            
            // Recalculate achat total
            $this->recalculateAchatTotal($ligneAchat->idachat);
            
            DB::commit();
            
            return response()->json([
                'status' => 200,
                'message' => 'Quantité mise à jour avec succès',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in LigneAchatController@update: ' . $e->getMessage(), [
                'ligne_id' => $request->id ?? null
            ]);
            
            return response()->json([
                'status' => 500,
                'message' => 'Une erreur est survenue: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a ligne achat.
     */
    public function destroy(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $ligneAchat = LigneAchat::find($request->id);
            
            if (!$ligneAchat) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Ligne achat non trouvée',
                ], 404);
            }
            
            $achatId = $ligneAchat->idachat;
            $ligneAchat->delete();
            
            // Recalculate achat total
            $this->recalculateAchatTotal($achatId);
            
            DB::commit();
            
            return response()->json([
                'status' => 200,
                'message' => 'Ligne achat supprimée avec succès',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in LigneAchatController@destroy: ' . $e->getMessage(), [
                'ligne_id' => $request->id ?? null
            ]);
            
            return response()->json([
                'status' => 500,
                'message' => 'Une erreur est survenue: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Receive products and update stock.
     */
    public function receiveProducts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'achat_id' => 'required|exists:achats,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ], 400);
        }

        try {
            DB::beginTransaction();
            
            $achat = Achat::find($request->achat_id);
            
            if (!$achat) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Achat non trouvé',
                ], 404);
            }
            
            // Update achat status
            $achat->status = 'Reçu';
            $achat->save();
            
            // Get all lines for this purchase
            $ligneAchats = LigneAchat::where('idachat', $achat->id)->get();
            
            // Update stock quantities
            foreach ($ligneAchats as $ligne) {
                $stock = Stock::where('id_product', $ligne->idproduit)->first();
                
                if ($stock) {
                    // Increment the quantity in stock
                    $stock->quantite += $ligne->qte;
                    $stock->save();
                    
                    Log::info('Updated stock quantity', [
                        'product_id' => $ligne->idproduit,
                        'added_quantity' => $ligne->qte,
                        'new_total' => $stock->quantite
                    ]);
                } else {
                    // Create new stock entry if it doesn't exist
                    $product = Product::find($ligne->idproduit);
                    
                    if ($product) {
                        $newStock = Stock::create([
                            'id_product' => $product->id,
                            'quantite' => $ligne->qte,
                            'seuil' => 10, // Default threshold
                            'id_tva' => 1, // Default TVA ID, should be adjusted
                            'id_unite' => 1 // Default unit ID, should be adjusted
                        ]);
                        
                        Log::info('Created new stock entry', [
                            'product_id' => $ligne->idproduit,
                            'initial_quantity' => $ligne->qte
                        ]);
                    }
                }
            }
            
            DB::commit();
            
            return response()->json([
                'status' => 200,
                'message' => 'Stock mis à jour avec succès',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in LigneAchatController@receiveProducts: ' . $e->getMessage(), [
                'achat_id' => $request->achat_id ?? null
            ]);
            
            return response()->json([
                'status' => 500,
                'message' => 'Une erreur est survenue: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Recalculate achat total based on its ligne achats.
     */
    private function recalculateAchatTotal($achatId)
    {
        $total = DB::table('ligne_Achat as la')
            ->join('products as p', 'la.idproduit', '=', 'p.id')
            ->leftJoin('stock as s', 'la.idstock', '=', 's.id')
            ->leftJoin('tvas as t', 's.id_tva', '=', 't.id')
            ->where('la.idachat', $achatId)
            ->select(DB::raw('SUM(CASE WHEN t.value IS NOT NULL THEN p.price_achat * la.qte * (1 + t.value/100) ELSE p.price_achat * la.qte END) as total'))
            ->first()
            ->total;

        Achat::where('id', $achatId)->update(['total' => round($total, 2)]);
        
        Log::info('Recalculated achat total', [
            'achat_id' => $achatId,
            'new_total' => round($total, 2)
        ]);
        
        return $total;
    }
}