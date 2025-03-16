@extends('dashboard.index')

@section('dashboard')


<!-- Custom JS -->
<script src="{{asset('js/achat/script.js')}}"></script>
<script>
    var csrf_token    = "{{csrf_token()}}";
   
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
                        <li class="breadcrumb-item active">Achat</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-body">
                            <div class="mb-3">
                                <button class="btn btn-primary" style="margin-right: 5px" data-bs-toggle="modal" data-bs-target="#ModalAddLocal">
                                    <i class="fa-solid fa-plus"></i> Ajouter un achat
                                </button>
                            </div>
                            
                            <!-- Local list -->
                            <div class="table-responsive">
                                <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer table-responsive">
                                    <table class="table datatable dataTable no-footer TableAchat" id="DataTables_Table_0" aria-describedby="DataTables_Table_0_info">
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
        </div>

        <!-- Add Local Modal -->
        <div class="modal fade" id="ModalAddLocal" tabindex="-1" aria-labelledby="ModalAddLocalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ModalAddLocalLabel">Ajouter un nouveau local</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <ul class="validationAddLocal"></ul>
                            <form action="{{ url('addLocal') }}" id="FormAddLocal">
                                <!-- Name -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Nom du local</label>
                                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
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
                        <button type="button" class="btn btn-primary" id="BtnAddLocal">Sauvegarder</button>
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