<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempAchat extends Model
{
    use HasFactory;

    protected $table = 'temp_Achat';

    protected $fillable = [
        'id_user',
        'idproduit',
        'id_fournisseur',
        'qte'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'idproduit');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class, 'id_fournisseur');
    }
}