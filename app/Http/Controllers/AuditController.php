<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
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
                    // Log parsing error but continue query
                    \Log::error('Date parsing error: ' . $e->getMessage());
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
                    // This is just a placeholder for the "View details" button
                    // The actual detail content will be loaded via AJAX
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
     * Get audit details
     */
    public function details(Request $request, $id)
    {
        // Find the audit record
        $audit = Audit::with('user')->find($id);
        
        if (!$audit) {
            // If AJAX request, return JSON response
            if ($request->ajax()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Audit non trouvé'
                ], 404);
            }
            
            // Otherwise redirect to the index page with error message
            return redirect('audit')->with('error', 'Audit non trouvé');
        }
        
        // Handle AJAX request (for backward compatibility)
        if ($request->ajax()) {
            $html = $this->generateDetailHtml($audit);
            
            return response()->json([
                'status' => 200,
                'html' => $html
            ]);
        }
        
        // Prepare data for the view - fixed JSON decoding issue
        $oldValues = is_string($audit->old_values) ? json_decode($audit->old_values, true) : $audit->old_values;
        $oldValues = $oldValues ?: [];
        
        $newValues = is_string($audit->new_values) ? json_decode($audit->new_values, true) : $audit->new_values;
        $newValues = $newValues ?: [];
        
        // Get readable field names based on model type
        $fieldNames = $this->getFieldNames($audit->auditable_type);
        
        // Format values for display
        $formattedValues = [
            'old' => [],
            'new' => []
        ];
        
        foreach ($oldValues as $key => $value) {
            $formattedValues['old'][$key] = $this->formatValue($value);
        }
        
        foreach ($newValues as $key => $value) {
            $formattedValues['new'][$key] = $this->formatValue($value);
        }
        
        // Return the details view with the data
        return view('audit.details', [
            'audit' => $audit,
            'oldValues' => $oldValues,
            'newValues' => $newValues,
            'modelType' => $this->getReadableModelName($audit->auditable_type),
            'modelName' => $this->getModelName($audit->auditable_type, $audit->auditable_id),
            'userName' => $audit->user ? $audit->user->name : 'Système',
            'fieldNames' => $fieldNames,
            'formattedValues' => $formattedValues
        ]);
    }
    
    /**
     * Generate HTML for audit details (used for AJAX modal)
     */
    private function generateDetailHtml($audit)
    {
        $html = '<div class="container">';
        
        // Basic info
        $html .= '<div class="row mb-4">';
        $html .= '<div class="col-md-6">';
        $html .= '<p><strong>Type:</strong> ' . $this->getReadableModelName($audit->auditable_type) . '</p>';
        $html .= '<p><strong>Élément:</strong> ' . $this->getModelName($audit->auditable_type, $audit->auditable_id) . '</p>';
        $html .= '</div>';
        $html .= '<div class="col-md-6">';
        $html .= '<p><strong>Action:</strong> ' . $this->getReadableEvent($audit->event) . '</p>';
        $html .= '<p><strong>Utilisateur:</strong> ' . ($audit->user ? $audit->user->name : 'Système') . '</p>';
        $html .= '<p><strong>Date:</strong> ' . $audit->created_at->format('d/m/Y H:i:s') . '</p>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Changes
        $html .= '<div class="row">';
        $html .= '<div class="col-12">';
        $html .= '<h5>Modifications</h5>';
        
        // Fix JSON decoding issue
        $oldValues = is_string($audit->old_values) ? json_decode($audit->old_values, true) : $audit->old_values;
        $oldValues = $oldValues ?: [];
        
        $newValues = is_string($audit->new_values) ? json_decode($audit->new_values, true) : $audit->new_values;
        $newValues = $newValues ?: [];
        
        $html .= '<div class="table-responsive">';
        $html .= '<table class="table table-bordered">';
        $html .= '<thead><tr><th>Champ</th><th>Ancienne valeur</th><th>Nouvelle valeur</th></tr></thead>';
        $html .= '<tbody>';
        
        // Handle create event
        if ($audit->event === 'created') {
            foreach ($newValues as $key => $value) {
                $html .= '<tr>';
                $html .= '<td>' . $this->getReadableFieldName($audit->auditable_type, $key) . '</td>';
                $html .= '<td class="text-muted">-</td>';
                $html .= '<td class="text-success">' . $this->formatValue($value) . '</td>';
                $html .= '</tr>';
            }
        } 
        // Handle update event
        else if ($audit->event === 'updated') {
            foreach ($newValues as $key => $value) {
                if (isset($oldValues[$key])) {
                    $html .= '<tr>';
                    $html .= '<td>' . $this->getReadableFieldName($audit->auditable_type, $key) . '</td>';
                    $html .= '<td class="text-danger">' . $this->formatValue($oldValues[$key]) . '</td>';
                    $html .= '<td class="text-success">' . $this->formatValue($value) . '</td>';
                    $html .= '</tr>';
                }
            }
        }
        // Handle delete event
        else if ($audit->event === 'deleted') {
            $html .= '<tr>';
            $html .= '<td colspan="3" class="text-center text-danger">Cet élément a été supprimé</td>';
            $html .= '</tr>';
            
            // If we have deleted_at timestamp
            if (isset($newValues['deleted_at'])) {
                $html .= '<tr>';
                $html .= '<td>Date de suppression</td>';
                $html .= '<td class="text-muted">-</td>';
                $html .= '<td class="text-danger">' . $this->formatValue($newValues['deleted_at']) . '</td>';
                $html .= '</tr>';
            }
        }
        // Handle restore event
        else if ($audit->event === 'restored') {
            $html .= '<tr>';
            $html .= '<td colspan="3" class="text-center text-success">Cet élément a été restauré</td>';
            $html .= '</tr>';
            
            // If we have deleted_at timestamp
            if (isset($oldValues['deleted_at'])) {
                $html .= '<tr>';
                $html .= '<td>Date de suppression</td>';
                $html .= '<td class="text-danger">' . $this->formatValue($oldValues['deleted_at']) . '</td>';
                $html .= '<td class="text-success">-</td>';
                $html .= '</tr>';
            }
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Get field names for a specific model type
     */
    private function getFieldNames($modelClass)
    {
        // Client model field mappings
        if ($modelClass === Client::class) {
            return [
                'first_name' => 'Prénom',
                'last_name' => 'Nom',
                'Telephone' => 'Téléphone',
                'Email' => 'Adresse email',
                'iduser' => 'Créé par (ID)',
                'deleted_at' => 'Date de suppression'
            ];
        }
        
        // User model field mappings
        else if ($modelClass === User::class) {
            return [
                'name' => 'Nom',
                'email' => 'Adresse email',
                'password' => 'Mot de passe',
                'deleted_at' => 'Date de suppression'
            ];
        }
        
        // Default empty array if model not recognized
        return [];
    }
    
    /**
     * Get model class from type string
     */
    private function getModelClass($type)
    {
        $mapping = [
            'client' => Client::class,
            'user' => User::class,
            // Ajoutez d'autres mappages au besoin
        ];
        
        return $mapping[$type] ?? null;
    }
    
    /**
     * Get readable event name
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
     * Get readable model name
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
     * Get a friendly name for a model instance
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
        
        // Add other model types here
        
        return 'ID: ' . $id;
    }
    
    /**
     * Get readable field name for display
     */
    private function getReadableFieldName($modelClass, $key)
    {
        $fieldNames = $this->getFieldNames($modelClass);
        return $fieldNames[$key] ?? $key;
    }
    
    /**
     * Format value for display
     */
    private function formatValue($value)
    {
        if ($value === null) {
            return '<em>Non défini</em>';
        }
        
        if (is_array($value)) {
            return '<code>' . htmlspecialchars(json_encode($value)) . '</code>';
        }
        
        // Check if it's a date string
        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $value)) {
            try {
                $date = Carbon::parse($value);
                return $date->format('d/m/Y H:i:s');
            } catch (\Exception $e) {
                // Not a valid date, continue with default formatting
            }
        }
        
        return htmlspecialchars($value);
    }
    
    /**
     * Export audits as CSV
     */
    public function export(Request $request)
    {
        $audits = Audit::with('user');
        
        // Apply filters
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
                // Log parsing error but continue query
                \Log::error('Date parsing error in export: ' . $e->getMessage());
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
            // Add UTF-8 BOM
            fputs($file, "\xEF\xBB\xBF");
            
            // Add headers
            fputcsv($file, ['Type', 'Élément', 'Action', 'Utilisateur', 'Modifications', 'Date']);
            
            foreach ($audits as $audit) {
                // Fix JSON decoding issue
                $oldValues = is_string($audit->old_values) ? json_decode($audit->old_values, true) : $audit->old_values;
                $oldValues = $oldValues ?: [];
                
                $newValues = is_string($audit->new_values) ? json_decode($audit->new_values, true) : $audit->new_values;
                $newValues = $newValues ?: [];
                
                $changes = '';
                if ($audit->event === 'created') {
                    $changes = 'Création: ';
                    foreach ($newValues as $key => $value) {
                        $changes .= $this->getReadableFieldName($audit->auditable_type, $key) . ': ' . $this->formatValueForCsv($value) . '; ';
                    }
                } else if ($audit->event === 'updated') {
                    $changes = 'Modification: ';
                    foreach ($newValues as $key => $value) {
                        if (isset($oldValues[$key])) {
                            $changes .= $this->getReadableFieldName($audit->auditable_type, $key) . ': ' . 
                                       $this->formatValueForCsv($oldValues[$key]) . ' -> ' . 
                                       $this->formatValueForCsv($value) . '; ';
                        }
                    }
                } else if ($audit->event === 'deleted') {
                    $changes = 'Suppression';
                } else if ($audit->event === 'restored') {
                    $changes = 'Restauration';
                }
                
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
     * Format value for CSV export
     */
    private function formatValueForCsv($value)
    {
        if ($value === null) {
            return 'Non défini';
        }
        
        if (is_array($value)) {
            return json_encode($value);
        }
        
        // Check if it's a date string
        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $value)) {
            try {
                $date = Carbon::parse($value);
                return $date->format('d/m/Y H:i:s');
            } catch (\Exception $e) {
                // Not a valid date, continue with default formatting
            }
        }
        
        return $value;
    }
}