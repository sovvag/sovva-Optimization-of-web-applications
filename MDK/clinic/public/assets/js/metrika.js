// Яндекс Метрика: подключение и отправка целей.
// Номер счётчика берётся из атрибута data-id (см. layouts/main.php).
(function () {
    var script = document.currentScript;
    var id = script ? parseInt(script.getAttribute('data-id'), 10) : 0;
    if (!id) {
        return;
    }

    // Стандартный загрузчик Метрики
    (function (m, e, t, r, i, k, a) {
        m[i] = m[i] || function () { (m[i].a = m[i].a || []).push(arguments); };
        m[i].l = 1 * new Date();
        k = e.createElement(t);
        a = e.getElementsByTagName(t)[0];
        k.async = 1;
        k.src = r;
        a.parentNode.insertBefore(k, a);
    })(window, document, 'script', 'https://mc.yandex.ru/metrika/tag.js', 'ym');

    ym(id, 'init', { clickmap: true, trackLinks: true, accurateTrackBounce: true });

    // Отправка цели: reachGoal('booking_submit')
    window.reachGoal = function (name) {
        try { ym(id, 'reachGoal', name); } catch (e) {}
    };

    document.addEventListener('DOMContentLoaded', function () {
        // Любой элемент с атрибутом data-goal="имя" отправляет цель при клике
        document.addEventListener('click', function (event) {
            var el = event.target.closest('[data-goal]');
            if (el) {
                window.reachGoal(el.getAttribute('data-goal'));
            }
        });
        // Цель, которую выставил сервер (например, после регистрации)
        var meta = document.querySelector('meta[name="ym-goal"]');
        if (meta) {
            window.reachGoal(meta.getAttribute('content'));
        }
    });
})();