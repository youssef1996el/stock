$(document).ready(function () {

    $(function () {
        if ($.fn.DataTable.isDataTable('.TableUsers')) {
            $('.TableUsers').DataTable().destroy();
        }
        initializeDataTable('.TableUsers', users);
        
        function initializeDataTable(selector, url) {
            var tableUsers = $(selector).DataTable({
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
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                   
                    { data: 'roles', name: 'roles' }, // ✅ إضافة عمود الأدوار
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
            $(selector + ' tbody').on('click', '.editUser', function(e)
            {
                e.preventDefault();
                $('#ModalEditUser').modal("show");
                var IdUser          = $(this).attr('data-id');
                var name             = $(this).closest('tr').find('td:eq(0)').text();
                var email            = $(this).closest('tr').find('td:eq(1)').text();
                $('#name').val(name);
                $('#email').val(email);
                $('#BtnUpdateUser').attr('data-value',IdUser);
            });

            $(selector + ' tbody').on('click', '.deleteuser', function(e)
            {
                e.preventDefault();
                var IdUser = $(this).attr('data-id');
                let notifier = new AWN();

                let onOk = () => {
                   
                    
                    $.ajax({
                        type: "post",
                        url: DeleteUser,
                        data: 
                        {
                            id : IdUser,
                            _token: csrf_token,
                        },
                        dataType: "json",
                        success: function (response) 
                        {
                            if(response.status == 200)
                            {
                                new AWN().success(response.message, {durations: {success: 5000}});
                                $('.TableUsers').DataTable().ajax.reload();
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
                    'Êtes-vous sûr de vouloir supprimer cet utilisateur ?',
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
        $('#phone, #phoneAdd').on('input', function() {
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
    
    
    $('#BtnADDUser').on('click',function(e)
    {
        e.preventDefault();
        
        let formData = new FormData($('#FormAddUser')[0]);
        formData.append('_token', csrf_token);

        $('#BtnADDUser').prop('disabled', true).text('Enregistrement...');

        
        $.ajax({
            type: "POST",
            url: Adduser,
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) 
            {
                $('#BtnADDUser').prop('disabled', false).text('Sauvegarder');
                if(response.status == 200)
                {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    $('#ModalAddUser').modal('hide');
                    $('.TableUsers').DataTable().ajax.reload();
                    $('#FormAddUser')[0].reset();
                }  
                else if(response.status == 404)
                {
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                }
                else if(response.status == 400)
                {
                   /*  alert(231);
                    $.each(response.errors, function(key, list_err) {
                        new AWN().warning(list_err, {durations: {warning: 5000}});
                    }); */
                    $('.validationAddUser').html("");
                    $('.validationAddUser').addClass('alert alert-danger');
                    $.each(response.errors, function(key, list_err) {
                        $('.validationAddUser').append('<li>' + list_err + '</li>');
                    });
    
                    setTimeout(() => {
                        $('.validationAddUser').fadeOut('slow', function() {
                            $(this).html("").removeClass('alert alert-danger').show();
                        });
                    }, 5000);
                }  
                else if (response.status == 404 || response.status == 500) {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function() {
                $('#BtnADDUser').prop('disabled', false).text('Sauvegarder');
                new AWN().alert("Une erreur est survenue, veuillez réessayer.", { durations: { alert: 5000 } });
            }
        });
    });





    $('#BtnUpdateUser').on('click', function(e) {
        e.preventDefault();
        
        let formData = new FormData($('#FormUpdateUser')[0]);
        formData.append('_token', csrf_token);
        formData.append('id', $(this).attr('data-value')); // Add method as PUT for updating
       
        $('#BtnUpdateUser').prop('disabled', true).text('Mise à jour...');
    
        $.ajax({
            type: "POST",
            url: UpdateUser,  // UpdateUser should be the URL for updating the user
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(response) {
                $('#BtnUpdateUser').prop('disabled', false).text('Mettre à jour');
                if (response.status == 200) {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    $('#ModalEditUser').modal('hide');
                    $('.TableUsers').DataTable().ajax.reload();
                }  
                else if (response.status == 404) {
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                }
                else if (response.status == 400) {
                    $('.validationUpdateUser').html("");
                    $('.validationUpdateUser').addClass('alert alert-danger');
                    $.each(response.errors, function(key, list_err) {
                        $('.validationUpdateUser').append('<li>' + list_err + '</li>');
                    });
    
                    setTimeout(() => {
                        $('.validationUpdateUser').fadeOut('slow', function() {
                            $(this).html("").removeClass('alert alert-danger').show();
                        });
                    }, 5000);
                }  
                else if (response.status == 500) {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function() {
                $('#BtnUpdateUser').prop('disabled', false).text('Mettre à jour');
                new AWN().alert("Une erreur est survenue, veuillez réessayer.", { durations: { alert: 5000 } });
            }
        });
    });
    
});