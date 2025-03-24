<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable; // Ajoutez cette ligne
use OwenIt\Auditing\Auditable as AuditableTrait;

class Fournisseur extends Model implements Auditable // Ajoutez "implements Auditable" ici
{
    use HasFactory;
    use SoftDeletes;
    use AuditableTrait;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fournisseurs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'entreprise',
        'Telephone',
        'iduser',
        'Email',
    ];
    
    protected $auditExclude = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
    /**
     * Get the user that owns the fournisseur.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'iduser');
    }
}