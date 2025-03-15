@extends('dashboard.index')

@section('dashboard')
<!-- Custom JS -->
<script src="{{asset('js/achat/script.js')}}"></script>
<script>
    var csrf_token          = "{{csrf_token()}}";
    var AddAchat            = "{{url('addAchat')}}";
    var achats              = "{{url('achat')}}";
    var UpdateAchat         = "{{url('updateAchat')}}";
    var DeleteAchat         = "{{url('DeleteAchat')}}";
    var editAchat           = "{{url('achat')}}/";
</script>
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
                        <li class="breadcrumb-item active">Achats</li>
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
                                <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer table-responsive">
                                    <table class="table datatable dataTable no-footer TableAchats" id="DataTables_Table_0" aria-describedby="DataTables_Table_0_info">
                                        <thead class="thead-light">
                                            <tr>
                                                <th scope="col">Total</th>
                                                <th scope="col">Statut</th>
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
        </div>

        <!-- Add Achat Modal -->
        <div class="modal fade" id="ModalAddAchat" tabindex="-1" aria-labelledby="ModalAddAchatLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ModalAddAchatLabel">Ajouter un nouvel achat</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <ul class="validationAddAchat"></ul>
                            <form action="{{ url('addAchat') }}" id="FormAddAchat">
                                <!-- Total & Status & Fournisseur -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Montant total</label>
                                            <input type="number" step="0.01" name="total" class="form-control @error('total') is-invalid @enderror" value="{{ old('total') }}">
                                            @error('total')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Statut</label>
                                            <select name="status" class="form-control @error('status') is-invalid @enderror">
                                                <option value="">Sélectionner un statut</option>
                                                @foreach($statusOptions as $status)
                                                    <option value="{{ $status }}">{{ $status }}</option>
                                                @endforeach
                                            </select>
                                            @error('status')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Fournisseur</label>
                                            <select name="id_Fournisseur" class="form-control @error('id_Fournisseur') is-invalid @enderror">
                                                <option value="">Sélectionner un fournisseur</option>
                                                @foreach($fournisseurs as $fournisseur)
                                                    <option value="{{ $fournisseur->id }}">{{ $fournisseur->entreprise }}</option>
                                                @endforeach
                                            </select>
                                            @error('id_Fournisseur')
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
                        <button type="button" class="btn btn-primary" id="BtnAddAchat">Sauvegarder</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Achat Modal -->
        <div class="modal fade" id="ModalEditAchat" tabindex="-1" aria-labelledby="ModalEditAchatLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ModalEditAchatLabel">Modifier l'achat</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <ul class="validationEditAchat"></ul>
                            <form action="{{ url('updateAchat') }}" id="FormUpdateAchat">
                                <!-- Total & Status & Fournisseur -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Montant total</label>
                                            <input type="number" step="0.01" id="total" name="total" class="form-control @error('total') is-invalid @enderror" value="{{ old('total') }}">
                                            @error('total')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Statut</label>
                                            <select id="status" name="status" class="form-control @error('status') is-invalid @enderror">
                                                <option value="">Sélectionner un statut</option>
                                                @foreach($statusOptions as $status)
                                                    <option value="{{ $status }}">{{ $status }}</option>
                                                @endforeach
                                            </select>
                                            @error('status')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Fournisseur</label>
                                            <select id="id_Fournisseur" name="id_Fournisseur" class="form-control @error('id_Fournisseur') is-invalid @enderror">
                                                <option value="">Sélectionner un fournisseur</option>
                                                @foreach($fournisseurs as $fournisseur)
                                                    <option value="{{ $fournisseur->id }}">{{ $fournisseur->entreprise }}</option>
                                                @endforeach
                                            </select>
                                            @error('id_Fournisseur')
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
                        <button type="button" class="btn btn-primary" id="BtnUpdateAchat">Mettre à jour</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection