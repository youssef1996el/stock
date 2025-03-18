<!DOCTYPE html>
<html lang="fr">
    <head>

        <meta charset="utf-8" />
        <title>Inscription | Hando - Modèle de Tableau de Bord Administratif Responsive</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Un thème d'administration complet qui peut être utilisé pour créer des CRM, CMS, etc."/>
        <meta name="author" content="Zoyothemes"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <!-- App favicon -->


        <!-- App css -->
        <link href="{{asset('css/custom/app.min.css')}}" rel="stylesheet" type="text/css" id="app-style" />
        <!-- Icons -->
        <link href="{{asset('css/custom/icons.min.css')}}" rel="stylesheet" type="text/css" />


        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/7.4.47/css/materialdesignicons.min.css" integrity="sha512-/k658G6UsCvbkGRB3vPXpsPHgWeduJwiWGPCGS14IQw3xpr63AEMdA8nMYG2gmYkXitQxDTn6iiK/2fD4T87qA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        
        <script src="{{asset('js/head.js')}}"></script>
        <style>
            .account-page-bg
            {
                background-image : url('images/image_login.jpg')
            }
        </style>


    </head>

    <body>
        <!-- Begin page -->
        <div class="account-page">
            <div class="container-fluid p-0">
                <div class="row align-items-center g-0 px-3 py-3 vh-100">

                    <div class="col-xl-5">
                        <div class="row">
                            <div class="col-md-8 mx-auto">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="mb-0 p-0 p-lg-3">
                                            <div class="mb-0 border-0 p-md-4 p-lg-0">
                                                <div class="mb-4 p-0 text-lg-start text-center">
                                                    <div class="auth-brand">
                                                        <a class='logo logo-light' href='/hando/html/'>
                                                            <span class="logo-lg">
                                                                <img src="{{asset('images/person-2.png')}}" alt="" height="24">
                                                            </span>
                                                        </a>
                                                        <a class='logo logo-dark' href='/hando/html/'>
                                                            <span class="logo-lg">
                                                                <img src="assets/images/logo-dark-3.png" alt="" height="24">
                                                            </span>
                                                        </a>
                                                    </div>
                                                </div>
        
                                                <div class="auth-title-section mb-4 text-lg-start text-center"> 
                                                    <h3 class="text-dark fw-semibold mb-3">Bienvenue ! Veuillez vous inscrire pour continuer.</h3>
                                                    
                                                </div>
        
                                                
                                                
                                                
        
                                                <div class="pt-0">
                                                    <form method="POST" action="{{ route('users.store') }}" class="my-4">
                                                        @csrf
                                                        <div class="form-group mb-3">
                                                            <label for="emailaddress" class="form-label">{{ __('Nom') }}</label>
                                                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}">
                                                            @error('name')
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $message }}</strong>
                                                                </span>
                                                            @enderror
                                                        </div>
                                                        <div class="form-group mb-3">
                                                            <label for="emailaddress" class="form-label">Adresse e-mail</label>
                                                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                                                            @error('email')
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $message }}</strong>
                                                                </span>
                                                            @enderror
                                                        </div>
                            
                                                        <div class="form-group mb-3">
                                                            <label for="emailaddress" class="form-label">Mot de passe</label>
                                                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                                                            @error('password')
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $message }}</strong>
                                                                </span>
                                                            @enderror
                                                        </div>


                                                        <div class="form-group mb-3">
                                                            <label for="emailaddress" class="form-label">Confirme mot de passe</label>
                                                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                                                            @error('password')
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $message }}</strong>
                                                                </span>
                                                            @enderror
                                                        </div>

                                                        @php
                                                            use Spatie\Permission\Models\Role;
                                                            $roles = Role::pluck('name')->all();
                                                        @endphp

                                                        <div class="form-group mb-3">
                                                            <label for="roles" class="form-label">Rôles</label>
                                                            <select class="form-select @error('roles') is-invalid @enderror" multiple aria-label="Roles" id="roles" name="roles[]">
                                                                @foreach ($roles as $role)
                                                                    @if ($role != 'Super Admin')
                                                                        <option value="{{ $role }}" {{ in_array($role, old('roles') ?? []) ? 'selected' : '' }}>
                                                                            {{ $role }}
                                                                        </option>
                                                                    @else
                                                                        @if (auth()->check() && auth()->user()->hasRole('Super Admin'))   
                                                                            <option value="{{ $role }}" {{ in_array($role, old('roles') ?? []) ? 'selected' : '' }}>
                                                                                {{ $role }}
                                                                            </option>
                                                                        @endif
                                                                    @endif
                                                                @endforeach
                                                            </select>
                                                            @error('roles')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        </div>

                            
                                                        
                                                        
                                                        <div class="form-group mb-0 row">
                                                            <div class="col-12">
                                                                <div class="d-grid">
                                                                    

                                                                    <button type="submit" class="btn btn-primary fw-semibold">
                                                                        {{ __('S\'inscrire') }}
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                
                                                    
                                                    
                                                </div>
                                            </div>
                                        </div> 
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-7 d-none d-xl-inline-block">
                        <div class="account-page-bg rounded-4">
                            
                        </div>
                    </div>

                </div>
            </div>
        </div>
        
        <!-- END wrapper -->

        <!-- Vendor -->
        <script src="{{asset('js/jquery/jquery.min.js')}}"></script>
        <script src="{{asset("js/bootstrap/js/bootstrap.bundle.min.js")}}"></script>
        <script src="{{asset("js/simplebar/simplebar.min.js")}}"></script>
        <script src="{{asset("js/node-waves/waves.min.js")}}"></script>
        <script src="{{asset("js/waypoint/lib/jquery.waypoints.min.js")}}"></script>
        <script src="{{asset("js/jquery-counterup/jquery.counterup.min.js")}}"></script>
        <script src="{{asset("js/feather-icons/feather.min.js")}}"></script>

        <!-- App js-->
        <script src="{{asset("js/app.js")}}"></script>
        
    </body>
</html>