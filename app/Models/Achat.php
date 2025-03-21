<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Achat extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'achats';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'total',
        'status',
        'id_Fournisseur',
        'id_user',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'total' => 'decimal:2',
    ];

    /**
     * Get the fournisseur that owns the achat.
     */
    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class, 'id_Fournisseur');
    }

    /**
     * Get the user that owns the achat.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}