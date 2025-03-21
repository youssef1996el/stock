<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class TempVente extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'temp_Vente';

    protected $fillable = [
        'id_user',
        'idproduit',
        'id_client',
        'qte',
    ];

    /**
     * Get the user who created the temporary vente.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /**
     * Get the product associated with the temporary vente.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'idproduit');
    }

    /**
     * Get the client associated with the temporary vente.
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'id_client');
    }
}