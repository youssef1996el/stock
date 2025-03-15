$(document).ready(function () {
    // Initialize DataTable for ligne achats
    var ligneAchatsTable = $('.TableLigneAchats').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: getLigneAchats_url,
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
            { data: 'product_name', name: 'product_name' },
            { data: 'price_achat', name: 'price_achat' },
            { data: 'qte', name: 'qte' },
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
    
    // Update status button click
    $('#BtnUpdateStatus').on('click', function(e) {
        e.preventDefault();
        
        // Get status value
        var status = $('#status').val();
        var achatId = window.location.pathname.split('/').pop();
        
        // If status is "Reçu", confirm with user
        if (status === 'Reçu') {
            let notifier = new AWN();
            
            let onOk = () => {
                updateStatus(achatId, status);
            };
            
            let onCancel = () => {
                notifier.info('Opération annulée');
            };
            
            notifier.confirm(
                'Marquer cet achat comme reçu va mettre à jour le stock. Êtes-vous sûr de vouloir continuer?',
                onOk,
                onCancel,
                {
                    labels: {
                        confirm: 'Confirmer',
                        cancel: 'Annuler'
                    }
                }
            );
        } else {
            updateStatus(achatId, status);
        }
    });
    
    // Function to update status
    function updateStatus(id, status) {
        $('#BtnUpdateStatus').prop('disabled', true).text('Mise à jour...');
        
        $.ajax({
            type: "POST",
            url: updateAchat_url,
            data: {
                id: id,
                status: status,
                _token: csrf_token
            },
            dataType: "json",
            success: function(response) {
                $('#BtnUpdateStatus').prop('disabled', false).text('Mettre à jour');
                
                if (response.status == 200) {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    
                    // Reload page to update UI based on new status
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function(xhr) {
                $('#BtnUpdateStatus').prop('disabled', false).text('Mettre à jour');
                
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
    }
    
    // Add ligne achat button click
    $('#BtnAddLigneAchat').on('click', function(e) {
        e.preventDefault();
        
        // Get form data
        var productId = $('#idproduit').val();
        var quantity = $('#qte').val();
        var achatId = window.location.pathname.split('/').pop();
        
        // Validate form data
        if (!productId) {
            new AWN().warning("Veuillez sélectionner un produit", { durations: { warning: 5000 } });
            return;
        }
        
        if (!quantity || quantity < 1) {
            new AWN().warning("La quantité doit être supérieure à 0", { durations: { warning: 5000 } });
            return;
        }
        
        // Disable button during request
        $('#BtnAddLigneAchat').prop('disabled', true).text('Ajout...');
        
        $.ajax({
            type: "POST",
            url: addLigneAchat_url,
            data: {
                idachat: achatId,
                idproduit: productId,
                qte: quantity,
                _token: csrf_token
            },
            dataType: "json",
            success: function(response) {
                $('#BtnAddLigneAchat').prop('disabled', false).text('Ajouter');
                
                if (response.status == 200) {
                    new AWN().success(response.message, {durations: {success: 5000}});
                    
                    // Reset form
                    $('#idproduit').val('');
                    $('#qte').val(1);
                    
                    // Reload table
                    $('.TableLigneAchats').DataTable().ajax.reload();
                } else {
                    new AWN().alert(response.message, { durations: { alert: 5000 } });
                }
            },
            error: function(xhr) {
                $('#BtnAddLigneAchat').prop('disabled', false).text('Ajouter');
                
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
    
    // Event delegation for delete ligne achat button
    $(document).on('click', '.deleteLigneAchat', function(e) {
        e.preventDefault();
        
        var ligneAchatId = $(this).data('id');
        let notifier = new AWN();
        
        let onOk = () => {
            $.ajax({
                type: "POST",
                url: deleteLigneAchat_url,
                data: {
                    id: ligneAchatId,
                    _token: csrf_token
                },
                dataType: "json",
                success: function(response) {
                    if (response.status == 200) {
                        notifier.success(response.message, {durations: {success: 5000}});
                        $('.TableLigneAchats').DataTable().ajax.reload();
                    } else {
                        notifier.alert(response.message, { durations: { alert: 5000 } });
                    }
                },
                error: function(xhr) {
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
            notifier.info('Suppression annulée');
        };
        
        notifier.confirm(
            'Voulez-vous vraiment supprimer ce produit de l\'achat?',
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
    
    // Receive products button click
    $('#BtnReceiveProducts').on('click', function(e) {
        e.preventDefault();
        
        var achatId = window.location.pathname.split('/').pop();
        let notifier = new AWN();
        
        let onOk = () => {
            // Disable button during request
            $('#BtnReceiveProducts').prop('disabled', true).text('Traitement...');
            
            $.ajax({
                type: "POST",
                url: receiveProducts_url,
                data: {
                    achat_id: achatId,
                    _token: csrf_token
                },
                dataType: "json",
                success: function(response) {
                    $('#BtnReceiveProducts').prop('disabled', false).text('Marquer comme reçu');
                    
                    if (response.status == 200) {
                        notifier.success(response.message, {durations: {success: 5000}});
                        
                        // Reload page to update UI based on new status
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        notifier.alert(response.message, { durations: { alert: 5000 } });
                    }
                },
                error: function(xhr) {
                    $('#BtnReceiveProducts').prop('disabled', false).text('Marquer comme reçu');
                    
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
            'Marquer cet achat comme reçu va mettre à jour le stock. Êtes-vous sûr de vouloir continuer?',
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