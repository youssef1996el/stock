$(document).ready(function () {

    $('.linkCallModalAddProduct').on('click',function(e)
    {
        $('#ModalAddProduct').modal("show");
        $('#ModalAddAchat').modal("hide");
    });
    // Initialize dependent dropdowns
    initializeDropdowns();


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
    
    function initializeTableTmpAchat(selector, IdFournisseur) {
        // First, completely destroy any existing DataTable
        if ($.fn.DataTable.isDataTable(selector)) {
            $(selector).DataTable().destroy();
            // Remove any existing DataTable wrappers to prevent duplication
            $(selector + '_wrapper').remove();
        }

        // Clean up any remnants that might cause duplication
        $(selector).closest('.table-responsive').find('.dataTables_filter, .dataTables_length, .dataTables_paginate, .dataTables_info').remove();
    
        // Reinitialize DataTable with a clean slate
        $(selector).DataTable({
            select: true,
            processing: true,
            serverSide: false,
            destroy: true,
            autoWidth: false,
            ajax: {
                url: GetTmpAchatByFournisseur,
                data: { id_fournisseur: IdFournisseur },
                dataType: 'json',
                type: 'GET',
                error: function(xhr, error, code) {
                    console.log('Error occurred: ' + error);
                }
            },
            columns: [
                { data: 'name', title: 'Produit' },
                { data: 'price_achat', title: 'Prix achat' },
                { data: 'qte', title: 'Quantité' },
                { data: 'entreprise', title: 'Fournisseur' },
                { data: 'action', title: 'Action', orderable: false, searchable: false }
            ],
            rowCallback: function(row, data, index) {
                $(row).attr('id', data.id);
            },
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
    }
    
    $('#DropDown_fournisseur').on('change', function() {
        let Fournisseur = $('#DropDown_fournisseur').val();
        if (Fournisseur == 0) {
            new AWN().alert('Veuillez sélectionner un fournisseur', {durations: {success: 5000}});
            return false;
        }
        
        // Initialize the TmpAchat table
        initializeTableTmpAchat('.TableAmpAchat', Fournisseur);
    });
    
    function initializeTableProduct(selector, data) {
        // First, completely destroy any existing DataTable
        if ($.fn.DataTable.isDataTable(selector)) {
            $(selector).DataTable().destroy();
            // Remove any existing DataTable wrappers to prevent duplication
            $(selector + '_wrapper').remove();
        }

        // Clean up any remnants that might cause duplication
        $(selector).closest('.table-responsive').find('.dataTables_filter, .dataTables_length, .dataTables_paginate, .dataTables_info').remove();
    
        // Initialize DataTable
        $(selector).DataTable({
            select: true,
            data: data,
            destroy: true,
            processing: true,
            serverSide: false,
            autoWidth: false,
            columns: [
                { data: 'name', title: 'Produit' },
                { data: 'quantite', title: 'Quantité' },
                { data: 'seuil', title: 'Seuil' },
                { data: 'price_achat', title: 'Prix achat' },
                { data: 'name_local', title: 'Local' }
            ],
            rowCallback: function(row, data, index) {
                $(row).attr('id', data.id); 
            },
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
    
        // Handle row click event to add item to TmpAchat
        $(selector + ' tbody').off('click', 'tr').on('click', 'tr', function(e) {
            e.preventDefault();
            let id = $(this).attr('id');
            let Fournisseur = $('#DropDown_fournisseur').val();
    
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
                }
            });
        });
    }

    $('.input_products').on('keydown', function(e) {
        if (e.keyCode === 13) {
            let name_product = $(this).val().trim();
            let Fournisseur = $('#DropDown_fournisseur').val();
            if(Fournisseur == 0) {
                new AWN().alert('Please selected fournisseur', {durations: {success: 5000}});
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
                        alert("No products found.");
                    }
                }
            });
        }
    });
    
    // Event handlers for edit and delete functionality
    $(document).on('click', '.edit-tmp-achat', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        let currentQty = $(this).closest('tr').find('td:eq(2)').text().trim();
        
        let newQty = prompt('Modifier la quantité:', currentQty);
        
        if (newQty !== null && newQty !== '' && !isNaN(newQty) && parseFloat(newQty) > 0) {
            $.ajax({
                type: "POST",
                url: "updateTmpAchatQty",
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
    
    $(document).on('click', '.btn-delete-item', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        let url = $(this).data('url');
        
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
                        
                        // Refresh the table
                        let Fournisseur = $('#DropDown_fournisseur').val();
                        initializeTableTmpAchat('.TableAmpAchat', Fournisseur);
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