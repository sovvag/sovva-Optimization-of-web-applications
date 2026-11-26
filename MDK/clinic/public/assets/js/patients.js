// ============================================================
// patients.js: окно "Пациент подписал отказ от персональных данных?"
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('delete-modal');
    if (!modal) {
        return;
    }
    var baseUrl = modal.getAttribute('data-base-url');
    var question = document.getElementById('dm-question');
    var denied = document.getElementById('dm-denied');

    // Кнопка "Удалить" в строке пациента
    document.querySelectorAll('.js-delete').forEach(function (button) {
        button.addEventListener('click', function () {
            document.getElementById('dm-name').textContent = button.dataset.name;
            document.getElementById('dm-form').action = baseUrl + '/' + button.dataset.id + '/delete';
            question.hidden = false;
            denied.hidden = true;
            window.Modal.open(modal);
        });
    });

    // Кнопка "Нет": удалять нельзя
    document.getElementById('dm-no').addEventListener('click', function () {
        question.hidden = true;
        denied.hidden = false;
    });
});
