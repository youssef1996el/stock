@extends('dashboard.index')

@section('dashboard')


<!-- JS Personnalisé -->
<script src="{{ asset('js/product/script.js') }}"></script>
<script>
    // Variables PHP vers JavaScript
    var csrf_token = "{{ csrf_token() }}";
    var addProduct_url = "{{ url('addProduct') }}";
    var products_url = "{{ url('products') }}";
    var updateProduct_url = "{{ url('updateProduct') }}";
    var deleteProduct_url = "{{ url('deleteProduct') }}";
    var editProduct_url = "{{ url('editProduct') }}";
    var getSubcategories_url = "{{ url('getSubcategories') }}";
    var getRayons_url = "{{ url('getRayons') }}";
</script>

<div class="content-page">
    <div class="content">
        <!-- Début du contenu -->
        <div class="container-fluid">
            <!-- Titre de la page -->
            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-semibold m-0">Liste des produits</h4>
                </div>
                
                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Applications</a></li>
                        <li class="breadcrumb-item active">Produits</li>
                    </ol>
                </div>
            </div>

            <!-- Liste des produits -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <!-- Bouton Ajouter Produit -->
                            <div class="mb-3">
                                @can('Products-ajoute')
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ModalAddProduct">
                                    <i class="fa-solid fa-plus"></i> Ajouter un produit
                                </button>
                                @endcan
                            </div>
                            
                            <!-- Tableau des produits -->
                            <div class="table-responsive">
                                <table class="table datatable TableProducts">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Code article</th>
                                            <th>Désignation</th>
                                            <th>Unité</th>
                                            <th>Catégorie</th>
                                            <th>Famille</th>
                                            <th>Emplacement</th>
                                            <th>Stock</th>
                                            <th>Prix d'achat</th>
                                            <th>Prix de vente</th>
                                            <th>Taux TVA</th>
                                            <th>Seuil</th>
                                            <th>Code barre</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Les données seront chargées par DataTables -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Ajouter Produit -->
            @can('Products-ajoute')
            <div class="modal fade" id="ModalAddProduct" tabindex="-1" aria-labelledby="ModalAddProductLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="ModalAddProductLabel">Ajouter un nouveau produit</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Erreurs de validation -->
                            <ul class="validationAddProduct"></ul>

                            <!-- Formulaire d'ajout de produit -->
                            <form id="FormAddProduct">
                                <!-- Informations de base du produit -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Désignation</label>
                                            <input type="text" name="name" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Unité</label>
                                            <select name="id_unite" id="id_unite" class="form-control" required>
                                                <option value="">Sélectionner une unité</option>
                                                @foreach($unites as $unite)
                                                    <option value="{{ $unite->id }}">{{ $unite->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Catégorie et Sous-catégorie -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Catégorie</label>
                                            <select name="id_categorie" id="id_categorie" class="form-control" required>
                                                <option value="">Sélectionner une catégorie</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Famille</label>
                                            <select name="id_subcategorie" id="id_subcategorie" class="form-control" required>
                                                <option value="">Sélectionner une famille</option>
                                                <!-- Sera rempli dynamiquement -->
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Emplacement -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Local</label>
                                            <select name="id_local" id="id_local" class="form-control" required>
                                                <option value="">Sélectionner un local</option>
                                                @foreach($locals as $local)
                                                    <option value="{{ $local->id }}">{{ $local->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Rayon</label>
                                            <select name="id_rayon" id="id_rayon" class="form-control" required>
                                                <option value="">Sélectionner un rayon</option>
                                                <!-- Sera rempli dynamiquement -->
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Prix -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Prix d'achat</label>
                                            <input type="number" step="0.01" name="price_achat" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Prix de vente</label>
                                            <input type="number" step="0.01" name="price_vente" class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Stock et Taxe -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Quantité</label>
                                            <input type="number" step="0.01" name="quantite" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Seuil</label>
                                            <input type="number" step="0.01" name="seuil" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>TVA</label>
                                            <select name="id_tva" class="form-control" required>
                                                <option value="">Sélectionner une TVA</option>
                                                @foreach($tvas as $tva)
                                                    <option value="{{ $tva->id }}">{{ $tva->value }}%</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informations supplémentaires -->
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Code barre</label>
                                            <input type="text" name="code_barre" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                            <button type="button" class="btn btn-primary" id="BtnAddProduct">Sauvegarder</button>
                        </div>
                    </div>
                </div>
            </div>
            @endcan

            <!-- Modal Modifier Produit -->
            @can('Products-modifier')
            <div class="modal fade" id="ModalEditProduct" tabindex="-1" aria-labelledby="ModalEditProductLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="ModalEditProductLabel">Modifier le produit</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Erreurs de validation -->
                            <ul class="validationEditProduct"></ul>

                            <!-- Formulaire de modification de produit -->
                            <form id="FormUpdateProduct">
                                <input type="hidden" id="edit_id" name="id">
                                
                                <!-- Informations de base du produit -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Désignation</label>
                                            <input type="text" id="edit_name" name="name" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Code article</label>
                                            <input type="text" id="edit_code_article" class="form-control" disabled>
                                            <small class="text-muted">Format: catégorie (3) + famille (3) + numéro séquentiel</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Unité</label>
                                            <select id="edit_id_unite" name="id_unite" class="form-control" required>
                                                <option value="">Sélectionner une unité</option>
                                                @foreach($unites as $unite)
                                                    <option value="{{ $unite->id }}">{{ $unite->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Code barre</label>
                                            <input type="text" id="edit_code_barre" name="code_barre" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <!-- Catégorie et Sous-catégorie -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Catégorie</label>
                                            <select id="edit_id_categorie" name="id_categorie" class="form-control" required>
                                                <option value="">Sélectionner une catégorie</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Famille</label>
                                            <select id="edit_id_subcategorie" name="id_subcategorie" class="form-control" required>
                                                <option value="">Sélectionner une famille</option>
                                                <!-- Sera rempli dynamiquement -->
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Emplacement -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Local</label>
                                            <select id="edit_id_local" name="id_local" class="form-control" required>
                                                <option value="">Sélectionner un local</option>
                                                @foreach($locals as $local)
                                                    <option value="{{ $local->id }}">{{ $local->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Rayon</label>
                                            <select id="edit_id_rayon" name="id_rayon" class="form-control" required>
                                                <option value="">Sélectionner un rayon</option>
                                                <!-- Sera rempli dynamiquement -->
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Prix -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Prix d'achat</label>
                                            <input type="number" step="0.01" id="edit_price_achat" name="price_achat" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Prix de vente</label>
                                            <input type="number" step="0.01" id="edit_price_vente" name="price_vente" class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Stock et Taxe -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Quantité</label>
                                            <input type="number" step="0.01" id="edit_quantite" name="quantite" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Seuil</label>
                                            <input type="number" step="0.01" id="edit_seuil" name="seuil" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>TVA</label>
                                            <select id="edit_id_tva" name="id_tva" class="form-control" required>
                                                <option value="">Sélectionner une TVA</option>
                                                @foreach($tvas as $tva)
                                                    <option value="{{ $tva->id }}">{{ $tva->value }}%</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                            <button type="button" class="btn btn-primary" id="BtnUpdateProduct">Mettre à jour</button>
                        </div>
                    </div>
                </div>
            </div>
            @endcan
        </div>
    </div>
</div>
@endsection