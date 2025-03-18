@extends('dashboard.index')

@section('dashboard')
<script src="{{asset('js/Users/script.js')}}"></script>
<script>
    var csrf_token                      = "{{csrf_token()}}";
    var Adduser                       = "{{route('users.store')}}";
    var users                      = "{{route('users.index')}}";
    var UpdateUser                      = "{{url('updateUser')}}";
    var DeleteUser                      = "{{url('DeleteUser')}}";
   
</script>
<div class="content-page">
    <div class="content">

        <!-- Start Content-->
        <div class="container-fluid">

            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-semibold m-0">Liste des Utilisateurs</h4>
                </div>
                
                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Applications</a></li>
                        <li class="breadcrumb-item active">Utilisateurs</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-body">
                            <div class=" mb-3">
                                <button class="btn btn-primary" style="margin-right: 5px" data-bs-toggle="modal" data-bs-target="#ModalAddUser">Ajouter utilisateur</button>
                            </div>
                            <div class="table-responsive">
                                <div class="datatable-wrapper datatable-loading no-footer sortable fixed-height fixed-columns">
                                    
                                    <div class="datatable-container" style="height: 665.531px;">
                                        <table class="table datatable datatable-table TableUsers" >
                                            <thead>
                                                <tr>
                                                    <th data-sortable="true">Nom</th>
                                                    <th data-sortable="true">Email</th>
                                                    
                                                    <th data-sortable="true">Rôles</th>
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


       

         <!-- Modal -->
<div class="modal fade" id="ModalAddUser" tabindex="-1" aria-labelledby="ModalAddUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalAddUserLabel">Ajouter un nouvel utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <ul class="validationAddUser"></ul>
                    <form action="{{ route('users.store') }}" id="FormAddUser">
                       
                
                        <!-- Nom complet & Email -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nom complet</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                
                        <!-- Mot de passe & Confirmation du mot de passe -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Mot de passe</label>
                                    <div class="pass-group">
                                        <input type="password" name="password" class="form-control pass-input @error('password') is-invalid @enderror">
                                        <span class="fas toggle-password fa-eye-slash"></span>
                                    </div>
                                    @error('password')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Confirmer le mot de passe</label>
                                    <div class="pass-group">
                                        <input type="password" name="password_confirmation" class="form-control pass-input">
                                        <span class="fas toggle-password fa-eye-slash"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                
                        <!-- Téléphone & Rôle -->
                        <div class="row">
                            
                
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Rôle</label>
                                    <select class="select form-select @error('roles') is-invalid @enderror" name="roles">
                                        <option value="">Sélectionner</option>
                                        @forelse ($roles as $role)
                                        @if ($role != 'Super Admin')
                                            <option value="{{ $role }}" {{ (old('roles') == $role) ? 'selected' : '' }}>
                                                {{ $role }}
                                            </option>
                                        @else
                                            @if (Auth::user()->hasRole('Super Admin'))   
                                                <option value="{{ $role }}" {{ (old('roles') == $role) ? 'selected' : '' }}>
                                                    {{ $role }}
                                                </option>
                                            @endif
                                        @endif
                                    @empty
                                    @endforelse
                                    </select>
                                    @error('roles')
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
                <button type="button" class="btn btn-primary" id="BtnADDUser">Sauvegarder</button>
            </div>
        </div>
    </div>
</div>



        
<div class="modal fade" id="ModalEditUser" tabindex="-1" aria-labelledby="ModalAddUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalAddUserLabel">Modifier l'utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <ul class="validationAddUser"></ul>
                    <form action="{{ url('updateUser') }}" id="FormUpdateUser">
                       
                
                        <!-- Nom complet & Email -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nom complet</label>
                                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                
                        <!-- Mot de passe & Confirmation du mot de passe -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Mot de passe</label>
                                    <div class="pass-group">
                                        <input type="password" id="password" name="password" class="form-control pass-input @error('password') is-invalid @enderror">
                                        <span class="fas toggle-password fa-eye-slash"></span>
                                    </div>
                                    @error('password')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Confirmer le mot de passe</label>
                                    <div class="pass-group">
                                        <input type="password" name="password_confirmation" class="form-control pass-input">
                                        <span class="fas toggle-password fa-eye-slash"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                
                        <!-- Téléphone & Rôle -->
                        <div class="row">
                           
                
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Rôle</label>
                                    <select class="select form-select @error('roles') is-invalid @enderror" name="roles" id="roles">
                                        <option value="">Sélectionner</option>
                                        @forelse ($roles as $role)
                                        @if ($role != 'Super Admin')
                                            <option value="{{ $role }}" {{ (old('roles') == $role) ? 'selected' : '' }}>
                                                {{ $role }}
                                            </option>
                                        @else
                                            @if (Auth::user()->hasRole('Super Admin'))   
                                                <option value="{{ $role }}" {{ (old('roles') == $role) ? 'selected' : '' }}>
                                                    {{ $role }}
                                                </option>
                                            @endif
                                        @endif
                                    @empty
                                    @endforelse
                                    </select>
                                    @error('roles')
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
                <button type="button" class="btn btn-primary" id="BtnUpdateUser">Sauvegarder</button>
            </div>
        </div>
    </div>
</div>



        
        
        
        
</div>



@endsection