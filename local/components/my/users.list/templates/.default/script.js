BX.ready(function() {
    $(document).on('click', 'a.export-button', function(event) {
        event.preventDefault();

        BX.ajax.runComponentAction(
            'my:users.list',
            'export',
            {
                mode: 'class',
                data: {
                    type: $(this).data('type'),
                }
            }
        ).then(function(response) {
            if (response.status === 'success') {
                window.open(response.data);
            }
        });

    });
});
