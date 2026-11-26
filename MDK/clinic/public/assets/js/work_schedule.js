// ============================================================
// work_schedule.js: страница "График работы"
// ============================================================
document.addEventListener('DOMContentLoaded', function () {

    // Сменили врача: сразу показываем его график
    var select = document.getElementById('doctor-select');
    if (select) {
        select.addEventListener('change', function () {
            document.getElementById('doctor-form').submit();
        });
    }

    var modal = document.getElementById('edit-modal');
    if (!modal) {
        return;
    }
    var baseUrl = modal.getAttribute('data-base-url');

    // Кнопка "Изменить" в строке смены
    document.querySelectorAll('.js-edit').forEach(function (button) {
        button.addEventListener('click', function () {
            var d = button.dataset;
            var hasApps = parseInt(d.apps, 10) > 0;

            document.getElementById('em-date').textContent = d.date;
            document.getElementById('em-start').value = d.start;
            document.getElementById('em-end').value = d.end;
            document.getElementById('em-minutes').value = d.minutes;
            document.getElementById('em-form').action = baseUrl + '/' + d.id + '/update';

            // Если на смену уже есть записи, менять её нельзя
            document.getElementById('em-warn').hidden = !hasApps;
            document.getElementById('em-save').disabled = hasApps;
            window.Modal.open(modal);
        });
    });
});
