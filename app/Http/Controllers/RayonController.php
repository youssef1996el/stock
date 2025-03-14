<?php

namespace App\Http\Controllers;

use App\Models\Rayon;
use App\Models\Local;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RayonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $dataRayon = DB::table('rayons as r')
                ->join('users as u', 'u.id', 'r.iduser')
                ->join('locals as l', 'l.id', 'r.id_local')
                ->select(
                    'r.id',
                    'r.name',
                    'l.name as local_name',
                    'u.name as username',
                    'r.created_at'
                );

            return DataTables::of($dataRayon)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';

                    // Edit button
                    $btn .= '<a href="#" class="btn btn-sm bg-primary-subtle me-1 editRayon"
                                data-id="' . $row->id . '">
                                <i class="fa-solid fa-pen-to-square text-primary"></i>
                            </a>';

                    // Delete button
                    $btn .= '<a href="#" class="btn btn-sm bg-danger-subtle deleteRayon"
                                data-id="' . $row->id . '" data-bs-toggle="tooltip" 
                                title="Supprimer Rayon">
                                <i class="fa-solid fa-trash text-danger"></i>
                            </a>';

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        
        $locals = Local::all();
        
        return view('rayon.index', [
            'rayons' => Rayon::latest('id')->paginate(10),
            'locals' => $locals
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'id_local' => 'required|exists:locals,id',
        ], [
            'required' => 'Le champ :attribute est requis.',
            'exists' => 'Le local sélectionné n\'existe pas.',
        ], [
            'name' => 'nom',
            'id_local' => 'local',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ], 400);
        }

        // Check if rayon already exists with the same name in the same local
        $exists = Rayon::where('name', $request->name)
            ->where('id_local', $request->id_local)
            ->exists();
            
        if ($exists) {
            return response()->json([
                'status' => 409, // Conflict status code
                'message' => 'Ce rayon existe déjà pour ce local',
            ], 409);
        }

        $rayon = Rayon::create([
            'name' => $request->name,
            'id_local' => $request->id_local,
            'iduser' => Auth::user()->id,
        ]);

        if($rayon) {
            return response()->json([
                'status' => 200,
                'message' => 'Rayon créé avec succès',
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
        $rayon = Rayon::find($id);
        
        if (!$rayon) {
            return response()->json([
                'status' => 404,
                'message' => 'Rayon non trouvé'
            ], 404);
        }
        
        return response()->json($rayon);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $rayon = Rayon::find($request->id);
        
        if (!$rayon) {
            return response()->json([
                'status' => 404,
                'message' => 'Rayon non trouvé'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'id_local' => 'required|exists:locals,id',
        ], [
            'required' => 'Le champ :attribute est requis.',
            'exists' => 'Le local sélectionné n\'existe pas.',
        ], [
            'name' => 'nom',
            'id_local' => 'local',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ], 400);
        }
        
        // Check if another rayon with the same name exists in the same local
        $exists = Rayon::where('name', $request->name)
            ->where('id_local', $request->id_local)
            ->where('id', '!=', $request->id) // Exclude current record
            ->exists();
            
        if ($exists) {
            return response()->json([
                'status' => 409, // Conflict status code
                'message' => 'Ce rayon existe déjà pour ce local',
            ], 409);
        }

        $rayon->name = $request->name;
        $rayon->id_local = $request->id_local;
        $saved = $rayon->save();
        
        if ($saved) {
            return response()->json([
                'status' => 200,
                'message' => 'Rayon mis à jour avec succès',
            ]);
        } else {
            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors de la mise à jour du rayon',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $rayon = Rayon::find($request->id);

        if (!$rayon) {
            return response()->json([
                'status' => 404,
                'message' => 'Rayon non trouvé'
            ], 404);
        }

        if ($rayon->delete()) {
            return response()->json([
                'status' => 200,
                'message' => 'Rayon supprimé avec succès'
            ]);
        }

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la suppression'
        ], 500);
    }
}