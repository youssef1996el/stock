<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TvaController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\LocalController;
use App\Http\Controllers\RayonController;
use App\Http\Controllers\UniteController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\AchatController;


Auth::routes();

Route::get('/', function () {
    return redirect()->route('login');
});


Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::resources([
    'roles' => RoleController::class,
    'users' => UserController::class,
    'products' => ProductController::class,
   
]);
// Route::get('/', function () {
//     return view('template.index');
// });

// TVA Routes
Route::get('tva'                    , [TvaController::class,        'index'     ]);
Route::post('addTva'                , [TvaController::class,        'store'     ]);
Route::get('tva/{id}/edit'          , [TvaController::class,        'edit'      ]);
Route::post('updateTva'             , [TvaController::class,        'update'    ]);
Route::post('DeleteTva'             , [TvaController::class,        'destroy'   ]);
// Category Routes


Route::get('categories'             , [CategoriesController::class, 'index'    ]);
Route::post('addCategory'           , [CategoriesController::class, 'store'    ]);
Route::get('editCategory/{id}'      , [CategoriesController::class, 'edit'     ]);
Route::post('updateCategory'        , [CategoriesController::class, 'update'   ]);
Route::post('DeleteCategory'        , [CategoriesController::class, 'destroy'  ]);
// sub Category Routes
Route::get('subcategory', [SubCategoryController::class, 'index']);
Route::post('addSubCategory', [SubCategoryController::class, 'store']);
Route::post('updateSubCategory', [SubCategoryController::class, 'update']);
Route::post('DeleteSubCategory', [SubCategoryController::class, 'destroy']);
Route::get('editSubCategory/{id}', [SubCategoryController::class, 'edit']);

// Local Routes
Route::get('local', [LocalController::class, 'index']);
Route::post('addLocal', [LocalController::class, 'store']);
Route::post('updateLocal', [LocalController::class, 'update']);
Route::post('DeleteLocal', [LocalController::class, 'destroy']);
Route::get('editLocal/{id}', [LocalController::class, 'edit']);
// Rayon Routes
Route::get('rayon', [RayonController::class, 'index']);
Route::post('addRayon', [RayonController::class, 'store']);
Route::post('updateRayon', [RayonController::class, 'update']);
Route::post('DeleteRayon', [RayonController::class, 'destroy']);
Route::get('editRayon/{id}', [RayonController::class, 'edit']);
// Product Routes

Route::get('products', [ProductController::class, 'index']);
Route::post('addProduct', [ProductController::class, 'store']);
Route::get('editProduct/{id}', [ProductController::class, 'edit']);
Route::post('updateProduct', [ProductController::class, 'update']);
Route::post('deleteProduct', [ProductController::class, 'destroy']);



// Fournisseur routes
Route::get('/fournisseur', [FournisseurController::class, 'index'])->name('fournisseur.index');
Route::post('/addFournisseur', [FournisseurController::class, 'store']);
Route::get('/editFournisseur/{id}', [FournisseurController::class, 'edit']);
Route::post('/updateFournisseur', [FournisseurController::class, 'update']);
Route::post('/DeleteFournisseur', [FournisseurController::class, 'destroy']);


// Dependent dropdown routes
Route::get('getSubcategories/{id}', [ProductController::class, 'getSubcategories']);
Route::get('getRayons/{id}', [ProductController::class, 'getRayons']);


// Unite routes


// Unite routes
Route::get('unite', [App\Http\Controllers\UniteController::class, 'index']);
Route::post('addUnite', [App\Http\Controllers\UniteController::class, 'store']);
Route::get('editUnite/{id}', [App\Http\Controllers\UniteController::class, 'edit']);
Route::post('updateUnite', [App\Http\Controllers\UniteController::class, 'update']);
Route::post('deleteUnite', [App\Http\Controllers\UniteController::class, 'destroy']);

// achat routes
Route::get('Achat'                ,[AchatController::class,'index'                     ]); 
Route::get('getProduct'           ,[AchatController::class,'getProduct'                ]); 
Route::post('PostInTmpAchat'      ,[AchatController::class,'PostInTmpAchat'            ]); 
Route::get('GetTmpAchatByFournisseur'      ,[AchatController::class,'GetTmpAchatByFournisseur'            ]); 



// Route::get('/productlist'           , function () {
//     return view('template.productlist');
// });
/* Route::get('/adduser', function () {
    return view('Users.create');
}); */
Route::post('adduser',[UserController::class,'store']);
// Route::get('/signin', function () {
//     return view('template.signin');
// });
// Route::get('/addproduct', function () {
//     return view('template.addproduct');
// });
// Route::get('/test', function () {
//     return view('template.test');
// });
// Route::get('/sidebar', function () {
//     return view('layouts.sidebar');
// });
// Route::get('/categorylist', function () {
//     return view('template.categorylist');
// });
// Route::get('/addcategory', function () {
//     return view('template.addcategory');
// });
// Route::get('/subcategorylist', function () {
//     return view('template.subcategorylist');
// });
// Route::get('/subaddcategory', function() {
//   return view('template.subaddcategory');
// });
// Route::get('/saleslist', function() {
//   return view('template.saleslist');
// });
// Route::get('/salesreturnlists', function() {
//     return view('template.salesreturnlists');
// });
// Route::get('/salesreturnlist', function() {
//     return view('template.salesreturnlist');
// });
// Route::get('/createsalesreturn', function() {
//     return view('template.createsalesreturn');
// });
// Route::get('/createsalesreturns', function() {
//     return view('template.createsalesreturns');
// });
// Route::get('/newuser', function() {
//     return view('template.newuser');
// });
// Route::get('/userlists', function() {
//     return view('template.userlists');
// });


Route::post('updateUser' ,[UserController::class,'update']);
Route::post('DeleteUser' ,[UserController::class,'destroy']);

Route::post('updateRole'   , [RoleController::class,'update']);
Route::post('DeleteRole'   , [RoleController::class,'destroy']);




