define(['jquery'], function($) {
    return {
        init: function() {
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
    };
});
