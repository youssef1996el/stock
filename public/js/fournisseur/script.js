$(document).ready(function () {

    $(function () {
        if ($.fn.DataTable.isDataTable('.TableFournisseurs')) {
            $('.TableFournisseurs').DataTable().destroy();
        }
        initializeDataTable('.TableFournisseurs', fournisseurs);
        
        function initializeDataTable(selector, url) {
            var tableFournisseurs = $(selector).DataTable({
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
                    { data: 'entreprise', name: 'entreprise' },
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
            $(selector + ' tbody').on('click', '.editFournisseur', function(e)
            {
                e.preventDefault();
                $('#ModalEditFournisseur').modal("show");
                var IdFournisseur = $(this).attr('data-id');
                
                $.ajax({
                    type: "GET",
                    url: EditFournisseur + '/' + IdFournisseur,
                    dataType: "json",
                    success: function(response) {
                        $('#entreprise').val(response.entreprise);
                        $('#Telephone').val(response.Telephone);
                        $('#Email').val(response.Email);
                        $('#BtnUpdateFournisseur').attr('data-value', IdFournisseur);
                    },
                    error: function() {
                        new AWN().alert("Erreur lors de la récupération des données", { durations: { alert: 5000 } });
                    }
                });
            });

            $(selector + ' tbody').on('click', '.deleteFournisseur', function(e)
            {
                e.preventDefault();
                var IdFournisseur = $(this).attr('data-id');
                let notifier = new AWN();

                let onOk = () => {
                    $.ajax({
                        type: "post",
                        url: DeleteFournisseur,
                        data: 
                        {
                            id: IdFournisseur,
                            _token: csrf_token,
                        },
                        dataType: "json",
                        success: function (response) 
                        {
                            if(response.status == 200)
                            {
                                new AWN().success(response.message, {durations: {success: 5000}});
                                $('.TableFournisseurs').DataTable().ajax.reload();
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
                    'Êtes-vous sûr de vouloir supprimer ce fournisseur ?',
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
        $('#phone_fournisseur, .phone_fournisseur_edit').on('input', function() {
            var number = $(this).val().replace(/[^\d]/g, ''); // إزالة أي أحرف غير رقمية
    
            if (number.length <= 10) {
                number = number.replace(/(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/, "$1-$2-$3-$4-$5");
            } else {
                number = number.substring(0, 10).replace(/(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/, "$1-$2-$3-$4-$5");
            }
    
            $(this).val(number);
        });
    }
    
    
    $(phoneFormatter);
    
    $('#BtnAddFournisseur').on('click', function(e) {
        e.preventDefault();
        
        let formData = new FormData($('#FormAddFournisseur')[0]);
        formData.append('_token', csrf_token);
    
        $('#BtnAddFournisseur').prop('disabled', true).text('Enregistrement...');
    
        $.ajax({
            type: "POST",
            url: AddFournisseur,
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) 
            {
                $('#BtnAddFournisseur').prop('disabled', false).text('Sauvegarder');
                if(response.status == 200)
                {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    $('#ModalAddFournisseur').modal('hide');
                    $('.TableFournisseurs').DataTable().ajax.reload();
                    $('#FormAddFournisseur')[0].reset();
                }  
                else if(response.status == 404)
                {
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                }
                else if(response.status == 400)
                {
                    $('.validationAddFournisseur').html("");
                    $('.validationAddFournisseur').addClass('alert alert-danger');
                    $.each(response.errors, function(key, list_err) {
                        $('.validationAddFournisseur').append('<li>' + list_err + '</li>');
                    });
    
                    setTimeout(() => {
                        $('.validationAddFournisseur').fadeOut('slow', function() {
                            $(this).html("").removeClass('alert alert-danger').show();
                        });
                    }, 5000);
                }
                else if (response.status == 422) {
                    // Traitement spécifique pour le cas où un fournisseur existe déjà
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
                else if (response.status == 404 || response.status == 500) {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function(xhr) {
                $('#BtnAddFournisseur').prop('disabled', false).text('Sauvegarder');
                
                // Récupérer le message d'erreur personnalisé du serveur
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    new AWN().alert(xhr.responseJSON.message, { durations: { alert: 5000 } });
                } else {
                    // Fallback au message générique seulement si aucun message personnalisé n'est disponible
                    new AWN().alert("Une erreur est survenue, veuillez réessayer.", { durations: { alert: 5000 } });
                }
            }
        });
    });

    $('#BtnUpdateFournisseur').on('click', function(e) {
        e.preventDefault();
        
        let formData = new FormData($('#FormUpdateFournisseur')[0]);
        formData.append('_token', csrf_token);
        formData.append('id', $(this).attr('data-value'));
       
        $('#BtnUpdateFournisseur').prop('disabled', true).text('Mise à jour...');
    
        $.ajax({
            type: "POST",
            url: UpdateFournisseur,
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(response) {
                $('#BtnUpdateFournisseur').prop('disabled', false).text('Mettre à jour');
                if (response.status == 200) {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    $('#ModalEditFournisseur').modal('hide');
                    $('.TableFournisseurs').DataTable().ajax.reload();
                }  
                else if (response.status == 404) {
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                }
                else if (response.status == 400) {
                    $('.validationEditFournisseur').html("");
                    $('.validationEditFournisseur').addClass('alert alert-danger');
                    $.each(response.errors, function(key, list_err) {
                        $('.validationEditFournisseur').append('<li>' + list_err + '</li>');
                    });
    
                    setTimeout(() => {
                        $('.validationEditFournisseur').fadeOut('slow', function() {
                            $(this).html("").removeClass('alert alert-danger').show();
                        });
                    }, 5000);
                }
                else if (response.status == 422) {
                    // Traitement spécifique pour le cas où un fournisseur avec le même nom existe déjà
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
                else if (response.status == 500) {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function(xhr) {
                $('#BtnUpdateFournisseur').prop('disabled', false).text('Mettre à jour');
                
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
    
});