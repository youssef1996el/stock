<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;
class TempAchat extends Model
{
    use HasFactory;
    // use SoftDeletes;

    protected $table = 'temp_achat';

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