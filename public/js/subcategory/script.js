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
            if ($.fn.DataTable.isDataTable('.TableSubCategories')) {
                $('.TableSubCategories').DataTable().destroy();
            }
            
            var tableSubCategories = $('.TableSubCategories').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: subcategories,
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
                    { data: 'category_name', name: 'category_name' },
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
            $('.TableSubCategories tbody').on('click', '.editSubCategory', function(e) {
                e.preventDefault();
                var IdSubCategory = $(this).attr('data-id');
                
                $.ajax({
                    type: "GET",
                    url: editSubCategory + "/" + IdSubCategory,
                    dataType: "json",
                    success: function(response) {
                        $('#ModalEditSubCategory').modal("show");
                        $('#name').val(response.name);
                        $('#id_categorie').val(response.id_categorie);
                        $('#BtnUpdateSubCategory').attr('data-value', IdSubCategory);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching subcategory:", error);
                        new AWN().alert("Une erreur est survenue, veuillez réessayer.", { durations: { alert: 5000 } });
                    }
                });
            });

            // Handle delete button click
            $('.TableSubCategories tbody').on('click', '.deleteSubCategory', function(e) {
                e.preventDefault();
                var IdSubCategory = $(this).attr('data-id');
                let notifier = new AWN();

                let onOk = () => {
                    $.ajax({
                        type: "post",
                        url: DeleteSubCategory,
                        data: {
                            id: IdSubCategory,
                            _token: csrf_token,
                        },
                        dataType: "json",
                        success: function (response) {
                            if(response.status == 200) {
                                new AWN().success(response.message, {durations: {success: 5000}});
                                $('.TableSubCategories').DataTable().ajax.reload();
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
                    'Êtes-vous sûr de vouloir supprimer cette sous-catégorie ?',
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
    
    // Add SubCategory
    $('#BtnAddSubCategory').on('click', function(e) {
        e.preventDefault();
        
        let formData = new FormData($('#FormAddSubCategory')[0]);
        formData.append('_token', csrf_token);

        $('#BtnAddSubCategory').prop('disabled', true).text('Enregistrement...');

        $.ajax({
            type: "POST",
            url: AddSubCategory,
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                $('#BtnAddSubCategory').prop('disabled', false).text('Sauvegarder');
                
                if(response.status == 200) {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    $('#ModalAddSubCategory').modal('hide');
                    $('.TableSubCategories').DataTable().ajax.reload();
                    $('#FormAddSubCategory')[0].reset();
                } else if(response.status == 409) {
                    // Handle already exists case
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                } else if(response.status == 404) {
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                } else if(response.status == 400) {
                    $('.validationAddSubCategory').html("");
                    $('.validationAddSubCategory').addClass('alert alert-danger');
                    $.each(response.errors, function(key, list_err) {
                        $('.validationAddSubCategory').append('<li>' + list_err + '</li>');
                    });
                    
                    setTimeout(() => {
                        $('.validationAddSubCategory').fadeOut('slow', function() {
                            $(this).html("").removeClass('alert alert-danger').show();
                        });
                    }, 5000);
                } else if (response.status == 500) {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function(xhr) {
                $('#BtnAddSubCategory').prop('disabled', false).text('Sauvegarder');
                
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

    // Update SubCategory
    $('#BtnUpdateSubCategory').on('click', function(e) {
        e.preventDefault();
        
        var IdSubCategory = $(this).attr('data-value');
        
        let formData = new FormData();
        formData.append('_token', csrf_token);
        formData.append('id', IdSubCategory);
        formData.append('name', $('#name').val());
        formData.append('id_categorie', $('#id_categorie').val());
        
        $('#BtnUpdateSubCategory').prop('disabled', true).text('Mise à jour...');
        
        $.ajax({
            type: "POST",
            url: UpdateSubCategory,
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(response) {
                $('#BtnUpdateSubCategory').prop('disabled', false).text('Mettre à jour');
                
                if (response.status == 200) {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    $('#ModalEditSubCategory').modal('hide');
                    $('.TableSubCategories').DataTable().ajax.reload();
                } else if (response.status == 409) {
                    // Handle already exists case
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                } else if (response.status == 404) {
                    new AWN().warning(response.message, {durations: {warning: 5000}});
                } else if (response.status == 400) {
                    $('.validationEditSubCategory').html("");
                    $('.validationEditSubCategory').addClass('alert alert-danger');
                    $.each(response.errors, function(key, list_err) {
                        $('.validationEditSubCategory').append('<li>' + list_err + '</li>');
                    });
                    
                    setTimeout(() => {
                        $('.validationEditSubCategory').fadeOut('slow', function() {
                            $(this).html("").removeClass('alert alert-danger').show();
                        });
                    }, 5000);
                } else {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function(xhr) {
                $('#BtnUpdateSubCategory').prop('disabled', false).text('Mettre à jour');
                
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