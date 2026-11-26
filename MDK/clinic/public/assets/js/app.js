// ============================================================
// app.js: общие скрипты для всех страниц.
// Главная проверка данных всё равно выполняется на сервере.
// ============================================================

// --- Модальные окна: Modal.open(элемент), Modal.close(элемент) ---
window.Modal = {
    open: function (modal) {
        modal.hidden = false;
        document.body.classList.add('modal-open');
    },
    close: function (modal) {
        modal.hidden = true;
        if (!document.querySelector('.modal:not([hidden])')) {
            document.body.classList.remove('modal-open');
        }
    }
};

// Закрытие окна: кнопка или фон с атрибутом data-close
document.addEventListener('click', function (event) {
    var closer = event.target.closest('[data-close]');
    if (closer) {
        var modal = closer.closest('.modal');
        if (modal) {
            window.Modal.close(modal);
        }
    }
});

// Закрытие окна клавишей Esc
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.modal:not([hidden])').forEach(function (modal) {
            window.Modal.close(modal);
        });
    }
});

document.addEventListener('DOMContentLoaded', function () {

    // --- Подтверждение действия: <form data-confirm="Вы уверены?"> ---
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.getAttribute('data-confirm'))) {
                event.preventDefault();
            }
        });
    });

    // --- Форма регистрации ---
    var form = document.getElementById('register-form');
    if (!form) {
        return;
    }

    var password = form.querySelector('[name=password]');
    var password2 = form.querySelector('[name=password2]');
    var hints = document.querySelectorAll('#pw-hints li');
    var errorBox = document.getElementById('js-error');

    function checkRules(value) {
        return {
            len: value.length >= 8,
            kase: /[a-zа-я]/.test(value) && /[A-ZА-Я]/.test(value),
            digit: /[0-9]/.test(value)
        };
    }

    password.addEventListener('input', function () {
        var rules = checkRules(password.value);
        hints.forEach(function (li) {
            var rule = li.getAttribute('data-rule');
            var ok = rule === 'len' ? rules.len : (rule === 'case' ? rules.kase : rules.digit);
            li.classList.toggle('ok', ok);
        });
    });

    form.addEventListener('submit', function (event) {
        var message = '';
        var rules = checkRules(password.value);

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email.value.trim())) {
            message = 'Введите корректный email, например name@mail.ru';
        } else if (!(rules.len && rules.kase && rules.digit)) {
            message = 'Пароль не соответствует правилам';
        } else if (password.value !== password2.value) {
            message = 'Пароли не совпадают';
        } else if (!form.consent.checked) {
            message = 'Нужно согласие на обработку персональных данных';
        }

        if (message !== '') {
            event.preventDefault();
            errorBox.textContent = message;
            errorBox.hidden = false;
        }
    });
});
