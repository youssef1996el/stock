@extends('dashboard.index')
@section('dashboard')
<div class="content-page">
    <div class="content">

        <!-- Début du contenu -->
        <div class="container-fluid ">
            <div class="card card-body py-3 mt-3">
                <div class="row align-items-center">
                    <div class="col-12">
                        <div class="d-sm-flex align-items-center justify-space-between">
                            <h4 class="mb-4 mb-sm-0 card-title">Gestion de Production</h4>
                            <nav aria-label="breadcrumb" class="ms-auto">
                                <ol class="breadcrumb">
                                   
                                    <li class="breadcrumb-item" aria-current="page">
                                        <span class="badge fw-medium fs-6 bg-primary-subtle text-primary">
                                            Détail achat
                                        </span>
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        
            <div class="widget-content searchable-container list">
                <div class="card card-body">
                    <h5 class="card-title border p-2 bg-light rounded-2 mb-4">Information fournisseur par commande N° {{$bonReception->id}}</h5>
                    <div class="row">
                        <div class="col-md-12 col-xl-6">
                            <div class="form-group">
                                <div class="mb-4">
                                    <label for="" style="min-width: 115px">Nom fournisseur :</label>
                                    <span class="border p-2 bg-light rounded-2">{{$Fournisseur->entreprise}}</span>
                                </div>
                            </div>
                            
        
                        </div>
                        <div class="col-md-12 col-xl-6">
                            <div class="form-group">
                                <div class="mb-4">
                                    <label for="" style="min-width: 115px">Téléphone fournisseur :</label>
                                    <span class="border p-2 bg-light rounded-2">{{$Fournisseur->Telephone}}</span>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        
            <div class="card card-body">
                <h5 class="card-title border p-2 bg-light rounded-2">Fiche détail achat </h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped TableLineOrder">
                        <thead>
                            <tr>
                                <th>Produit</th>
        
                                <th>Quantite</th>
        
                                <th>Prix</th>
        
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $SumTotal = 0;
                            @endphp
                            @foreach ($Data_Achat as $value)
                                @php
                                    $SumTotal +=$value->total;
                                @endphp
                                <tr>
                                    <td>{{$value->name}}</td>
                                    <td>{{$value->qte}}</td>
                                    <td class="text-end">{{ number_format($value->price_achat, 2, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format($value->total, 2, ',', ' ') }}</td>

                                    
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex">
                        <div class="flex-fill"></div>
                        <div class="flex-fill">
                            <table class="table table-striped table-bordered">
                                <tbody><tr>
                                    <th>Total HT</th>
                                    <th class="text-end">{{$SumTotal}} DH</th>
                                </tr>
                                
                            </tbody></table>
                        </div>
                    </div>
        
        
                </div>
            </div>
        </div>
    </div>
</div>
@endsection