


<!DOCTYPE html>
<html lang="en">
    <head>

        <meta charset="utf-8" />
        <title>Log In | Hando - Responsive Admin Dashboard Template</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="A fully featured admin theme which can be used to build CRM, CMS, etc."/>
        <meta name="author" content="Zoyothemes"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <!-- App favicon -->


        <!-- App css -->
        <link href="{{asset('css/custom/app.min.css')}}" rel="stylesheet" type="text/css" id="app-style" />
        <!-- Icons -->
        <link href="{{asset('css/custom/icons.min.css')}}" rel="stylesheet" type="text/css" />


        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/7.4.47/css/materialdesignicons.min.css" integrity="sha512-/k658G6UsCvbkGRB3vPXpsPHgWeduJwiWGPCGS14IQw3xpr63AEMdA8nMYG2gmYkXitQxDTn6iiK/2fD4T87qA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <script src="{{asset('js/head.js')}}"></script>
        <style>
            .account-page-bg
            {
                background-image : url('images/image_login.jpg')
            }

            .password-container {
                position: relative;
                width: 100%;
            }
    
            .password-container input {
                width: 100%;
                padding-right: 40px; /* لإتاحة مساحة للأيقونة داخل الحقل */
            }
    
            .password-container i {
                position: absolute;
                top: 72%;
                right: 10px;
                transform: translateY(-50%);
                cursor: pointer;
                color: #888;
            }
    
            .password-container i:hover {
                color: #333;
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
                                                                {{-- <img src="{{asset('images/person-2.png')}}" alt="" height="24"> --}}
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
                                                    <h3 class="text-dark fw-semibold mb-3">Bienvenue ! Veuillez vous connecter pour continuer.</h3>
                                                    
                                                </div>
        
                                                
                                                
                                                
        
                                                <div class="pt-0">
                                                    <form method="POST" action="{{ route('login') }}" class="my-4">
                                                        @csrf
                                                        <div class="form-group mb-3">
                                                            <label for="emailaddress" class="form-label">Adresse email</label>
                                                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                                            @error('email')
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $message }}</strong>
                                                                </span>
                                                            @enderror
                                                        </div>
                                                        <div class="form-group mb-3 password-container">
                                                            <label for="password" class="form-label">Mot de passe</label>
                                                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                                                            <i class="fa-solid fa-eye" id="togglePassword"></i>
                                                            @error('password')
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $message }}</strong>
                                                                </span>
                                                            @enderror
                                                        </div>
                                                        
                                                        <div class="form-group d-flex mb-3">
                                                            <div class="col-sm-6">
                                                                <div class="form-check">
                                                                    <input type="checkbox" class="form-check-input" id="checkbox-signin" {{ old('remember') ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="checkbox-signin">Souviens-toi de moi</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-6 text-end">   
                                                                @if (Route::has('password.request'))
                                                                    <a class="text-muted fs-14" href="{{ route('password.request') }}">
                                                                        {{ __('Mot de passe oublié ?') }}
                                                                    </a>
                                                                @endif                         
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="form-group mb-0 row">
                                                            <div class="col-12">
                                                                <div class="d-grid">
                                                                    

                                                                    <button type="submit" class="btn btn-primary fw-semibold">
                                                                        {{ __('Se connecter') }}
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                
                                                    <div class="text-center text-muted">
                                                        <p class="mb-0">Vous n'avez pas de compte ?<a class='text-primary ms-2 fw-medium' href='{{ route('register') }}'>Inscrivez-vous</a></p>
                                                    </div>
                                                    
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
        <script>
            document.getElementById("togglePassword").addEventListener("click", function () {
                let passwordField = document.getElementById("password");
                let icon = this;
        
                if (passwordField.type === "password") {
                    passwordField.type = "text";
                    icon.classList.remove("fa-eye");
                    icon.classList.add("fa-eye-slash");
                } else {
                    passwordField.type = "password";
                    icon.classList.remove("fa-eye-slash");
                    icon.classList.add("fa-eye");
                }
            });
        </script>
    </body>
</html>