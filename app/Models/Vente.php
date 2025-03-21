<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Vente extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'total',
        'status',
        'id_client',
        'id_user',
    ];

    /**
     * Get the client associated with the vente.
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'id_client');
    }

    /**
     * Get the user who created the vente.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /**
     * Get the ligne_ventes for the vente.
     */
    public function ligneVentes()
    {
        return $this->hasMany(LigneVente::class, 'idvente');
    }
}