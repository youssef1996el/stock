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
use Illuminate\Support\Facades\Validator;
use Hashids\Hashids;
use Barryvdh\DomPDF\Facade\Pdf;
class AchatController extends Controller
{
    public function index(Request $request)
    {
        if($request->ajax())
        {
            $hashids = new Hashids();
            $Data_Achat = DB::table('achats as a')
            ->join('fournisseurs as f','f.id','=','a.id_Fournisseur')
            ->join('users as u'       ,'u.id','=','a.id_user')
            ->whereNull('a.deleted_at')
            ->select('a.total','a.status','f.entreprise','u.name','a.created_at','a.id')
            ->get();
            return DataTables::of($Data_Achat)
                    ->addIndexColumn()
                    ->addColumn('action', function ($row) use ($hashids) {  // <-- Pass $hashids inside the closure
                        $btn = '';

                        // Edit button
                        $btn .= '<a href="#" class="btn btn-sm bg-primary-subtle me-1" 
                                    data-id="' . $row->id . '">
                                    <i class="fa-solid fa-pen-to-square text-primary"></i>
                                </a>';

                        // Detail button (hashed ID)
                        $btn .= '<a href="' . url('ShowBonReception/' . $hashids->encode($row->id)) . '" 
                                    class="btn btn-sm bg-success-subtle me-1" 
                                    data-id="' . $row->id . '" 
                                    target="_blank">
                                    <i class="fa-solid fa-eye text-success"></i>
                                </a>';
                        // Approve
                        $btn .= '<a href="#" class="btn btn-sm bg-success-subtle me-1" data-id="' . $row->id . '">
                                <i class="fa-solid fa-circle-chevron-down text-success"></i>
                            </a>';

                        $btn .= '<a href="' . url('Invoice/' . $hashids->encode($row->id)) . '" class="btn btn-sm bg-info-subtle me-1" data-id="' . $row->id . '" target="_blank">
                                <i class="fa-solid fa-print text-info"></i>
                            </a>';

                        // Delete button
                        $btn .= '<a href="#" class="btn btn-sm bg-danger-subtle" 
                                    data-id="' . $row->id . '" data-bs-toggle="tooltip" 
                                    title="Supprimer Catégorie">
                                    <i class="fa-solid fa-trash text-danger"></i>
                                </a>';

                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }
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
                ->select('p.name', 's.quantite', 's.seuil', 'p.price_achat', 'l.name as name_local','p.id','p.price_vente')
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
                    'message' => 'Quantité mise à jour avec succès',
                ]);
            } else {
                
                TempAchat::create($data);

                
                DB::commit();

                return response()->json([
                    'status' => 200,
                    'message' => 'Ajouté avec succès',
                ]);
            }
        } catch (\Exception $e) {
           
            DB::rollBack();

            return response()->json([
                'status' => 500,
                'message' => 'Une erreur est survenue. Veuillez réessayer.',
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
                    $btn .= '<a href="#" class="btn btn-sm bg-primary-subtle me-1 EditTmp"
                                data-id="' . $row->id . '">
                                <i class="fa-solid fa-pen-to-square text-primary"></i>
                            </a>';

                    // Delete button
                    $btn .= '<a href="#" class="btn btn-sm bg-danger-subtle DeleteTmp"
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
                'message' => 'Aucun article trouvé pour ce fournisseur'
            ]);
        }

        // Calculate total purchase amount
        $SumAchat = $TempAchat->sum('total_by_product');

        // Create new purchase
        $Achat = Achat::create([
            'total'         => $SumAchat,
            'status'        => "En cours de traitement",
            'id_Fournisseur'=> $fournisseur,
            'id_user'       => $userId,
        ]);

        if (!$Achat) {
            return response()->json([
                'status'  => 500,
                'message' => 'Échec de la création de l\'enregistrement d\'achat'
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
            'message' => 'Achat ajouté avec succès'
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
            ], 400); // تأكد من وضع كود الحالة HTTP هنا
        }
        $TempAchat = TempAchat::where('id',$request->id)->update([
            'qte'   => $request->qte,
        ]);
        if($TempAchat)
        {
            return response()->json([
                'status'    => 200,
                'message'   => 'Mise à jour effectuée avec succès.'
            ]);
        }
        
    }

    public function DeleteRowsTmpAchat(Request $request)
    {
        $TempAchat = TempAchat::where('id',$request->id)->delete();
        if($TempAchat)
        {
            return response()->json([
                'status'    => 200,
                'message'   => 'Suppression effectuée avec succès.'
            ]);
        }
    }

    public function GetTotalTmpByForunisseurAndUser(Request $request)
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

      

        // Calculate total purchase amount
        $SumAchat = $TempAchat->sum('total_by_product');

        return response()->json([
            'status'    => 200,
            'total'     => $SumAchat
        ]);
    }


    public function ShowBonReception($id)
    {
        $hashids = new Hashids();
        $decoded = $hashids->decode($id);

        if (empty($decoded)) {
            abort(404); // Handle invalid hash
        }
    
        $id = $decoded[0]; // Extract the original ID

       
    
        // Now, use $id to retrieve the BonReception
        $bonReception = Achat::findOrFail($id);
        $Fournisseur  = DB::table('fournisseurs as f')
        ->join('achats as a','a.id_Fournisseur','=','f.id')
        ->select('f.*')
        ->where('a.id',$id)
        ->first();
        $Data_Achat = $data = DB::table('achats as a')
        ->join('ligne_achat as l', 'a.id', '=', 'l.idachat')
        ->join('products as p', 'l.idproduit', '=', 'p.id')
        ->select('p.price_achat', 'l.qte', DB::raw('p.price_achat * l.qte as total'), 'p.name')
        ->where('a.id', $id)
        ->get();
    
    
        return view('Achat.List', compact('bonReception','Fournisseur','Data_Achat'));
    }

    public function Invoice($id)
    {
        $hashids = new Hashids();
        $decoded = $hashids->decode($id);

        if (empty($decoded)) {
            abort(404); // Handle invalid hash
        }
    
        $id = $decoded[0]; // Extract the original ID
        $Data_Achat = $data = DB::table('achats as a')
        ->join('ligne_achat as l', 'a.id', '=', 'l.idachat')
        ->join('products as p', 'l.idproduit', '=', 'p.id')
        ->select('p.price_achat', 'l.qte', DB::raw('p.price_achat * l.qte as total'), 'p.name','a.created_at')
        ->where('a.id', $id)
        ->get();

       
        $imagePath = public_path('images/logo_top.png');
        $imageData = base64_encode(file_get_contents($imagePath));
        $logo_bottom = public_path('images/logo_bottom.png');
        $imageData_bottom = base64_encode(file_get_contents($logo_bottom));
        $context = stream_context_create([
            'ssl'  => [
                'verify_peer'  => FALSE,
                'verify_peer_name' => FALSE,
                'allow_self_signed' => TRUE,
            ]
        ]);
        $html = view('Achat.Facture', [
            'Data_Achat' => $Data_Achat,
            'imageData' => $imageData,
            'imageData_bottom' => $imageData_bottom,
        ])->render();
    
        // تحميل HTML إلى PDF
        $pdf = Pdf::loadHTML($html)->output();
    
        // تحديد رؤوس الاستجابة
        $headers = [
            "Content-type" => "application/pdf",
        ];
        return response()->streamDownload(
            fn() => print($pdf),
            "Bon.pdf",
            $headers
        );
    
        
        
    }



}