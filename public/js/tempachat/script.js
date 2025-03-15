$(document).ready(function () {
    // Initialize DataTable for temp achats
    var tempAchatsTable = $('#temp_achats_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: getTempAchats_url,
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
            { data: 'nom_user', name: 'nom_user' },
            { data: 'id_achat', name: 'id_achat' },
            { data: 'nom_produit', name: 'nom_produit' },
            { data: 'qte', name: 'qte' },
            { data: 'price_achat', name: 'price_achat' },
            { data: 'subtotal', name: 'subtotal' },
            { data: 'total', name: 'total' },
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
    
    // Event handling for category change
    $('#id_categorie').on('change', function() {
        var categoryId = $(this).val();
        var productDropdown = $('#id_produit');
        
        // Reset product dropdown
        productDropdown.empty().append('<option value="">Sélectionner un produit</option>');
        
        if (!categoryId) {
            // If no category is selected, load all products
            $.ajax({
                url: window.location.href,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response && response.length > 0) {
                        $.each(response, function(key, product) {
                            productDropdown.append(
                                `<option value="${product.id}">${product.name} - ${parseFloat(product.price_achat).toFixed(2)}€</option>`
                            );
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Erreur de chargement des produits:", error);
                    new AWN().alert("Impossible de charger les produits", { durations: { alert: 5000 } });
                }
            });
        } else {
            // If a category is selected, load products for that category
            $.ajax({
                url: getProductsByCategory_url + "/" + categoryId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 200 && response.products.length > 0) {
                        $.each(response.products, function(key, product) {
                            productDropdown.append(
                                `<option value="${product.id}">${product.name} - ${parseFloat(product.price_achat).toFixed(2)}€</option>`
                            );
                        });
                    } else {
                        console.warn('Aucun produit trouvé');
                        new AWN().warning("Aucun produit trouvé pour cette catégorie", { durations: { warning: 5000 } });
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Erreur de chargement des produits:", error);
                    new AWN().alert("Impossible de charger les produits", { durations: { alert: 5000 } });
                }
            });
        }
    });
    
    // Add TempAchat button click
    $('#btn_add_temp_achat').on('click', function(e) {
        e.preventDefault();
        
        // Get form data
        var productId = $('#id_produit').val();
        var fournisseurId = $('#id_fournisseur').val();
        var qte = $('#temp_qte').val();
        
        // Validate data
        if (!productId) {
            new AWN().warning("Veuillez sélectionner un produit", { durations: { warning: 5000 } });
            return;
        }
        
        if (!fournisseurId) {
            new AWN().warning("Veuillez sélectionner un fournisseur", { durations: { warning: 5000 } });
            return;
        }
        
        if (!qte || qte < 1) {
            new AWN().warning("La quantité doit être supérieure à 0", { durations: { warning: 5000 } });
            return;
        }
        
        // Disable button during request
        $('#btn_add_temp_achat').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Ajout en cours...');
        
        // Send AJAX request
        $.ajax({
            type: "POST",
            url: addTempAchat_url,
            data: {
                id_produit: productId,
                id_fournisseur: fournisseurId,
                qte: qte,
                _token: csrf_token
            },
            dataType: "json",
            success: function(response) {
                $('#btn_add_temp_achat').prop('disabled', false).html('<i class="fa-solid fa-plus me-1"></i> Ajouter au panier');
                
                if (response.status == 200) {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    $('#temp_achats_table').DataTable().ajax.reload();
                    
                    // Reset quantity
                    $('#temp_qte').val(1);
                } else {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function(xhr) {
                $('#btn_add_temp_achat').prop('disabled', false).html('<i class="fa-solid fa-plus me-1"></i> Ajouter au panier');
                
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
        });
    });
    
    // Event delegation for increase quantity button
    $(document).on('click', '.increaseTempAchat', function(e) {
        e.preventDefault();
        
        var tempAchatId = $(this).data('id');
        
        $.ajax({
            type: "POST",
            url: increaseTempAchat_url,
            data: {
                id: tempAchatId,
                _token: csrf_token
            },
            dataType: "json",
            success: function(response) {
                if (response.status == 200) {
                    $('#temp_achats_table').DataTable().ajax.reload();
                } else {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function(xhr) {
                new AWN().alert("Une erreur est survenue", { durations: { alert: 5000 } });
            }
        });
    });
    
    // Event delegation for decrease quantity button
    $(document).on('click', '.decreaseTempAchat', function(e) {
        e.preventDefault();
        
        var tempAchatId = $(this).data('id');
        
        $.ajax({
            type: "POST",
            url: decreaseTempAchat_url,
            data: {
                id: tempAchatId,
                _token: csrf_token
            },
            dataType: "json",
            success: function(response) {
                if (response.status == 200) {
                    $('#temp_achats_table').DataTable().ajax.reload();
                } else {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function(xhr) {
                new AWN().alert("Une erreur est survenue", { durations: { alert: 5000 } });
            }
        });
    });
    
    // Event delegation for edit temp achat button
    $(document).on('click', '.editTempAchat', function(e) {
        e.preventDefault();
        
        var tempAchatId = $(this).data('id');
        
        // Disable edit button during loading
        $(this).prop('disabled', true);
        
        $.ajax({
            type: "GET",
            url: editTempAchat_url + "/" + tempAchatId,
            dataType: "json",
            success: function(response) {
                // Enable edit button
                $('.editTempAchat').prop('disabled', false);
                
                // Show edit modal
                $('#ModalEditTempAchat').modal("show");
                
                // Clear any previous validation errors
                $('.validationEditTempAchat').html("").removeClass('alert alert-danger');
                
                // Populate form with data
                $('#edit_id').val(response.id);
                $('#edit_qte').val(response.qte);
                
                // Set read-only fields
                if (response.product) {
                    $('#edit_product_name').val(response.product.name);
                }
                if (response.fournisseur) {
                    $('#edit_fournisseur_name').val(response.fournisseur.entreprise);
                }
            },
            error: function(xhr, status, error) {
                // Enable edit button
                $('.editTempAchat').prop('disabled', false);
                
                // Detailed error logging
                console.error("Erreur lors de la récupération de l'article:", {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
                
                // User-friendly error notification
                let errorMessage = "Erreur de chargement de l'article";
                
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
    
    // Update TempAchat button click
    $('#BtnUpdateTempAchat').on('click', function(e) {
        e.preventDefault();
        
        // Get form data
        var tempAchatId = $('#edit_id').val();
        var qte = $('#edit_qte').val();
        
        // Validate data
        if (!qte || qte < 1) {
            new AWN().warning("La quantité doit être supérieure à 0", { durations: { warning: 5000 } });
            return;
        }
        
        // Disable button during request
        $('#BtnUpdateTempAchat').prop('disabled', true).text('Mise à jour...');
        
        // Send AJAX request
        $.ajax({
            type: "POST",
            url: updateTempAchat_url,
            data: {
                id: tempAchatId,
                qte: qte,
                _token: csrf_token
            },
            dataType: "json",
            success: function(response) {
                $('#BtnUpdateTempAchat').prop('disabled', false).text('Mettre à jour');
                
                if (response.status == 200) {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    $('#ModalEditTempAchat').modal('hide');
                    $('#temp_achats_table').DataTable().ajax.reload();
                } else {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function(xhr) {
                $('#BtnUpdateTempAchat').prop('disabled', false).text('Mettre à jour');
                
                try {
                    var errorResponse = JSON.parse(xhr.responseText);
                    if (errorResponse) {
                        if (errorResponse.errors) {
                            $('.validationEditTempAchat').html("");
                            $('.validationEditTempAchat').addClass('alert alert-danger');
                            $.each(errorResponse.errors, function(key, list_err) {
                                $('.validationEditTempAchat').append('<li>' + list_err + '</li>');
                            });
                            
                            setTimeout(() => {
                                $('.validationEditTempAchat').fadeOut('slow', function() {
                                    $(this).html("").removeClass('alert alert-danger').show();
                                });
                            }, 5000);
                        } else if (errorResponse.message) {
                            new AWN().alert(errorResponse.message, { durations: { alert: 5000 } });
                        } else {
                            new AWN().alert("Une erreur est survenue", { durations: { alert: 5000 } });
                        }
                    } else {
                        new AWN().alert("Une erreur est survenue", { durations: { alert: 5000 } });
                    }
                } catch (e) {
                    new AWN().alert("Une erreur est survenue", { durations: { alert: 5000 } });
                }
            }
        });
    });
    
    // Event delegation for delete temp achat button
    $(document).on('click', '.deleteTempAchat', function(e) {
        e.preventDefault();
        
        var tempAchatId = $(this).data('id');
        
        // Confirm deletion
        let notifier = new AWN();
        
        let onOk = () => {
            $.ajax({
                type: "POST",
                url: deleteTempAchat_url,
                data: {
                    id: tempAchatId,
                    _token: csrf_token
                },
                dataType: "json",
                success: function(response) {
                    if (response.status == 200) {
                        new AWN().success(response.message, {durations: {success: 5000}});
                        $('#temp_achats_table').DataTable().ajax.reload();
                    } else {
                        new AWN().alert(response.message, { durations: { alert: 5000 } });
                    }
                },
                error: function(xhr) {
                    new AWN().alert("Une erreur est survenue", { durations: { alert: 5000 } });
                }
            });
        };
        
        let onCancel = () => {
            notifier.info('Suppression annulée');
        };
        
        notifier.confirm(
            'Voulez-vous vraiment supprimer cet article?',
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
    
    // Save achat button click
    $('#btn_save_achat').on('click', function(e) {
        e.preventDefault();
        
        var fournisseurId = $('#id_fournisseur').val();
        
        if (!fournisseurId) {
            new AWN().warning("Veuillez sélectionner un fournisseur", { durations: { warning: 5000 } });
            return;
        }
        
        // Confirm before saving
        let notifier = new AWN();
        
        let onOk = () => {
            // Disable button during request
            $('#btn_save_achat').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Traitement...');
            
            $.ajax({
                type: "POST",
                url: addAchat_url,
                data: {
                    id_fournisseur: fournisseurId,
                    _token: csrf_token
                },
                dataType: "json",
                success: function(response) {
                    $('#btn_save_achat').prop('disabled', false).html('<i class="fa-solid fa-save me-1"></i> Confirmer la commande');
                    
                    if (response.status == 200) {
                        notifier.success(response.message, {durations: {success: 5000}});
                        
                        // Redirect to achat list or detail page
                        setTimeout(function() {
                            window.location.href = "/achats";
                        }, 2000);
                    } else {
                        notifier.alert(response.message, { durations: { alert: 5000 } });
                    }
                },
                error: function(xhr) {
                    $('#btn_save_achat').prop('disabled', false).html('<i class="fa-solid fa-save me-1"></i> Confirmer la commande');
                    
                    try {
                        var errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse && errorResponse.message) {
                            notifier.alert(errorResponse.message, { durations: { alert: 5000 } });
                        } else {
                            notifier.alert("Une erreur est survenue", { durations: { alert: 5000 } });
                        }
                    } catch (e) {
                        notifier.alert("Une erreur est survenue", { durations: { alert: 5000 } });
                    }
                }
            });
        };
        
        let onCancel = () => {
            notifier.info('Opération annulée');
        };
        
        notifier.confirm(
            'Voulez-vous vraiment enregistrer cette commande?',
            onOk,
            onCancel,
            {
                labels: {
                    confirm: 'Confirmer',
                    cancel: 'Annuler'
                }
            }
        );
    });
});