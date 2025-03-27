@extends('dashboard.index')

@section('dashboard')
<script src="{{asset('js/Roles/script.js')}}"></script>
<script>
    var csrf_token                      = "{{csrf_token()}}";
    var AddRoles                       = "{{route('roles.store')}}";
    var roles                      = "{{route('roles.index')}}";
    var updateRole                      = "{{url('updateRole')}}";
    var DeleteRole                      = "{{url('DeleteRole')}}"; 
   
</script>
<div class="content-page">
    <div class="content">

        <!-- Start Content-->
        <div class="container-fluid">

            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-semibold m-0">List de roles </h4>
                </div>
                
                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Applications</a></li>
                        <li class="breadcrumb-item active">roles</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-body">
                            <div class=" mb-3">
                                @can('rôles-ajoute')
                                    <button class="btn btn-primary" style="margin-right: 5px" data-bs-toggle="modal" data-bs-target="#ModalAddRoles">Add roles</button>
                                @endcan
                            </div>
                            <div class="table-responsive">
                                <div class="datatable-wrapper datatable-loading no-footer sortable fixed-height fixed-columns">
                                    
                                    <div class="datatable-container" style="height: 665.531px;">
                                        <table class="table datatable datatable-table TableRoles" >
                                            <thead>
                                                <tr>
                                                     
                                                    
                                                    
                                                    <th data-sortable="true">S#</th>
                                                    <th data-sortable="true" style="max-width:100px;">Role Name</th>
                                                    <th data-sortable="true">Permissions</th>
                                                    <th data-sortable="true" style="width: 250px;">Action</th>
                                                    
                                                    
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
<div class="modal fade" id="ModalAddRoles" tabindex="-1" aria-labelledby="ModalAddUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalAddUserLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <ul class="validationAddRoles"></ul>
                    <form action="{{ route('roles.store') }}" id="FormAddRoles"> 
                       
                
                        <!-- Full Name & Email -->
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Name roles</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                
                            <div class="col-12">
                                <label for="">Permissions</label>           
                                <select class="form-select @error('permissions') is-invalid @enderror" multiple aria-label="Permissions" id="permissions" name="permissions[]" style="height: 210px;">
                                    @forelse ($permissions as $permission)
                                        <option value="{{ $permission->id }}" {{ in_array($permission->id, old('permissions') ?? []) ? 'selected' : '' }}>
                                            {{ $permission->name }}
                                        </option>
                                    @empty
    
                                    @endforelse
                                </select>
                                @error('permissions')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>  
                    </form>
                </div>
            </div>
            <div class="modal-footer text-end">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ferme</button>
                <button type="button" class="btn btn-primary" id="BtnADDRoles"> Sauvegarder</button>
            </div>
        </div>
    </div>
</div>



        
<div class="modal fade" id="ModalEditRoles" tabindex="-1" aria-labelledby="ModalAddUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalAddUserLabel">Edit  roles</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <ul class="validationEditRoles"></ul>
                    <form action="{{ url('updateRole') }}" id="FormUpdateRoles">  
                        
                
                        <!-- Full Name & Email -->
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Name roles</label>
                                    <input type="text" id="nameRole" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                
                            <div class="col-12">           
                                <select class="form-select @error('permissions') is-invalid @enderror" multiple aria-label="Permissions" id="permissions" name="permissions[]" style="height: 210px;">
                                    @forelse ($permissions as $permission)
                                        <option value="{{ $permission->id }}" {{ in_array($permission->id, old('permissions') ?? []) ? 'selected' : '' }}>
                                            {{ $permission->name }}
                                        </option>
                                    @empty
    
                                    @endforelse
                                </select>
                                @error('permissions')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>  
                    </form>
                </div>
                
                
                  
            </div>
            <div class="modal-footer text-end">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ferme</button>
                <button type="button" class="btn btn-primary" id="BtnEditRoles"> Sauvegarder</button>
            </div>
        </div>
    </div>
</div>



        
        
        
        
</div>



@endsection