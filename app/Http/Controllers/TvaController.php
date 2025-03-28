<?php

namespace App\Http\Controllers;

use App\Models\Tva;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TvaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $dataTva = DB::table('tvas as t')
                        ->join('users as u','u.id','t.iduser')
                        ->whereNull('t.deleted_at')
                ->select(
                    't.id',
                    't.name',
                    't.value',
                    'u.name as username',
                    't.created_at'
                );

            return DataTables::of($dataTva)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (auth()->user()->can('Taxes-modifier')) { 
                        // Edit button
                        $btn .= '<a href="#" class="btn btn-sm bg-primary-subtle me-1 editTva" data-id="' . $row->id . '">
                                    <i class="fa-solid fa-pen-to-square text-primary"></i>
                                </a>';
                    }
                   
                    if (auth()->user()->can('Taxes-supprimer')) { 
                        // Delete button
                        $btn .= '<a href="#" class="btn btn-sm bg-danger-subtle deleteTva"
                                    data-id="' . $row->id . '" data-bs-toggle="tooltip" 
                                    title="Supprimer TVA">
                                    <i class="fa-solid fa-trash text-danger"></i>
                                </a>';
                    }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
             
        return view('tva.index', [
            'tvas' => Tva::latest('id')->paginate(10)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'value' => 'required|numeric',
        ], [
            'required' => 'Le champ :attribute est requis.',
            'numeric' => 'Le champ :attribute doit être un nombre.',
        ], [
            'name' => 'nom',
            'value' => 'valeur',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ], 400);
        }
    
        // Nettoyer et standardiser le nom pour la comparaison
        $cleanedName = strtolower(trim($request->name));
        $cleanedValue = trim($request->value);
        
        // Vérifier si une TVA avec un nom similaire existe déjà (insensible à la casse)
        $nameExists = Tva::whereRaw('LOWER(TRIM(name)) = ?', [$cleanedName])->count();
        
        if ($nameExists > 0) {
            return response()->json([
                'status' => 422,
                'message' => 'Une TVA avec ce nom existe déjà',
            ], 422);
        }
        
        // Vérifier si une TVA avec cette valeur existe déjà
        $valueExists = Tva::where('value', $cleanedValue)->count();
        
        if ($valueExists > 0) {
            return response()->json([
                'status' => 422,
                'message' => 'Une TVA avec cette valeur existe déjà',
            ], 422);
        }
    
        // Créer la TVA avec les valeurs nettoyées
        $tva = Tva::create([
            'name' => trim($request->name), // Conserver la casse d'origine mais supprimer les espaces
            'value' => $cleanedValue,
            'iduser' => Auth::user()->id,
        ]);
    
        if($tva) {
            return response()->json([
                'status' => 200,
                'message' => 'TVA créée avec succès',
            ]);
        } else { 
            return response()->json([
                'status' => 500,
                'message' => 'Quelque chose ne va pas'
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Tva $tva): RedirectResponse
    {
        return redirect()->route('tva.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $tva = Tva::find($id);
        
        if (!$tva) {
            return response()->json([
                'status' => 404,
                'message' => 'TVA non trouvée'
            ], 404);
        }

        return response()->json($tva);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $tva = Tva::find($request->id);

        if (!$tva) {
            return response()->json([
                'status' => 404,
                'message' => 'TVA non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'value' => 'required|numeric',
        ], [
            'required' => 'Le champ :attribute est requis.',
            'numeric' => 'Le champ :attribute doit être un nombre.',
        ], [
            'name' => 'nom',
            'value' => 'valeur',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ], 400);
        }

        $tva->update([
            'name' => $request->name,
            'value' => $request->value,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'TVA mise à jour avec succès',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $tva = Tva::find($request->id);

        if (!$tva) {
            return response()->json([
                'status' => 404,
                'message' => 'TVA non trouvée'
            ], 404);
        }

        if ($tva->delete()) {
            return response()->json([
                'status' => 200,
                'message' => 'TVA supprimée avec succès'
            ]);
        }

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la suppression'
        ], 500);
    }
}