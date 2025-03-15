<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LigneAchat extends Model
{
    use HasFactory;

    protected $table = 'ligne_Achat';

    protected $fillable = [
        'id_user',
        'idachat',
        'idproduit',
        'idstock',
        'qte'
    ];

    public function achat()
    {
        return $this->belongsTo(Achat::class, 'idachat');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'idproduit');
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class, 'idstock');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}