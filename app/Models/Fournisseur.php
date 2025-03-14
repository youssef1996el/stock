<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fournisseur extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fournisseurs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'entreprise',
        'Telephone',
        'iduser',
        'Email',
    ];

    /**
     * Get the user that owns the fournisseur.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'iduser');
    }
}