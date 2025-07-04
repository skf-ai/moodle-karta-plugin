define(['jquery'], function($) {
    function setupCreditsButtons() {
        $('a[data-action="enable"]').on('click', function(e) {
            e.preventDefault();
            var name = $(this).data('name');
            var credits = prompt('Enter credits for ' + name + ':', '0');
            if (credits !== null) {
                window.location = $(this).attr('href') + '&credits=' + parseInt(credits, 10);
            }
        });

        $('a[data-action="addcredits"]').on('click', function(e) {
            e.preventDefault();
            var name = $(this).data('name');
            var credits = prompt('Enter credits to add for ' + name + ':', '0');
            if (credits !== null) {
                window.location = $(this).attr('href') + '&credits=' + parseInt(credits, 10);
            }
        });
    }

    function addFilterInputs($table) {
        var $thead = $table.find('thead');
        var $filterRow = $('<tr class="filter-row"></tr>');
        $thead.find('th').each(function(i) {
            $(this).addClass('sortable');
            if (i < 3) {
                $filterRow.append($('<th>').append(
                    $('<input>', {type: 'text', 'data-column': i, class: 'filter-input'})));
            } else {
                $filterRow.append($('<th>'));
            }
        });
        $thead.append($filterRow);
    }

    function filterRows() {
        var $table = $('#chatbot-user-table');
        var filters = [];
        $table.find('input.filter-input').each(function() {
            filters[$(this).data('column')] = $(this).val().toLowerCase();
        });
        $table.find('tbody tr').each(function() {
            var $tds = $(this).children('td');
            var visible = true;
            for (var i = 0; i < filters.length; i++) {
                if (filters[i] && $tds.eq(i).text().toLowerCase().indexOf(filters[i]) === -1) {
                    visible = false;
                    break;
                }
            }
            $(this).toggle(visible);
        });
    }

    function sortTable(col) {
        var $table = $('#chatbot-user-table');
        var $tbody = $table.find('tbody');
        var rows = $tbody.find('tr').toArray();
        var asc = !$table.data('sortdir') || $table.data('sortcol') !== col || $table.data('sortdir') === 'desc';

        rows.sort(function(a, b) {
            var A = $(a).children('td').eq(col).text();
            var B = $(b).children('td').eq(col).text();
            if ($.isNumeric(A) && $.isNumeric(B)) {
                A = parseFloat(A);
                B = parseFloat(B);
            } else {
                A = A.toLowerCase();
                B = B.toLowerCase();
            }
            if (A < B) {return asc ? -1 : 1;}
            if (A > B) {return asc ? 1 : -1;}
            return 0;
        });

        $.each(rows, function(i, row) {
            $tbody.append(row);
        });
        $table.data('sortdir', asc ? 'asc' : 'desc');
        $table.data('sortcol', col);
    }

    function setupSortingFiltering() {
        var $table = $('#chatbot-user-table');
        if (!$table.length) {
            return;
        }
        addFilterInputs($table);
        $table.find('input.filter-input').on('keyup change', filterRows);
        $table.find('th.sortable').on('click', function() {
            sortTable($(this).index());
        });
    }

    return {
        init: function() {
            setupCreditsButtons();
            setupSortingFiltering();
        }
    };
});
