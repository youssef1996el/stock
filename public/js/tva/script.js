$(document).ready(function () {

    $(function () {
        if ($.fn.DataTable.isDataTable('.TableTvas')) {
            $('.TableTvas').DataTable().destroy();
        }
        initializeDataTable('.TableTvas', tvas);
        
        function initializeDataTable(selector, url) {
            var tableTvas = $(selector).DataTable({
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
                    { data: 'name', name: 'name' },
                    { data: 'value', name: 'value' },
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
            $(selector + ' tbody').on('click', '.editTva', function(e)
            {
                e.preventDefault();
                $('#ModalEditTva').modal("show");
                var IdTva = $(this).attr('data-id');
                var name = $(this).closest('tr').find('td:eq(0)').text();
                var value = $(this).closest('tr').find('td:eq(1)').text();
                $('#name').val(name);
                $('#value').val(value);
                $('#BtnUpdateTva').attr('data-value', IdTva);
            });

            $(selector + ' tbody').on('click', '.deleteTva', function(e)
            {
                e.preventDefault();
                var IdTva = $(this).attr('data-id');
                let notifier = new AWN();

                let onOk = () => {
                    $.ajax({
                        type: "post",
                        url: DeleteTva,
                        data: 
                        {
                            id: IdTva,
                            _token: csrf_token,
                        },
                        dataType: "json",
                        success: function (response) 
                        {
                            if(response.status == 200)
                            {
                                new AWN().success(response.message, {durations: {success: 5000}});
                                $('.TableTvas').DataTable().ajax.reload();
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
                    'Êtes-vous sûr de vouloir supprimer cette TVA ?',
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
    
    $('#BtnAddTva').on('click', function(e) {
        e.preventDefault();
        
        let formData = new FormData($('#FormAddTva')[0]);
        formData.append('_token', csrf_token);
    
        $('#BtnAddTva').prop('disabled', true).text('Enregistrement...');
    
        $.ajax({
            type: "POST",
            url: AddTva,
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) 
            {
                $('#BtnAddTva').prop('disabled', false).text('Sauvegarder');
                if(response.status == 200)
                {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    $('#ModalAddTva').modal('hide');
                    $('.TableTvas').DataTable().ajax.reload();
                    $('#FormAddTva')[0].reset();
                }  
                else if(response.status == 404)
                {
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                }
                else if(response.status == 400)
                {
                    $('.validationAddTva').html("");
                    $('.validationAddTva').addClass('alert alert-danger');
                    $.each(response.errors, function(key, list_err) {
                        $('.validationAddTva').append('<li>' + list_err + '</li>');
                    });
    
                    setTimeout(() => {
                        $('.validationAddTva').fadeOut('slow', function() {
                            $(this).html("").removeClass('alert alert-danger').show();
                        });
                    }, 5000);
                }
                else if(response.status == 422)
                {
                    // Traitement du cas où une TVA avec le même nom ou valeur existe déjà
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
                else if (response.status == 404 || response.status == 500) {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function(xhr) {
                $('#BtnAddTva').prop('disabled', false).text('Sauvegarder');
                
                // Récupérer le message d'erreur personnalisé du serveur
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    new AWN().alert(xhr.responseJSON.message, { durations: { alert: 5000 } });
                } else {
                    // Fallback au message générique
                    new AWN().alert("Une erreur est survenue, veuillez réessayer.", { durations: { alert: 5000 } });
                }
            }
        });
    });

    $('#BtnUpdateTva').on('click', function(e) {
        e.preventDefault();
        
        let formData = new FormData($('#FormUpdateTva')[0]);
        formData.append('_token', csrf_token);
        formData.append('id', $(this).attr('data-value'));
       
        $('#BtnUpdateTva').prop('disabled', true).text('Mise à jour...');
    
        $.ajax({
            type: "POST",
            url: UpdateTva,
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(response) {
                $('#BtnUpdateTva').prop('disabled', false).text('Mettre à jour');
                if (response.status == 200) {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    $('#ModalEditTva').modal('hide');
                    $('.TableTvas').DataTable().ajax.reload();
                }  
                else if (response.status == 404) {
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                }
                else if (response.status == 400) {
                    $('.validationEditTva').html("");
                    $('.validationEditTva').addClass('alert alert-danger');
                    $.each(response.errors, function(key, list_err) {
                        $('.validationEditTva').append('<li>' + list_err + '</li>');
                    });
    
                    setTimeout(() => {
                        $('.validationEditTva').fadeOut('slow', function() {
                            $(this).html("").removeClass('alert alert-danger').show();
                        });
                    }, 5000);
                }  
                else if (response.status == 500) {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function() {
                $('#BtnUpdateTva').prop('disabled', false).text('Mettre à jour');
                new AWN().alert("Une erreur est survenue, veuillez réessayer.", { durations: { alert: 5000 } });
            }
        });
    });
    
});