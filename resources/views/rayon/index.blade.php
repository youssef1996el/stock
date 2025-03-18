@extends('dashboard.index')

@section('dashboard')
{{-- <!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">

<!-- jQuery and DataTables JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<!-- Awesome Notifications for alerts -->
<script src="https://cdn.jsdelivr.net/npm/awesome-notifications@3.1.3/dist/index.var.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/awesome-notifications@3.1.3/dist/style.min.css"> --}}

<!-- Custom JS -->
<script src="{{asset('js/rayon/script.js')}}"></script>
<script>
    var csrf_token    = "{{csrf_token()}}";
    var AddRayon      = "{{url('addRayon')}}";
    var rayons        = "{{url('rayon')}}";
    var UpdateRayon   = "{{url('updateRayon')}}";
    var DeleteRayon   = "{{url('DeleteRayon')}}";
    var editRayon     = "{{url('editRayon')}}";
</script>
<div class="content-page">
    <div class="content">

        <!-- Start Content-->
        <div class="container-fluid">

            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-semibold m-0">Liste des rayons</h4>
                </div>
                
                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Applications</a></li>
                        <li class="breadcrumb-item active">Rayons</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-body">
                            <div class="mb-3">
                                <button class="btn btn-primary" style="margin-right: 5px" data-bs-toggle="modal" data-bs-target="#ModalAddRayon">
                                    <i class="fa-solid fa-plus"></i> Ajouter un rayon
                                </button>
                            </div>
                            
                            <!-- Rayon list -->
                            <div class="table-responsive">
                                <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer table-responsive">
                                    <table class="table datatable dataTable no-footer TableRayons" id="DataTables_Table_0" aria-describedby="DataTables_Table_0_info">
                                        <thead class="thead-light">
                                            <tr>
                                                <th scope="col">Nom</th>
                                                <th scope="col">Local</th>
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

        <!-- Add Rayon Modal -->
        <div class="modal fade" id="ModalAddRayon" tabindex="-1" aria-labelledby="ModalAddRayonLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ModalAddRayonLabel">Ajouter un nouveau rayon</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <ul class="validationAddRayon"></ul>
                            <form action="{{ url('addRayon') }}" id="FormAddRayon">
                                <!-- Name & Local -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nom du rayon</label>
                                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                                            @error('name')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Local</label>
                                            <select name="id_local" class="form-control @error('id_local') is-invalid @enderror">
                                                <option value="">Sélectionner un local</option>
                                                @foreach($locals as $local)
                                                    <option value="{{ $local->id }}">{{ $local->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('id_local')
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
                        <button type="button" class="btn btn-primary" id="BtnAddRayon">Sauvegarder</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Rayon Modal -->
        <div class="modal fade" id="ModalEditRayon" tabindex="-1" aria-labelledby="ModalEditRayonLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ModalEditRayonLabel">Modifier le rayon</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <ul class="validationEditRayon"></ul>
                            <form action="{{ url('updateRayon') }}" id="FormUpdateRayon">
                                <!-- Name & Local -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nom du rayon</label>
                                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                                            @error('name')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Local</label>
                                            <select id="id_local" name="id_local" class="form-control @error('id_local') is-invalid @enderror">
                                                <option value="">Sélectionner un local</option>
                                                @foreach($locals as $local)
                                                    <option value="{{ $local->id }}">{{ $local->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('id_local')
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
                        <button type="button" class="btn btn-primary" id="BtnUpdateRayon">Mettre à jour</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection