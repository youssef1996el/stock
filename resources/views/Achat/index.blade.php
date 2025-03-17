@extends('dashboard.index')

@section('dashboard')

<!-- Custom JS -->
<script src="{{asset('js/achat/script.js')}}"></script>
<script>
<<<<<<< HEAD
    var getSubcategories_url = "{{ url('getSubcategories') }}";
    var getRayons_url  = "{{ url('getRayons') }}";
    var csrf_token     = "{{csrf_token()}}";
    var getProduct     = "{{url('getProduct')}}";
    var PostInTmpAchat = "{{url('PostInTmpAchat')}}";
    var GetTmpAchatByFournisseur = "{{url('GetTmpAchatByFournisseur')}}";
    var GetAchatList   = "{{url('getAchatList')}}"; // Add this URL for the main table
    var StoreAchat     = "{{url('StoreAchat')}}";
=======
     var getSubcategories_url = "{{ url('getSubcategories') }}";
     var getRayons_url  = "{{ url('getRayons') }}";
     var csrf_token     = "{{csrf_token()}}";
     var getProduct     = "{{url('getProduct')}}";
     var PostInTmpAchat = "{{url('PostInTmpAchat')}}";
     var GetTmpAchatByFournisseur = "{{url('GetTmpAchatByFournisseur')}}";
     var GetAchatList   = "{{url('getAchatList')}}";
>>>>>>> 95264044d5449bde5e9966f8df21fe498ac80c51
</script>
<style>
    .table-responsive {
        overflow-x: hidden;
    }
    .TableProductAchat tbody tr:hover {
        cursor: pointer; 
    }
    .dataTables-custom-controls {
        margin-bottom: 15px;
    }
    .dataTables-custom-controls label {
        margin-bottom: 0;
    }
    .dataTables-custom-controls .form-control {
        display: inline-block;
        width: auto;
        vertical-align: middle;
    }
    .dataTables-custom-controls .form-select {
        display: inline-block;
        width: auto;
        vertical-align: middle;
    }
</style>

<div class="content-page">
    <div class="content">

        <!-- Start Content-->
        <div class="container-fluid">

            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-semibold m-0">Liste des achats</h4>
                </div>
                
                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Apps</a></li>
                        <li class="breadcrumb-item active">Achat</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-body">
                            <div class="mb-3">
                                <button class="btn btn-primary" style="margin-right: 5px" data-bs-toggle="modal" data-bs-target="#ModalAddAchat">
                                    <i class="fa-solid fa-plus"></i> Ajouter un achat
                                </button>
                            </div>
                            
                            <!-- Achat list -->
                            <div class="table-responsive">
                                <table class="table datatable TableAchat">
                                    <thead class="thead-light">
                                        <tr>
                                            <th scope="col">Fournisseur</th>
                                            <th scope="col">Créé par</th>
                                            <th scope="col">Créé le</th>
                                            <th scope="col">Action</th>    
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be loaded by DataTables -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Local Modal -->
        <div class="modal fade" id="ModalAddAchat" tabindex="-1" aria-labelledby="ModalAddAchat" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ModalAddLocalLabel">Ajouter un nouveau local</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                           <div class="col-sm-12 col-md-12 col-xl-6">
                                <div class="form-group">
                                    <label for="" class="label-form">Fournisseur</label>
                                    <select name="fournisseur" class="form-select" id="DropDown_fournisseur">
                                        <option value="0">Please selected fournisseur</option>
                                        @foreach ($Fournisseur as $item)
                                            <option value="{{$item->id}}">{{$item->entreprise}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mt-2">
                                    <div class="row">
                                        <div class="col-6">
                                            <label for="" class="form-label">Produit</label>
                                        </div>
                                        <div class="col-6 text-end">
                                            <a href="#" class="text-danger linkCallModalAddProduct">Add Produit</a>
                                        </div>
                                    </div>
                                    <input type="text" class="form-control input_products" placeholder="entre your porudct">
                                </div>
                                <div class="form-group mt-2">
                                    <div class="card text-start">
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped datatable TableProductAchat">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th scope="col">Produit</th>
                                                            <th scope="col">Quantité</th>
                                                            <th scope="col">Seuil</th>
                                                            <th scope="col">Prix Achat</th> 
                                                            <th scope="col">Local</th> 
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- Data will be loaded by DataTables -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                           </div>
                           <div class="col-sm-12 col-md-12 col-xl-6">
                                <div class="form-group mt-3" style="min-height: 123px;">
                                    <div class="card text-start">
                                        <div class="card-body">
                                            <p class="card-text">Total : <span>0.00</span> </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mt-3">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped datatable TableAmpAchat">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th scope="col">Produit</th>
                                                        <th scope="col">Prix Achat</th>
                                                        <th scope="col">Quantité</th>
                                                        <th scope="col">Fournisseur</th>
                                                        <th scope="col">Action</th>    
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Data will be loaded by DataTables -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                           </div>
                        </div>
                    </div>
                    <div class="modal-footer text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="button" class="btn btn-primary" id="BtnSaveAchat">Sauvegarder</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="ModalAddProduct" tabindex="-1" aria-labelledby="ModalAddProductLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ModalAddProductLabel">Ajouter un nouveau produit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Validation Errors -->
                        <ul class="validationAddProduct"></ul>

                        <!-- Add Product Form -->
                        <form id="FormAddProduct">
                            @csrf <!-- Add CSRF token -->
                            <!-- Basic Product Information -->
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

                            <!-- Category and Subcategory -->
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
                                            <!-- Will be populated dynamically -->
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Location -->
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
                                            <!-- Will be populated dynamically -->
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing -->
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

                            <!-- Stock and Tax -->
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

                            <!-- Additional Information -->
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

        <!-- Edit Local Modal -->
        <div class="modal fade" id="ModalEditLocal" tabindex="-1" aria-labelledby="ModalEditLocalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ModalEditLocalLabel">Modifier le local</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <ul class="validationEditLocal"></ul>
                            <form action="{{ url('updateLocal') }}" id="FormUpdateLocal">
                                @csrf <!-- Add CSRF token -->
                                <!-- Name -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Nom du local</label>
                                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                                            @error('name')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="modal-footer text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="button" class="btn btn-primary" id="BtnUpdateLocal">Mettre à jour</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection