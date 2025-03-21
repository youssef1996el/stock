@extends('dashboard.index')

@section('dashboard')
<div class="content-page">
    <div class="content">
        <!-- Start Content-->
        <div class="container-fluid">
            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-semibold m-0">Détails de l'audit</h4>
                </div>
                
                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0">
                        <li class="breadcrumb-item"><a href="{{ url('audit') }}">Historique</a></li>
                        <li class="breadcrumb-item active">Détails</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <!-- Info du modèle modifié -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h5 class="card-title">Informations générales</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="30%">Type</th>
                                                <td>{{ $modelType }}</td>
                                            </tr>
                                            <tr>
                                                <th>Élément</th>
                                                <td>{{ $modelName }}</td>
                                            </tr>
                                            <tr>
                                                <th>Action</th>
                                                <td>
                                                    @if($audit->event == 'created')
                                                        <span class="badge bg-success">Création</span>
                                                    @elseif($audit->event == 'updated')
                                                        <span class="badge bg-info">Modification</span>
                                                    @elseif($audit->event == 'deleted')
                                                        <span class="badge bg-danger">Suppression</span>
                                                    @elseif($audit->event == 'restored')
                                                        <span class="badge bg-warning">Restauration</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ $audit->event }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Utilisateur</th>
                                                <td>{{ $userName }}</td>
                                            </tr>
                                            <tr>
                                                <th>Date</th>
                                                <td>{{ $audit->created_at->format('d/m/Y H:i:s') }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Table des modifications -->
                            <div class="row">
                                <div class="col-12">
                                    <h5 class="card-title">Modifications</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th width="30%">Champ</th>
                                                    <th>Ancienne valeur</th>
                                                    <th>Nouvelle valeur</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if($audit->event === 'created')
                                                    @foreach($newValues as $key => $value)
                                                        <tr>
                                                            <td>{{ $fieldNames[$key] ?? $key }}</td>
                                                            <td class="text-muted">-</td>
                                                            <td class="text-success">{!! $formattedValues['new'][$key] ?? $value !!}</td>
                                                        </tr>
                                                    @endforeach
                                                @elseif($audit->event === 'updated')
                                                    @foreach($newValues as $key => $value)
                                                        @if(isset($oldValues[$key]))
                                                            <tr>
                                                                <td>{{ $fieldNames[$key] ?? $key }}</td>
                                                                <td class="text-danger">{!! $formattedValues['old'][$key] ?? $oldValues[$key] !!}</td>
                                                                <td class="text-success">{!! $formattedValues['new'][$key] ?? $value !!}</td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                @elseif($audit->event === 'deleted')
                                                    <tr>
                                                        <td colspan="3" class="text-center text-danger">Cet élément a été supprimé</td>
                                                    </tr>
                                                    @if(isset($newValues['deleted_at']))
                                                        <tr>
                                                            <td>Date de suppression</td>
                                                            <td class="text-muted">-</td>
                                                            <td class="text-danger">{!! $formattedValues['new']['deleted_at'] ?? $newValues['deleted_at'] !!}</td>
                                                        </tr>
                                                    @endif
                                                @elseif($audit->event === 'restored')
                                                    <tr>
                                                        <td colspan="3" class="text-center text-success">Cet élément a été restauré</td>
                                                    </tr>
                                                    @if(isset($oldValues['deleted_at']))
                                                        <tr>
                                                            <td>Date de suppression</td>
                                                            <td class="text-danger">{!! $formattedValues['old']['deleted_at'] ?? $oldValues['deleted_at'] !!}</td>
                                                            <td class="text-success">-</td>
                                                        </tr>
                                                    @endif
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Boutons d'action -->
                            <div class="row mt-4">
                                <div class="col-12 text-end">
                                    <a href="{{ url('audit') }}" class="btn btn-secondary">
                                        <i class="fa fa-arrow-left"></i> Retour à la liste
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection