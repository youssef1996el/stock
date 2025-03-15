<?php

namespace App\Http\Controllers;

use App\Models\Achat;
use App\Models\Fournisseur;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AchatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $dataAchat = DB::table('achats as a')
                ->join('users as u', 'u.id', 'a.id_user')
                ->join('fournisseurs as f', 'f.id', 'a.id_Fournisseur')
                ->select(
                    'a.id',
                    'a.total',
                    'a.status',
                    'f.entreprise as fournisseur_name',
                    'u.name as username',
                    'a.created_at'
                );

            return DataTables::of($dataAchat)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';

                    // Edit button
                    $btn .= '<a href="#" class="btn btn-sm bg-primary-subtle me-1 editAchat"
                                data-id="' . $row->id . '">
                                <i class="fa-solid fa-pen-to-square text-primary"></i>
                            </a>';

                    // Delete button
                    $btn .= '<a href="#" class="btn btn-sm bg-danger-subtle deleteAchat"
                                data-id="' . $row->id . '" data-bs-toggle="tooltip" 
                                title="Supprimer Achat">
                                <i class="fa-solid fa-trash text-danger"></i>
                            </a>';

                    return $btn;
                })
                ->editColumn('status', function($row) {
                    $statusClasses = [
                        'En cours de traitement' => 'badge bg-warning',
                        'Accepté' => 'badge bg-success',
                        'Refusé' => 'badge bg-danger',
                        'Reçu' => 'badge bg-info'
                    ];
                    
                    $class = $statusClasses[$row->status] ?? 'badge bg-secondary';
                    
                    return '<span class="' . $class . '">' . $row->status . '</span>';
                })
                ->editColumn('total', function($row) {
                    return number_format($row->total, 2) . ' €';
                })
                ->editColumn('created_at', function($row) {
                    return date('d/m/Y', strtotime($row->created_at));
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        
        $fournisseurs = Fournisseur::all();
        $statusOptions = ['En cours de traitement', 'Accepté', 'Refusé', 'Reçu'];
        
        return view('achat.index', [
            'achats' => Achat::latest('id')->paginate(10),
            'fournisseurs' => $fournisseurs,
            'statusOptions' => $statusOptions
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'total' => 'required|numeric|min:0',
            'status' => 'required|in:En cours de traitement,Accepté,Refusé,Reçu',
            'id_Fournisseur' => 'required|exists:fournisseurs,id',
        ], [
            'required' => 'Le champ :attribute est requis.',
            'numeric' => 'Le champ :attribute doit être un nombre.',
            'min' => 'Le champ :attribute doit être au moins :min.',
            'in' => 'Le statut sélectionné est invalide.',
            'exists' => 'Le fournisseur sélectionné n\'existe pas.',
        ], [
            'total' => 'montant total',
            'status' => 'statut',
            'id_Fournisseur' => 'fournisseur',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ], 400);
        }

        $achat = Achat::create([
            'total' => $request->total,
            'status' => $request->status,
            'id_Fournisseur' => $request->id_Fournisseur,
            'id_user' => Auth::user()->id,
        ]);

        if($achat) {
            return response()->json([
                'status' => 200,
                'message' => 'Achat créé avec succès',
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
        $achat = Achat::find($id);
        
        if (!$achat) {
            return response()->json([
                'status' => 404,
                'message' => 'Achat non trouvé'
            ], 404);
        }
        
        return response()->json($achat);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $achat = Achat::find($request->id);
        
        if (!$achat) {
            return response()->json([
                'status' => 404,
                'message' => 'Achat non trouvé'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'total' => 'required|numeric|min:0',
            'status' => 'required|in:En cours de traitement,Accepté,Refusé,Reçu',
            'id_Fournisseur' => 'required|exists:fournisseurs,id',
        ], [
            'required' => 'Le champ :attribute est requis.',
            'numeric' => 'Le champ :attribute doit être un nombre.',
            'min' => 'Le champ :attribute doit être au moins :min.',
            'in' => 'Le statut sélectionné est invalide.',
            'exists' => 'Le fournisseur sélectionné n\'existe pas.',
        ], [
            'total' => 'montant total',
            'status' => 'statut',
            'id_Fournisseur' => 'fournisseur',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ], 400);
        }

        $achat->total = $request->total;
        $achat->status = $request->status;
        $achat->id_Fournisseur = $request->id_Fournisseur;
        $saved = $achat->save();
        
        if ($saved) {
            return response()->json([
                'status' => 200,
                'message' => 'Achat mis à jour avec succès',
            ]);
        } else {
            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors de la mise à jour de l\'achat',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $achat = Achat::find($request->id);

        if (!$achat) {
            return response()->json([
                'status' => 404,
                'message' => 'Achat non trouvé'
            ], 404);
        }

        if ($achat->delete()) {
            return response()->json([
                'status' => 200,
                'message' => 'Achat supprimé avec succès'
            ]);
        }

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la suppression'
        ], 500);
    }
}