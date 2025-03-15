$(document).ready(function() {
    // Initialize DataTable for ligne achats
    var ligneAchatsTable = $('.TableLigneAchats').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: getLigneAchats_url + '/' + achat_id,
            dataSrc: function(json) {
                return json.data;
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables error:', error, thrown);
                alert("Erreur de chargement des données");
            }
        },
        columns: [
            { data: 'product_name', name: 'product_name' },
            { data: 'code_article', name: 'code_article' },
            { data: 'price_achat', name: 'price_achat' },
            { data: 'qte', name: 'qte' },
            { data: 'tva_rate', name: 'tva_rate' },
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
        },
        footerCallback: function(row, data, start, end, display) {
            var api = this.api();
            
            // Calculate total
            var total = api
                .column(6)
                .data()
                .reduce(function(a, b) {
                    return parseFloat(a) + parseFloat(b.replace(' €', '').replace(',', '.'));
                }, 0);
                
            // Update total display
            $('#achat_total').text(total.toFixed(2) + ' €');
        }
    });
    
    // Add product to achat
    $('#BtnAddLigneAchat').on('click', function() {
        var productId = $('#add_idproduit').val();
        var quantity = $('#add_qte').val();
        
        if (!productId) {
            alert("Veuillez sélectionner un produit");
            return;
        }
        
        if (!quantity || quantity < 1) {
            alert("La quantité doit être supérieure à 0");
            return;
        }
        
        $.ajax({
            type: "POST",
            url: addLigneAchat_url,
            data: {
                idachat: achat_id,
                idproduit: productId,
                qte: quantity,
                _token: csrf_token,
            },
            dataType: "json",
            success: function(response) {
                if (response.status == 200) {
                    alert(response.message);
                    $('.TableLigneAchats').DataTable().ajax.reload();
                    
                    // Reset form
                    $('#add_idproduit').val('');
                    $('#add_qte').val(1);
                    $('#ModalAddLigneAchat').modal('hide');
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert("Erreur lors de l'ajout du produit");
            }
        });
    });
    
    // Update ligne achat quantity
    $('#BtnUpdateLigneAchat').on('click', function() {
        var ligneId = $('#edit_id').val();
        var quantity = $('#edit_qte').val();
        
        if (!quantity || quantity < 1) {
            alert("La quantité doit être supérieure à 0");
            return;
        }
        
        $.ajax({
            type: "POST",
            url: updateLigneAchat_url,
            data: {
                id: ligneId,
                qte: quantity,
                _token: csrf_token,
            },
            dataType: "json",
            success: function(response) {
                if (response.status == 200) {
                    alert(response.message);
                    $('.TableLigneAchats').DataTable().ajax.reload();
                    $('#ModalEditLigneAchat').modal('hide');
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert("Erreur lors de la mise à jour");
            }
        });
    });
    
    // Delete ligne achat
    $(document).on('click', '.deleteLigneAchat', function() {
        var ligneId = $(this).data('id');
        
        if (confirm('Voulez-vous vraiment supprimer cet article ?')) {
            $.ajax({
                type: "POST",
                url: deleteLigneAchat_url,
                data: {
                    id: ligneId,
                    _token: csrf_token,
                },
                dataType: "json",
                success: function(response) {
                    if (response.status == 200) {
                        alert(response.message);
                        $('.TableLigneAchats').DataTable().ajax.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    alert("Erreur lors de la suppression");
                }
            });
        }
    });
    
    // Edit ligne achat
    $(document).on('click', '.editLigneAchat', function() {
        var ligneId = $(this).data('id');
        var product = $(this).data('product');
        var quantity = $(this).data('qte');
        
        $('#edit_id').val(ligneId);
        $('#edit_product').text(product);
        $('#edit_qte').val(quantity);
        
        $('#ModalEditLigneAchat').modal('show');
    });
});