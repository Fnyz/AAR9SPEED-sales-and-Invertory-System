<?php
// Shared scripts for Tabler pages: jQuery, Tabler (includes Bootstrap's JS),
// CSRF header on AJAX posts, theme switch, notifications and toasts.
?>
<div class="toast-stack" id="toast-stack" aria-live="polite"></div>
<script src="scripts/jquery-3.6.3.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.6.1/dist/js/tabler.min.js"></script>
<script>
    $.ajaxSetup({
        beforeSend: function (xhr, settings) {
            if (settings.type && settings.type.toUpperCase() === 'POST') {
                xhr.setRequestHeader('X-CSRF-Token', $('meta[name="csrf-token"]').attr('content'));
            }
        }
    });

    // Small toast helper: showToast('Category added', 'success' | 'danger' | 'warning')
    function showToast(message, type) {
        var tone = type || 'success';
        var icon = tone === 'success' ? 'ti-check' : (tone === 'danger' ? 'ti-alert-circle' : 'ti-alert-triangle');
        var $toast = $('<div class="alert alert-' + tone + ' alert-dismissible shadow-sm mb-0" role="status"></div>');
        $toast.append($('<div class="d-flex align-items-center gap-2"></div>')
            .append('<i class="ti ' + icon + ' fs-2"></i>')
            .append($('<div></div>').text(message)));
        $toast.append('<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
        $('#toast-stack').append($toast);
        setTimeout(function () { $toast.fadeOut(200, function () { $toast.remove(); }); }, 4000);
    }

    $(document).ajaxError(function (event, jqxhr) {
        var message = 'Something went wrong. Please try again.';
        if (jqxhr.status === 422) {
            try { message = JSON.parse(jqxhr.responseText).error || message; } catch (e) {}
        } else if (jqxhr.status === 401) {
            location.href = 'login.php?timeout=1';
            return;
        } else if (jqxhr.status === 403) {
            try { message = JSON.parse(jqxhr.responseText).error; } catch (e) { message = null; }
            message = message || 'Your session expired. Reload the page and try again.';
        }
        showToast(message, 'danger');
    });

    $(document).on('click', '[data-theme-set]', function () {
        var theme = $(this).attr('data-theme-set');
        document.documentElement.setAttribute('data-bs-theme', theme);
        try { localStorage.setItem('aar9-theme', theme); } catch (e) {}
    });

    function notify() {
        $.post('ajax/notification.php', { view: '' }, function (data) {
            var html = (data.depleted || '') + (data.pending || '') + (data.inactive || '');
            $('#notification-list').html(html || '<li class="px-3 py-2 text-secondary">No stock alerts right now.</li>');
            if (Number(data.unseen) > 0) {
                $('#pendings').text(data.unseen).prop('hidden', false);
            } else {
                $('#pendings').prop('hidden', true);
            }
            if (data.relay) relayAlerts(data.relay);
        }, 'json');
    }

    // NOTIFY_VIA_BROWSER (hosts that block outgoing connections): post waiting
    // alerts to the Discord webhook from this browser, then tell the server.
    // Sent as form data in "no-cors" mode, which Discord accepts without a CORS
    // check; the reply can't be read, so a message counts as sent once posted.
    function relayAlerts(relay) {
        if (!window.fetch || !window.FormData) return;
        var sent = [];
        // One at a time, so messages arrive in Discord in the order they were made.
        relay.items.reduce(function (chain, item) {
            return chain.then(function () {
                var form = new FormData();
                form.append('payload_json', JSON.stringify({
                    username: 'AAR9SPEED',
                    content: item.content,
                    allowed_mentions: { parse: [] }
                }));
                return fetch(relay.webhook, { method: 'POST', mode: 'no-cors', body: form })
                    .then(function () { sent.push(item.id); }, function () {});
            });
        }, Promise.resolve()).then(function () {
            if (sent.length) {
                $.post('ajax/notification.php', { relay_done: 1, token: relay.token, ids: sent.join(',') });
            }
        });
    }

    $(notify);

    function errorText(xhr) {
        try { return JSON.parse(xhr.responseText).error || 'Something went wrong. Please try again.'; }
        catch (e) { return 'Something went wrong. Please try again.'; }
    }

    // Dialog forms: <form data-ajax-form action="ajax/…" data-success="Saved {name}.">
    // Uses the browser's own field checks, posts the named fields, shows a server
    // error inside the form (.form-error), and reloads with the success message.
    $(document).on('submit', 'form[data-ajax-form]', function (event) {
        event.preventDefault();
        var form = this, $form = $(form), $error = $form.find('.form-error');
        $error.prop('hidden', true);
        if (!form.checkValidity()) {
            $form.addClass('was-validated');
            $form.find(':invalid').first().trigger('focus');
            return;
        }
        var $submit = $form.find('[type="submit"]').prop('disabled', true);
        $.ajax({ url: $form.attr('action'), method: 'POST', global: false, data: $form.serialize() })
            .done(function () {
                var message = String($form.data('success') || 'Saved.').replace(/\{(\w+)\}/g, function (m, field) {
                    return $.trim($form.find('[name="' + field + '"]').val());
                });
                reloadWith(message);
            })
            .fail(function (xhr) {
                $error.text(errorText(xhr)).prop('hidden', false);
                $submit.prop('disabled', false);
            });
    });
    $(document).on('hidden.bs.modal', '.modal', function () {
        $(this).find('form').removeClass('was-validated').find('.form-error').prop('hidden', true);
    });

    // Inline delete confirm in table rows: .row-actions holds [data-row-ask];
    // .row-confirm holds [data-row-cancel] and [data-row-delete data-action data-key].
    $(document).on('click', '[data-row-ask]', function () {
        var $cell = $(this).closest('td');
        $cell.find('.row-actions').addClass('d-none');
        $cell.find('.row-confirm').removeClass('d-none').addClass('d-flex');
    });
    $(document).on('click', '[data-row-cancel]', function () {
        var $cell = $(this).closest('td');
        $cell.find('.row-confirm').addClass('d-none').removeClass('d-flex');
        $cell.find('.row-actions').removeClass('d-none');
    });
    $(document).on('click', '[data-row-delete]', function () {
        var $button = $(this), $row = $button.closest('tr');
        var data = { action: $button.data('action') };
        data[$button.data('key')] = $row.data('id');
        $button.prop('disabled', true);
        $.ajax({ url: 'ajax/operation.php', method: 'POST', global: false, data: data })
            .done(function () { reloadWith('Deleted ' + $row.attr('data-name') + '.'); })
            .fail(function (xhr) {
                showToast(errorText(xhr), 'danger');
                $button.prop('disabled', false);
                $row.find('[data-row-cancel]').trigger('click');
            });
    });

    // Client-side table search: <input data-table-search="#rows"> filters rows by data-search.
    $(document).on('input', '[data-table-search]', function () {
        var query = $.trim($(this).val()).toLowerCase(), target = $(this).data('table-search'), shown = 0;
        $(target).find('tr').each(function () {
            var match = query === '' || String($(this).attr('data-search')).indexOf(query) !== -1;
            $(this).prop('hidden', !match);
            if (match) shown++;
        });
        $($(this).data('empty')).prop('hidden', shown > 0);
    });

    // Auto sign-out after SESSION_IDLE_MINUTES without keyboard or mouse use.
    // Activity is shared between tabs (localStorage), so working in one tab
    // keeps the others signed in. The server enforces the same limit.
    (function () {
        var idleMs = <?php echo (function_exists('session_idle_minutes') ? session_idle_minutes() : 30) * 60000 ?>;
        var warnMs = 60000, pingEvery = 60000, lastPing = Date.now(), localLast = Date.now(), $warning = null;
        if (!<?php echo json_encode(isset($_SESSION['userId'])) ?>) return; // not signed in

        function lastActive() {
            var shared = 0;
            try { shared = Number(localStorage.getItem('aar9-last-active')) || 0; } catch (e) {}
            return Math.max(localLast, shared);
        }
        function markActive() {
            localLast = Date.now();
            try { localStorage.setItem('aar9-last-active', String(localLast)); } catch (e) {}
            if ($warning) { $warning.remove(); $warning = null; }
            if (localLast - lastPing > pingEvery) {
                lastPing = localLast;
                $.ajax({ url: 'ajax/keepalive.php', method: 'POST', global: false })
                    .fail(function (xhr) { if (xhr.status === 401) location.href = 'login.php?timeout=1'; });
            }
        }
        var throttle = 0;
        $(document).on('mousemove keydown click scroll touchstart', function () {
            if (Date.now() - throttle > 5000) { throttle = Date.now(); markActive(); }
        });
        markActive();

        setInterval(function () {
            var left = idleMs - (Date.now() - lastActive());
            if (left <= 0) {
                location.href = 'Model/logout.php?timeout=1';
            } else if (left <= warnMs && !$warning) {
                $warning = $('<div class="alert alert-warning shadow-sm mb-0" role="alert"></div>')
                    .append($('<div class="d-flex align-items-center gap-2 mb-2"></div>')
                        .append('<i class="ti ti-clock-exclamation fs-2"></i>')
                        .append('<div>You will be signed out in a minute because there has been no activity.</div>'))
                    .append($('<button type="button" class="btn btn-sm btn-warning w-100">Stay signed in</button>')
                        .on('click', function () { lastPing = 0; markActive(); }));
                $('#toast-stack').append($warning);
            } else if (left > warnMs && $warning) {
                $warning.remove(); $warning = null; // another tab was used
            }
        }, 10000);
    })();

    // Reload the page and show a message once it is back.
    function reloadWith(message) {
        try { sessionStorage.setItem('aar9-flash', message); } catch (e) {}
        location.reload();
    }
    $(function () {
        var message = null;
        try { message = sessionStorage.getItem('aar9-flash'); sessionStorage.removeItem('aar9-flash'); } catch (e) {}
        if (message) showToast(message, 'success');
    });
</script>
<?php if (!empty($_SESSION['flash'])) : $flashMsg = $_SESSION['flash']; unset($_SESSION['flash']); ?>
    <script>$(function () { showToast(<?php echo json_encode($flashMsg['message']) ?>, <?php echo json_encode($flashMsg['type']) ?>); });</script>
<?php endif; ?>
