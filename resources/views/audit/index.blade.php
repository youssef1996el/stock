@extends('dashboard.index')

@section('dashboard')
<!-- Required CSS for DateRangePicker -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<script src="{{asset('js/audit/script.js')}}"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    var csrf_token = "{{csrf_token()}}";
    var auditUrl = "{{url('audit')}}";
</script> 
<div class="content-page">
    <div class="content">

        <!-- Start Content-->
        <div class="container-fluid">

            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-semibold m-0">Historique des modifications</h4>
                </div>
                
                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Applications</a></li>
                        <li class="breadcrumb-item active">Historique</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-body">
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label>Type</label>
                                            <select id="modelFilter" class="form-control">
                                                <option value="">Tous les types</option>
                                                <option value="client">Formateur</option>
                                                <option value="fournisseur">Fournisseurs</option>
                                                <option value="local">Locaux</option>
                                                <option value="tva">TVA</option>
                                                <option value="rayon">Rayons</option>
                                                <option value="unite">Unités</option>
                                                <option value="category">Catégories</option>
                                                <option value="subcategory">Famille</option>
                                                <option value="product">Produits</option>
                                                <option value="user">Utilisateurs</option>
                                                <!-- Add other model types here -->
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label>Utilisateur</label>
                                            <select id="userFilter" class="form-control">
                                                <option value="">Tous les utilisateurs</option>
                                                @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label>Action</label>
                                            <select id="eventFilter" class="form-control">
                                                <option value="">Toutes les actions</option>
                                                <option value="created">Création</option>
                                                <option value="updated">Modification</option>
                                                <option value="deleted">Suppression</option>
                                                <option value="restored">Restauration</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label>Période</label>
                                            <input type="text" id="dateRangeFilter" class="form-control" placeholder="Sélectionner une période" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-12 text-end">
                                        <button id="clearFilters" class="btn btn-secondary btn-sm">
                                            <i class="fa fa-filter-circle-xmark"></i> Réinitialiser les filtres
                                        </button>
                                        <button id="exportAuditCSV" class="btn btn-success btn-sm" data-url="{{ url('audit/export') }}">
                                            <i class="fa fa-file-csv"></i> Exporter CSV
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <div class="datatable-wrapper datatable-loading no-footer sortable fixed-height fixed-columns">
                                    
                                    <div class="datatable-container" style="height: 665.531px;">
                                        <table class="table datatable datatable-table TableAudits" >
                                            <thead>
                                                <tr>
                                                    <th data-sortable="true">Type</th>
                                                    <th data-sortable="true">Élément</th>
                                                    <th data-sortable="true">Action</th>
                                                    <th data-sortable="true">Utilisateur</th>
                                                    <th data-sortable="true">Détails</th>
                                                    <th data-sortable="true">Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Data will be loaded here -->
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
</div>

<script>
// Ajout du script pour le bouton "Réinitialiser les filtres"
$(document).ready(function() {
    // Initialisation du bouton de réinitialisation des filtres
    $('#clearFilters').on('click', function() {
        $('#modelFilter').val('');
        $('#userFilter').val('');
        $('#eventFilter').val('');
        $('#dateRangeFilter').val('');
        
        // Recharger le tableau
        $('.TableAudits').DataTable().ajax.reload();
    });
    
    // Initialisation du bouton d'exportation CSV
    $('#exportAuditCSV').on('click', function() {
        // Préparation des filtres pour l'URL d'exportation
        let queryParams = {};
        
        if ($('#modelFilter').val()) {
            queryParams.model_type = $('#modelFilter').val();
        }
        
        if ($('#userFilter').val()) {
            queryParams.user_id = $('#userFilter').val();
        }
        
        if ($('#eventFilter').val()) {
            queryParams.event = $('#eventFilter').val();
        }
        
        if ($('#dateRangeFilter').val()) {
            const dates = $('#dateRangeFilter').data('daterangepicker');
            queryParams.start_date = dates.startDate.format('YYYY-MM-DD');
            queryParams.end_date = dates.endDate.format('YYYY-MM-DD');
        }
        
        // Construction de la chaîne de requête
        const queryString = Object.keys(queryParams)
            .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(queryParams[key]))
            .join('&');
        
        // Redirection vers l'URL d'exportation
        window.location.href = $(this).data('url') + '?' + queryString;
    });
});
</script>
@endsection