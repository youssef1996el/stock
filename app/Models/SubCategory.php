<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class SubCategory extends Model implements Auditable
{
    use HasFactory;
    use SoftDeletes;
    use AuditableTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sub_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'id_categorie',
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
     * Get the category that owns the subcategory.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'id_categorie');
    }

    /**
     * Get the user that owns the subcategory.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'iduser');
    }
}