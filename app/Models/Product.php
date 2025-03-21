<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

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
     * Generate a unique code article based on category and subcategory names
     * Format: first 3 letters of category + first 3 letters of subcategory + sequential number (001, 002, etc.)
     * The sequence number continues across all products regardless of category or family
     * 
     * @param string $categoryName The category name
     * @param string $subcategoryName The subcategory (family) name
     * @return string The generated code article
     */
    public static function generateCodeArticle($categoryName, $subcategoryName)
    {
        // Clean up the category and subcategory names and get their first 3 letters
        $categoryPrefix = strtolower(substr(preg_replace('/\s+/', '', $categoryName), 0, 3));
        $subcategoryPrefix = strtolower(substr(preg_replace('/\s+/', '', $subcategoryName), 0, 3));

        // Combine the prefixes to form the beginning of the code
        $prefix = $categoryPrefix . $subcategoryPrefix;

        // Find the last created product to get the highest sequence number globally
        $lastProduct = self::orderBy('id', 'desc')->first();
        
        // Start with 001 if no products exist
        $nextNumber = 1;
        
        if ($lastProduct && !empty($lastProduct->code_article)) {
            // Extract the numeric part from the last product code (last 3 digits)
            if (preg_match('/(\d{3})$/', $lastProduct->code_article, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            }
        }

        // Format the next number as a 3-digit number with leading zeros
        $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        // Construct the new product code
        $newCode = $prefix . $formattedNumber;

        // Keep generating new codes until we find a unique one
        while (self::where('code_article', $newCode)->exists()) {
            $nextNumber++;
            $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            $newCode = $prefix . $formattedNumber;
        }

        return $newCode;
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