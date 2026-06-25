<script src="scripts/jquery-3.6.3.js"></script>
<script>
    $.ajaxSetup({
        beforeSend: function(xhr, settings) {
            if (settings.type && settings.type.toUpperCase() === 'POST') {
                xhr.setRequestHeader('X-CSRF-Token', $('meta[name="csrf-token"]').attr('content'));
            }
        }
    });

    $(document).ajaxError(function(event, jqxhr) {
        if (jqxhr.status === 422) {
            try {
                var resp = JSON.parse(jqxhr.responseText);
                swal({ title: 'Validation Error', text: resp.error, icon: 'error', button: 'OK' });
            } catch(e) {}
        }
    });
</script>
