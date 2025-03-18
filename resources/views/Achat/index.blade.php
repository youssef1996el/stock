@extends('dashboard.index')

@section('dashboard')

<!-- Scripts personnalisés -->
<script src="{{asset('js/achat/script.js')}}"></script>
<script>

    var getSubcategories_url = "{{ url('getSubcategories') }}";
    var getRayons_url  = "{{ url('getRayons') }}";
    var csrf_token     = "{{csrf_token()}}";
    var getProduct     = "{{url('getProduct')}}";
    var PostInTmpAchat = "{{url('PostInTmpAchat')}}";
    var GetTmpAchatByFournisseur = "{{url('GetTmpAchatByFournisseur')}}";
    var GetAchatList   = "{{url('getAchatList')}}"; // URL pour le tableau principal
    var StoreAchat     = "{{url('StoreAchat')}}";
    var Achat          = "{{url('Achat')}}";

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

        <!-- Début du contenu -->
        <div class="container-fluid">

            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-semibold m-0">Liste des achats</h4>
                </div>
                
                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Applications</a></li>
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
                            
                            <!-- Liste des achats -->
                            <div class="table-responsive">
                                <table class="table datatable TableAchat">
                                    <thead class="thead-light">
                                        <tr>
                                            <th scope="col">Fournisseur</th>
                                            <th scope="col">Total</th>
                                            <th scope="col">status</th>
                                            <th scope="col">Créé par</th>
                                            <th scope="col">Créé le</th>
                                            <th scope="col">Action</th>    
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
        </div>

        <!-- Modal Ajouter un Achat -->
        <div class="modal fade" id="ModalAddAchat" tabindex="-1" aria-labelledby="ModalAddAchat" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ModalAddLocalLabel">Ajouter un nouveau local</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                           <div class="col-sm-12 col-md-12 col-xl-6">
                                <div class="form-group">
                                    <label for="" class="label-form">Fournisseur</label>
                                    <select name="fournisseur" class="form-select" id="DropDown_fournisseur">
                                        <option value="0">Veuillez sélectionner un fournisseur</option>
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
                                            <a href="#" class="text-danger linkCallModalAddProduct">Ajouter Produit</a>
                                        </div>
                                    </div>
                                    <input type="text" class="form-control input_products" placeholder="Entrez votre produit">
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
                                                        <!-- Les données seront chargées par DataTables -->
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
                                                    <!-- Les données seront chargées par DataTables -->
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
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Erreurs de Validation -->
                        <ul class="validationAddProduct"></ul>

                        <!-- Formulaire d'ajout de produit -->
                        <form id="FormAddProduct">
                            @csrf <!-- Jeton CSRF -->
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

                            <!-- Stock et TVA -->
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

        <!-- Modal Modifier le Local -->
        <div class="modal fade" id="ModalEditLocal" tabindex="-1" aria-labelledby="ModalEditLocalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ModalEditLocalLabel">Modifier le local</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <ul class="validationEditLocal"></ul>
                            <form action="{{ url('updateLocal') }}" id="FormUpdateLocal">
                                @csrf <!-- Jeton CSRF -->
                                <!-- Nom -->
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