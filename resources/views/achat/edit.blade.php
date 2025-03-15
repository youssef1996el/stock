@extends('dashboard.index')

@section('dashboard')
<!-- Custom JS -->
<script>
    // Pass PHP variables to JavaScript
    var csrf_token = "{{ csrf_token() }}";
    var updateAchat_url = "{{ url('updateAchat') }}";
    var getLigneAchats_url = "{{ url('getLigneAchats', $achat->id) }}";
    var addLigneAchat_url = "{{ url('addLigneAchat') }}";
    var updateLigneAchat_url = "{{ url('updateLigneAchat') }}";
    var deleteLigneAchat_url = "{{ url('deleteLigneAchat') }}";
    var receiveProducts_url = "{{ url('receiveProducts') }}";
</script>
<script src="{{ asset('js/achat/edit.js') }}"></script>

<div class="content-page">
    <div class="content">
        <!-- Start Content-->
        <div class="container-fluid">
            <!-- Page Title -->
            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-semibold m-0">Modifier l'achat #{{ $achat->id }}</h4>
                </div>
                
                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0">
                        <li class="breadcrumb-item"><a href="{{ url('achats') }}">Achats</a></li>
                        <li class="breadcrumb-item active">Modifier</li>
                    </ol>
                </div>
            </div>

            <!-- Achat Info Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Informations de l'achat</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Fournisseur:</strong> {{ $achat->fournisseur->entreprise }}</p>
                                    <p><strong>Créé par:</strong> {{ $achat->user->name }}</p>
                                    <p><strong>Date de création:</strong> {{ $achat->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Total:</strong> {{ number_format($achat->total, 2) }} €</p>
                                    <p>
                                        <strong>Statut:</strong>
                                        <span class="badge 
                                            @if($achat->status == 'En cours de traitement') bg-warning
                                            @elseif($achat->status == 'Accepté') bg-info
                                            @elseif($achat->status == 'Refusé') bg-danger
                                            @elseif($achat->status == 'Reçu') bg-success
                                            @endif
                                        ">
                                            {{ $achat->status }}
                                        </span>
                                    </p>
                                    
                                    <div class="mt-3">
                                        <form id="FormUpdateStatus">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <select id="status" name="status" class="form-control">
                                                        <option value="En cours de traitement" {{ $achat->status == 'En cours de traitement' ? 'selected' : '' }}>En cours de traitement</option>
                                                        <option value="Accepté" {{ $achat->status == 'Accepté' ? 'selected' : '' }}>Accepté</option>
                                                        <option value="Refusé" {{ $achat->status == 'Refusé' ? 'selected' : '' }}>Refusé</option>
                                                        <option value="Reçu" {{ $achat->status == 'Reçu' ? 'selected' : '' }}>Reçu</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <button type="button" id="BtnUpdateStatus" class="btn btn-primary">Mettre à jour</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Product Section -->
            @if($achat->status != 'Reçu')
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Ajouter un produit</h5>
                        </div>
                        <div class="card-body">
                            <form id="FormAddLigneAchat">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Produit</label>
                                        <select name="idproduit" id="idproduit" class="form-control" required>
                                            <option value="">Sélectionner un produit</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }} - {{ number_format($product->price_achat, 2) }} €</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Quantité</label>
                                        <input type="number" name="qte" id="qte" class="form-control" value="1" min="1" required>
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <button type="button" id="BtnAddLigneAchat" class="btn btn-primary mt-4">Ajouter</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Products List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Produits de l'achat</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table datatable TableLigneAchats">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Code</th>
                                            <th>Produit</th>
                                            <th>Prix unitaire</th>
                                            <th>Quantité</th>
                                            <th>Sous-total</th>
                                            <th>Total (TVA)</th>
                                            @if($achat->status != 'Reçu')
                                            <th>Actions</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be loaded by DataTables -->
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Receive Products Button (only if status is not Reçu) -->
                            @if($achat->status != 'Reçu')
                            <div class="text-end mt-4">
                                <button id="BtnReceiveProducts" class="btn btn-success">
                                    <i class="fa-solid fa-check me-1"></i> Marquer comme reçu
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection