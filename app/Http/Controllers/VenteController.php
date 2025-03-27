<?php

namespace App\Http\Controllers;

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
use Hashids\Hashids;
use Barryvdh\DomPDF\Facade\Pdf;

class VenteController extends Controller
{
    public function index(Request $request)
    {
        if($request->ajax())
        {
            $hashids = new Hashids();
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
                ->addColumn('action', function ($row) use ($hashids) {
                    $btn = '';
    
                    // Edit button
                    if (auth()->user()->can('Commande-modifier')) {
                        $btn .= '<a href="#" class="btn btn-sm bg-primary-subtle me-1 "
                                    data-id="' . $row->id . '">
                                    <i class="fa-solid fa-pen-to-square text-primary"></i>
                                </a>';
                    }
                    
                    // Detail button with hash ID
                    if (auth()->user()->can('Commande')) {
                        $btn .= '<a href="' . url('ShowBonVente/' . $hashids->encode($row->id)) . '" 
                                    class="btn btn-sm bg-success-subtle me-1" 
                                    data-id="' . $row->id . '" 
                                    target="_blank">
                                    <i class="fa-solid fa-eye text-success"></i>
                                </a>';
                    }
                    
                    // Print invoice button
                    if (auth()->user()->can('Commande')) {
                        $btn .= '<a href="' . url('FactureVente/' . $hashids->encode($row->id)) . '" class="btn btn-sm bg-info-subtle me-1" data-id="' . $row->id . '" target="_blank">
                                <i class="fa-solid fa-print text-info"></i>
                            </a>';
                    }
    
                    // Delete button
                    if (auth()->user()->can('Commande-supprimer')) {
                        $btn .= '<a href="#" class="btn btn-sm bg-danger-subtle "
                                    data-id="' . $row->id . '" data-bs-toggle="tooltip" 
                                    title="Supprimer Vente">
                                    <i class="fa-solid fa-trash text-danger"></i>
                                </a>';
                    }
    
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
        return view('vente.index')
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
        // Check permission before posting to temp vente
        if (!auth()->user()->can('Commande-ajoute')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission d\'ajouter une commande'
            ], 403);
        }
        
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

                    // Edit button with permission check
                    if (auth()->user()->can('Commande-modifier')) {
                        $btn .= '<a href="#" class="btn btn-sm bg-primary-subtle me-1 EditTmp" 
                                    data-id="' . $row->id . '">
                                    <i class="fa-solid fa-pen-to-square text-primary"></i>
                                </a>';
                    }

                    // Delete button with permission check
                    if (auth()->user()->can('Commande-supprimer')) {
                        $btn .= '<a href="#" class="btn btn-sm bg-danger-subtle DeleteTmp"
                                    data-id="' . $row->id . '" data-bs-toggle="tooltip" 
                                    title="Supprimer Vente">
                                    <i class="fa-solid fa-trash text-danger"></i>
                                </a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
    }

    public function Store(Request $request)
    {
        // Check permission before storing
        if (!auth()->user()->can('Commande-ajoute')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission d\'ajouter une commande'
            ], 403);
        }
        
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

    public function UpdateQteTmpVente(Request $request)
    {
        // Check permission before updating
        if (!auth()->user()->can('Commande-modifier')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission de modifier une commande'
            ], 403);
        }
        
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
        // Check permission before deleting
        if (!auth()->user()->can('Commande-supprimer')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission de supprimer une commande'
            ], 403);
        }
        
        $TempVente = TempVente::where('id', $request->id)->delete();
        if($TempVente) {
            return response()->json([
                'status'    => 200,
                'message'   => 'Suppression effectuée avec succès.'
            ]);
        }
    }

    public function deleteOldVentes()
    {
        // Check permission before bulk deleting 
        if (!auth()->user()->can('Commande-supprimer')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission de supprimer des commandes'
            ], 403);
        }
        
        try {
            // Find ventes older than 24 hours with status "En cours de traitement"
            $cutoffTime = now()->subHours(24);
            
            $oldVentes = Vente::where('status', 'En cours de traitement')
                             ->where('created_at', '<', $cutoffTime)
                             ->get();
            
            $count = 0;
            
            foreach ($oldVentes as $vente) {
                // Begin transaction to ensure atomicity
                DB::beginTransaction();
                
                try {
                    // Delete related line items first
                    LigneVente::where('idvente', $vente->id)->delete();
                    
                    // Then delete the vente itself
                    $vente->delete();
                    
                    DB::commit();
                    $count++;
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error("Failed to delete vente ID: {$vente->id}. Error: " . $e->getMessage());
                }
            }
            
            \Log::info("Auto-deleted {$count} ventes that were 24+ hours old with unchanged status.");
            
            return response()->json([
                'status' => 200,
                'message' => "Successfully deleted {$count} old sales orders."
            ]);
        } catch (\Exception $e) {
            \Log::error("Error in deleteOldVentes method: " . $e->getMessage());
            
            return response()->json([
                'status' => 500,
                'message' => "An error occurred while trying to delete old sales orders."
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

    public function ShowBonVente($id)
    {
        // Check permission for viewing
        if (!auth()->user()->can('Commande')) {
            abort(403, 'Vous n\'avez pas la permission de voir ce bon de vente');
        }
        
        $hashids = new Hashids();
        $decoded = $hashids->decode($id);

        if (empty($decoded)) {
            abort(404); // Handle invalid hash
        }

        $id = $decoded[0]; // Extract the original ID

        // Now, use $id to retrieve the BonVente
        $bonVente = Vente::findOrFail($id);
        $Client = DB::table('clients as c')
            ->join('ventes as v', 'v.id_Client', '=', 'c.id')
            ->select('c.*')
            ->where('v.id', $id)
            ->first();
        $Data_Vente = DB::table('ventes as v')
            ->join('ligne_vente as l', 'v.id', '=', 'l.idvente')
            ->join('products as p', 'l.idproduit', '=', 'p.id')
            ->select('p.price_vente', 'l.qte', DB::raw('p.price_vente * l.qte as total'), 'p.name')
            ->where('v.id', $id)
            ->get();

        return view('vente.list', compact('bonVente', 'Client', 'Data_Vente'));
    }
    
    public function FactureVente($id)
    {
        // Check permission for viewing invoice
        if (!auth()->user()->can('Commande')) {
            abort(403, 'Vous n\'avez pas la permission de voir cette facture');
        }
        
        $hashids = new Hashids();
        $decoded = $hashids->decode($id);

        if (empty($decoded)) {
            abort(404); // Handle invalid hash
        }

        $id = $decoded[0]; // Extract the original ID
        $Data_Vente = DB::table('ventes as v')
            ->join('ligne_vente as l', 'v.id', '=', 'l.idvente')
            ->join('products as p', 'l.idproduit', '=', 'p.id')
            ->select('p.price_vente', 'l.qte', DB::raw('p.price_vente * l.qte as total'), 'p.name', 'v.created_at')
            ->where('v.id', $id)
            ->get();

        $imagePath = public_path('images/logo_top.png');
        $imageData = base64_encode(file_get_contents($imagePath));
        $logo_bottom = public_path('images/logo_bottom.png');
        $imageData_bottom = base64_encode(file_get_contents($logo_bottom));
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => FALSE,
                'verify_peer_name' => FALSE,
                'allow_self_signed' => TRUE,
            ]
        ]);
        $html = view('vente.facture', [
            'Data_Vente' => $Data_Vente,
            'imageData' => $imageData,
            'imageData_bottom' => $imageData_bottom,
        ])->render();

        // Load HTML to PDF
        $pdf = Pdf::loadHTML($html)->output();

        // Set response headers
        $headers = [
            "Content-type" => "application/pdf",
        ];
        return response()->streamDownload(
            fn() => print($pdf),
            "FactureVente.pdf",
            $headers
        );
    }
}