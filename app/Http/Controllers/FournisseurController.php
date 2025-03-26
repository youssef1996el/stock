<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FournisseurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $dataFournisseur = DB::table('fournisseurs as f')
                        ->join('users as u','u.id','f.iduser')
                        ->whereNull('f.deleted_at')
                ->select(
                    'f.id',
                    'f.entreprise',
                    'f.Telephone',
                    'f.Email',
                    'u.name as username',
                    'f.created_at'
                );

            return DataTables::of($dataFournisseur)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';
                    
                    if (auth()->user()->can('Fournisseurs-modifier')) {
                        // Edit button
                        $btn .= '<a href="#" class="btn btn-sm bg-primary-subtle me-1 editFournisseur"
                                    data-id="' . $row->id . '">
                                    <i class="fa-solid fa-pen-to-square text-primary"></i>
                                </a>';
                    }
                    
                    if (auth()->user()->can('Fournisseurs-supprimer')) {
                        // Delete button
                        $btn .= '<a href="#" class="btn btn-sm bg-danger-subtle deleteFournisseur"
                                    data-id="' . $row->id . '" data-bs-toggle="tooltip" 
                                    title="Supprimer Fournisseur">
                                    <i class="fa-solid fa-trash text-danger"></i>
                                </a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
             
        return view('fournisseur.index', [
            'fournisseurs' => Fournisseur::latest('id')->paginate(10)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if user has permission to add suppliers
        if (!auth()->user()->can('Fournisseurs-ajoute')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission d\'ajouter des fournisseurs'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'entreprise' => 'required|string|max:255',
            'Telephone' => 'required|string|max:20',
            'Email' => 'required|email|max:255',
        ], [
            'required' => 'Le champ :attribute est requis.',
            'email' => 'Le champ :attribute doit être une adresse email valide.',
        ], [
            'entreprise' => 'entreprise',
            'Telephone' => 'téléphone',
            'Email' => 'email',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ], 400);
        }

        $fournisseur = Fournisseur::create([
            'entreprise' => $request->entreprise,
            'Telephone' => $request->Telephone,
            'Email' => $request->Email,
            'iduser' => Auth::user()->id,
        ]);

        if($fournisseur) {
            return response()->json([
                'status' => 200,
                'message' => 'Fournisseur créé avec succès',
            ]);
        } else { 
            return response()->json([
                'status' => 500,
                'message' => 'Une erreur est survenue. Veuillez réessayer.'
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Fournisseur $fournisseur): RedirectResponse
    {
        return redirect()->route('fournisseur.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $fournisseur = Fournisseur::find($id);
        
        if (!$fournisseur) {
            return response()->json([
                'status' => 404,
                'message' => 'Fournisseur non trouvé'
            ], 404);
        }

        return response()->json($fournisseur);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        // Check if user has permission to modify suppliers
        if (!auth()->user()->can('Fournisseurs-modifier')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission de modifier des fournisseurs'
            ], 403);
        }

        $fournisseur = Fournisseur::find($request->id);

        if (!$fournisseur) {
            return response()->json([
                'status' => 404,
                'message' => 'Fournisseur non trouvé'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'entreprise' => 'required|string|max:255',
            'Telephone' => 'required|string|max:20',
            'Email' => 'required|email|max:255',
        ], [
            'required' => 'Le champ :attribute est requis.',
            'email' => 'Le champ :attribute doit être une adresse email valide.',
        ], [
            'entreprise' => 'entreprise',
            'Telephone' => 'téléphone',
            'Email' => 'email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ], 400);
        }

        $fournisseur->update([
            'entreprise' => $request->entreprise,
            'Telephone' => $request->Telephone,
            'Email' => $request->Email,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Fournisseur mis à jour avec succès',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        // Check if user has permission to delete suppliers
        if (!auth()->user()->can('Fournisseurs-supprimer')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission de supprimer des fournisseurs'
            ], 403);
        }
        
        $fournisseur = Fournisseur::find($request->id);

        if (!$fournisseur) {
            return response()->json([
                'status' => 404,
                'message' => 'Fournisseur non trouvé'
            ], 404);
        }

        if ($fournisseur->delete()) {
            return response()->json([
                'status' => 200,
                'message' => 'Fournisseur supprimé avec succès'
            ]);
        }

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la suppression'
        ], 500);
    }
}