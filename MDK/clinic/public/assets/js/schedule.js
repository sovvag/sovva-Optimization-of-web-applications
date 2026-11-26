// ============================================================
// schedule.js: страница расписания.
// Окно записи: стрелки переноса (шаг 10 минут), повод, комментарий,
// длительность, удаление. Окно "Запись": администратор сам записывает пациента.
// После каждого действия таблица обновляется без перезагрузки страницы.
// ============================================================
document.addEventListener('DOMContentLoaded', function () {

    // Выбрали дату в календаре: сразу открываем этот день
    var dateInput = document.getElementById('date-input');
    if (dateInput) {
        dateInput.addEventListener('change', function () {
            document.getElementById('date-form').submit();
        });
    }

    var area = document.getElementById('sched-area');
    var modal = document.getElementById('slot-modal');
    if (!area || !modal) {
        return;
    }

    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var baseUrl = modal.getAttribute('data-url');
    var canManage = modal.getAttribute('data-manage') === '1';

    function byId(id) {
        return document.getElementById(id);
    }

    // POST-запрос, ответ в формате JSON
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
            return response.json().then(function (data) {
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Ошибка запроса');
                }
                return data;
            });
        });
    }

    function errorText(error) {
        return error instanceof SyntaxError ? 'Не удалось выполнить запрос. Обновите страницу.' : error.message;
    }

    function showMsg(box, text, isError) {
        box.textContent = text;
        box.className = 'booking-msg ' + (isError ? 'is-error' : 'is-ok');
        box.hidden = false;
    }

    // Обновить таблицу: загружаем страницу заново и берём из неё свежую область
    function refresh() {
        return fetch(window.location.href, { credentials: 'same-origin', headers: { 'Accept': 'text/html' } })
            .then(function (response) { return response.text(); })
            .then(function (html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');
                var fresh = doc.getElementById('sched-area');
                if (fresh) {
                    area.innerHTML = fresh.innerHTML;
                }
            });
    }

    // ---------------- Окно существующей записи ----------------
    var current = null;   // данные открытой записи
    var msg = byId('sm-msg');

    function showTime() {
        byId('sm-time').textContent = current.start + ' \u2013 ' + current.end;
    }

    function openAppointment(d) {
        current = {
            id: d.id, status: d.status, start: d.start, end: d.end, duration: d.duration
        };
        byId('sm-patient').textContent = d.patient;
        byId('sm-patient').href = d.patientUrl;
        byId('sm-phone').textContent = d.phone;
        byId('sm-doctor').textContent = d.doctor;
        byId('sm-price').textContent = d.price;

        var status = byId('sm-status');
        status.textContent = d.statusLabel;
        status.className = 'status status-' + d.status;

        showTime();
        byId('sm-duration').value = d.duration;
        byId('sm-reason').value = d.reason;
        byId('sm-note').value = d.note;
        msg.hidden = true;

        // Если запись уже завершена или у пользователя нет прав, поля только для чтения
        var editable = canManage && (d.status === 'pending' || d.status === 'confirmed');
        ['sm-duration', 'sm-reason', 'sm-note'].forEach(function (id) {
            byId(id).disabled = !editable;
        });
        if (byId('sm-earlier')) {
            byId('sm-earlier').disabled = !editable;
            byId('sm-later').disabled = !editable;
        }
        if (byId('sm-save')) {
            byId('sm-save').hidden = !editable;
        }
        if (byId('sm-delete')) {
            byId('sm-delete').hidden = d.status === 'completed';
        }
        if (byId('sm-approve')) {
            byId('sm-approve').hidden = d.status !== 'pending';
            byId('sm-reject').hidden = d.status !== 'pending';
        }
        window.Modal.open(modal);
    }

    // Клики по таблице обрабатываем на самой области: после обновления таблицы кнопки новые
    area.addEventListener('click', function (event) {
        var cell = event.target.closest('.cell-btn');
        if (cell) {
            openAppointment(cell.dataset);
            return;
        }
        var free = event.target.closest('.free-btn');
        if (free && createModal) {
            openCreate(free.dataset.doctor, free.dataset.time, free.dataset.minutes);
        }
    });

    // Стрелки: перенос записи на 10 минут раньше или позже
    function move(delta) {
        byId('sm-earlier').disabled = true;
        byId('sm-later').disabled = true;
        post(baseUrl + '/' + current.id + '/move', { delta: delta })
            .then(function (data) {
                current.start = data.appointment.start;
                current.end = data.appointment.end;
                showTime();
                msg.hidden = true;
                return refresh();
            })
            .catch(function (error) { showMsg(msg, errorText(error), true); })
            .then(function () {
                byId('sm-earlier').disabled = false;
                byId('sm-later').disabled = false;
            });
    }
    if (byId('sm-earlier')) {
        byId('sm-earlier').addEventListener('click', function () { move(-10); });
        byId('sm-later').addEventListener('click', function () { move(10); });
    }

    // Сохранить повод, комментарий и длительность
    if (byId('sm-save')) {
        byId('sm-save').addEventListener('click', function () {
            post(baseUrl + '/' + current.id + '/update', {
                reason: byId('sm-reason').value,
                note: byId('sm-note').value,
                duration: byId('sm-duration').value
            }).then(function (data) {
                current.end = data.appointment.end;
                current.duration = data.appointment.duration;
                showTime();
                showMsg(msg, 'Изменения сохранены.', false);
                return refresh();
            }).catch(function (error) { showMsg(msg, errorText(error), true); });
        });
    }

    // Одобрить, отклонить, удалить
    function act(path, params, confirmText) {
        if (confirmText && !window.confirm(confirmText)) {
            return;
        }
        post(baseUrl + '/' + current.id + '/' + path, params)
            .then(function () {
                window.Modal.close(modal);
                return refresh();
            })
            .catch(function (error) { showMsg(msg, errorText(error), true); });
    }
    if (byId('sm-approve')) {
        byId('sm-approve').addEventListener('click', function () { act('status', { status: 'confirmed' }); });
        byId('sm-reject').addEventListener('click', function () {
            act('status', { status: 'rejected' }, 'Отклонить заявку и освободить время?');
        });
    }
    if (byId('sm-delete')) {
        byId('sm-delete').addEventListener('click', function () {
            act('delete', {}, 'Удалить запись? Это действие нельзя отменить.');
        });
    }

    // ---------------- Окно "Запись" ----------------
    var createModal = byId('create-modal');
    if (!createModal || !byId('cm-submit')) {
        return;
    }
    var cmMsg = byId('cm-msg');
    var search = byId('cm-search');
    var results = byId('cm-results');
    var chosen = byId('cm-chosen');
    var patientId = null;
    var timer = null;

    function choosePatient(id, name) {
        patientId = id;
        byId('cm-chosen-name').textContent = name;
        chosen.hidden = false;
        results.hidden = true;
        search.hidden = true;
    }

    function clearPatient() {
        patientId = null;
        chosen.hidden = true;
        search.hidden = false;
        search.value = '';
        search.focus();
    }
    byId('cm-clear').addEventListener('click', clearPatient);

    // Поиск пациента по мере ввода (с небольшой задержкой)
    search.addEventListener('input', function () {
        clearTimeout(timer);
        var query = search.value.trim();
        if (query.length < 2) {
            results.hidden = true;
            return;
        }
        timer = setTimeout(function () {
            fetch(createModal.getAttribute('data-search-url') + '?q=' + encodeURIComponent(query), {
                credentials: 'same-origin', headers: { 'Accept': 'application/json' }
            }).then(function (r) { return r.json(); }).then(function (data) {
                results.textContent = '';
                if (!data.items.length) {
                    var none = document.createElement('p');
                    none.className = 'muted small';
                    none.textContent = 'Никого не найдено';
                    results.appendChild(none);
                }
                data.items.forEach(function (item) {
                    var button = document.createElement('button');
                    button.type = 'button';
                    button.textContent = item.name + (item.phone ? ' \u00b7 ' + item.phone : '');
                    button.addEventListener('click', function () { choosePatient(item.id, item.name); });
                    results.appendChild(button);
                });
                results.hidden = false;
            }).catch(function () {});
        }, 250);
    });

    // Открыть окно (из кнопки "Запись" или кликом по свободной ячейке)
    function openCreate(doctorId, time, minutes) {
        cmMsg.hidden = true;
        clearPatient();
        if (doctorId) {
            byId('cm-doctor').value = doctorId;
        }
        byId('cm-time').value = time || '';
        byId('cm-duration').value = minutes || byId('cm-doctor').selectedOptions[0].getAttribute('data-minutes');
        byId('cm-note').value = '';
        window.Modal.open(createModal);
    }
    byId('create-open').addEventListener('click', function () { openCreate(null, '', null); });

    // При смене врача подставляем длительность его приёма
    byId('cm-doctor').addEventListener('change', function () {
        byId('cm-duration').value = byId('cm-doctor').selectedOptions[0].getAttribute('data-minutes');
    });

    byId('cm-submit').addEventListener('click', function () {
        if (!patientId) {
            showMsg(cmMsg, 'Выберите пациента из картотеки.', true);
            return;
        }
        post(createModal.getAttribute('data-url'), {
            patient_id: patientId,
            doctor_id: byId('cm-doctor').value,
            date: createModal.getAttribute('data-date'),
            time: byId('cm-time').value,
            duration: byId('cm-duration').value,
            reason: byId('cm-reason').value,
            note: byId('cm-note').value
        }).then(function () {
            window.Modal.close(createModal);
            return refresh();
        }).catch(function (error) { showMsg(cmMsg, errorText(error), true); });
    });
});
