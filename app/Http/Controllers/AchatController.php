<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fournisseur;
use App\Models\Category;
use App\Models\Local;
use App\Models\SubCategory;
use App\Models\Rayon;
use App\Models\Tva;
use App\Models\Unite;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use App\Models\TempAchat;
use App\Models\Achat;
use App\Models\LigneAchat;
class AchatController extends Controller
{
    public function index()
    {
        $Fournisseur  = Fournisseur::all();
        $categories = Category::all();
        $subcategories = SubCategory::all();
        $locals = Local::all();
        $rayons = Rayon::all();
        $tvas = Tva::all();
        $unites = Unite::all();
        return view('Achat.index')
            ->with('Fournisseur',$Fournisseur)
            ->with('categories',$categories)
            ->with('subcategories',$subcategories)
            ->with('locals',$locals)
            ->with('rayons',$rayons)
            ->with('tvas',$tvas)
            ->with('unites',$unites);
    } 
    
    public function getProduct(Request $request)
    {
        $name_product = $request->product;
    
        
        if ($request->ajax()) {
            
            $Data_Product = DB::table('products as p')
                ->join('stock as s', 'p.id', '=', 's.id_product')
                ->join('locals as l', 'p.id_local', '=', 'l.id')
                ->where('p.name', 'like', '%' . $name_product . '%')
                ->select('p.name', 's.quantite', 's.seuil', 'p.price_achat', 'l.name as name_local','p.id')
                ->get();
            return response()->json([
                'status' => 200,
                'data'   => $Data_Product
            ]);
        }
    }

    public function PostInTmpAchat(Request $request)
    {
        
        $data = $request->all();
        $data['id_user'] = Auth::user()->id;
        $data['qte'] = 1;

        
        DB::beginTransaction();

        try {
            
            $existingProduct = TempAchat::where('idproduit', $data['idproduit'])
                ->where('id_fournisseur', $data['id_fournisseur'])
                ->where('id_user', $data['id_user'])
                ->first();

            if ($existingProduct) {
               
                $existingProduct->increment('qte', 1);

               
                DB::commit();

                return response()->json([
                    'status' => 200,
                    'message' => 'Quantity is updated successfully',
                ]);
            } else {
                
                TempAchat::create($data);

                
                DB::commit();

                return response()->json([
                    'status' => 200,
                    'message' => 'Added successfully',
                ]);
            }
        } catch (\Exception $e) {
           
            DB::rollBack();

            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function GetTmpAchatByFournisseur(Request $request)
    {
        $Data = DB::table('temp_achat as t')
        ->join('fournisseurs as f', 't.id_fournisseur', '=', 'f.id')
        ->join('products as p', 't.idproduit', '=', 'p.id')
        ->join('users as u', 't.id_user', '=', 'u.id')
        ->where('t.id_fournisseur', '=', $request->id_fournisseur)
        ->select('t.id', 'p.name', 'p.price_achat', 'f.entreprise', 't.qte');
        
        

        return DataTables::of($Data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';

                    // Edit button
                    $btn .= '<a href="#" class="btn btn-sm bg-primary-subtle me-1 "
                                data-id="' . $row->id . '">
                                <i class="fa-solid fa-pen-to-square text-primary"></i>
                            </a>';

                    // Delete button
                    $btn .= '<a href="#" class="btn btn-sm bg-danger-subtle "
                                data-id="' . $row->id . '" data-bs-toggle="tooltip" 
                                title="Supprimer Catégorie">
                                <i class="fa-solid fa-trash text-danger"></i>
                            </a>';

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        
    }

    public function Store(Request $request)
    {
        $userId = Auth::id();
        $fournisseur = $request->id_fournisseur;

        // Retrieve temporary purchase data
        $TempAchat = DB::table('temp_achat as t')
            ->join('products as p', 'p.id', '=', 't.idproduit')
            ->where('t.id_user', $userId)
            ->where('t.id_fournisseur', $fournisseur)
            ->select('t.id_fournisseur', 't.qte', 't.idproduit', 'p.price_achat', 
                DB::raw('t.qte * p.price_achat as total_by_product'))
            ->get();

        if ($TempAchat->isEmpty()) {
            return response()->json([
                'status'  => 400,
                'message' => 'No items found for this fournisseur'
            ]);
        }

        // Calculate total purchase amount
        $SumAchat = $TempAchat->sum('total_by_product');

        // Create new purchase
        $Achat = Achat::create([
            'total'         => $SumAchat,
            'status'        => 'In process',
            'id_Fournisseur'=> $fournisseur,
            'id_user'       => $userId,
        ]);

        if (!$Achat) {
            return response()->json([
                'status'  => 500,
                'message' => 'Failed to create purchase record'
            ]);
        }

        // Insert purchase details in bulk
        $LignesAchat = [];
        foreach ($TempAchat as $item) {
            $LignesAchat[] = [
                'id_user'   => $userId,
                'idachat'   => $Achat->id,
                'idproduit' => $item->idproduit,
                'qte'       => $item->qte,
                'created_at'=> now(),
                'updated_at'=> now(),
            ];
        }

        LigneAchat::insert($LignesAchat); // Bulk insert for better performance

        // Delete temporary purchase records
        TempAchat::where('id_user', $userId)
            ->where('id_fournisseur', $fournisseur)
            ->delete();

        return response()->json([
            'status'  => 200,
            'message' => 'Purchase added successfully'
        ]);
    }


}
