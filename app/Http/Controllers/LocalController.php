<?php

namespace App\Http\Controllers;

use App\Models\Local;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $dataLocal = DB::table('locals as l')
                ->join('users as u', 'u.id', 'l.iduser')
                ->select(
                    'l.id',
                    'l.name',
                    'u.name as username',
                    'l.created_at'
                );

            return DataTables::of($dataLocal)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';

                    // Edit button
                    $btn .= '<a href="#" class="btn btn-sm bg-primary-subtle me-1 editLocal"
                                data-id="' . $row->id . '">
                                <i class="fa-solid fa-pen-to-square text-primary"></i>
                            </a>';

                    // Delete button
                    $btn .= '<a href="#" class="btn btn-sm bg-danger-subtle deleteLocal"
                                data-id="' . $row->id . '" data-bs-toggle="tooltip" 
                                title="Supprimer Local">
                                <i class="fa-solid fa-trash text-danger"></i>
                            </a>';

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        
        return view('local.index', [
            'locals' => Local::latest('id')->paginate(10)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ], [
            'required' => 'Le champ :attribute est requis.',
        ], [
            'name' => 'nom',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ], 400);
        }

        // Check if local already exists with the same name
        $exists = Local::where('name', $request->name)->exists();
            
        if ($exists) {
            return response()->json([
                'status' => 409, // Conflict status code
                'message' => 'Ce local existe déjà',
            ], 409);
        }

        $local = Local::create([
            'name' => $request->name,
            'iduser' => Auth::user()->id,
        ]);

        if($local) {
            return response()->json([
                'status' => 200,
                'message' => 'Local créé avec succès',
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
        $local = Local::find($id);
        
        if (!$local) {
            return response()->json([
                'status' => 404,
                'message' => 'Local non trouvé'
            ], 404);
        }
        
        return response()->json($local);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $local = Local::find($request->id);
        
        if (!$local) {
            return response()->json([
                'status' => 404,
                'message' => 'Local non trouvé'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ], [
            'required' => 'Le champ :attribute est requis.',
        ], [
            'name' => 'nom',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ], 400);
        }
        
        // Check if another local with the same name exists
        $exists = Local::where('name', $request->name)
            ->where('id', '!=', $request->id) // Exclude current record
            ->exists();
            
        if ($exists) {
            return response()->json([
                'status' => 409, // Conflict status code
                'message' => 'Ce local existe déjà',
            ], 409);
        }

        $local->name = $request->name;
        $saved = $local->save();
        
        if ($saved) {
            return response()->json([
                'status' => 200,
                'message' => 'Local mis à jour avec succès',
            ]);
        } else {
            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors de la mise à jour du local',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $local = Local::find($request->id);

        if (!$local) {
            return response()->json([
                'status' => 404,
                'message' => 'Local non trouvé'
            ], 404);
        }

        if ($local->delete()) {
            return response()->json([
                'status' => 200,
                'message' => 'Local supprimé avec succès'
            ]);
        }

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la suppression'
        ], 500);
    }
}