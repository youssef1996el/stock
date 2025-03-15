@extends('dashboard.index')

@section('dashboard')
<!-- Custom JS -->
<script>
  var csrf_token = "{{ csrf_token() }}";
var addTempAchat_url = "{{ url('addTempAchat') }}";
var getTempAchats_url = "{{ url('getTempAchats') }}";
var increaseTempAchat_url = "{{ url('increaseTempAchat') }}";
var decreaseTempAchat_url = "{{ url('decreaseTempAchat') }}";
var deleteTempAchat_url = "{{ url('deleteTempAchat') }}";
var getProductsByCategory_url = "{{ url('getProductsByCategory') }}";
var getProductId_url = "{{ url('getProductId') }}";
var addAchat_url = "{{ url('addAchat') }}";
var editTempAchat_url = "{{ url('editTempAchat') }}";  
var updateTempAchat_url = "{{ url('updateTempAchat') }}";
</script>
<script src="{{ asset('js/tempachat/script.js') }}"></script>

<div class="content-page">
    <div class="content">
        <!-- Start Content-->
        <div class="container-fluid">
            <!-- Page Title -->
            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-semibold m-0">Nouveau bon de commande</h4>
                </div>
                
                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0">
                        <li class="breadcrumb-item"><a href="{{ url('achats') }}">Achats</a></li>
                        <li class="breadcrumb-item active">Nouveau</li>
                    </ol>
                </div>
            </div>

            <!-- Main Content -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Ajouter des produits</h5>
                        </div>
                        <div class="card-body">
                            <!-- Validation Errors -->
                            <ul class="validationAddTempAchat"></ul>
                            
                            <!-- Product Selection Form -->
                            <form id="FormAddTempAchat">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Fournisseur</label>
                                            <select id="id_fournisseur" class="form-select">
                                                <option value="">Sélectionner un fournisseur</option>
                                                @foreach($fournisseurs as $fournisseur)
                                                    <option value="{{ $fournisseur->id }}">{{ $fournisseur->entreprise }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Catégorie</label>
                                            <select id="id_categorie" class="form-select">
                                                <option value="">Toutes les catégories</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Produit</label>
                                            <select id="id_produit" class="form-select">
                                                <option value="">Sélectionner un produit</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->name }} - {{ number_format($product->price_achat, 2) }}€</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Quantité</label>
                                            <input type="number" id="temp_qte" class="form-control" value="1" min="1">
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button type="button" id="btn_add_temp_achat" class="btn btn-primary">
                                            <i class="fa-solid fa-plus me-1"></i> Ajouter au panier
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- Temp Achats Table -->
                            <div class="mt-4">
                                <h5 class="mb-3">Produits à commander</h5>
                                <div class="table-responsive">
                                    <table class="table" id="temp_achats_table">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Utilisateur</th>
                                                <th>Référence</th>
                                                <th>Produit</th>
                                                <th>Quantité</th>
                                                <th>Prix unitaire</th>
                                                <th>Sous-total</th>
                                                <th>Total (TVA)</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Data will be loaded by DataTables -->
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Save Button -->
                                <div class="text-end mt-4">
                                    <button id="btn_save_achat" class="btn btn-success">
                                        <i class="fa-solid fa-save me-1"></i> Confirmer la commande
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit TempAchat Modal -->
<div class="modal fade" id="ModalEditTempAchat" tabindex="-1" aria-labelledby="ModalEditTempAchatLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalEditTempAchatLabel">Modifier la quantité</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Validation Errors -->
                <ul class="validationEditTempAchat"></ul>

                <!-- Edit TempAchat Form -->
                <form id="FormUpdateTempAchat">
                    <input type="hidden" id="edit_id" name="id">
                    
                    <!-- Product Information (Read-only) -->
                    <div class="mb-3">
                        <label>Produit</label>
                        <input type="text" id="edit_product_name" class="form-control" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label>Fournisseur</label>
                        <input type="text" id="edit_fournisseur_name" class="form-control" readonly>
                    </div>
                    
                    <!-- Quantity -->
                    <div class="mb-3">
                        <label>Quantité</label>
                        <input type="number" id="edit_qte" name="qte" class="form-control" min="1" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" id="BtnUpdateTempAchat">Mettre à jour</button>
            </div>
        </div>
    </div>
</div>
@endsection