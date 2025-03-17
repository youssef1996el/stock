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
    
   
        let Fournisseur = 0;
        var tabletmpAchat = new DataTable('.TableAmpAchat');

        $('#DropDown_fournisseur').on('change', function() 
        {
            Fournisseur = $('#DropDown_fournisseur').val();
            if (Fournisseur == 0) {
                new AWN().alert('Veuillez sélectionner un fournisseur', {durations: {success: 5000}});
                return false;
            }

        
        });




    // Function to initialize TableProduct DataTable
    function initializeTableProduct(selector, data) {
        if ($.fn.DataTable.isDataTable(selector)) {
            $(selector).DataTable().clear().destroy();
        }
    

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

        
    }

    $('.TableProductAchat').on('click','tbody tr',function(e)
    {
        e.preventDefault();
        let id = $(this).attr('id');
       
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
                    
                }
            }
        });
        
    });

    // Event listener for product input search
    $('.input_products').on('keydown', function(e) {
        if (e.keyCode === 13) {
            let name_product = $(this).val().trim();
            let Fournisseur = $('#DropDown_fournisseur').val();

            if (Fournisseur == 0) {
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
                        alert("Aucun produit trouvé.");
                    }
                }
            });
        }
    });

});