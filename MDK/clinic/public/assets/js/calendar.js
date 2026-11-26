// ============================================================
// calendar.js: окно записи на приём (календарь и свободное время)
// Данные берём у сервера: GET /api/slots?doctor_id=1&month=2026-11
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    var openButton = document.getElementById('booking-open');
    var modal = document.getElementById('booking-modal');
    if (!openButton || !modal) {
        return;
    }

    var api = modal.getAttribute('data-api');
    var postUrl = modal.getAttribute('data-post');
    var mineUrl = modal.getAttribute('data-mine');
    var doctorId = modal.getAttribute('data-doctor-id');
    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    var monthNames = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
    var monthGen = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];

    var grid = document.getElementById('cal-grid');
    var title = document.getElementById('cal-title');
    var prev = document.getElementById('cal-prev');
    var next = document.getElementById('cal-next');
    var slotsList = document.getElementById('slots-list');
    var slotsTitle = document.getElementById('slots-title');
    var summary = document.getElementById('booking-summary');
    var submit = document.getElementById('booking-submit');
    var msg = document.getElementById('booking-msg');

    var today = new Date();
    today.setHours(0, 0, 0, 0);
    var minMonth = new Date(today.getFullYear(), today.getMonth(), 1);   // раньше текущего месяца нельзя
    var maxMonth = new Date(today.getFullYear(), today.getMonth() + 3, 1);   // и дальше чем на 3 месяца
    var shown = new Date(minMonth.getTime());

    var days = {};            // свободное время по датам: {"2026-11-12": [{id, time}, ...]}
    var selectedDate = null;
    var selectedSlot = null;

    function pad(n) {
        return n < 10 ? '0' + n : '' + n;
    }

    function dateKey(year, month, day) {
        return year + '-' + pad(month + 1) + '-' + pad(day);
    }

    // "2026-11-12" -> "12 ноября"
    function formatDate(key) {
        var parts = key.split('-');
        return parseInt(parts[2], 10) + ' ' + monthGen[parseInt(parts[1], 10) - 1];
    }

    function showMessage(text, isError) {
        msg.textContent = text;
        msg.className = 'booking-msg ' + (isError ? 'is-error' : 'is-ok');
        msg.hidden = false;
    }

    function errorText(error) {
        if (error instanceof SyntaxError) {
            return 'Не удалось выполнить запрос. Обновите страницу.';
        }
        return error.message;
    }

    // Запрос к серверу, ответ в формате JSON
    function request(url, options) {
        var opts = options || {};
        opts.credentials = 'same-origin';
        opts.headers = Object.assign({ 'Accept': 'application/json' }, opts.headers || {});
        return fetch(url, opts).then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Ошибка запроса');
                }
                return data;
            });
        });
    }

    function updateSummary() {
        if (selectedSlot) {
            summary.textContent = formatDate(selectedDate) + ', ' + selectedSlot.time;
            submit.disabled = false;
        } else {
            summary.textContent = '';
            submit.disabled = true;
        }
    }

    function resetSelection() {
        selectedDate = null;
        selectedSlot = null;
        slotsList.textContent = '';
        slotsTitle.textContent = 'Выберите день в календаре';
        updateSummary();
    }

    // Рисуем сетку дней месяца
    function drawGrid() {
        var year = shown.getFullYear();
        var month = shown.getMonth();
        grid.textContent = '';

        var offset = (new Date(year, month, 1).getDay() + 6) % 7;   // неделя начинается с понедельника
        for (var i = 0; i < offset; i++) {
            grid.appendChild(document.createElement('span'));
        }

        var daysInMonth = new Date(year, month + 1, 0).getDate();
        for (var day = 1; day <= daysInMonth; day++) {
            var key = dateKey(year, month, day);
            var button = document.createElement('button');
            button.type = 'button';
            button.textContent = day;
            button.className = 'cal-day';

            if (days[key] && days[key].length) {
                button.classList.add('work');
                if (key === selectedDate) {
                    button.classList.add('selected');
                }
                button.addEventListener('click', makeDayHandler(key));
            } else {
                button.disabled = true;
            }
            grid.appendChild(button);
        }
    }

    function makeDayHandler(key) {
        return function () {
            selectDate(key);
        };
    }

    // Выбрали день: показываем справа свободное время
    function selectDate(key) {
        selectedDate = key;
        selectedSlot = null;
        drawGrid();

        slotsTitle.textContent = 'Свободное время, ' + formatDate(key);
        slotsList.textContent = '';

        days[key].forEach(function (slot) {
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'slot-btn';
            button.textContent = slot.time;
            button.addEventListener('click', function () {
                selectedSlot = slot;
                slotsList.querySelectorAll('.slot-btn').forEach(function (b) {
                    b.classList.remove('on');
                });
                button.classList.add('on');
                updateSummary();
            });
            slotsList.appendChild(button);
        });
        updateSummary();
    }

    // Загружаем свободное время за показанный месяц
    function loadMonth() {
        title.textContent = monthNames[shown.getMonth()] + ' ' + shown.getFullYear();
        prev.disabled = shown <= minMonth;
        next.disabled = shown >= maxMonth;

        var month = shown.getFullYear() + '-' + pad(shown.getMonth() + 1);
        return request(api + '?doctor_id=' + encodeURIComponent(doctorId) + '&month=' + month)
            .then(function (data) {
                days = data.days || {};
                drawGrid();
            })
            .catch(function (error) {
                days = {};
                drawGrid();
                showMessage(errorText(error), true);
            });
    }

    function changeMonth(step) {
        shown = new Date(shown.getFullYear(), shown.getMonth() + step, 1);
        msg.hidden = true;
        resetSelection();
        loadMonth();
    }

    prev.addEventListener('click', function () { changeMonth(-1); });
    next.addEventListener('click', function () { changeMonth(1); });

    // Открыть окно
    openButton.addEventListener('click', function () {
        shown = new Date(minMonth.getTime());
        msg.hidden = true;
        resetSelection();
        window.Modal.open(modal);
        loadMonth();
    });

    // Отправить заявку
    submit.addEventListener('click', function () {
        if (!selectedSlot) {
            return;
        }
        submit.disabled = true;

        request(postUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': csrf
            },
            body: 'slot_id=' + encodeURIComponent(selectedSlot.id)
        }).then(function (data) {
            if (window.reachGoal) { window.reachGoal('booking_submit'); }
            showMessage(data.message + ' Переходим в «Мои записи»...', false);
            setTimeout(function () {
                window.location.href = mineUrl;
            }, 1500);
        }).catch(function (error) {
            // Например, кто-то успел занять это время: обновляем календарь
            showMessage(errorText(error), true);
            var oldDate = selectedDate;
            selectedSlot = null;
            loadMonth().then(function () {
                if (days[oldDate] && days[oldDate].length) {
                    selectDate(oldDate);
                } else {
                    resetSelection();
                }
            });
            updateSummary();
        });
    });
});
