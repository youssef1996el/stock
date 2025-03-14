<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stock';  // Changed from 'stocks' to 'stock'

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_product',
        'id_tva',
        'id_unite',
        'quantite',
        'seuil'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'quantite' => 'float',
        'seuil' => 'float'
    ];

    /**
     * Get the product associated with the stock.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product');
    }

    /**
     * Get the TVA associated with the stock.
     */
    public function tva()
    {
        return $this->belongsTo(Tva::class, 'id_tva');
    }

    /**
     * Get the Unite associated with the stock.
     */
    public function unite()
    {
        return $this->belongsTo(Unite::class, 'id_unite');
    }
}