// ============================================================
// profile.js: предпросмотр выбранного фото и быстрая проверка размера.
// Настоящая проверка файла выполняется на сервере (FileUploader.php).
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('avatar-input');
    if (!input) {
        return;
    }
    var preview = document.getElementById('avatar-preview');
    var letters = document.getElementById('avatar-letters');
    var errorBox = document.getElementById('avatar-js-error');
    var MAX = 2 * 1024 * 1024;

    input.addEventListener('change', function () {
        errorBox.hidden = true;
        var file = input.files[0];
        if (!file) {
            return;
        }
        if (file.size > MAX) {
            errorBox.textContent = 'Файл слишком большой. Максимум 2 МБ.';
            errorBox.hidden = false;
            input.value = '';
            return;
        }
        if (file.type !== 'image/jpeg' && file.type !== 'image/png') {
            errorBox.textContent = 'Допустимы только JPG и PNG.';
            errorBox.hidden = false;
            input.value = '';
            return;
        }

        // Показываем выбранное фото до отправки (data: адрес разрешён политикой безопасности)
        var reader = new FileReader();
        reader.onload = function () {
            preview.src = reader.result;
            preview.hidden = false;
            if (letters) {
                letters.hidden = true;
            }
        };
        reader.readAsDataURL(file);
    });
});
