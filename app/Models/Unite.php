<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Unite extends Model implements Auditable
{
    use HasFactory;
    use SoftDeletes;
    use AuditableTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'unite';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'iduser',
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
     * Get the user that owns the unite.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'iduser');
    }

    /**
     * Get the stock items that use this unite.
     */
    public function stocks()
    {
        return $this->hasMany(Stock::class, 'unit_id');
    }
}