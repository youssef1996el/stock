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
            if ($.fn.DataTable.isDataTable('.TableLocals')) {
                $('.TableLocals').DataTable().destroy();
            }
            
            var tableLocals = $('.TableLocals').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: locals,
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
            $('.TableLocals tbody').on('click', '.editLocal', function(e) {
                e.preventDefault();
                var IdLocal = $(this).attr('data-id');
                
                $.ajax({
                    type: "GET",
                    url: editLocal + "/" + IdLocal,
                    dataType: "json",
                    success: function(response) {
                        $('#ModalEditLocal').modal("show");
                        $('#name').val(response.name);
                        $('#BtnUpdateLocal').attr('data-value', IdLocal);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching local:", error);
                        new AWN().alert("Une erreur est survenue, veuillez réessayer.", { durations: { alert: 5000 } });
                    }
                });
            });

            // Handle delete button click
            $('.TableLocals tbody').on('click', '.deleteLocal', function(e) {
                e.preventDefault();
                var IdLocal = $(this).attr('data-id');
                let notifier = new AWN();

                let onOk = () => {
                    $.ajax({
                        type: "post",
                        url: DeleteLocal,
                        data: {
                            id: IdLocal,
                            _token: csrf_token,
                        },
                        dataType: "json",
                        success: function (response) {
                            if(response.status == 200) {
                                new AWN().success(response.message, {durations: {success: 5000}});
                                $('.TableLocals').DataTable().ajax.reload();
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
                    'Êtes-vous sûr de vouloir supprimer ce local ?',
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
    
    // Add Local
    $('#BtnAddLocal').on('click', function(e) {
        e.preventDefault();
        
        let formData = new FormData($('#FormAddLocal')[0]);
        formData.append('_token', csrf_token);

        $('#BtnAddLocal').prop('disabled', true).text('Enregistrement...');

        $.ajax({
            type: "POST",
            url: AddLocal,
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                $('#BtnAddLocal').prop('disabled', false).text('Sauvegarder');
                
                if(response.status == 200) {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    $('#ModalAddLocal').modal('hide');
                    $('.TableLocals').DataTable().ajax.reload();
                    $('#FormAddLocal')[0].reset();
                } else if(response.status == 409) {
                    // Handle already exists case
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                } else if(response.status == 404) {
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                } else if(response.status == 400) {
                    $('.validationAddLocal').html("");
                    $('.validationAddLocal').addClass('alert alert-danger');
                    $.each(response.errors, function(key, list_err) {
                        $('.validationAddLocal').append('<li>' + list_err + '</li>');
                    });
                    
                    setTimeout(() => {
                        $('.validationAddLocal').fadeOut('slow', function() {
                            $(this).html("").removeClass('alert alert-danger').show();
                        });
                    }, 5000);
                } else if (response.status == 500) {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function(xhr) {
                $('#BtnAddLocal').prop('disabled', false).text('Sauvegarder');
                
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

    // Update Local
    $('#BtnUpdateLocal').on('click', function(e) {
        e.preventDefault();
        
        var IdLocal = $(this).attr('data-value');
        
        let formData = new FormData();
        formData.append('_token', csrf_token);
        formData.append('id', IdLocal);
        formData.append('name', $('#name').val());
        
        $('#BtnUpdateLocal').prop('disabled', true).text('Mise à jour...');
        
        $.ajax({
            type: "POST",
            url: UpdateLocal,
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(response) {
                $('#BtnUpdateLocal').prop('disabled', false).text('Mettre à jour');
                
                if (response.status == 200) {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    $('#ModalEditLocal').modal('hide');
                    $('.TableLocals').DataTable().ajax.reload();
                } else if (response.status == 409) {
                    // Handle already exists case
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                } else if (response.status == 404) {
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                } else if (response.status == 400) {
                    $('.validationEditLocal').html("");
                    $('.validationEditLocal').addClass('alert alert-danger');
                    $.each(response.errors, function(key, list_err) {
                        $('.validationEditLocal').append('<li>' + list_err + '</li>');
                    });
                    
                    setTimeout(() => {
                        $('.validationEditLocal').fadeOut('slow', function() {
                            $(this).html("").removeClass('alert alert-danger').show();
                        });
                    }, 5000);
                } else {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function(xhr) {
                $('#BtnUpdateLocal').prop('disabled', false).text('Mettre à jour');
                
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