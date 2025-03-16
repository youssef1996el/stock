<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fournisseur;
use App\Models\Category;
use App\Models\Local;
use App\Models\SubCategory;
use App\Models\Rayon;
use App\Models\Tva;
use App\Models\Unite;
class AchatController extends Controller
{
    public function index()
    {
        $Fournisseur  = Fournisseur::all();
        $categories = Category::all();
        $subcategories = SubCategory::all();
        $locals = Local::all();
        $rayons = Rayon::all();
        $tvas = Tva::all();
        $unites = Unite::all();
        return view('Achat.index')
        ->with('Fournisseur',$Fournisseur)
        ->with('categories',$categories)
        ->with('subcategories',$subcategories)
        ->with('locals',$locals)
        ->with('rayons',$rayons)
        ->with('tvas',$tvas)
        ->with('unites',$unites);
    }
}
