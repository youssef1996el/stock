<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;
class LigneVente extends Model
{
    use HasFactory;
    // use SoftDeletes;

    protected $table = 'ligne_Vente';

    protected $fillable = [
        'id_user',
        'idvente',
        'idproduit',
        'qte',
    ];

    /**
     * Get the user who created the ligne vente.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /**
     * Get the vente that owns the ligne vente.
     */
    public function vente()
    {
        return $this->belongsTo(Vente::class, 'idvente');
    }

    /**
     * Get the product associated with the ligne vente.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'idproduit');
    }
}