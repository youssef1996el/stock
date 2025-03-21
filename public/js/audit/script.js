$(document).ready(function () {
    
    /**
     * Initialize DataTable
     */
    if ($.fn.DataTable.isDataTable('.TableAudits')) {
        $('.TableAudits').DataTable().destroy();
    }
    
    var tableAudits = $('.TableAudits').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: auditUrl,
            type: 'GET',
            data: function(d) {
                // Récupérer les valeurs des filtres
                var modelType = $('#modelFilter').val();
                var userId = $('#userFilter').val();
                var eventType = $('#eventFilter').val();
                
                // S'assurer que nous n'envoyons que les filtres non vides
                if (modelType) {
                    d.model_type = modelType;
                }
                
                if (userId) {
                    d.user_id = userId;
                }
                
                if (eventType) {
                    d.event = eventType;
                }
                
                // Ajouter la plage de dates si sélectionnée
                if ($('#dateRangeFilter').val()) {
                    var dates = $('#dateRangeFilter').data('daterangepicker');
                    if (dates && dates.startDate && dates.endDate) {
                        d.start_date = dates.startDate.format('YYYY-MM-DD');
                        d.end_date = dates.endDate.format('YYYY-MM-DD');
                    }
                }
            },
            dataSrc: function (json) {
                if (json.data.length === 0) {
                    $('.paging_full_numbers').css('display', 'none');
                }
                return json.data;
            },
            error: function(xhr, error, thrown) {
                console.log('DataTables error: ' + error + ' ' + thrown);
                console.log(xhr);
                new AWN().alert("Une erreur est survenue lors du chargement des données.", { durations: { alert: 5000 } });
            }
        },
        columns: [
            { 
                data: 'model_type', 
                name: 'model_type',
                render: function(data) {
                    return '<span class="badge bg-secondary">' + data + '</span>';
                }
            },
            { data: 'model_name', name: 'model_name' },
            { 
                data: 'event', 
                name: 'event',
                render: function(data) {
                    const labels = {
                        'created': '<span class="badge bg-success">Création</span>',
                        'updated': '<span class="badge bg-info">Modification</span>',
                        'deleted': '<span class="badge bg-danger">Suppression</span>',
                        'restored': '<span class="badge bg-warning">Restauration</span>'
                    };
                    
                    return labels[data] || '<span class="badge bg-secondary">' + data + '</span>';
                }
            },
            { data: 'user_name', name: 'user_name' },
            { 
                data: 'changes', 
                name: 'changes',
                render: function(data, type, row) {
                    // Changed to a direct link to the details page
                    return '<a href="' + auditUrl + '/details/' + row.id + '" class="btn btn-sm btn-info">' +
                           '<i class="fa fa-eye"></i> Voir détails</a>';
                }
            },
            { 
                data: 'created_at', 
                name: 'created_at',
                render: function(data) {
                    // Format date to French format
                    const date = new Date(data);
                    return date.toLocaleDateString('fr-FR') + ' ' + 
                           date.toLocaleTimeString('fr-FR');
                }
            }
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
        },
        order: [[5, 'desc']], // Sort by date descending
        responsive: true,
    });
    
    /**
     * Initialize Date Range Picker
     */
    if ($('#dateRangeFilter').length) {
        $('#dateRangeFilter').daterangepicker({
            opens: 'left',
            autoUpdateInput: false,
            showDropdowns: true,
            ranges: {
                'Aujourd\'hui': [moment(), moment()],
                'Hier': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Les 7 derniers jours': [moment().subtract(6, 'days'), moment()],
                'Les 30 derniers jours': [moment().subtract(29, 'days'), moment()],
                'Ce mois': [moment().startOf('month'), moment().endOf('month')],
                'Le mois dernier': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            locale: {
                format: 'DD/MM/YYYY',
                applyLabel: 'Appliquer',
                cancelLabel: 'Annuler',
                fromLabel: 'Du',
                toLabel: 'Au',
                customRangeLabel: 'Période personnalisée',
                daysOfWeek: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
                monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
                firstDay: 1
            }
        });
        
        // When a date range is selected
        $('#dateRangeFilter').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
            tableAudits.ajax.reload();
        });
        
        // When date range selection is canceled
        $('#dateRangeFilter').on('cancel.daterangepicker', function() {
            $(this).val('');
            tableAudits.ajax.reload();
        });
        
        // Make readonly
        $('#dateRangeFilter').attr('readonly', 'readonly');
    }
    
    /**
     * Apply filters when changed
     */
    $('#modelFilter').on('change', function() {
        tableAudits.ajax.reload();
    });
    
    $('#userFilter').on('change', function() {
        tableAudits.ajax.reload();
    });
    
    $('#eventFilter').on('change', function() {
        tableAudits.ajax.reload();
    });
    
    /**
     * Clear filters button
     */
    $('#clearFilters').on('click', function() {
        $('#modelFilter').val('');
        $('#userFilter').val('');
        $('#eventFilter').val('');
        $('#dateRangeFilter').val('');
        
        tableAudits.ajax.reload();
    });
});