$(document).ready(function () {
    // Store table instances for later reference
    var tmpAchatTable = null;
    var productTable = null;
    var achatMainTable = null;

    // Debug URLs to ensure they're defined correctly
    console.log('Debug URLs:');
    console.log('GetTmpAchatByFournisseur URL:', GetTmpAchatByFournisseur);
    console.log('getProduct URL:', getProduct);
    console.log('GetAchatList URL:', typeof GetAchatList !== 'undefined' ? GetAchatList : 'Not defined');

    $('.linkCallModalAddProduct').on('click', function(e) {
        $('#ModalAddProduct').modal("show");
        $('#ModalAddAchat').modal("hide");
    });
    
    // Initialize dependent dropdowns
    initializeDropdowns();

    // Initialize the main Achat table if it exists
    if ($('.TableAchat').length > 0 && typeof GetAchatList !== 'undefined') {
        initializeMainAchatTable();
    }

    function initializeMainAchatTable() {
        // Destroy if exists
        if ($.fn.DataTable.isDataTable('.TableAchat')) {
            $('.TableAchat').DataTable().destroy();
        }

        // Initialize the main Achat table
        achatMainTable = $('.TableAchat').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: GetAchatList,
                type: 'GET',
                error: function(xhr, textStatus, error) {
                    console.error('DataTables AJAX Error in main table:', error);
                    console.error('Status:', xhr.status);
                    console.error('Response Text:', xhr.responseText);
                }
            },
            columns: [
                { data: 'fournisseur', name: 'fournisseur' },
                { data: 'created_by', name: 'created_by' },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[2, 'desc']]
        });
    }

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

    function initializeTableTmpAchat(selector, IdFournisseur) {
        // Get the parent container for proper organization
        var tableContainer = $(selector).closest('.table-responsive');
        
        // Remove existing custom controls if any
        tableContainer.find('.dataTables-custom-controls').remove();
        
        // Properly destroy the DataTable if it exists
        if ($.fn.DataTable.isDataTable(selector)) {
            $(selector).DataTable().destroy();
            // Make sure we don't have duplicate wrappers
            tableContainer.find('.dataTables_wrapper').not(':first').remove();
        }
        
        // Create custom controls container above the table
        var customControls = $('<div class="dataTables-custom-controls row mb-2"></div>');
        
        // Add length control
        var lengthControl = $('<div class="col-md-6"></div>');
        lengthControl.append('<div class="d-flex align-items-center"><label class="me-2">Afficher</label>' +
                             '<select class="form-select form-select-sm custom-page-length me-2" style="width: auto;">' +
                             '<option value="10">10</option>' +
                             '<option value="25">25</option>' +
                             '<option value="50">50</option>' +
                             '<option value="100">100</option>' +
                             '</select>' +
                             '<span>éléments</span></div>');
        
        // Add search control
        var searchControl = $('<div class="col-md-6"></div>');
        searchControl.append('<div class="d-flex justify-content-end">' +
                             '<label class="me-2">Rechercher :</label>' +
                             '<input type="search" class="form-control form-control-sm custom-search-input" style="width: auto;">' +
                             '</div>');
        
        // Add controls to container
        customControls.append(lengthControl);
        customControls.append(searchControl);
        
        // Insert controls before the table
        tableContainer.prepend(customControls);
        
        // Initialize DataTable with alternative approach to avoid Ajax error
        $.ajax({
            url: GetTmpAchatByFournisseur,
            type: "GET",
            data: { id_fournisseur: IdFournisseur },
            dataType: "json",
            beforeSend: function() {
                // Show loading indicator
                $(selector).html('<tr><td colspan="5" class="text-center">Chargement...</td></tr>');
            },
            success: function(response) {
                // Clear loading
                $(selector).empty();
                
                // Check if we have valid data
                if (response && response.data) {
                    // Initialize DataTable with the data
                    tmpAchatTable = $(selector).DataTable({
                        data: response.data,
                        select: true,
                        processing: true,
                        serverSide: false,  // Client-side processing
                        destroy: true,
                        autoWidth: false,
                        dom: 'rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                        columns: [
                            { data: 'name', title: 'Produit', defaultContent: '' },
                            { data: 'price_achat', title: 'Prix achat', defaultContent: '' },
                            { data: 'qte', title: 'Quantité', defaultContent: '' },
                            { data: 'entreprise', title: 'Fournisseur', defaultContent: '' },
                            { data: 'action', title: 'Action', orderable: false, searchable: false, defaultContent: '' }
                        ],
                        rowCallback: function(row, data, index) {
                            if (data && data.id) {
                                $(row).attr('id', data.id);
                            }
                        },
                        language: {
                            "sInfo": "Affichage de _START_ à _END_ sur _TOTAL_ éléments",
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
                    
                    // Connect custom search input
                    tableContainer.find('.custom-search-input').on('keyup', function() {
                        tmpAchatTable.search(this.value).draw();
                    });
                    
                    // Connect custom page length
                    tableContainer.find('.custom-page-length').on('change', function() {
                        tmpAchatTable.page.len($(this).val()).draw();
                    });
                } else {
                    // If no data or invalid format, initialize empty table
                    tmpAchatTable = $(selector).DataTable({
                        data: [],
                        select: true,
                        processing: true,
                        serverSide: false,
                        destroy: true,
                        autoWidth: false,
                        dom: 'rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                        columns: [
                            { data: 'name', title: 'Produit', defaultContent: '' },
                            { data: 'price_achat', title: 'Prix achat', defaultContent: '' },
                            { data: 'qte', title: 'Quantité', defaultContent: '' },
                            { data: 'entreprise', title: 'Fournisseur', defaultContent: '' },
                            { data: 'action', title: 'Action', orderable: false, searchable: false, defaultContent: '' }
                        ],
                        language: {
                            "sInfo": "Affichage de _START_ à _END_ sur _TOTAL_ éléments",
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
                    
                    // Connect custom controls
                    tableContainer.find('.custom-search-input').on('keyup', function() {
                        tmpAchatTable.search(this.value).draw();
                    });
                    
                    tableContainer.find('.custom-page-length').on('change', function() {
                        tmpAchatTable.page.len($(this).val()).draw();
                    });
                }
            },
            error: function(xhr, textStatus, error) {
                console.error('DataTables AJAX Error:', error);
                console.error('Status:', xhr.status);
                console.error('Response Text:', xhr.responseText);
                
                // Initialize with empty data on error
                tmpAchatTable = $(selector).DataTable({
                    data: [],
                    select: true,
                    processing: true,
                    serverSide: false,
                    destroy: true,
                    autoWidth: false,
                    dom: 'rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                    columns: [
                        { data: 'name', title: 'Produit', defaultContent: '' },
                        { data: 'price_achat', title: 'Prix achat', defaultContent: '' },
                        { data: 'qte', title: 'Quantité', defaultContent: '' },
                        { data: 'entreprise', title: 'Fournisseur', defaultContent: '' },
                        { data: 'action', title: 'Action', orderable: false, searchable: false, defaultContent: '' }
                    ],
                    language: {
                        "sInfo": "Affichage de _START_ à _END_ sur _TOTAL_ éléments",
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
                
                // Connect custom controls
                tableContainer.find('.custom-search-input').on('keyup', function() {
                    tmpAchatTable.search(this.value).draw();
                });
                
                tableContainer.find('.custom-page-length').on('change', function() {
                    tmpAchatTable.page.len($(this).val()).draw();
                });
                
                // Show error message
                new AWN().warning('Unable to load data. Please try again.', {durations: {warning: 5000}});
            }
        });
    }

    $('#DropDown_fournisseur').on('change', function() {
        let Fournisseur = $(this).val();
        if (Fournisseur == 0) {
            new AWN().alert('Veuillez sélectionner un fournisseur', {durations: {success: 5000}});
            return false;
        }
    
        // Initialize the TmpAchat table
        initializeTableTmpAchat('.TableAmpAchat', Fournisseur);
    });

    function initializeTableProduct(selector, data) {
        // Get the parent container for proper organization
        var tableContainer = $(selector).closest('.table-responsive');
        
        // Remove existing custom controls if any
        tableContainer.find('.dataTables-custom-controls').remove();
        
        // Properly destroy the DataTable if it exists
        if ($.fn.DataTable.isDataTable(selector)) {
            $(selector).DataTable().destroy();
            // Make sure we don't have duplicate wrappers
            tableContainer.find('.dataTables_wrapper').not(':first').remove();
        }
        
        // Create custom controls container above the table
        var customControls = $('<div class="dataTables-custom-controls row mb-2"></div>');
        
        // Add length control
        var lengthControl = $('<div class="col-md-6"></div>');
        lengthControl.append('<div class="d-flex align-items-center"><label class="me-2">Afficher</label>' +
                             '<select class="form-select form-select-sm custom-page-length me-2" style="width: auto;">' +
                             '<option value="10">10</option>' +
                             '<option value="25">25</option>' +
                             '<option value="50">50</option>' +
                             '<option value="100">100</option>' +
                             '</select>' +
                             '<span>éléments</span></div>');
        
        // Add search control
        var searchControl = $('<div class="col-md-6"></div>');
        searchControl.append('<div class="d-flex justify-content-end">' +
                             '<label class="me-2">Rechercher :</label>' +
                             '<input type="search" class="form-control form-control-sm custom-search-input" style="width: auto;">' +
                             '</div>');
        
        // Add controls to container
        customControls.append(lengthControl);
        customControls.append(searchControl);
        
        // Insert controls before the table
        tableContainer.prepend(customControls);
        
        // Initialize DataTable
        productTable = $(selector).DataTable({
            select: true,
            data: data || [],
            destroy: true,
            processing: true,
            serverSide: false,
            autoWidth: false,
            dom: 'rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>', // Only table, info and pagination
            columns: [
                { data: 'name', title: 'Produit', defaultContent: '' },
                { data: 'quantite', title: 'Quantité', defaultContent: '' },
                { data: 'seuil', title: 'Seuil', defaultContent: '' },
                { data: 'price_achat', title: 'Prix achat', defaultContent: '' },
                { data: 'name_local', title: 'Local', defaultContent: '' }
            ],
            rowCallback: function(row, data, index) {
                if (data && data.id) {
                    $(row).attr('id', data.id); 
                }
            },
            language: {
                "sInfo": "Affichage de _START_ à _END_ sur _TOTAL_ éléments",
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
            },
            initComplete: function() {
                // Connect custom search input
                tableContainer.find('.custom-search-input').on('keyup', function() {
                    productTable.search(this.value).draw();
                });
                
                // Connect custom page length
                tableContainer.find('.custom-page-length').on('change', function() {
                    productTable.page.len($(this).val()).draw();
                });
            }
        });
    
        // Handle row click event to add item to TmpAchat
        $(selector + ' tbody').off('click', 'tr').on('click', 'tr', function(e) {
            e.preventDefault();
            let id = $(this).attr('id');
            if (!id) return; // Skip if ID is not set
            
            let Fournisseur = $('#DropDown_fournisseur').val();
            if (Fournisseur == 0) {
                new AWN().alert('Veuillez sélectionner un fournisseur', {durations: {success: 5000}});
                return false;
            }
    
            $.ajax({
                type: "POST",
                url: PostInTmpAchat,
                data: {
                    '_token': csrf_token,
                    'idproduit': id,
                    'id_fournisseur': Fournisseur,
                },
                dataType: "json",
                success: function(response) {
                    if (response.status == 200) {
                        new AWN().success(response.message, {durations: {success: 5000}});
                        
                        // Reinitialize TableAmpAchat after adding an item
                        initializeTableTmpAchat('.TableAmpAchat', Fournisseur);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error adding product to TmpAchat:', error);
                    new AWN().alert('Error adding product. Please try again.', {durations: {alert: 5000}});
                }
            });
        });
    }

    $('.input_products').on('keydown', function(e) {
        if (e.keyCode === 13) {
            let name_product = $(this).val().trim();
            let Fournisseur = $('#DropDown_fournisseur').val();
            if(Fournisseur == 0) {
                new AWN().alert('Veuillez sélectionner un fournisseur', {durations: {success: 5000}});
                return false;
            }
            $.ajax({
                type: "get",
                url: getProduct,
                data: {
                    product: name_product
                },
                dataType: "json",
                success: function(response) {
                    if (response.status == 200) {
                        initializeTableProduct('.TableProductAchat', response.data);
                        $('.input_products').val(""); 
                    } else {
                        new AWN().alert("Aucun produit trouvé.", {durations: {alert: 5000}});
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error searching for product:', error);
                    new AWN().alert('Error searching for product. Please try again.', {durations: {alert: 5000}});
                }
            });
        }
    });
    
    // Add Product Form Submission
    $('#BtnAddProduct').on('click', function(e) {
        e.preventDefault();
        
        // Get form data
        var formData = new FormData($('#FormAddProduct')[0]);
        
        $.ajax({
            type: "POST",
            url: "addProduct", // Make sure this URL is defined in your routes
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(response) {
                if (response.status == 200) {
                    // Reset form
                    $('#FormAddProduct')[0].reset();
                    
                    // Close modal
                    $('#ModalAddProduct').modal('hide');
                    
                    // Show success message
                    new AWN().success(response.message, {durations: {success: 5000}});
                    
                    // Reopen the purchase modal
                    $('#ModalAddAchat').modal('show');
                } else {
                    // Display validation errors
                    $('.validationAddProduct').html('');
                    $.each(response.errors, function(key, value) {
                        $('.validationAddProduct').append('<li class="text-danger">' + value + '</li>');
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error adding product:', error);
                
                // Try to parse error response
                try {
                    var errorResponse = JSON.parse(xhr.responseText);
                    
                    if (errorResponse.errors) {
                        $('.validationAddProduct').html('');
                        $.each(errorResponse.errors, function(key, value) {
                            $('.validationAddProduct').append('<li class="text-danger">' + value + '</li>');
                        });
                    } else {
                        new AWN().alert('Error adding product. Please try again.', {durations: {alert: 5000}});
                    }
                } catch (e) {
                    new AWN().alert('Error adding product. Please try again.', {durations: {alert: 5000}});
                }
            }
        });
    });
    
    // Update Local Form Submission
    $('#BtnUpdateLocal').on('click', function(e) {
        e.preventDefault();
        
        // Get form data
        var formData = new FormData($('#FormUpdateLocal')[0]);
        
        $.ajax({
            type: "POST",
            url: $('#FormUpdateLocal').attr('action'),
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(response) {
                if (response.status == 200) {
                    // Reset form
                    $('#FormUpdateLocal')[0].reset();
                    
                    // Close modal
                    $('#ModalEditLocal').modal('hide');
                    
                    // Show success message
                    new AWN().success(response.message, {durations: {success: 5000}});
                    
                    // Refresh the main table
                    if (achatMainTable) {
                        achatMainTable.ajax.reload();
                    }
                } else {
                    // Display validation errors
                    $('.validationEditLocal').html('');
                    $.each(response.errors, function(key, value) {
                        $('.validationEditLocal').append('<li class="text-danger">' + value + '</li>');
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error updating local:', error);
                
                // Try to parse error response
                try {
                    var errorResponse = JSON.parse(xhr.responseText);
                    
                    if (errorResponse.errors) {
                        $('.validationEditLocal').html('');
                        $.each(errorResponse.errors, function(key, value) {
                            $('.validationEditLocal').append('<li class="text-danger">' + value + '</li>');
                        });
                    } else {
                        new AWN().alert('Error updating. Please try again.', {durations: {alert: 5000}});
                    }
                } catch (e) {
                    new AWN().alert('Error updating. Please try again.', {durations: {alert: 5000}});
                }
            }
        });
    });
    
    // Add Local Form Submission (Finalizing purchase)
    $('#BtnAddLocal').on('click', function(e) {
        e.preventDefault();
        
        // Get the fournisseur
        let Fournisseur = $('#DropDown_fournisseur').val();
        if (Fournisseur == 0) {
            new AWN().alert('Veuillez sélectionner un fournisseur', {durations: {success: 5000}});
            return false;
        }
        
        // Check if there are items in the table
        if (tmpAchatTable && tmpAchatTable.data().count() === 0) {
            new AWN().alert('Veuillez ajouter au moins un produit', {durations: {success: 5000}});
            return false;
        }
        
        // Show confirmation dialog
        if (confirm('Êtes-vous sûr de vouloir valider cet achat?')) {
            $.ajax({
                type: "POST",
                url: "saveAchat", // Make sure this URL is defined in your routes
                data: {
                    '_token': csrf_token,
                    'id_fournisseur': Fournisseur
                },
                dataType: "json",
                success: function(response) {
                    if (response.status == 200) {
                        // Close modal
                        $('#ModalAddAchat').modal('hide');
                        
                        // Show success message
                        new AWN().success(response.message, {durations: {success: 5000}});
                        
                        // Refresh the main table
                        if (achatMainTable) {
                            achatMainTable.ajax.reload();
                        }
                    } else {
                        new AWN().alert(response.message, {durations: {alert: 5000}});
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error saving purchase:', error);
                    new AWN().alert('Error saving purchase. Please try again.', {durations: {alert: 5000}});
                }
            });
        }
    });
    
// Delete functionality
$(document).on('click', '.btn-delete-item', function(e) {
    e.preventDefault();
    
    let id = $(this).data('id');
    let url = $(this).data('url');
    
    if (!id || !url) {
        console.error('Missing id or url for delete operation');
        return;
    }
    
    if (confirm('Êtes-vous sûr de vouloir supprimer cet élément?')) {
        $.ajax({
            type: "POST",
            url: url,
            data: {
                '_token': csrf_token,
                'id': id
            },
            dataType: "json",
            success: function(response) {
                if (response.status == 200) {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    
                    // Refresh tables
                    if ($(e.target).closest('.TableAmpAchat').length) {
                        let Fournisseur = $('#DropDown_fournisseur').val();
                        initializeTableTmpAchat('.TableAmpAchat', Fournisseur);
                    } else if (achatMainTable) {
                        achatMainTable.ajax.reload();
                    }
                } else {
                    new AWN().alert(response.message, {durations: {alert: 5000}});
                }
            },
            error: function(xhr, status, error) {
                console.error('Error deleting item:', error);
                new AWN().alert('Error deleting item. Please try again.', {durations: {alert: 5000}});
            }
        });
    }
});

// Edit item in temporary achat table
$(document).on('click', '.edit-tmp-achat', function(e) {
    e.preventDefault();
    
    let id = $(this).data('id');
    if (!id) {
        console.error('Missing id for edit operation');
        return;
    }
    
    let currentQty = $(this).closest('tr').find('td:eq(2)').text().trim();
    
    // Use a prompt to get the new quantity
    let newQty = prompt('Modifier la quantité:', currentQty);
    
    if (newQty !== null && newQty !== '' && !isNaN(newQty) && parseFloat(newQty) > 0) {
        $.ajax({
            type: "POST",
            url: "updateTmpAchatQty", // Make sure this URL is defined in your routes
            data: {
                '_token': csrf_token,
                'id': id,
                'qte': newQty
            },
            dataType: "json",
            success: function(response) {
                if (response.status == 200) {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    
                    // Refresh the table
                    let Fournisseur = $('#DropDown_fournisseur').val();
                    initializeTableTmpAchat('.TableAmpAchat', Fournisseur);
                } else {
                    new AWN().alert(response.message, {durations: {alert: 5000}});
                }
            },
            error: function(xhr, status, error) {
                console.error('Error updating quantity:', error);
                new AWN().alert('Error updating quantity. Please try again.', {durations: {alert: 5000}});
            }
        });
    } else if (newQty !== null) {
        new AWN().alert('Veuillez saisir une quantité valide', {durations: {alert: 5000}});
    }
});

// View Achat Details
$(document).on('click', '.view-achat-details', function(e) {
    e.preventDefault();
    
    let id = $(this).data('id');
    if (!id) {
        console.error('Missing id for view details operation');
        return;
    }
    
    $.ajax({
        type: "GET",
        url: "viewAchatDetails/" + id, // Make sure this URL is defined in your routes
        dataType: "json",
        success: function(response) {
            if (response.status == 200) {
                // Create a modal to display details
                let detailsHtml = `
                <div class="modal fade" id="ModalAchatDetails" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Détails de l'achat</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p><strong>Fournisseur:</strong> ${response.achat.fournisseur}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Date:</strong> ${response.achat.created_at}</p>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Produit</th>
                                                <th>Prix d'achat</th>
                                                <th>Quantité</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>`;
                
                let totalAmount = 0;
                
                $.each(response.details, function(key, item) {
                    let itemTotal = item.price_achat * item.quantite;
                    totalAmount += itemTotal;
                    
                    detailsHtml += `
                        <tr>
                            <td>${item.product_name}</td>
                            <td>${item.price_achat}</td>
                            <td>${item.quantite}</td>
                            <td>${itemTotal.toFixed(2)}</td>
                        </tr>`;
                });
                
                detailsHtml += `
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                                <td><strong>${totalAmount.toFixed(2)}</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                            </div>
                        </div>
                    </div>
                </div>`;
                
                // Append modal to body and show it
                $('body').append(detailsHtml);
                $('#ModalAchatDetails').modal('show');
                
                // Remove modal from DOM when hidden
                $('#ModalAchatDetails').on('hidden.bs.modal', function() {
                    $(this).remove();
                });
            } else {
                new AWN().alert(response.message, {durations: {alert: 5000}});
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading details:', error);
            new AWN().alert('Error loading details. Please try again.', {durations: {alert: 5000}});
        }
    });
});

    $('#BtnSaveAchat').off('click').on('click', function(e) {
        e.preventDefault();
        let Fournisseur = $('#DropDown_fournisseur').val();
        if(Fournisseur == 0) {
            new AWN().alert('Veuillez sélectionner un fournisseur', {durations: {success: 5000}});
            return false;
        }
        $.ajax({
            type: "POST",
            url: StoreAchat,
            data:
            {
                id_fournisseur : Fournisseur,
                '_token'       : csrf_token
            },
            dataType: "json",
            success: function (response) 
            {
                if(response.status == 200)
                {
                    
                }
            }
        });
    });

});