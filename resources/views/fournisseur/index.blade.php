@extends('dashboard.index')

@section('dashboard')
<script src="{{asset('js/fournisseur/script.js')}}"></script>
<script>
    var csrf_token                = "{{csrf_token()}}";
    var AddFournisseur            = "{{url('addFournisseur')}}";
    var fournisseurs              = "{{url('fournisseur')}}";
    var EditFournisseur           = "{{url('editFournisseur')}}";
    var UpdateFournisseur         = "{{url('updateFournisseur')}}";
    var DeleteFournisseur         = "{{url('DeleteFournisseur')}}";
</script> 
<div class="content-page">
    <div class="content">

        <!-- Start Content-->
        <div class="container-fluid">

            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-semibold m-0">Liste des fournisseurs</h4>
                </div>
                
                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Apps</a></li>
                        <li class="breadcrumb-item active">Fournisseurs</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-body">
                            <div class=" mb-3">
                                <button class="btn btn-primary" style="margin-right: 5px" data-bs-toggle="modal" data-bs-target="#ModalAddFournisseur">Ajouter fournisseur</button>
                            </div>
                            <div class="table-responsive">
                                <div class="datatable-wrapper datatable-loading no-footer sortable fixed-height fixed-columns">
                                    
                                    <div class="datatable-container" style="height: 665.531px;">
                                        <table class="table datatable datatable-table TableFournisseurs" >
                                            <thead>
                                                <tr>
                                                    <th data-sortable="true">Entreprise</th>
                                                    <th data-sortable="true">Téléphone</th>
                                                    <th data-sortable="true">Email</th>
                                                    <th data-sortable="true">Créé par</th>
                                                    <th data-sortable="true">Créé le</th>
                                                    <th data-sortable="true">Action</th>  
                                                </tr>
                                            </thead>
                                                <tbody>
                                                   
                                                </tbody>
                                            </table>
                                        </div>
                                                    
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Add Fournisseur Modal -->
        <div class="modal fade" id="ModalAddFournisseur" tabindex="-1" aria-labelledby="ModalAddFournisseurLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ModalAddFournisseurLabel">Ajouter un nouveau fournisseur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <ul class="validationAddFournisseur"></ul>
                        <form action="{{ url('addFournisseur') }}" id="FormAddFournisseur">
                            
                            <!-- Entreprise, Telephone, Email -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label>Entreprise</label>
                                        <input type="text" name="entreprise" class="form-control @error('entreprise') is-invalid @enderror" value="{{ old('entreprise') }}">
                                        @error('entreprise')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Téléphone</label>
                                        <input type="text" name="Telephone" id="phone_fournisseur" class="form-control @error('Telephone') is-invalid @enderror" value="{{ old('Telephone') }}">
                                        @error('Telephone')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                    
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Email</label>
                                        <input type="email" name="Email" class="form-control @error('Email') is-invalid @enderror" value="{{ old('Email') }}">
                                        @error('Email')
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
                    <button type="button" class="btn btn-primary" id="BtnAddFournisseur">Sauvegarder</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Fournisseur Modal -->
    <div class="modal fade" id="ModalEditFournisseur" tabindex="-1" aria-labelledby="ModalEditFournisseurLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ModalEditFournisseurLabel">Modifier fournisseur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <ul class="validationEditFournisseur"></ul>
                        <form action="{{ url('updateFournisseur') }}" id="FormUpdateFournisseur">
                            
                            <!-- Entreprise, Telephone, Email -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label>Entreprise</label>
                                        <input type="text" id="entreprise" name="entreprise" class="form-control @error('entreprise') is-invalid @enderror" value="{{ old('entreprise') }}">
                                        @error('entreprise')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Téléphone</label>
                                        <input type="text" id="Telephone" name="Telephone" class="form-control phone_fournisseur_edit @error('Telephone') is-invalid @enderror" value="{{ old('Telephone') }}">
                                        @error('Telephone')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                    
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Email</label>
                                        <input type="email" id="Email" name="Email" class="form-control @error('Email') is-invalid @enderror" value="{{ old('Email') }}">
                                        @error('Email')
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
                    <button type="button" class="btn btn-primary" id="BtnUpdateFournisseur">Mettre à jour</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection