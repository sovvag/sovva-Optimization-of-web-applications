// ============================================================
// staff.js: всплывающие уведомления администратора
// в правом нижнем углу о новых заявках на запись.
// Раз в 10 секунд спрашиваем у сервера, нет ли новых заявок.
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    var body = document.body;
    var notifyUrl = body.getAttribute('data-notify-url');
    if (!notifyUrl) {
        return;
    }
    var statusUrl = body.getAttribute('data-status-url');
    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    var area = document.createElement('div');
    area.className = 'toast-area';
    area.setAttribute('aria-live', 'polite');
    document.body.appendChild(area);

    var shown = {};   // какие уведомления уже показаны

    // Отправка POST-запроса и получение JSON
    function post(url, params) {
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json',
                'X-CSRF-Token': csrf
            },
            body: new URLSearchParams(params)
        }).then(function (response) {
            return response.json();
        });
    }

    function makeButton(text, className) {
        var button = document.createElement('button');
        button.type = 'button';
        button.className = className;
        button.textContent = text;
        return button;
    }

    // Показать одно уведомление (весь текст вставляем через textContent: это защита от XSS)
    function addToast(item) {
        var toast = document.createElement('div');
        toast.className = 'toast';

        var close = makeButton('\u00d7', 'toast-close');
        close.setAttribute('aria-label', 'Закрыть');

        var title = document.createElement('strong');
        title.textContent = 'Новая заявка на приём';

        var who = document.createElement('p');
        who.className = 'toast-who';
        who.textContent = item.patient;

        var meta = document.createElement('p');
        meta.className = 'toast-meta';
        meta.textContent = item.doctor + ' \u00b7 ' + item.when;

        var error = document.createElement('p');
        error.className = 'toast-error';
        error.hidden = true;

        var actions = document.createElement('div');
        actions.className = 'toast-actions';
        var approve = makeButton('Одобрить', 'btn btn-primary btn-small');
        var reject = makeButton('Отклонить', 'btn btn-ghost btn-small');
        actions.appendChild(approve);
        actions.appendChild(reject);

        toast.appendChild(close);
        toast.appendChild(title);
        toast.appendChild(who);
        toast.appendChild(meta);
        toast.appendChild(error);
        toast.appendChild(actions);
        area.appendChild(toast);

        // Одобрить или отклонить прямо из уведомления
        function decide(status) {
            approve.disabled = true;
            reject.disabled = true;
            post(statusUrl + '/' + item.appointment_id + '/status', { status: status })
                .then(function (result) {
                    if (!result.success) {
                        error.textContent = result.message;
                        error.hidden = false;
                        return;
                    }
                    toast.remove();
                    // Если открыта таблица расписания, обновляем её
                    if (document.getElementById('date-form')) {
                        window.location.reload();
                    }
                })
                .catch(function () {
                    error.textContent = 'Не удалось выполнить действие. Обновите страницу.';
                    error.hidden = false;
                });
        }
        approve.addEventListener('click', function () { decide('confirmed'); });
        reject.addEventListener('click', function () { decide('rejected'); });

        // Крестик: просто скрыть уведомление (заявка остаётся в таблице расписания)
        close.addEventListener('click', function () {
            toast.remove();
            post(notifyUrl + '/' + item.id + '/read', {}).catch(function () {});
        });
    }

    function poll() {
        fetch(notifyUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(function (response) { return response.ok ? response.json() : null; })
            .then(function (data) {
                if (!data || !Array.isArray(data.items)) {
                    return;
                }
                data.items.forEach(function (item) {
                    if (!shown[item.id]) {
                        shown[item.id] = true;
                        addToast(item);
                    }
                });
            })
            .catch(function () {});
    }

    poll();
    setInterval(poll, 10000);
});
