<!DOCTYPE html>
<html lang="fr">

    <head>

        <meta charset="utf-8" />
        <title>Tableau de bord | Hando - Modèle de tableau de bord d'administration réactif</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Un thème d'administration entièrement fonctionnel qui peut être utilisé pour créer des CRM, CMS, etc."/>
        <meta name="author" content="Zoyothemes"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">

        <!-- App css -->
        
        <link href="{{asset('css/custom/app.min.css')}}" rel="stylesheet" type="text/css" id="app-style" />

        <link rel="stylesheet" href="{{asset('css/styleNotification.css')}}">
        <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
        <!-- Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        
        <link href="{{asset('css/custom/icons.min.css')}}" rel="stylesheet" type="text/css" />

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/7.4.47/css/materialdesignicons.min.css" integrity="sha512-/k658G6UsCvbkGRB3vPXpsPHgWeduJwiWGPCGS14IQw3xpr63AEMdA8nMYG2gmYkXitQxDTn6iiK/2fD4T87qA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        
        <script src="{{asset('js/head.js')}}"></script>

        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <!-- DataTables -->
        <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">


        <script src="{{asset('js/notification/index.js')}}"></script>

        <style>
             .dataTables_wrapper .dataTables_paginate .paginate_button {
                border-radius: 50% !important;
                padding: 0.5em 0.9em !important;
                background: rgb(202, 91, 176);
                background: linear-gradient(to bottom, #f9f9f9, #cfe8ff) !important;
                
                }
                /* Scrollbar track */
                ::-webkit-scrollbar {
                width: 12px;
                }

                ::-webkit-scrollbar-track {
                background: #f1f1f1;
                
                }

                /* Scrollbar thumb */
                ::-webkit-scrollbar-thumb {
                background: linear-gradient(to bottom, #f9f9f9, #cfe8ff);
                border-radius: 10px;

                }
               /*  body
                {
                    height: 100vh;
                    
                } */
        </style>

    </head>

    <!-- body start -->
    <body data-menu-color="light" data-sidebar="default">
        {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
        <!-- Begin page -->
        <div id="app-layout">
            
            <!-- Topbar Start -->
            <div class="topbar-custom">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between">
                        <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">
                            <li>
                                <button class="button-toggle-menu nav-link">
                                    <i data-feather="menu" class="noti-icon"></i>
                                </button>
                            </li>
                            <li class="d-none d-lg-block">
                                <h5 class="mb-0 text-uppercase">Bienvenue, {{Auth::user()->name}}</h5>
                            </li>
                        </ul>
                        {{-- <div class="py-2 w-50 w-md-50 w-lg-25 d-flex justify-content-center">
                            <p class="text-center mt-2 fs-3" style="
                                background: linear-gradient(to right, #cb6ce6, #ff5757);
                                -webkit-background-clip: text;
                                -webkit-text-fill-color: transparent;
                                font-weight: 500;
                                white-space: nowrap; /* يمنع كسر النص */
                            ">
                                Compagnie est active : {{$company}}
                            </p>
                        </div> --}}
                        
                        <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">
                            

                            

                            <!-- Button Trigger Customizer Offcanvas -->
                            <li class="d-none d-sm-flex">
                                <button type="button" class="btn nav-link" data-toggle="fullscreen">
                                    <i data-feather="maximize" class="align-middle fullscreen noti-icon"></i>
                                </button>
                            </li>

                            <!-- Light/Dark Mode Button Themes -->
                            <li class="d-none d-sm-flex">
                                <button type="button" class="btn nav-link" id="light-dark-mode">
                                    <i data-feather="moon" class="align-middle dark-mode"></i>
                                    <i data-feather="sun" class="align-middle light-mode"></i>
                                </button>
                            </li>

                            <li class="dropdown notification-list topbar-dropdown">
                                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                    <i data-feather="bell" class="noti-icon"></i>
                                    <span class="badge bg-danger rounded-circle noti-icon-badge">9</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end dropdown-lg">
                                    <!-- item-->
                                    <div class="dropdown-item noti-title">
                                        <h5 class="m-0">
                                            <span class="float-end"><a href="" class="text-dark"><small>Tout effacer</small></a></span>Notification
                                        </h5>
                                    </div>

                                    <div class="noti-scroll" data-simplebar>
                                        <!-- item-->
                                        <a href="javascript:void(0);"
                                            class="dropdown-item notify-item text-muted link-primary active">
                                            <div class="notify-icon">
                                                <img src="" class="img-fluid rounded-circle" alt="" />
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <p class="notify-details">Carl Steadham</p>
                                                <small class="text-muted">Il y a 5 min</small>
                                            </div>
                                            <p class="mb-0 user-msg">
                                                <small class="fs-14">Terminé <span class="text-reset">Amélioration du flux de travail dans Figma</span></small>
                                            </p>
                                        </a>

                                        <!-- item-->
                                        <a href="javascript:void(0);" class="dropdown-item notify-item text-muted link-primary">
                                            <div class="notify-icon">
                                                <img src="" class="img-fluid rounded-circle" alt="" />
                                            </div>
                                            <div class="notify-content">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <p class="notify-details">Olivia McGuire</p>
                                                    <small class="text-muted">Il y a 1 min</small>
                                                </div>

                                                <div class="d-flex mt-2 align-items-center">
                                                    <div class="notify-sub-icon">
                                                        <i class="mdi mdi-download-box text-dark"></i>
                                                    </div>

                                                    <div>
                                                        <p class="notify-details mb-0">dark-themes.zip</p>
                                                        <small class="text-muted">2.4 MB</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>

                                        <!-- item-->
                                        <a href="javascript:void(0);" class="dropdown-item notify-item text-muted link-primary">
                                            <div class="notify-icon">
                                                <img src=" " class="img-fluid rounded-circle" alt="" />
                                            </div>
                                            <div class="notify-content">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <p class="notify-details">Travis Williams</p>
                                                    <small class="text-muted">Il y a 7 min</small>
                                                </div>
                                                <p class="noti-mentioned p-2 rounded-2 mb-0 mt-2">
                                                    <span class="text-primary">@Patryk</span> Veuillez vous assurer que vous êtes....
                                                </p>
                                            </div>
                                        </a>

                                        <!-- item-->
                                        <a href="javascript:void(0);" class="dropdown-item notify-item text-muted link-primary">
                                            <div class="notify-icon">
                                                <img src="" class="img-fluid rounded-circle" alt="" />
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <p class="notify-details">Violette Lasky</p>
                                                <small class="text-muted">Il y a 5 min</small>
                                            </div>
                                            <p class="mb-0 user-msg">
                                                <small class="fs-14">Terminé <span class="text-reset">Créer de nouveaux composants</span></small>
                                            </p>
                                        </a>

                                        <!-- item-->
                                        <a href="javascript:void(0);" class="dropdown-item notify-item text-muted link-primary">
                                            <div class="notify-icon">
                                                <img src="" class="img-fluid rounded-circle" alt="" />
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <p class="notify-details">Ralph Edwards</p>
                                                <small class="text-muted">Il y a 5 min</small>
                                            </div>
                                            <p class="mb-0 user-msg">
                                                <small class="fs-14">Terminé<span class="text-reset">Amélioration du flux de travail dans React</span></small>
                                            </p>
                                        </a>

                                        <!-- item-->
                                        <a href="javascript:void(0);" class="dropdown-item notify-item text-muted link-primary">
                                            <div class="notify-icon">
                                                <img src="" class="img-fluid rounded-circle" alt="" />
                                            </div>
                                            <div class="notify-content">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <p class="notify-details">Jocab jones</p>
                                                    <small class="text-muted">Il y a 7 min</small>
                                                </div>
                                                <p class="noti-mentioned p-2 rounded-2 mb-0 mt-2">
                                                    <span class="text-reset">@Patryk</span> Veuillez vous assurer que vous êtes....
                                                </p>
                                            </div>
                                        </a>
                                    </div>

                                    <!-- All-->
                                    <a href="javascript:void(0);" class="dropdown-item text-center text-primary notify-item notify-all">Voir tout
                                        <i class="fe-arrow-right"></i>
                                    </a>
                                </div>
                            </li>

                            <!-- User Dropdown -->
                            <li class="dropdown notification-list topbar-dropdown">
                                <a class="nav-link dropdown-toggle nav-user me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                    <img src="{{asset('images/user.jpg')}}" alt="user-image" class="rounded-circle" />
                                    <span class="pro-user-name ms-1"> {{ Auth::user()->name }} <i class="mdi mdi-chevron-down"></i></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end profile-dropdown">
                                    <!-- item-->
                                    <div class="dropdown-header noti-title">
                                        <h6 class="text-overflow m-0">Bienvenue !</h6>
                                    </div>

                                    <!-- item-->
                                    <a class='dropdown-item notify-item' href='/hando/html/pages-profile'>
                                        <i class="mdi mdi-account-circle-outline fs-16 align-middle"></i>
                                        <span>Mon Compte</span>
                                    </a>

                                    <!-- item-->
                                    <a class='dropdown-item notify-item' href='/hando/html/auth-lock-screen'>
                                        <i class="mdi mdi-lock-outline fs-16 align-middle"></i>
                                        <span>Écran de verrouillage</span>
                                    </a>

                                    <div class="dropdown-divider"></div>

                                    <!-- item-->
                                    <a class='dropdown-item notify-item' href="{{ route('logout') }}"
                                        onclick="event.preventDefault();
                                                document.getElementById('logout-form').submit();">
                                        <i class="mdi mdi-location-exit fs-16 align-middle"></i>
                                        <span>Déconnexion</span>
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- end Topbar -->

            <!-- Left Sidebar Start -->
            <div class="app-sidebar-menu">
                <div class="h-100" data-simplebar>

                    <!--- Sidemenu -->
                    <div id="sidebar-menu">

                        <div class="logo-box">
                            <a class='logo logo-light' href='/home'>
                                <span class="logo-sm">
                                    <img src="{{asset('images/2.png')}}" alt="" height="22">
                                </span>
                                <span class="logo-lg">
                                    <img src="{{asset('images/2.png')}}" alt="" height="200" width="200" style="margin-top:-68px">
                                </span>
                            </a>
                            <a class='logo logo-dark' href='/home'>
                                <span class="logo-sm">
                                    <img src="{{asset('images/2.png')}}" alt="" height="22">
                                </span>
                                <span class="logo-lg">
                                    <img src="{{asset('images/2.png')}}" alt="" height="200" width="200" style="margin-top:-68px">
                                </span>
                            </a>
                        </div>

                        <ul id="side-menu">
                            <li class="menu-title">Menu</li>
                            <li>
                                <a class='tp-link' href="/home" {{ Request::is('home') || Request::is('Dashboard') ? 'active' : '' }}>
                                    <i data-feather="home"></i>
                                    <span> Page d'accueil </span> 
                                </a>
                            </li>
                            @can('Taxes')
                                <li class="menu-title">TVA</li>

                                <li>
                                    <a class='tp-link' href="{{url('tva')}}" >
                                        <i class="fa-solid fa-percent"></i>
                                        <span> Taxe </span>
                                    </a>
                                </li>
                            @endcan
                            
                            @can('Products')
                                <li class="menu-title">Produits</li>

                                <li>
                                    <a class='tp-link' href="{{url('products')}}" >
                                        <i class="fa-solid fa-box"></i>
                                        <span> Produits </span>
                                    </a>
                                </li>
                            @endcan
                                                
                            @can('Fournisseurs')
                                <li class="menu-title">Fournisseur</li>

                                <li>
                                    <a class='tp-link' href="{{url('fournisseur')}}" >
                                        <i class="fa-solid fa-truck-field"></i>
                                        <span> Fournisseur </span>
                                    </a>
                                </li>
                            @endcan

                            @can('Formateurs')
                            <li class="menu-title">Formateurs</li>
                                <li>
                                   <a class='tp-link' href="{{url('client')}}">
                                        <i class="fa-solid fa-chalkboard-teacher"></i>
                                         <span> Gestion formateurs </span>
                                    </a>
                                </li>
                            @endcan

                            @can('Categories')
                                <li class="menu-title mt-2">Catégories</li>
                                                
                                <li>
                                    <a class='tp-link' href='{{url('categories')}}'>
                                        <i class="fa-solid fa-list-check"></i>
                                        <span> Liste de Catégories </span>
                                    </a>
                                </li>
                            @endcan
                            
                            
                            @can('Local')
                                <li class="menu-title mt-2">Local</li>                 
                                <li>
                                    <a class='tp-link' href='{{url('local')}}'>
                                        <i class="fa-solid fa-location-dot"></i>
                                        <span> Local </span>
                                    </a>
                                </li>
                            @endcan

                            @can('Rayon')
                                <li>
                                    <a class='tp-link' href='{{url('rayon')}}'>
                                        <i class="fa-solid fa-table-cells"></i>
                                        <span> Rayon </span>
                                    </a>
                                </li>
                            @endcan

                            @can('Famille')
                                <li class="menu-title mt-2">Sous-Catégories</li>
                                                
                                <li>
                                    <a class='tp-link' href='{{url('subcategory')}}'>
                                        <i class="fa-solid fa-sitemap"></i>
                                        
                                        <span> Famille </span>
                                    </a>
                                </li>
                            @endcan

                            @can('Achat')
                                <li>
                                    <a class='tp-link' href='{{url('Achat')}}'>
                                        <i class="fa-solid fa-shopping-cart"></i>
                                        <span> Achats </span>
                                    </a>
                                </li> 
                            @endcan

                            @can('Commande')
                                <li>
                                    <a class='tp-link' href='{{url('Vente')}}'>
                                        <i class="fa-solid fa-cash-register"></i>
                                    
                                        <span> commande </span>
                                    </a>
                                </li>
                            @endcan

                            @can('Historique')
                                <li>
                                    <a class='tp-link' href='{{url('audit')}}'>
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                        <span> Historique </span>
                                    </a>
                                </li>
                            @endcan  
                            
                            @can('Unité')
                                <li>
                                    <a class='tp-link' href='{{url('unite')}}'>
                                        <i class="fa-solid fa-list-check"></i>
                                        <span> Unité </span>
                                    </a>
                                </li>
                            @endcan 

                            @can('utilisateur')
                                <li class="menu-title mt-2"> Utilisateurs</li>

                                <li>
                                    <a class='tp-link' href="{{route('users.index')}}" >
                                        <span class="mdi mdi-account-group"></span>
                                        <span> Liste des utilisateurs </span>
                                    </a>
                                    
                                </li>
                            @endcan 

                            @can('utilisateur')
                                <li class="menu-title mt-2">  Pouvoirs </li>

                                <li>
                                    <a href="{{url('roles')}}" class='tp-link'>
                                        <span class="mdi mdi-account-key-outline"></span>
                                        <span>  Pouvoirs utilisateurs </span>
                                    </a>
                                    
                                </li>
                            @endcan 

                            <li class="menu-title mt-2">  Paramètres </li>

                            <li>
                                <a href="#sidebarCharts" >
                                    <span class="mdi mdi-cog-transfer"></span>
                                    <span>  Information  </span>
                                </a>
                                
                            </li>

                        </ul>
            
                    </div>
                    <!-- End Sidebar -->

                    <div class="clearfix"></div>

                </div>
            </div>
            <!-- Left Sidebar End -->

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->
            @yield(section: 'dashboard')
            
            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->

        </div>
        <!-- END wrapper -->

        <!-- Vendor -->
         <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script> 
        <script src="{{asset('js/jquery/jquery.min.js')}}"></script> 
        <script src="{{asset("js/bootstrap/js/bootstrap.bundle.min.js")}}"></script>
        <script src="{{asset("js/simplebar/simplebar.min.js")}}"></script>
        <script src="{{asset("js/node-waves/waves.min.js")}}"></script>
        <script src="{{asset("js/waypoint/lib/jquery.waypoints.min.js")}}"></script>
        <script src="{{asset("js/jquery-counterup/jquery.counterup.min.js")}}"></script>
        <script src="{{asset("js/feather-icons/feather.min.js")}}"></script>
        <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>

        <!-- Apexcharts JS -->
        
        <script src="{{asset("js/apexcharts/apexcharts.min.js")}}"></script>

        <!-- Widgets Init Js -->
        
        <script src="{{asset("js/pages/crm-dashboard.init.js")}}"></script>

        <!-- App js-->
        
        <script src="{{asset("js/app.js")}}"></script>

    </body>

</html>