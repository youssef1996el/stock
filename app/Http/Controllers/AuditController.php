<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Client;
use App\Models\User;
use App\Models\Fournisseur;
use App\Models\Local;
use App\Models\Tva;
use App\Models\Rayon;
use App\Models\Unite;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\SubCategory;
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
        
        // Process special fields (like resolving IDs to names)
        $oldValues = $this->processSpecialFields($audit->auditable_type, $oldValues, $audit->auditable_id);
        $newValues = $this->processSpecialFields($audit->auditable_type, $newValues, $audit->auditable_id);
        
        // Suppression des champs ID dans les valeurs
        if (isset($oldValues['id'])) {
            unset($oldValues['id']);
        }
        if (isset($newValues['id'])) {
            unset($newValues['id']);
        }
        
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
     * Process special fields like resolving IDs to names.
     */
    private function processSpecialFields($modelClass, $values, $auditableId = null)
    {
        $processedValues = $values;
        
        // Process IDs to names for specific fields
        foreach ($processedValues as $key => $value) {
            // Process user IDs to names
            if (in_array($key, ['iduser', 'id_user']) && !empty($value)) {
                $user = User::find($value);
                if ($user) {
                    $processedValues[$key] = $user->name;
                }
            }
            
            // Process other foreign keys as needed
            if ($key === 'id_local' && !empty($value)) {
                $local = Local::find($value);
                if ($local) {
                    $processedValues[$key] = $local->name;
                }
            }
            
            if ($key === 'id_rayon' && !empty($value)) {
                $rayon = Rayon::find($value);
                if ($rayon) {
                    $processedValues[$key] = $rayon->name;
                }
            }
            
            if ($key === 'id_categorie' && !empty($value)) {
                $category = Category::find($value);
                if ($category) {
                    $processedValues[$key] = $category->name;
                }
            }
            
            if ($key === 'id_subcategorie' && !empty($value)) {
                $subcategory = SubCategory::find($value);
                if ($subcategory) {
                    $processedValues[$key] = $subcategory->name;
                }
            }
            
            // Handling unite for Product model
            if ($key === 'unite' && $modelClass === Product::class) {
                // For products, if unite is null, get it from the Stock table
                if (empty($value) && $auditableId) {
                    $stock = Stock::where('id_product', $auditableId)->first();
                    if ($stock && $stock->id_unite) {
                        $unite = Unite::find($stock->id_unite);
                        if ($unite) {
                            $processedValues[$key] = $unite->name;
                        }
                    }
                } else if (!empty($value)) {
                    $unite = Unite::find($value);
                    if ($unite) {
                        $processedValues[$key] = $unite->name;
                    }
                }
            }
        }
        
        return $processedValues;
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
                
                // Remove ID from values
                if (isset($oldValues['id'])) {
                    unset($oldValues['id']);
                }
                if (isset($newValues['id'])) {
                    unset($newValues['id']);
                }
                
                // Process special fields
                $oldValues = $this->processSpecialFields($audit->auditable_type, $oldValues, $audit->auditable_id);
                $newValues = $this->processSpecialFields($audit->auditable_type, $newValues, $audit->auditable_id);
                
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
                if ($key !== 'id') { // Ne pas inclure l'ID dans les changements
                    $fieldName = $this->getReadableFieldName($modelClass, $key);
                    $changes .= $fieldName . ': ' . $this->formatValue($value) . '; ';
                }
            }
        } else if ($event === 'updated') {
            $changes = 'Modification: ';
            foreach ($newValues as $key => $value) {
                if ($key !== 'id' && isset($oldValues[$key])) { // Ne pas inclure l'ID dans les changements
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
        
        // Fournisseur model field names
        else if ($modelClass === Fournisseur::class) {
            return [
                'entreprise' => 'Entreprise',
                'Telephone' => 'Téléphone',
                'Email' => 'Adresse email',
                'iduser' => 'Créé par',
                'deleted_at' => 'Date de suppression'
            ];
        }
        
        // Local model field names
        else if ($modelClass === Local::class) {
            return [
                'name' => 'Nom',
                'iduser' => 'Créé par',
                'deleted_at' => 'Date de suppression'
            ];
        }
        
        // Tva model field names
        else if ($modelClass === Tva::class) {
            return [
                'name' => 'Nom',
                'value' => 'Valeur (%)',
                'iduser' => 'Créé par',
                'deleted_at' => 'Date de suppression'
            ];
        }
        
        // Rayon model field names
        else if ($modelClass === Rayon::class) {
            return [
                'name' => 'Nom',
                'iduser' => 'Créé par',
                'id_local' => 'Local',
                'deleted_at' => 'Date de suppression'
            ];
        }
        
        // Unite model field names
        else if ($modelClass === Unite::class) {
            return [
                'name' => 'Nom',
                'iduser' => 'Créé par',
                'deleted_at' => 'Date de suppression'
            ];
        }
        
        // Category model field names
        else if ($modelClass === Category::class) {
            return [
                'name' => 'Nom',
                'iduser' => 'Créé par',
                'deleted_at' => 'Date de suppression'
            ];
        }
        
        // SubCategory model field names
        else if ($modelClass === SubCategory::class) {
            return [
                'name' => 'Nom',
                'id_categorie' => 'Catégorie',
                'iduser' => 'Créé par',
                'deleted_at' => 'Date de suppression'
            ];
        }
        
        // Product model field names
        else if ($modelClass === Product::class) {
            return [
                'name' => 'Nom',
                'code_article' => 'Code article',
                'unite' => 'Unité',
                'price_achat' => 'Prix d\'achat',
                'price_vente' => 'Prix de vente',
                'code_barre' => 'Code barre',
                'emplacement' => 'Emplacement',
                'id_categorie' => 'Catégorie',
                'id_subcategorie' => 'Sous-catégorie',
                'id_local' => 'Local',
                'id_rayon' => 'Rayon',
                'id_user' => 'Créé par',
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
            'fournisseur' => Fournisseur::class,
            'local' => Local::class,
            'tva' => Tva::class,
            'rayon' => Rayon::class,
            'unite' => Unite::class,
            'category' => Category::class,
            'subcategory' => SubCategory::class,
            'product' => Product::class,
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
            Fournisseur::class => 'Fournisseur',
            Local::class => 'Local',
            Tva::class => 'TVA',
            Rayon::class => 'Rayon',
            Unite::class => 'Unité',
            Category::class => 'Catégorie',
            SubCategory::class => 'Sous-catégorie',
            Product::class => 'Produit',
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
            return $client ? $client->first_name . ' ' . $client->last_name : 'Client';
        }
        
        if ($modelClass === User::class) {
            $user = User::withTrashed()->find($id);
            return $user ? $user->name : 'Utilisateur';
        }
        
        if ($modelClass === Fournisseur::class) {
            $fournisseur = Fournisseur::withTrashed()->find($id);
            return $fournisseur ? $fournisseur->entreprise : 'Fournisseur';
        }
        
        if ($modelClass === Local::class) {
            $local = Local::withTrashed()->find($id);
            return $local ? $local->name : 'Local';
        }
        
        if ($modelClass === Tva::class) {
            $tva = Tva::withTrashed()->find($id);
            return $tva ? $tva->name . ' (' . $tva->value . '%)' : 'TVA';
        }
        
        if ($modelClass === Rayon::class) {
            $rayon = Rayon::withTrashed()->find($id);
            return $rayon ? $rayon->name : 'Rayon';
        }
        
        if ($modelClass === Unite::class) {
            $unite = Unite::withTrashed()->find($id);
            return $unite ? $unite->name : 'Unité';
        }
        
        if ($modelClass === Category::class) {
            $category = Category::withTrashed()->find($id);
            return $category ? $category->name : 'Catégorie';
        }
        
        if ($modelClass === SubCategory::class) {
            $subcategory = SubCategory::withTrashed()->find($id);
            return $subcategory ? $subcategory->name : 'Sous-catégorie';
        }
        
        if ($modelClass === Product::class) {
            $product = Product::withTrashed()->find($id);
            return $product ? $product->name . ' (' . $product->code_article . ')' : 'Produit';
        }
        
        // Pour tout autre modèle, retourner simplement le nom de la classe
        return class_basename($modelClass);
    }
}