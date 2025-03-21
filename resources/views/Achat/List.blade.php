@extends('dashboard.index')

@section('dashboard')
<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-semibold m-0">Détails de la modification</h4>
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
                            <!-- Info section -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="fw-semibold">Type:</label>
                                        <span>{{ $modelType }}</span>
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-semibold">Élément:</label>
                                        <span>{{ $modelName }}</span>
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-semibold">Action:</label>
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
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="fw-semibold">Utilisateur:</label>
                                        <span>{{ $userName }}</span>
                                    </div>
                                    <div class="mb-3">
                                        <label class="fw-semibold">Date:</label>
                                        <span>{{ $audit->created_at->format('d/m/Y H:i:s') }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Changes table -->
                            <div class="table-responsive">
                                <h5>Modifications</h5>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Champ</th>
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
                                                    <td class="text-success">{{ is_array($value) ? json_encode($value) : $value }}</td>
                                                </tr>
                                            @endforeach
                                        @elseif($audit->event === 'updated')
                                            @foreach($newValues as $key => $value)
                                                @if(isset($oldValues[$key]))
                                                    <tr>
                                                        <td>{{ $fieldNames[$key] ?? $key }}</td>
                                                        <td class="text-danger">{{ is_array($oldValues[$key]) ? json_encode($oldValues[$key]) : $oldValues[$key] }}</td>
                                                        <td class="text-success">{{ is_array($value) ? json_encode($value) : $value }}</td>
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
                                                    <td class="text-danger">{{ $newValues['deleted_at'] }}</td>
                                                </tr>
                                            @endif
                                        @elseif($audit->event === 'restored')
                                            <tr>
                                                <td colspan="3" class="text-center text-success">Cet élément a été restauré</td>
                                            </tr>
                                            @if(isset($oldValues['deleted_at']))
                                                <tr>
                                                    <td>Date de suppression</td>
                                                    <td class="text-danger">{{ $oldValues['deleted_at'] }}</td>
                                                    <td class="text-success">-</td>
                                                </tr>
                                            @endif
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Back button -->
                            <div class="mt-4">
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
@endsection