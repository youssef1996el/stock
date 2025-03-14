<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'code_article',
        'unite',
        'price_achat',
        'price_vente',
        'code_barre',
        'emplacement',
        'id_categorie',
        'id_subcategorie',
        'id_local',
        'id_rayon',
        'id_user',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'id_categorie');
    }

    /**
     * Get the subcategory that owns the product.
     */
    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class, 'id_subcategorie');
    }

    /**
     * Get the local that owns the product.
     */
    public function local()
    {
        return $this->belongsTo(Local::class, 'id_local');
    }

    /**
     * Get the rayon that owns the product.
     */
    public function rayon()
    {
        return $this->belongsTo(Rayon::class, 'id_rayon');
    }

    /**
     * Get the user that owns the product.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /**
     * Get the stock associated with the product.
     */
    public function stock()
    {
        return $this->hasOne(Stock::class, 'id_product');
    }

    /**
     * Generate a code article based on category and subcategory.
     *
     * @param string $categoryName The category name
     * @param string $subcategoryName The subcategory name
     * @return string The generated code article
     */
    public static function generateCodeArticle($categoryName, $subcategoryName)
    {
        // Extract first 3 letters of category and subcategory
        $categoryPrefix = strtolower(substr(preg_replace('/\s+/', '', $categoryName), 0, 3));
        $subcategoryPrefix = strtolower(substr(preg_replace('/\s+/', '', $subcategoryName), 0, 3));
        
        // Combine prefixes
        $prefix = $categoryPrefix . $subcategoryPrefix;
        
        // Get the latest code with this prefix
        $latestCode = self::where('code_article', 'like', $prefix . '%')
            ->orderBy('code_article', 'desc')
            ->first();
        
        // If no codes exist with this prefix, start with 001
        // Otherwise, increment the last number
        $nextNumber = 1;
        if ($latestCode) {
            // Extract the number from the end of the code
            if (preg_match('/(\d+)$/', $latestCode->code_article, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            }
        }
        
        // Format the number as a 3-digit string with leading zeros
        $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        
        // Return the complete code
        return $prefix . $formattedNumber;
    }

    /**
     * Generate the emplacement string.
     * 
     * @return string The formatted emplacement string
     */
    public function generateEmplacement()
    {
        // Get related models
        $localName = $this->local ? $this->local->name : '';
        $rayonName = $this->rayon ? $this->rayon->name : '';
        $categoryName = $this->category ? $this->category->name : '';
        $subcategoryName = $this->subcategory ? $this->subcategory->name : '';
        
        // Format: LOCAL / RAYON / CATEGORIE / FAMILLE / code-article
        $emplacement = implode(' / ', array_filter([
            $localName,
            $rayonName,
            $categoryName,
            $subcategoryName,
            $this->code_article
        ]));
        
        return $emplacement;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        // Before saving, generate emplacement if not provided
        static::saving(function ($product) {
            if (empty($product->emplacement)) {
                $product->emplacement = $product->generateEmplacement();
            }
        });
    }
}