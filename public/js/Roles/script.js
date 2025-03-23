$(document).ready(function () {
    $(function () {
        if ($.fn.DataTable.isDataTable('.TableRoles')) {
            $('.TableRoles').DataTable().destroy();
        }
        initializeDataTable('.TableRoles', roles);
        
        function initializeDataTable(selector, url) {
            var TableRoles = $(selector).DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: url,
                    dataSrc: function (json) {
                        if (json.data.length === 0) {
                            $('.paging_full_numbers').css('display', 'none');
                        }
                        return json.data;
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false }, // Row number
                    { data: 'name', name: 'name' },
                    { data: 'permissions', name: 'permissions' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                language: {
                    "sInfo": "",
                    "sInfoEmpty": "Affichage de l'élément 0 à 0 sur 0 élément",
                    "sInfoFiltered": "(filtré à partir de _MAX_ éléments au total)",
                    "sLengthMenu": "Afficher _MENU_ éléments",
                    "sLoadingRecords": "Chargement...",
                    "sProcessing": "Traitement...",
                    "sSearch": "Rechercher :",
                    "sZeroRecords": "Aucun élément correspondant trouvé",
                    "oPaginate": {
                        "sFirst": "Premier",
                        "sLast": "Dernier",
                        "sNext": "Suivant",
                        "sPrevious": "Précédent"
                    }
                }
            });
            $(selector + ' tbody').on('click', '.editRole', function(e)
            {
                e.preventDefault();
                $('#ModalEditRoles').modal("show");
                var IdRole           = $(this).attr('data-id');
                var name             = $(this).closest('tr').find('td:eq(1)').text();
               
                $('#nameRole').val(name);
                
                $('#BtnEditRoles').attr('data-value',IdRole);
            });

            $(selector + ' tbody').on('click', '.deleterole', function (e) {
                e.preventDefault();
                var roleId = $(this).attr('data-id');
                let notifier = new AWN();
            
                let onOk = () => {
                    $.ajax({
                        type: "post",
                        url: DeleteRole, // Use RESTful DELETE request
                        data: 
                        {
                            id : roleId,
                            _token: csrf_token,
                        },
                        dataType: "json",
                        success: function (response) {
                            if (response.status === 200) {
                                new AWN().success(response.message, { durations: { success: 5000 } });
                                $('.TableRoles').DataTable().ajax.reload(); // Reload table after deletion
                            } else if (response.status === 403) {
                                new AWN().warning(response.message, { durations: { warning: 5000 } });
                            }
                        },
                        error: function (xhr) {
                            let errorMessage = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : "Une erreur est survenue, veuillez réessayer.";
                            new AWN().alert(errorMessage, { durations: { alert: 5000 } });
                        }
                    });
                };
            
                let onCancel = () => {
                    notifier.info('Annulation de la suppression');
                };
            
                notifier.confirm(
                    'Êtes-vous sûr de vouloir supprimer ce rôle ?',
                    onOk,
                    onCancel,
                    {
                        labels: {
                            confirm: 'Supprimer',
                            cancel: 'Annuler'
                        }
                    }
                );
            });
            
        }
    });


    $('#BtnADDRoles').on('click',function(e)
    {
        e.preventDefault();
        
        let formData = new FormData($('#FormAddRoles')[0]);
        formData.append('_token', csrf_token);

        $('#BtnADDRoles').prop('disabled', true).text('Enregistrement...'); 
        $.ajax({
            type: "POST",
            url: AddRoles,
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json", 
            success: function (response) 
            {
                $('#BtnADDRoles').prop('disabled', false).text('Sauvegarder');
                if(response.status == 200)
                {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    $('#ModalAddRoles').modal('hide');
                    $('.TableRoles').DataTable().ajax.reload();
                    $('#FormAddRoles')[0].reset();
                }  
                else if(response.status == 404)
                {
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                }
                else if(response.dataError == 400)
                {
                    
                    $('.validationAddRoles').html("");
                    $('.validationAddRoles').addClass('alert alert-danger');
                    $.each(response.errors, function(key, list_err) {
                        $('.validationAddRoles').append('<li>' + list_err + '</li>');
                    });
    
                    setTimeout(() => {
                        $('.validationAddRoles').fadeOut('slow', function() {
                            $(this).html("").removeClass('alert alert-danger').show();
                        });
                    }, 5000);
                }  
                else if (response.dataError == 404 || response.dataError == 500) {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function() {
                $('#BtnADDRoles').prop('disabled', false).text('Sauvegarder');
                new AWN().alert("Une erreur est survenue, veuillez réessayer.", { durations: { alert: 5000 } });
            }
        });
    });

    $('#BtnEditRoles').on('click',function(e)
    {
        e.preventDefault();
        
        let formData = new FormData($('#FormUpdateRoles')[0]);
        formData.append('_token', csrf_token);
        formData.append('id'    , $(this).attr('data-value'));
        $('#BtnADDRoles').prop('disabled', true).text('Enregistrement...'); 
        $.ajax({
            type: "POST",
            url: updateRole,
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) 
            {
                $('#BtnEditRoles').prop('disabled', false).text('Sauvegarder');
                if(response.status == 200)
                {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    $('#ModalEditRoles').modal('hide');
                    $('.TableRoles').DataTable().ajax.reload();
                    $('#FormUpdateRoles')[0].reset();
                }  
                else if(response.status == 404)
                {
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                }
                
                else if (response.status == 404 || response.status == 500) {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function() {
                $('#BtnEditRoles').prop('disabled', false).text('Sauvegarder');
                new AWN().alert("Une erreur est survenue, veuillez réessayer.", { durations: { alert: 5000 } });
            }
        });
    });
});