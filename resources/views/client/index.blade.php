@extends('dashboard.index')

@section('dashboard')
<script src="{{asset('js/client/script.js')}}"></script>
<script>
    var csrf_token                = "{{csrf_token()}}";
    var AddClient                 = "{{url('addClient')}}";
    var clients                   = "{{url('client')}}";
    var EditClient                = "{{url('editClient')}}";
    var UpdateClient              = "{{url('updateClient')}}";
    var DeleteClient              = "{{url('DeleteClient')}}";
</script> 
<div class="content-page">
    <div class="content">

        <!-- Start Content-->
        <div class="container-fluid">

            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-semibold m-0">Liste des clients</h4>
                </div>
                
                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Applications</a></li>
                        <li class="breadcrumb-item active">Clients</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-body">
                            <div class=" mb-3">
                                <button class="btn btn-primary" style="margin-right: 5px" data-bs-toggle="modal" data-bs-target="#ModalAddClient">Ajouter client</button>
                            </div>
                            <div class="table-responsive">
                                <div class="datatable-wrapper datatable-loading no-footer sortable fixed-height fixed-columns">
                                    
                                    <div class="datatable-container" style="height: 665.531px;">
                                        <table class="table datatable datatable-table TableClients" >
                                            <thead>
                                                <tr>
                                                    <th data-sortable="true">Prénom</th>
                                                    <th data-sortable="true">Nom</th>
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

        <!-- Add Client Modal -->
        <div class="modal fade" id="ModalAddClient" tabindex="-1" aria-labelledby="ModalAddClientLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ModalAddClientLabel">Ajouter un nouveau client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <ul class="validationAddClient"></ul>
                        <form action="{{ url('addClient') }}" id="FormAddClient">
                            
                            <!-- First Name, Last Name -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Prénom</label>
                                        <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}">
                                        @error('first_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Nom</label>
                                        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}">
                                        @error('last_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Téléphone</label>
                                        <input type="text" name="Telephone" id="phone_client" class="form-control @error('Telephone') is-invalid @enderror" value="{{ old('Telephone') }}">
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
                    <button type="button" class="btn btn-primary" id="BtnAddClient">Sauvegarder</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Client Modal -->
    <div class="modal fade" id="ModalEditClient" tabindex="-1" aria-labelledby="ModalEditClientLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ModalEditClientLabel">Modifier client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <ul class="validationEditClient"></ul>
                        <form action="{{ url('updateClient') }}" id="FormUpdateClient">
                            
                            <!-- First Name, Last Name -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Prénom</label>
                                        <input type="text" id="first_name" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}">
                                        @error('first_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Nom</label>
                                        <input type="text" id="last_name" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}">
                                        @error('last_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Téléphone</label>
                                        <input type="text" id="Telephone" name="Telephone" class="form-control phone_client_edit @error('Telephone') is-invalid @enderror" value="{{ old('Telephone') }}">
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
                    <button type="button" class="btn btn-primary" id="BtnUpdateClient">Mettre à jour</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection