<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $dataClient = DB::table('clients as c')
                        ->join('users as u','u.id','c.iduser')
                ->select(
                    'c.id',
                    'c.first_name',
                    'c.last_name',
                    'c.Telephone',
                    'c.Email',
                    'u.name as username',
                    'c.created_at'
                )
                ->whereNull('c.deleted_at'); 

            return DataTables::of($dataClient)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';

                    // Edit button - only show if user has permission
                    if (auth()->user()->can('Formateurs-modifier')) {
                        $btn .= '<a href="#" class="btn btn-sm bg-primary-subtle me-1 editClient"
                                    data-id="' . $row->id . '">
                                    <i class="fa-solid fa-pen-to-square text-primary"></i>
                                </a>';
                    }

                    // Delete button - only show if user has permission
                    if (auth()->user()->can('Formateurs-supprimer')) {
                        $btn .= '<a href="#" class="btn btn-sm bg-danger-subtle deleteClient"
                                    data-id="' . $row->id . '" data-bs-toggle="tooltip" 
                                    title="Supprimer Client">
                                    <i class="fa-solid fa-trash text-danger"></i>
                                </a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
             
        return view('client.index', [
            'clients' => Client::latest('id')->paginate(10)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check permission before storing
        if (!auth()->user()->can('Formateurs-ajoute')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission d\'ajouter un Formateur'
            ], 403);
        }
    
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'Telephone' => 'required|string|max:20',
            'Email' => 'required|email|max:255',
        ], [
            'required' => 'Le champ :attribute est requis.',
            'email' => 'Le champ :attribute doit être une adresse email valide.',
        ], [
            'first_name' => 'prénom',
            'last_name' => 'nom',
            'Telephone' => 'téléphone',
            'Email' => 'email',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ], 400);
        }
    
        // Nettoyer et standardiser les noms pour la comparaison
        $cleanedFirstName = strtolower(trim($request->first_name));
        $cleanedLastName = strtolower(trim($request->last_name));
        
        // Vérifier si un client avec le même prénom ET nom existe déjà (insensible à la casse)
        $exists = Client::whereRaw('LOWER(TRIM(first_name)) = ?', [$cleanedFirstName])
                       ->whereRaw('LOWER(TRIM(last_name)) = ?', [$cleanedLastName])
                       ->count();
        
        if ($exists > 0) {
            return response()->json([
                'status' => 422,
                'message' => 'Un formateur avec ce prénom et ce nom existe déjà',
            ], 422);
        }
    
        $client = Client::create([
            'first_name' => trim($request->first_name),
            'last_name' => trim($request->last_name),
            'Telephone' => $request->Telephone,
            'Email' => $request->Email,
            'iduser' => Auth::user()->id,
        ]);
    
        if($client) {
            return response()->json([
                'status' => 200,
                'message' => 'Formateur créé avec succès',
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
    public function show(Client $client): RedirectResponse
    {
        return redirect()->route('client.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        // Check permission before editing
        if (!auth()->user()->can('Formateurs-modifier')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission de modifier un formateur'
            ], 403);
        }

        $client = Client::find($id);
        
        if (!$client) {
            return response()->json([
                'status' => 404,
                'message' => 'formateur non trouvé'
            ], 404);
        }

        return response()->json($client);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        // Check permission before updating
        if (!auth()->user()->can('Formateurs-modifier')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission de modifier un client'
            ], 403);
        }
    
        $client = Client::find($request->id);
    
        if (!$client) {
            return response()->json([
                'status' => 404,
                'message' => 'Client non trouvé'
            ], 404);
        }
    
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'Telephone' => 'required|string|max:20',
            'Email' => 'required|email|max:255',
        ], [
            'required' => 'Le champ :attribute est requis.',
            'email' => 'Le champ :attribute doit être une adresse email valide.',
        ], [
            'first_name' => 'prénom',
            'last_name' => 'nom',
            'Telephone' => 'téléphone',
            'Email' => 'email',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ], 400);
        }
    
        // Check if another client with the same name exists (case insensitive)
        $cleanedFirstName = strtolower(trim($request->first_name));
        $cleanedLastName = strtolower(trim($request->last_name));
        
        // Check for duplicate excluding the current client being updated
        $exists = Client::where('id', '!=', $request->id)
                       ->whereRaw('LOWER(TRIM(first_name)) = ?', [$cleanedFirstName])
                       ->whereRaw('LOWER(TRIM(last_name)) = ?', [$cleanedLastName])
                       ->count();
        
        if ($exists > 0) {
            return response()->json([
                'status' => 422,
                'message' => 'Un formateur avec ce prénom et ce nom existe déjà',
            ], 422);
        }
    
        $client->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'Telephone' => $request->Telephone,
            'Email' => $request->Email,
        ]);
    
        return response()->json([
            'status' => 200,
            'message' => 'formateur mis à jour avec succès',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        // Check permission before deleting
        if (!auth()->user()->can('Formateurs-supprimer')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission de supprimer un formateur'
            ], 403);
        }

        $client = Client::find($request->id);

        if (!$client) {
            return response()->json([
                'status' => 404,
                'message' => 'formateur non trouvé'
            ], 404);
        }

        if ($client->delete()) {
            return response()->json([
                'status' => 200,
                'message' => 'formateur supprimé avec succès'
            ]);
        }

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la suppression'
        ], 500);
    }

    /**
     * Display a listing of the trashed clients.
     */
    public function trashed(Request $request)
    {
        if ($request->ajax()) {
            $dataClient = DB::table('clients as c')
                        ->join('users as u','u.id','c.iduser')
                        ->whereNotNull('c.deleted_at') // Only trashed items
                        ->select(
                            'c.id',
                            'c.first_name',
                            'c.last_name',
                            'c.Telephone',
                            'c.Email',
                            'u.name as username',
                            'c.created_at',
                            'c.deleted_at'
                        );

            return DataTables::of($dataClient)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';

                    // Restore button - only show if user has permission
                    if (auth()->user()->can('Formateurs-modifier')) {
                        $btn .= '<a href="#" class="btn btn-sm bg-success-subtle me-1 restoreClient"
                                    data-id="' . $row->id . '">
                                    <i class="fa-solid fa-trash-restore text-success"></i>
                                </a>';
                    }

                    // Force Delete button - only show if user has permission
                    if (auth()->user()->can('Formateurs-supprimer')) {
                        $btn .= '<a href="#" class="btn btn-sm bg-danger-subtle forceDeleteClient"
                                    data-id="' . $row->id . '" data-bs-toggle="tooltip" 
                                    title="Supprimer définitivement">
                                    <i class="fa-solid fa-trash-alt text-danger"></i>
                                </a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
             
        return view('client.trashed');
    }

    /**
     * Restore the specified client from trash.
     */
    public function restore(Request $request)
    {
        // Check permission before restoring
        if (!auth()->user()->can('Formateurs-modifier')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission de restaurer un client'
            ], 403);
        }

        $client = Client::onlyTrashed()->find($request->id);

        if (!$client) {
            return response()->json([
                'status' => 404,
                'message' => 'Client non trouvé'
            ], 404);
        }

        if ($client->restore()) {
            return response()->json([
                'status' => 200,
                'message' => 'Client restauré avec succès'
            ]);
        }

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la restauration'
        ], 500);
    }

    /**
     * Permanently delete the specified client from database.
     */
    public function forceDelete(Request $request)
    {
        // Check permission before force deleting
        if (!auth()->user()->can('Formateurs-supprimer')) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'avez pas la permission de supprimer définitivement un client'
            ], 403);
        }

        $client = Client::onlyTrashed()->find($request->id);

        if (!$client) {
            return response()->json([
                'status' => 404,
                'message' => 'Client non trouvé'
            ], 404);
        }

        if ($client->forceDelete()) {
            return response()->json([
                'status' => 200,
                'message' => 'Client supprimé définitivement'
            ]);
        }

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la suppression définitive'
        ], 500);
    }
}