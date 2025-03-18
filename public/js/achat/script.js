$(document).ready(function () {
    let Fournisseur = 0;
    $('.linkCallModalAddProduct').on('click', function(e) {
        $('#ModalAddProduct').modal("show");
        $('#ModalAddAchat').modal("hide");
    });

    function GetTotalTmpByForunisseurAndUserScript(Fournisseur)
    {
        $.ajax({
            type: "get",
            url: GetTotalTmpByForunisseurAndUser,
            data: {
                'id_fournisseur' : Fournisseur,
            },
            dataType: "json",
            success: function (response) {
                if(response.status == 200)
                {
                    $('.TotalByFournisseurAndUser').text(response.total + " DH");

                }
            }
        });
    }
    
    // Initialize dependent dropdowns
    initializeDropdowns();

    // Keep track of active DataTables to prevent duplication
    let activeDataTables = {
        tmpAchat: null,
        productSearch: null
    };

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
        // Properly destroy DataTable if it exists
        if (activeDataTables.tmpAchat) {
            activeDataTables.tmpAchat.destroy();
            activeDataTables.tmpAchat = null;
        }
    
        // Reinitialize DataTable
        activeDataTables.tmpAchat = $(selector).DataTable({
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
                    console.error('Error occurred: ' + error);
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

        $(selector + ' tbody').off('click','tr .EditTmp');

        $(selector + ' tbody').on('click','tr .EditTmp',function(e)
        {
            e.preventDefault();
            
            let IdTmp = $(this).attr('data-id');
            let Qtetmp = $(this).closest('tr').find('td:eq(2)').text();
            $('#ModalEditQteTmp').modal('show');
            $('#BtnUpdateQteTmp').attr('data-id',IdTmp); 
            $('#QteTmp').val(Qtetmp);   
        });


        $(selector + ' tbody').on('click','tr .DeleteTmp',function(e)
        {
            e.preventDefault();
            
            let IdTmp = $(this).attr('data-id');
            $.ajax({
                type: "POST",
                url: DeleteRowsTmpAchat,
                data: 
                {
                    '_token' : csrf_token,
                    'id'     : IdTmp,
                },
                dataType: "json",
                success: function (response) {
                    if(response.status == 200){
                        new AWN().success(response.message, {durations: {success: 5000}});
                        initializeTableTmpAchat('.TableAmpAchat', Fournisseur);
                        GetTotalTmpByForunisseurAndUserScript(Fournisseur);
                    }
                }
            });
        });


        
        
        return activeDataTables.tmpAchat;
    }

  
    $('#DropDown_fournisseur').on('change', function() {
         Fournisseur = $('#DropDown_fournisseur').val();
        if (Fournisseur == 0) {
            new AWN().alert('Veuillez sélectionner un fournisseur', {durations: {success: 5000}});
            return false;
        }
    
        // Initialize or refresh the TmpAchat table
        initializeTableTmpAchat('.TableAmpAchat', Fournisseur);
        GetTotalTmpByForunisseurAndUserScript(Fournisseur);
    });

    function initializeTableProduct(selector, data) {
        // Properly destroy DataTable if it exists
        if (activeDataTables.productSearch) {
            activeDataTables.productSearch.destroy();
            activeDataTables.productSearch = null;
        }
    
        // Initialize DataTable
        activeDataTables.productSearch = $(selector).DataTable({
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
    
        // Remove any existing event handlers before adding new ones
        $(selector + ' tbody').off('click', 'tr');
        
        // Handle row click event to add item to TmpAchat
        $(selector + ' tbody').on('click', 'tr', function(e) {
            e.preventDefault();
            let id = $(this).attr('id');
            let Fournisseur = $('#DropDown_fournisseur').val();
            
            if (!id || id === '') {
                console.warn('No ID found for this row');
                return;
            }
            
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
                        
                        // Refresh the TmpAchat table
                        initializeTableTmpAchat('.TableAmpAchat', Fournisseur);
                        GetTotalTmpByForunisseurAndUserScript(Fournisseur);
                    } else {
                        new AWN().alert(response.message || 'Une erreur est survenue', {durations: {alert: 5000}});
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error adding product:", error);
                    new AWN().alert("Impossible d'ajouter le produit", {durations: {alert: 5000}});
                }
            });
        });
        
        return activeDataTables.productSearch;
    }
            
    // Product search functionality
    $('.input_products').on('keydown', function(e) {
        if (e.keyCode === 13) {
            e.preventDefault(); // Prevent form submission
            
            let name_product = $(this).val().trim();
            if (name_product === '') {
                new AWN().warning('Veuillez saisir un nom de produit', {durations: {warning: 5000}});
                return false;
            }
            
            let Fournisseur = $('#DropDown_fournisseur').val();
            if (Fournisseur == 0) {
                new AWN().alert('Veuillez sélectionner un fournisseur', {durations: {alert: 5000}});
                return false;
            }
            
            $.ajax({
                type: "GET",
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
                        new AWN().info("Aucun produit trouvé.", {durations: {info: 5000}});
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error searching for product:", error);
                    new AWN().alert("Erreur lors de la recherche", {durations: {alert: 5000}});
                }
            });
        }
    });

    function initializeTableAchatDataTable() {
        try {
            if ($.fn.DataTable.isDataTable('.TableAchat')) {
                $('.TableAchat').DataTable().destroy();
            }
            
            var TableAchat = $('.TableAchat').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: Achat,
                    dataSrc: function (json) {
                        setTimeout(() => {
                            if (json.data.length === 0) {
                                $('.paging_full_numbers').hide();
                            }
                        }, 100);
                        return json.data;
                    },
                    error: function(xhr, error, thrown) {
                        console.log('DataTables error: ' + error + ' ' + thrown);
                        console.log(xhr);
                    }
                },
                columns: [
                    { data: 'entreprise'   , name: 'entreprise' },
                    { data: 'total'         , name: 'total' },
                    { data: 'status'        , name: 'status' },
                    { data: 'name'          , name: 'name ' },
                    { data: 'created_at'    , name: 'created_at' },
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

        } catch (error) {
            console.error("Error initializing DataTable:", error);
        }
    }
    initializeTableAchatDataTable();
    
   

    $('#BtnSaveAchat').on('click',function(e)
    {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: StoreAchat,
            data: 
            {
                '_token'       : csrf_token,
                id_fournisseur : Fournisseur,
            },
            dataType: "json",
            success: function (response) {
                if(response.status == 200){
                    new AWN().success(response.message, {durations: {success: 5000}});
                    initializeTableAchatDataTable();
                    $('#ModalAddAchat').modal("hide");

                }
            }
        });
    });

    $('#BtnUpdateQteTmp').off('click').on('click',function(e)
    {
        e.preventDefault();
        let Qte = $('#QteTmp').val();
        let id  = $(this).attr('data-id');
        if(Qte <= 0)
        {
            new AWN().alert("La quantité doit être supérieure à zéro", {durations: {alert: 5000}});
            return false;
        }
        $('#BtnUpdateQteTmp').prop('disabled', true).text('Enregistrement...');
        $.ajax({
            type: "POST",
            url: UpdateQteTmp,
            data: 
            {
                '_token'  : csrf_token,
                'qte'     : Qte,
                'id'      : id,
            },
            dataType: "json",
            success: function (response) {
                $('#BtnUpdateQteTmp').prop('disabled', false).text('Sauvegarder');
                if(response.status == 200){
                    new AWN().success(response.message, {durations: {success: 5000}});
                    initializeTableTmpAchat('.TableAmpAchat', Fournisseur);
                    GetTotalTmpByForunisseurAndUserScript(Fournisseur);
                    $('#ModalEditQteTmp').modal('hide');
                    
                }
                else if(response.status == 400)
                {
                  
                    $('.validationUpdateQteTmp').html("");
                    $('.validationUpdateQteTmp').addClass('alert alert-danger');
                    $.each(response.errors, function(key, list_err) {
                        $('.validationUpdateQteTmp').append('<li>' + list_err + '</li>');
                    });
                }
            },
            error: function(xhr, status, error) {
                
                new AWN().alert("Impossible modifier quantité", {durations: {alert: 5000}});
            }
        });
    });
});