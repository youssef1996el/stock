$(document).ready(function () {

    $(function () {
        if ($.fn.DataTable.isDataTable('.TableClients')) {
            $('.TableClients').DataTable().destroy();
        }
        initializeDataTable('.TableClients', clients);
        
        function initializeDataTable(selector, url) {
            var tableClients = $(selector).DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: url,
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
                    { data: 'first_name', name: 'first_name' },
                    { data: 'last_name', name: 'last_name' },
                    { data: 'Telephone', name: 'Telephone' },
                    { data: 'Email', name: 'Email' },
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
            $(selector + ' tbody').on('click', '.editClient', function(e)
            {
                e.preventDefault();
                $('#ModalEditClient').modal("show");
                var IdClient = $(this).attr('data-id');
                
                $.ajax({
                    type: "GET",
                    url: EditClient + '/' + IdClient,
                    dataType: "json",
                    success: function(response) {
                        $('#first_name').val(response.first_name);
                        $('#last_name').val(response.last_name);
                        $('#Telephone').val(response.Telephone);
                        $('#Email').val(response.Email);
                        $('#BtnUpdateClient').attr('data-value', IdClient);
                    },
                    error: function() {
                        new AWN().alert("Erreur lors de la récupération des données", { durations: { alert: 5000 } });
                    }
                });
            });

            $(selector + ' tbody').on('click', '.deleteClient', function(e)
            {
                e.preventDefault();
                var IdClient = $(this).attr('data-id');
                let notifier = new AWN();

                let onOk = () => {
                    $.ajax({
                        type: "post",
                        url: DeleteClient,
                        data: 
                        {
                            id: IdClient,
                            _token: csrf_token,
                        },
                        dataType: "json",
                        success: function (response) 
                        {
                            if(response.status == 200)
                            {
                                new AWN().success(response.message, {durations: {success: 5000}});
                                $('.TableClients').DataTable().ajax.reload();
                            }   
                            else if(response.status == 404)
                            {
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
                    'Êtes-vous sûr de vouloir supprimer ce client ?',
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

    function phoneFormatter() {
        $('#phone_client, .phone_client_edit').on('input', function() {
            var number = $(this).val().replace(/[^\d]/g, ''); // Remove any non-numeric characters
    
            if (number.length <= 10) {
                number = number.replace(/(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/, "$1-$2-$3-$4-$5");
            } else {
                number = number.substring(0, 10).replace(/(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/, "$1-$2-$3-$4-$5");
            }
    
            $(this).val(number);
        });
    }
    
    
    $(phoneFormatter);
    
    $('#BtnAddClient').on('click', function(e)
    {
        e.preventDefault();
        
        let formData = new FormData($('#FormAddClient')[0]);
        formData.append('_token', csrf_token);

        $('#BtnAddClient').prop('disabled', true).text('Enregistrement...');

        $.ajax({
            type: "POST",
            url: AddClient,
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) 
            {
                $('#BtnAddClient').prop('disabled', false).text('Sauvegarder');
                if(response.status == 200)
                {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    $('#ModalAddClient').modal('hide');
                    $('.TableClients').DataTable().ajax.reload();
                    $('#FormAddClient')[0].reset();
                }  
                else if(response.status == 404)
                {
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                }
                else if(response.status == 400)
                {
                    $('.validationAddClient').html("");
                    $('.validationAddClient').addClass('alert alert-danger');
                    $.each(response.errors, function(key, list_err) {
                        $('.validationAddClient').append('<li>' + list_err + '</li>');
                    });
    
                    setTimeout(() => {
                        $('.validationAddClient').fadeOut('slow', function() {
                            $(this).html("").removeClass('alert alert-danger').show();
                        });
                    }, 5000);
                }  
                else if (response.status == 404 || response.status == 500) {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function() {
                $('#BtnAddClient').prop('disabled', false).text('Sauvegarder');
                new AWN().alert("Une erreur est survenue, veuillez réessayer.", { durations: { alert: 5000 } });
            }
        });
    });

    $('#BtnUpdateClient').on('click', function(e) {
        e.preventDefault();
        
        let formData = new FormData($('#FormUpdateClient')[0]);
        formData.append('_token', csrf_token);
        formData.append('id', $(this).attr('data-value'));
       
        $('#BtnUpdateClient').prop('disabled', true).text('Mise à jour...');
    
        $.ajax({
            type: "POST",
            url: UpdateClient,
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(response) {
                $('#BtnUpdateClient').prop('disabled', false).text('Mettre à jour');
                if (response.status == 200) {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    $('#ModalEditClient').modal('hide');
                    $('.TableClients').DataTable().ajax.reload();
                }  
                else if (response.status == 404) {
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                }
                else if (response.status == 400) {
                    $('.validationEditClient').html("");
                    $('.validationEditClient').addClass('alert alert-danger');
                    $.each(response.errors, function(key, list_err) {
                        $('.validationEditClient').append('<li>' + list_err + '</li>');
                    });
    
                    setTimeout(() => {
                        $('.validationEditClient').fadeOut('slow', function() {
                            $(this).html("").removeClass('alert alert-danger').show();
                        });
                    }, 5000);
                }  
                else if (response.status == 500) {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function() {
                $('#BtnUpdateClient').prop('disabled', false).text('Mettre à jour');
                new AWN().alert("Une erreur est survenue, veuillez réessayer.", { durations: { alert: 5000 } });
            }
        });
    });
    
});