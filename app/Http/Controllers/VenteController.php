<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Category;
use App\Models\Local;
use App\Models\SubCategory;
use App\Models\Rayon;
use App\Models\Tva;
use App\Models\Unite;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use App\Models\TempVente;
use App\Models\Vente;
use App\Models\LigneVente;
use Illuminate\Support\Facades\Validator;

class VenteController extends Controller
{
    public function index(Request $request)
    {
        if($request->ajax())
        {
            $Data_Vente = DB::table('ventes as v')
            ->join('clients as c','c.id','=','v.id_client')
            ->join('users as u','u.id','=','v.id_user')
            ->select('v.total','v.status','c.first_name','c.last_name','u.name','v.created_at','v.id')
            ->get();
            return DataTables::of($Data_Vente)
                ->addIndexColumn()
                ->addColumn('client_name', function ($row) {
                    return $row->first_name . ' ' . $row->last_name;
                })
                ->addColumn('action', function ($row) {
                    $btn = '';

                    // Edit button
                    $btn .= '<a href="#" class="btn btn-sm bg-primary-subtle me-1 "
                                data-id="' . $row->id . '">
                                <i class="fa-solid fa-pen-to-square text-primary"></i>
                            </a>';
                    // detail button
                    $btn .= '<a href="#" class="btn btn-sm bg-success-subtle me-1 "
                                data-id="' . $row->id . '">
                               <i class="fa-solid fa-eye text-success"></i>
                            </a>';

                    // Delete button
                    $btn .= '<a href="#" class="btn btn-sm bg-danger-subtle "
                                data-id="' . $row->id . '" data-bs-toggle="tooltip" 
                                title="Supprimer Vente">
                                <i class="fa-solid fa-trash text-danger"></i>
                            </a>';

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $clients = Client::all();
        $categories = Category::all();
        $subcategories = SubCategory::all();
        $locals = Local::all();
        $rayons = Rayon::all();
        $tvas = Tva::all();
        $unites = Unite::all();
        return view('Vente.index')
            ->with('clients', $clients)
            ->with('categories', $categories)
            ->with('subcategories', $subcategories)
            ->with('locals', $locals)
            ->with('rayons', $rayons)
            ->with('tvas', $tvas)
            ->with('unites', $unites);
    } 
    
    public function getProduct(Request $request)
    {
        $name_product = $request->product;
    
        if ($request->ajax()) {
            $Data_Product = DB::table('products as p')
                ->join('stock as s', 'p.id', '=', 's.id_product')
                ->join('locals as l', 'p.id_local', '=', 'l.id')
                ->where('p.name', 'like', '%' . $name_product . '%')
                ->select('p.name', 's.quantite', 's.seuil', 'p.price_vente', 'l.name as name_local', 'p.id')
                ->get();
            return response()->json([
                'status' => 200,
                'data'   => $Data_Product
            ]);
        }
    }

    public function PostInTmpVente(Request $request)
    {
        $data = $request->all();
        $data['id_user'] = Auth::user()->id;
        $data['qte'] = 1;
        
        DB::beginTransaction();

        try {
            $existingProduct = TempVente::where('idproduit', $data['idproduit'])
                ->where('id_client', $data['id_client'])
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
                TempVente::create($data);
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

    public function GetTmpVenteByClient(Request $request)
    {
        $Data = DB::table('temp_vente as t')
        ->join('clients as c', 't.id_client', '=', 'c.id')
        ->join('products as p', 't.idproduit', '=', 'p.id')
        ->join('users as u', 't.id_user', '=', 'u.id')
        ->where('t.id_client', '=', $request->id_client)
        ->select('t.id', 'p.name', 'p.price_vente', DB::raw("CONCAT(c.first_name, ' ', c.last_name) as client_name"), 't.qte');
        
        return DataTables::of($Data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';

                    // Edit button
                    $btn .= '<a href="#" class="btn btn-sm bg-primary-subtle me-1 EditTmp"
                                data-id="' . $row->id . '">
                                <i class="fa-solid fa-pen-to-square text-primary"></i>
                            </a>';

                    // Delete button
                    $btn .= '<a href="#" class="btn btn-sm bg-danger-subtle DeleteTmp"
                                data-id="' . $row->id . '" data-bs-toggle="tooltip" 
                                title="Supprimer Vente">
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
        $client = $request->id_client;

        // Retrieve temporary sales data
        $TempVente = DB::table('temp_vente as t')
            ->join('products as p', 'p.id', '=', 't.idproduit')
            ->where('t.id_user', $userId)
            ->where('t.id_client', $client)
            ->select('t.id_client', 't.qte', 't.idproduit', 'p.price_vente', 
                DB::raw('t.qte * p.price_vente as total_by_product'))
            ->get();

        if ($TempVente->isEmpty()) {
            return response()->json([
                'status'  => 400,
                'message' => 'No items found for this client'
            ]);
        }

        // Calculate total sales amount
        $SumVente = $TempVente->sum('total_by_product');

        // Create new sale
        $Vente = Vente::create([
            'total'     => $SumVente,
            'status'    => "En cours de traitement",
            'id_client' => $client,
            'id_user'   => $userId,
        ]);

        if (!$Vente) {
            return response()->json([
                'status'  => 500,
                'message' => 'Failed to create sale record'
            ]);
        }

        // Insert sales details in bulk
        $LignesVente = [];
        foreach ($TempVente as $item) {
            $LignesVente[] = [
                'id_user'   => $userId,
                'idvente'   => $Vente->id,
                'idproduit' => $item->idproduit,
                'qte'       => $item->qte,
                'created_at'=> now(),
                'updated_at'=> now(),
            ];
        }

        LigneVente::insert($LignesVente); // Bulk insert for better performance

        // Delete temporary sales records
        TempVente::where('id_user', $userId)
            ->where('id_client', $client)
            ->delete();

        return response()->json([
            'status'  => 200,
            'message' => 'Sale added successfully'
        ]);
    }  

    public function UpdateQteTmp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qte' => 'required',
        ], [
            'required' => 'Le champ :attribute est requis.',
        ], [
            'qte' => 'quantité',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ], 400);
        }
        $TempVente = TempVente::where('id', $request->id)->update([
            'qte' => $request->qte,
        ]);
        
        if($TempVente) {
            return response()->json([
                'status'    => 200,
                'message'   => 'Mise à jour effectuée avec succès.'
            ]);
        }
    }

    public function DeleteRowsTmpVente(Request $request)
    {
        $TempVente = TempVente::where('id', $request->id)->delete();
        if($TempVente) {
            return response()->json([
                'status'    => 200,
                'message'   => 'Supprimier effectuée avec succès.'
            ]);
        }
    }

    public function GetTotalTmpByClientAndUser(Request $request)
    {
        $userId = Auth::id();
        $client = $request->id_client;

        // Retrieve temporary sales data
        $TempVente = DB::table('temp_vente as t')
            ->join('products as p', 'p.id', '=', 't.idproduit')
            ->where('t.id_user', $userId)
            ->where('t.id_client', $client)
            ->select('t.id_client', 't.qte', 't.idproduit', 'p.price_vente', 
                DB::raw('t.qte * p.price_vente as total_by_product'))
            ->get();

        // Calculate total sales amount
        $SumVente = $TempVente->sum('total_by_product');

        return response()->json([
            'status'    => 200,
            'total'     => $SumVente
        ]);
    }
}