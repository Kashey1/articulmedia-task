BX.ready(function() {

    $(document).on('click', 'a#users-export-csv', function(event) {
        event.preventDefault();

        BX.ajax.runComponentAction(
            'my:users.list',
            'exportCsv',
            {
                mode: 'class'
            }
        ).then(function(response) {
            if (response.status === 'success') {
                window.open(response.data);
            }
        });

    });

    $(document).on('click', 'a#users-export-xml', function(event) {
        event.preventDefault();

        BX.ajax.runComponentAction(
            'my:users.list',
            'exportXml',
            {
                mode: 'class'
            }
        ).then(function(response) {
            if (response.status === 'success') {
                window.open(response.data);
            }
        });
    });
    
});
