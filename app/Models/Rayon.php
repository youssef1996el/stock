<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Rayon extends Model implements Auditable
{
    use HasFactory;
    use SoftDeletes;
    use AuditableTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rayons';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'iduser',
        'id_local',
    ];
    
    /**
     * Attributes to exclude from the Audit.
     *
     * @var array
     */
    protected $auditExclude = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Get the user that owns the rayon.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'iduser');
    }

    /**
     * Get the local that owns the rayon.
     */
    public function local()
    {
        return $this->belongsTo(Local::class, 'id_local');
    }
}