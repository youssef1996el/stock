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
            if ($.fn.DataTable.isDataTable('.TableUnites')) {
                $('.TableUnites').DataTable().destroy();
            }
            
            var tableUnites = $('.TableUnites').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: unites,
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
            $('.TableUnites tbody').on('click', '.editUnite', function(e) {
                e.preventDefault();
                var IdUnite = $(this).attr('data-id');
                
                $.ajax({
                    type: "GET",
                    url: editUnite + "/" + IdUnite,
                    dataType: "json",
                    success: function(response) {
                        $('#ModalEditUnite').modal("show");
                        $('#name').val(response.name);
                        $('#BtnUpdateUnite').attr('data-value', IdUnite);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching unite:", error);
                        new AWN().alert("Une erreur est survenue, veuillez réessayer.", { durations: { alert: 5000 } });
                    }
                });
            });

            // Handle delete button click
            $('.TableUnites tbody').on('click', '.deleteUnite', function(e) {
                e.preventDefault();
                var IdUnite = $(this).attr('data-id');
                let notifier = new AWN();

                let onOk = () => {
                    $.ajax({
                        type: "post",
                        url: DeleteUnite,
                        data: {
                            id: IdUnite,
                            _token: csrf_token,
                        },
                        dataType: "json",
                        success: function (response) {
                            if(response.status == 200) {
                                new AWN().success(response.message, {durations: {success: 5000}});
                                $('.TableUnites').DataTable().ajax.reload();
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
                    'Êtes-vous sûr de vouloir supprimer cette unité ?',
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
    
    // Add Unite
    $('#BtnAddUnite').on('click', function(e) {
        e.preventDefault();
        
        let formData = new FormData($('#FormAddUnite')[0]);
        formData.append('_token', csrf_token);
    
        $('#BtnAddUnite').prop('disabled', true).text('Enregistrement...');
    
        $.ajax({
            type: "POST",
            url: AddUnite,
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                $('#BtnAddUnite').prop('disabled', false).text('Sauvegarder');
                
                if(response.status == 200) {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    $('#ModalAddUnite').modal('hide');
                    $('.TableUnites').DataTable().ajax.reload();
                    $('#FormAddUnite')[0].reset();
                } else if(response.status == 409) {
                    // Handle already exists case
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                } else if(response.status == 404) {
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                } else if(response.status == 400) {
                    $('.validationAddUnite').html("");
                    $('.validationAddUnite').addClass('alert alert-danger');
                    $.each(response.errors, function(key, list_err) {
                        $('.validationAddUnite').append('<li>' + list_err + '</li>');
                    });
                    
                    setTimeout(() => {
                        $('.validationAddUnite').fadeOut('slow', function() {
                            $(this).html("").removeClass('alert alert-danger').show();
                        });
                    }, 5000);
                } else if (response.status == 500) {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function(xhr) {
                $('#BtnAddUnite').prop('disabled', false).text('Sauvegarder');
                
                if (xhr.status === 403) {
                    new AWN().warning(xhr.responseJSON.message, {durations: {warning: 5000}});
                } else if (xhr.status === 409) {
                    new AWN().warning(xhr.responseJSON.message, {durations: {warning: 5000}});
                } else {
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
            }
        });
    });

    // Update Unite
    $('#BtnUpdateUnite').on('click', function(e) {
        e.preventDefault();
        
        var IdUnite = $(this).attr('data-value');
        
        let formData = new FormData();
        formData.append('_token', csrf_token);
        formData.append('id', IdUnite);
        formData.append('name', $('#name').val());
        
        $('#BtnUpdateUnite').prop('disabled', true).text('Mise à jour...');
        
        $.ajax({
            type: "POST",
            url: UpdateUnite,
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(response) {
                $('#BtnUpdateUnite').prop('disabled', false).text('Mettre à jour');
                
                if (response.status == 200) {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    $('#ModalEditUnite').modal('hide');
                    $('.TableUnites').DataTable().ajax.reload();
                } else if (response.status == 409) {
                    // Handle already exists case
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                } else if (response.status == 404) {
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                } else if (response.status == 400) {
                    $('.validationEditUnite').html("");
                    $('.validationEditUnite').addClass('alert alert-danger');
                    $.each(response.errors, function(key, list_err) {
                        $('.validationEditUnite').append('<li>' + list_err + '</li>');
                    });
                    
                    setTimeout(() => {
                        $('.validationEditUnite').fadeOut('slow', function() {
                            $(this).html("").removeClass('alert alert-danger').show();
                        });
                    }, 5000);
                } else {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function(xhr) {
                $('#BtnUpdateUnite').prop('disabled', false).text('Mettre à jour');
                
                if (xhr.status === 403) {
                    new AWN().warning(xhr.responseJSON.message, {durations: {warning: 5000}});
                } else if (xhr.status === 409) {
                    new AWN().warning(xhr.responseJSON.message, {durations: {warning: 5000}});
                } else {
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
            }
        });
    });
});