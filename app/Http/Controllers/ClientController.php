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
                );

            return DataTables::of($dataClient)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';

                    // Edit button
                    $btn .= '<a href="#" class="btn btn-sm bg-primary-subtle me-1 editClient"
                                data-id="' . $row->id . '">
                                <i class="fa-solid fa-pen-to-square text-primary"></i>
                            </a>';

                    // Delete button
                    $btn .= '<a href="#" class="btn btn-sm bg-danger-subtle deleteClient"
                                data-id="' . $row->id . '" data-bs-toggle="tooltip" 
                                title="Supprimer Client">
                                <i class="fa-solid fa-trash text-danger"></i>
                            </a>';

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

        $client = Client::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'Telephone' => $request->Telephone,
            'Email' => $request->Email,
            'iduser' => Auth::user()->id,
        ]);

        if($client) {
            return response()->json([
                'status' => 200,
                'message' => 'Client créé avec succès',
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
        $client = Client::find($id);
        
        if (!$client) {
            return response()->json([
                'status' => 404,
                'message' => 'Client non trouvé'
            ], 404);
        }

        return response()->json($client);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
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

        $client->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'Telephone' => $request->Telephone,
            'Email' => $request->Email,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Client mis à jour avec succès',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $client = Client::find($request->id);

        if (!$client) {
            return response()->json([
                'status' => 404,
                'message' => 'Client non trouvé'
            ], 404);
        }

        if ($client->delete()) {
            return response()->json([
                'status' => 200,
                'message' => 'Client supprimé avec succès'
            ]);
        }

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la suppression'
        ], 500);
    }
}