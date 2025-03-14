$(document).ready(function () {
    // Add DataTables library if not already included in your layout
    var datatablesScript = document.createElement('script');
    datatablesScript.src = 'https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js';
    document.head.appendChild(datatablesScript);

    var datatablesCssLink = document.createElement('link');
    datatablesCssLink.rel = 'stylesheet';
    datatablesCssLink.href = 'https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css';
    document.head.appendChild(datatablesCssLink);

    // Wait for DataTables to load
    setTimeout(function() {
        initializeDataTable();
    }, 500);

    function initializeDataTable() {
        try {
            if ($.fn.DataTable.isDataTable('.TableRayons')) {
                $('.TableRayons').DataTable().destroy();
            }
            
            var tableRayons = $('.TableRayons').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: rayons,
                    dataSrc: function (json) {
                        if (json.data.length === 0) {
                            $('.paging_full_numbers').css('display', 'none');
                        }
                        return json.data;
                    },
                    error: function(xhr, error, thrown) {
                        console.log('DataTables error: ' + error + ' ' + thrown);
                        console.log(xhr);
                    }
                },
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'local_name', name: 'local_name' },
                    { data: 'username', name: 'username' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
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
            
            // Handle edit button click
            $('.TableRayons tbody').on('click', '.editRayon', function(e) {
                e.preventDefault();
                var IdRayon = $(this).attr('data-id');
                
                $.ajax({
                    type: "GET",
                    url: editRayon + "/" + IdRayon,
                    dataType: "json",
                    success: function(response) {
                        $('#ModalEditRayon').modal("show");
                        $('#name').val(response.name);
                        $('#id_local').val(response.id_local);
                        $('#BtnUpdateRayon').attr('data-value', IdRayon);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching rayon:", error);
                        new AWN().alert("Une erreur est survenue, veuillez réessayer.", { durations: { alert: 5000 } });
                    }
                });
            });

            // Handle delete button click
            $('.TableRayons tbody').on('click', '.deleteRayon', function(e) {
                e.preventDefault();
                var IdRayon = $(this).attr('data-id');
                let notifier = new AWN();

                let onOk = () => {
                    $.ajax({
                        type: "post",
                        url: DeleteRayon,
                        data: {
                            id: IdRayon,
                            _token: csrf_token,
                        },
                        dataType: "json",
                        success: function (response) {
                            if(response.status == 200) {
                                new AWN().success(response.message, {durations: {success: 5000}});
                                $('.TableRayons').DataTable().ajax.reload();
                            } else if(response.status == 404) {
                                new AWN().warning(response.message, {durations: {warning: 5000}});
                            }
                        },
                        error: function() {
                            new AWN().alert("Une erreur est survenue, veuillez réessayer.", { durations: { alert: 5000 } });
                        }
                    });
                };

                let onCancel = () => {
                    notifier.info('Annulation de la suppression');
                };

                notifier.confirm(
                    'Êtes-vous sûr de vouloir supprimer ce rayon ?',
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
        } catch (error) {
            console.error("Error initializing DataTable:", error);
        }
    }
    
    // Add Rayon
    $('#BtnAddRayon').on('click', function(e) {
        e.preventDefault();
        
        let formData = new FormData($('#FormAddRayon')[0]);
        formData.append('_token', csrf_token);

        $('#BtnAddRayon').prop('disabled', true).text('Enregistrement...');

        $.ajax({
            type: "POST",
            url: AddRayon,
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                $('#BtnAddRayon').prop('disabled', false).text('Sauvegarder');
                
                if(response.status == 200) {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    $('#ModalAddRayon').modal('hide');
                    $('.TableRayons').DataTable().ajax.reload();
                    $('#FormAddRayon')[0].reset();
                } else if(response.status == 409) {
                    // Handle already exists case
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                } else if(response.status == 404) {
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                } else if(response.status == 400) {
                    $('.validationAddRayon').html("");
                    $('.validationAddRayon').addClass('alert alert-danger');
                    $.each(response.errors, function(key, list_err) {
                        $('.validationAddRayon').append('<li>' + list_err + '</li>');
                    });
                    
                    setTimeout(() => {
                        $('.validationAddRayon').fadeOut('slow', function() {
                            $(this).html("").removeClass('alert alert-danger').show();
                        });
                    }, 5000);
                } else if (response.status == 500) {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function(xhr) {
                $('#BtnAddRayon').prop('disabled', false).text('Sauvegarder');
                
                // Try to parse the error response
                try {
                    var errorResponse = JSON.parse(xhr.responseText);
                    if (errorResponse && errorResponse.message) {
                        new AWN().alert(errorResponse.message, { durations: { alert: 5000 } });
                    } else {
                        new AWN().alert("Une erreur est survenue, veuillez réessayer.", { durations: { alert: 5000 } });
                    }
                } catch (e) {
                    new AWN().alert("Une erreur est survenue, veuillez réessayer.", { durations: { alert: 5000 } });
                }
            }
        });
    });

    // Update Rayon
    $('#BtnUpdateRayon').on('click', function(e) {
        e.preventDefault();
        
        var IdRayon = $(this).attr('data-value');
        
        let formData = new FormData();
        formData.append('_token', csrf_token);
        formData.append('id', IdRayon);
        formData.append('name', $('#name').val());
        formData.append('id_local', $('#id_local').val());
        
        $('#BtnUpdateRayon').prop('disabled', true).text('Mise à jour...');
        
        $.ajax({
            type: "POST",
            url: UpdateRayon,
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(response) {
                $('#BtnUpdateRayon').prop('disabled', false).text('Mettre à jour');
                
                if (response.status == 200) {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    $('#ModalEditRayon').modal('hide');
                    $('.TableRayons').DataTable().ajax.reload();
                } else if (response.status == 409) {
                    // Handle already exists case
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                } else if (response.status == 404) {
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                } else if (response.status == 400) {
                    $('.validationEditRayon').html("");
                    $('.validationEditRayon').addClass('alert alert-danger');
                    $.each(response.errors, function(key, list_err) {
                        $('.validationEditRayon').append('<li>' + list_err + '</li>');
                    });
                    
                    setTimeout(() => {
                        $('.validationEditRayon').fadeOut('slow', function() {
                            $(this).html("").removeClass('alert alert-danger').show();
                        });
                    }, 5000);
                } else {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function(xhr) {
                $('#BtnUpdateRayon').prop('disabled', false).text('Mettre à jour');
                
                // Try to parse the error response
                try {
                    var errorResponse = JSON.parse(xhr.responseText);
                    if (errorResponse && errorResponse.message) {
                        new AWN().alert(errorResponse.message, { durations: { alert: 5000 } });
                    } else {
                        new AWN().alert("Une erreur est survenue, veuillez réessayer.", { durations: { alert: 5000 } });
                    }
                } catch (e) {
                    new AWN().alert("Une erreur est survenue, veuillez réessayer.", { durations: { alert: 5000 } });
                }
            }
        });
    });
});