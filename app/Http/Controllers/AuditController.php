<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;

class AuditController extends Controller
{
    /**
     * Display a listing of all audits.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $audits = Audit::with('user')
                ->select([
                    'id',
                    'user_id',
                    'event',
                    'auditable_id',
                    'auditable_type',
                    'old_values',
                    'new_values',
                    'created_at',
                ]);

            // Apply model type filter
            if ($request->has('model_type') && !empty($request->model_type)) {
                $modelClass = $this->getModelClass($request->model_type);
                if ($modelClass) {
                    $audits->where('auditable_type', $modelClass);
                }
            }

            // Apply user filter
            if ($request->has('user_id') && !empty($request->user_id)) {
                $audits->where('user_id', $request->user_id);
            }

            // Apply event type filter
            if ($request->has('event') && !empty($request->event)) {
                $audits->where('event', $request->event);
            }

            // Apply date range filter
            if ($request->has('start_date') && $request->has('end_date')) {
                try {
                    $start = Carbon::parse($request->start_date)->startOfDay();
                    $end = Carbon::parse($request->end_date)->endOfDay();
                    $audits->whereBetween('created_at', [$start, $end]);
                } catch (\Exception $e) {
                    // Continue with query without date filter
                }
            }

            return DataTables::of($audits)
                ->addIndexColumn()
                ->addColumn('model_type', function ($row) {
                    return $this->getReadableModelName($row->auditable_type);
                })
                ->addColumn('model_name', function ($row) {
                    return $this->getModelName($row->auditable_type, $row->auditable_id);
                })
                ->addColumn('user_name', function ($row) {
                    return $row->user ? $row->user->name : 'Système';
                })
                ->addColumn('changes', function ($row) {
                    // Just a placeholder for the view details button
                    return '';
                })
                ->editColumn('event', function ($row) {
                    $labels = [
                        'created' => '<span class="badge bg-success">Création</span>',
                        'updated' => '<span class="badge bg-info">Modification</span>',
                        'deleted' => '<span class="badge bg-danger">Suppression</span>',
                        'restored' => '<span class="badge bg-warning">Restauration</span>',
                    ];
                    
                    return $labels[$row->event] ?? '<span class="badge bg-secondary">' . $row->event . '</span>';
                })
                ->rawColumns(['model_type', 'event', 'changes'])
                ->make(true);
        }
        
        return view('audit.index');
    }

    /**
     * Display the details of a specific audit.
     */
    public function details($id)
    {
        // Find the audit record
        $audit = Audit::with('user')->find($id);
        
        if (!$audit) {
            return redirect('audit')->with('error', 'Audit non trouvé');
        }
        
        // Get values safely
        $oldValues = is_string($audit->old_values) ? json_decode($audit->old_values, true) : $audit->old_values;
        $oldValues = $oldValues ?: [];
        
        $newValues = is_string($audit->new_values) ? json_decode($audit->new_values, true) : $audit->new_values;
        $newValues = $newValues ?: [];
        
        // Get field names
        $fieldNames = $this->getFieldNames($audit->auditable_type);
        
        // Return the view with data
        return view('audit.details', [
            'audit' => $audit,
            'oldValues' => $oldValues,
            'newValues' => $newValues,
            'modelType' => $this->getReadableModelName($audit->auditable_type),
            'modelName' => $this->getModelName($audit->auditable_type, $audit->auditable_id),
            'userName' => $audit->user ? $audit->user->name : 'Système',
            'fieldNames' => $fieldNames,
            'eventName' => $this->getReadableEvent($audit->event)
        ]);
    }
    
    /**
     * Export audits as CSV.
     */
    public function export(Request $request)
    {
        $audits = Audit::with('user');
        
        // Apply filters - same as index method
        if ($request->has('model_type') && !empty($request->model_type)) {
            $modelClass = $this->getModelClass($request->model_type);
            if ($modelClass) {
                $audits->where('auditable_type', $modelClass);
            }
        }
        
        if ($request->has('user_id') && !empty($request->user_id)) {
            $audits->where('user_id', $request->user_id);
        }
        
        if ($request->has('event') && !empty($request->event)) {
            $audits->where('event', $request->event);
        }
        
        if ($request->has('start_date') && $request->has('end_date')) {
            try {
                $start = Carbon::parse($request->start_date)->startOfDay();
                $end = Carbon::parse($request->end_date)->endOfDay();
                $audits->whereBetween('created_at', [$start, $end]);
            } catch (\Exception $e) {
                // Continue without date filter
            }
        }
        
        $audits = $audits->orderBy('created_at', 'desc')->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="historique_' . date('Y-m-d') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];
        
        $callback = function() use ($audits) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM
            
            // Headers
            fputcsv($file, ['Type', 'Élément', 'Action', 'Utilisateur', 'Modifications', 'Date']);
            
            foreach ($audits as $audit) {
                $oldValues = is_string($audit->old_values) ? json_decode($audit->old_values, true) : $audit->old_values;
                $oldValues = $oldValues ?: [];
                
                $newValues = is_string($audit->new_values) ? json_decode($audit->new_values, true) : $audit->new_values;
                $newValues = $newValues ?: [];
                
                // Format the changes description
                $changes = $this->formatChangesForCsv($audit->event, $oldValues, $newValues, $audit->auditable_type);
                
                fputcsv($file, [
                    $this->getReadableModelName($audit->auditable_type),
                    $this->getModelName($audit->auditable_type, $audit->auditable_id),
                    $this->getReadableEvent($audit->event),
                    $audit->user ? $audit->user->name : 'Système',
                    $changes,
                    $audit->created_at->format('d/m/Y H:i:s')
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Format changes for CSV export.
     */
    private function formatChangesForCsv($event, $oldValues, $newValues, $modelClass)
    {
        $changes = '';
        
        if ($event === 'created') {
            $changes = 'Création: ';
            foreach ($newValues as $key => $value) {
                $fieldName = $this->getReadableFieldName($modelClass, $key);
                $changes .= $fieldName . ': ' . $this->formatValue($value) . '; ';
            }
        } else if ($event === 'updated') {
            $changes = 'Modification: ';
            foreach ($newValues as $key => $value) {
                if (isset($oldValues[$key])) {
                    $fieldName = $this->getReadableFieldName($modelClass, $key);
                    $changes .= $fieldName . ': ' . $this->formatValue($oldValues[$key]) . ' → ' . 
                               $this->formatValue($value) . '; ';
                }
            }
        } else if ($event === 'deleted') {
            $changes = 'Suppression';
        } else if ($event === 'restored') {
            $changes = 'Restauration';
        }
        
        return $changes;
    }
    
    /**
     * Format a value for display.
     */
    private function formatValue($value)
    {
        if ($value === null) {
            return 'Non défini';
        }
        
        if (is_array($value)) {
            return json_encode($value);
        }
        
        // Handle date values
        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $value)) {
            try {
                return Carbon::parse($value)->format('d/m/Y H:i:s');
            } catch (\Exception $e) {
                // Not a valid date, return as is
            }
        }
        
        return $value;
    }
    
    /**
     * Get readable field name.
     */
    private function getReadableFieldName($modelClass, $key)
    {
        $fieldNames = $this->getFieldNames($modelClass);
        return $fieldNames[$key] ?? $key;
    }
    
    /**
     * Get field names for a model type.
     */
    private function getFieldNames($modelClass)
    {
        // Client model field names
        if ($modelClass === Client::class) {
            return [
                'first_name' => 'Prénom',
                'last_name' => 'Nom',
                'Telephone' => 'Téléphone',
                'Email' => 'Adresse email',
                'iduser' => 'Créé par',
                'deleted_at' => 'Date de suppression'
            ];
        }
        
        // User model field names
        else if ($modelClass === User::class) {
            return [
                'name' => 'Nom',
                'email' => 'Email',
                'password' => 'Mot de passe',
                'deleted_at' => 'Date de suppression'
            ];
        }
        
        return [];
    }
    
    /**
     * Get model class from type string.
     */
    private function getModelClass($type)
    {
        $mapping = [
            'client' => Client::class,
            'user' => User::class,
            // Add other mappings as needed
        ];
        
        return $mapping[$type] ?? null;
    }
    
    /**
     * Get readable event name.
     */
    private function getReadableEvent($event)
    {
        $events = [
            'created' => 'Création',
            'updated' => 'Modification',
            'deleted' => 'Suppression',
            'restored' => 'Restauration',
        ];
        
        return $events[$event] ?? ucfirst($event);
    }
    
    /**
     * Get readable model name.
     */
    private function getReadableModelName($modelClass)
    {
        $mapping = [
            Client::class => 'Client',
            User::class => 'Utilisateur',
            // Add other models here
        ];
        
        return $mapping[$modelClass] ?? class_basename($modelClass);
    }
    
    /**
     * Get a friendly name for a model instance.
     */
    private function getModelName($modelClass, $id)
    {
        if ($modelClass === Client::class) {
            $client = Client::withTrashed()->find($id);
            return $client ? $client->first_name . ' ' . $client->last_name : 'Client #' . $id;
        }
        
        if ($modelClass === User::class) {
            $user = User::withTrashed()->find($id);
            return $user ? $user->name : 'Utilisateur #' . $id;
        }
        
        return 'ID: ' . $id;
    }
}