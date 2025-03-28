$(document).ready(function () {
    // Dynamic script and CSS loading
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

    // Initialize dependent dropdowns
    initializeDropdowns();

    // DataTable Initialization
    function initializeDataTable() {
        try {
            // Destroy existing DataTable if it exists
            if ($.fn.DataTable.isDataTable('.TableProducts')) {
                $('.TableProducts').DataTable().destroy();
            }
            
            // Initialize DataTable
            var tableProducts = $('.TableProducts').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: products_url,
                    dataSrc: function (json) {
                        if (json.data.length === 0) {
                            $('.paging_full_numbers').css('display', 'none');
                        }
                        return json.data;
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTables error:', error, thrown);
                        new AWN().alert("Erreur de chargement des données", { durations: { alert: 5000 } });
                    }
                },
                columns: [
                    { data: 'code_article', name: 'code_article' },
                    { data: 'name', name: 'name' },
                    { data: 'unite', name: 'unite' },
                    { data: 'categorie', name: 'categorie' },
                    { data: 'famille', name: 'famille' },
                    { data: 'emplacement', name: 'emplacement' },
                    { data: 'stock', name: 'stock' },
                    { data: 'price_achat', name: 'price_achat' },
                    { data: 'price_vente', name: 'price_vente' },
                    { data: 'taux_taxe', name: 'taux_taxe' },
                    { data: 'seuil', name: 'seuil' },
                    { data: 'code_barre', name: 'code_barre' },
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
            
            // Edit Product Handler
            $('.TableProducts tbody').on('click', '.editProduct', function(e) {
                e.preventDefault();
                var productId = $(this).attr('data-id');
                
                // Disable edit button during loading
                $(this).prop('disabled', true);
                
                $.ajax({
                    type: "GET",
                    url: editProduct_url + "/" + productId,
                    dataType: "json",
                    success: function(response) {
                        // Enable edit button
                        $('.editProduct').prop('disabled', false);
                        
                        // Detailed logging
                        console.log("Données du produit:", response);
                        
                        // Clear any previous dropdown options to prevent duplicates
                        $('#edit_id_subcategorie').empty().append('<option value="">Sélectionner une famille</option>');
                        $('#edit_id_rayon').empty().append('<option value="">Sélectionner un rayon</option>');
                        
                        // Show edit modal
                        $('#ModalEditProduct').modal("show");
                        
                        // Clear any previous validation errors
                        $('.validationEditProduct').html("").removeClass('alert alert-danger');
                        
                        // Populate basic product information
                        $('#edit_id').val(response.id);
                        $('#edit_name').val(response.name);
                        $('#edit_price_achat').val(response.price_achat);
                        $('#edit_price_vente').val(response.price_vente);
                        $('#edit_code_barre').val(response.code_barre);
                        
                        // Display code_article in a disabled field if you want to show it
                        if ($('#edit_code_article').length) {
                            $('#edit_code_article').val(response.code_article);
                        }
                        
                        // Set category dropdown
                        $('#edit_id_categorie').val(response.id_categorie);
                        
                        // Set local dropdown
                        $('#edit_id_local').val(response.id_local);
                        
                        // Load dependent dropdowns
                        loadSubcategories('#edit_id_categorie', '#edit_id_subcategorie', response.id_subcategorie);
                        loadRayons('#edit_id_local', '#edit_id_rayon', response.id_rayon);
                        
                        // Set stock fields if stock exists
                        if (response.stock) {
                            $('#edit_id_tva').val(response.stock.id_tva);
                            // Only set id_unite if it exists in the response
                            if (response.stock.id_unite) {
                                $('#edit_id_unite').val(response.stock.id_unite);
                            }
                            $('#edit_quantite').val(response.stock.quantite);
                            $('#edit_seuil').val(response.stock.seuil);
                        } else {
                            // Reset stock fields if no stock data
                            $('#edit_id_tva, #edit_quantite, #edit_seuil').val('');
                            if ($('#edit_id_unite').length > 0) {
                                $('#edit_id_unite').val('');
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        // Enable edit button
                        $('.editProduct').prop('disabled', false);
                        
                        // Detailed error logging
                        console.error("Erreur lors de la récupération du produit:", {
                            status: status,
                            error: error,
                            responseText: xhr.responseText
                        });
                        
                        // User-friendly error notification
                        let errorMessage = "Erreur de chargement du produit";
                        
                        try {
                            // Try to parse error response
                            var errorResponse = JSON.parse(xhr.responseText);
                            if (errorResponse && errorResponse.message) {
                                errorMessage = errorResponse.message;
                            }
                        } catch(e) {
                            // Use default error message if parsing fails
                        }
                        
                        // Show error notification
                        new AWN().alert(errorMessage, { 
                            durations: { alert: 5000 } 
                        });
                    }
                });
            });

            // Delete Product Handler
            $('.TableProducts tbody').on('click', '.deleteProduct', function(e) {
                e.preventDefault();
                var productId = $(this).attr('data-id');
                let notifier = new AWN();

                let onOk = () => {
                    $.ajax({
                        type: "POST",
                        url: deleteProduct_url,
                        data: {
                            id: productId,
                            _token: csrf_token,
                        },
                        dataType: "json",
                        success: function (response) {
                            if(response.status == 200) {
                                notifier.success(response.message, {durations: {success: 5000}});
                                $('.TableProducts').DataTable().ajax.reload();
                            } else {
                                notifier.alert(response.message, {durations: {alert: 5000}});
                            }
                        },
                        error: function(xhr) {
                            notifier.alert("Erreur lors de la suppression", { durations: { alert: 5000 } });
                        }
                    });
                };

                let onCancel = () => {
                    notifier.info('Suppression annulée');
                };

                notifier.confirm(
                    'Voulez-vous vraiment supprimer ce produit ?',
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
            console.error("Erreur d'initialisation du DataTable:", error);
            new AWN().alert("Erreur d'initialisation du tableau", { durations: { alert: 5000 } });
        }
    }
    
    // Load Subcategories Function
    function loadSubcategories(categorySelector, subcategorySelector, selectedValue = null) {
        var categoryId = $(categorySelector).val();
        var subcategorySelect = $(subcategorySelector);
        
        // Reset subcategory dropdown
        subcategorySelect.empty().append('<option value="">Sélectionner une famille</option>');
        
        if (!categoryId) {
            console.warn('Aucune catégorie sélectionnée');
            return;
        }

        $.ajax({
            url: getSubcategories_url + "/" + categoryId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log("Réponse des sous-catégories:", response);
                
                if (response.status === 200 && response.subcategories.length > 0) {
                    $.each(response.subcategories, function(key, subcategory) {
                        subcategorySelect.append(
                            `<option value="${subcategory.id}">${subcategory.name}</option>`
                        );
                    });
                    
                    // Set selected value if provided
                    if (selectedValue) {
                        subcategorySelect.val(selectedValue);
                    }
                } else {
                    console.warn('Aucune sous-catégorie trouvée');
                    new AWN().warning("Aucune famille trouvée pour cette catégorie", { durations: { warning: 5000 } });
                }
            },
            error: function(xhr, status, error) {
                console.error("Erreur de chargement des sous-catégories:", error);
                new AWN().alert("Impossible de charger les familles", { durations: { alert: 5000 } });
            }
        });
    }
    
    // Load Rayons Function
    function loadRayons(localSelector, rayonSelector, selectedValue = null) {
        var localId = $(localSelector).val();
        var rayonSelect = $(rayonSelector);
        
        // Reset rayon dropdown
        rayonSelect.empty().append('<option value="">Sélectionner un rayon</option>');
        
        if (!localId) {
            console.warn('Aucun local sélectionné');
            return;
        }

        $.ajax({
            url: getRayons_url + "/" + localId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log("Réponse des rayons:", response);
                
                if (response.status === 200 && response.rayons.length > 0) {
                    $.each(response.rayons, function(key, rayon) {
                        rayonSelect.append(
                            `<option value="${rayon.id}">${rayon.name}</option>`
                        );
                    });
                    
                    // Set selected value if provided
                    if (selectedValue) {
                        rayonSelect.val(selectedValue);
                    }
                } else {
                    console.warn('Aucun rayon trouvé');
                    new AWN().warning("Aucun rayon trouvé pour ce local", { durations: { warning: 5000 } });
                }
            },
            error: function(xhr, status, error) {
                console.error("Erreur de chargement des rayons:", error);
                new AWN().alert("Impossible de charger les rayons", { durations: { alert: 5000 } });
            }
        });
    }
    
    // Initialize Dropdowns
    function initializeDropdowns() {
        // Category change - load subcategories
        $('#id_categorie, #edit_id_categorie').on('change', function() {
            var targetCategory = $(this).attr('id') === 'id_categorie' 
                ? '#id_subcategorie' 
                : '#edit_id_subcategorie';
            
            loadSubcategories(
                '#' + $(this).attr('id'), 
                targetCategory
            );
        });
        
        // Local change - load rayons
        $('#id_local, #edit_id_local').on('change', function() {
            var targetLocal = $(this).attr('id') === 'id_local' 
                ? '#id_rayon' 
                : '#edit_id_rayon';
            
            loadRayons(
                '#' + $(this).attr('id'), 
                targetLocal
            );
        });
    }
    
    // Add Product Handler
    $('#BtnAddProduct').on('click', function(e) {
        e.preventDefault();
        
        let formData = new FormData($('#FormAddProduct')[0]);
        formData.append('_token', csrf_token);
    
        $('#BtnAddProduct').prop('disabled', true).text('Enregistrement...');
    
        $.ajax({
            type: "POST",
            url: addProduct_url,
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                $('#BtnAddProduct').prop('disabled', false).text('Sauvegarder');
                
                if(response.status == 200) {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    $('#ModalAddProduct').modal('hide');
                    $('.TableProducts').DataTable().ajax.reload();
                    $('#FormAddProduct')[0].reset();
                } else if(response.status == 400) {
                    $('.validationAddProduct').html("");
                    $('.validationAddProduct').addClass('alert alert-danger');
                    $.each(response.errors, function(key, list_err) {
                        $('.validationAddProduct').append('<li>' + list_err + '</li>');
                    });
                    
                    setTimeout(() => {
                        $('.validationAddProduct').fadeOut('slow', function() {
                            $(this).html("").removeClass('alert alert-danger').show();
                        });
                    }, 5000);
                } else if(response.status == 422) {
                    // Traitement spécifique pour le cas où un produit avec le même nom existe déjà
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                } else {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function(xhr) {
                $('#BtnAddProduct').prop('disabled', false).text('Sauvegarder');
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    new AWN().alert(xhr.responseJSON.message, { durations: { alert: 5000 } });
                } else {
                    try {
                        var errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse && errorResponse.message) {
                            new AWN().alert(errorResponse.message, { durations: { alert: 5000 } });
                        } else {
                            new AWN().alert("Une erreur est survenue", { durations: { alert: 5000 } });
                        }
                    } catch (e) {
                        new AWN().alert("Une erreur est survenue", { durations: { alert: 5000 } });
                    }
                }
            }
        });
    });

    // Update Product Handler
    $('#BtnUpdateProduct').on('click', function(e) {
        e.preventDefault();
        
        // Make sure we're using the correct form
        if ($('#FormUpdateProduct').length === 0) {
            console.error("Form #FormUpdateProduct not found!");
            new AWN().alert("Erreur: formulaire introuvable", { durations: { alert: 5000 } });
            return;
        }
        
        let formData = new FormData($('#FormUpdateProduct')[0]);
        formData.append('_token', csrf_token);
        formData.append('id', $('#edit_id').val());
        
        $('#BtnUpdateProduct').prop('disabled', true).text('Mise à jour...');
        
        $.ajax({
            type: "POST",
            url: updateProduct_url,
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(response) {
                $('#BtnUpdateProduct').prop('disabled', false).text('Mettre à jour');
                
                if (response.status == 200) {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    $('#ModalEditProduct').modal('hide');
                    $('.TableProducts').DataTable().ajax.reload();
                } else if (response.status == 400) {
                    $('.validationEditProduct').html("");
                    $('.validationEditProduct').addClass('alert alert-danger');
                    $.each(response.errors, function(key, list_err) {
                        $('.validationEditProduct').append('<li>' + list_err + '</li>');
                    });
                    
                    setTimeout(() => {
                        $('.validationEditProduct').fadeOut('slow', function() {
                            $(this).html("").removeClass('alert alert-danger').show();
                        });
                    }, 5000);
                } else if (response.status == 422) {
                    // Traitement spécifique pour le cas où un produit avec le même nom existe déjà
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                } else {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function(xhr) {
                $('#BtnUpdateProduct').prop('disabled', false).text('Mettre à jour');
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    new AWN().alert(xhr.responseJSON.message, { durations: { alert: 5000 } });
                } else {
                    try {
                        var errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse && errorResponse.message) {
                            new AWN().alert(errorResponse.message, { durations: { alert: 5000 } });
                        } else {
                            new AWN().alert("Une erreur est survenue", { durations: { alert: 5000 } });
                        }
                    } catch (e) {
                        new AWN().alert("Une erreur est survenue", { durations: { alert: 5000 } });
                    }
                }
            }
        });
    });
    // Initial dropdown population on page load
    $(document).ready(function() {
        // Populate subcategories if category is pre-selected
        if ($('#id_categorie').val()) {
            loadSubcategories('#id_categorie', '#id_subcategorie');
        }
        
        // Populate rayons if local is pre-selected
        if ($('#id_local').val()) {
            loadRayons('#id_local', '#id_rayon');
        }
    });
});