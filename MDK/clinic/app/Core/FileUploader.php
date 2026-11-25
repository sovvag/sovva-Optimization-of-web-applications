<?php
// ============================================================
// FileUploader.php: безопасная загрузка картинок (аватары, фото врачей).
//
// Главная идея: НЕ верим ни имени файла, ни расширению, ни типу из браузера.
// Проверяем содержимое файла и затем ПЕРЕРИСОВЫВАЕМ картинку через GD.
// Всё, что не пиксели (PHP-код, скрипты, EXIF-данные), при этом пропадает.
// ============================================================
class FileUploader
{
    const MAX_BYTES  = 2097152;   // 2 МБ
    const MAX_PIXELS = 9000000;   // 9 мегапикселей (защита от "бомбы" из огромной картинки)
    const MIN_SIDE   = 100;       // минимальная сторона, пикселей

    // Типы, которые разрешаем (проверяются ПО СОДЕРЖИМОМУ файла)
    private static function allowedTypes()
    {
        $types = ['image/jpeg' => IMAGETYPE_JPEG, 'image/png' => IMAGETYPE_PNG];
        if (function_exists('imagecreatefromwebp') && defined('IMAGETYPE_WEBP')) {
            $types['image/webp'] = IMAGETYPE_WEBP;
        }
        return $types;
    }

    // Сохранить картинку из $_FILES['поле'].
    //   $folder  - папка внутри public/uploads (avatars или doctors)
    //   $width, $height - размер готовой картинки (лишнее по краям обрезается)
    // Возвращает путь вида "uploads/avatars/abc.jpg", null (если файл не выбран)
    // или бросает RuntimeException с понятным текстом ошибки.
    public static function saveImage($file, $folder, $width, $height)
    {
        if (!in_array($folder, ['avatars', 'doctors'], true)) {
            throw new RuntimeException('Недопустимая папка загрузки.');
        }
        if (!is_array($file) || !isset($file['error']) || is_array($file['error'])) {
            throw new RuntimeException('Файл не загружен.');
        }

        // 1. Ошибки самой загрузки
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            throw new RuntimeException('Файл слишком большой. Максимум 2 МБ.');
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Не удалось загрузить файл. Попробуйте ещё раз.');
        }

        $tmp = $file['tmp_name'];
        if (!is_uploaded_file($tmp)) {           // файл должен прийти именно через форму
            throw new RuntimeException('Файл не загружен.');
        }
        if (filesize($tmp) > self::MAX_BYTES) {
            throw new RuntimeException('Файл слишком большой. Максимум 2 МБ.');
        }
        if (!extension_loaded('gd')) {
            throw new RuntimeException('На сервере не включено расширение GD (нужно для обработки фото).');
        }

        // 2. Тип файла по СОДЕРЖИМОМУ (расширение и тип от браузера игнорируем)
        $info = @getimagesize($tmp);
        if ($info === false) {
            throw new RuntimeException('Файл не является изображением.');
        }
        $mime = $info['mime'];
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $tmp);
            finfo_close($finfo);
        }
        $allowed = self::allowedTypes();
        if (!isset($allowed[$mime]) || $allowed[$mime] !== $info[2]) {
            throw new RuntimeException('Допустимы только изображения JPG и PNG.');
        }

        // 3. Размеры картинки
        $w = (int)$info[0];
        $h = (int)$info[1];
        if ($w < self::MIN_SIDE || $h < self::MIN_SIDE) {
            throw new RuntimeException('Изображение слишком маленькое (минимум ' . self::MIN_SIDE . ' px).');
        }
        if ($w * $h > self::MAX_PIXELS) {
            throw new RuntimeException('Изображение слишком большое по размеру. Уменьшите его.');
        }

        // 4. Читаем картинку через GD. Если внутри не картинка, GD вернёт false.
        $src = false;
        if ($info[2] === IMAGETYPE_JPEG) {
            $src = @imagecreatefromjpeg($tmp);
        } elseif ($info[2] === IMAGETYPE_PNG) {
            $src = @imagecreatefrompng($tmp);
        } elseif (defined('IMAGETYPE_WEBP') && $info[2] === IMAGETYPE_WEBP) {
            $src = @imagecreatefromwebp($tmp);
        }
        if (!$src) {
            throw new RuntimeException('Не удалось прочитать изображение. Файл повреждён.');
        }

        // 5. Фото с телефона бывают повёрнуты: поворачиваем по данным EXIF
        if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($tmp);
            $angles = [3 => 180, 6 => -90, 8 => 90];
            if (is_array($exif) && isset($exif['Orientation'], $angles[$exif['Orientation']])) {
                $rotated = imagerotate($src, $angles[$exif['Orientation']], 0);
                if ($rotated) {
                    $src = $rotated;
                }
            }
        }
        $w = imagesx($src);
        $h = imagesy($src);

        // 6. Обрезаем по центру до нужных пропорций
        $targetRatio = $width / $height;
        if ($w / $h > $targetRatio) {            // картинка шире нужного: режем по бокам
            $cropH = $h;
            $cropW = (int)round($h * $targetRatio);
        } else {                                 // картинка выше нужного: режем сверху и снизу
            $cropW = $w;
            $cropH = (int)round($w / $targetRatio);
        }
        $cropX = (int)(($w - $cropW) / 2);
        $cropY = (int)(($h - $cropH) / 2);

        // 7. Рисуем НОВУЮ картинку на белом фоне (прозрачность PNG заменится белым)
        $dst = imagecreatetruecolor($width, $height);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, $width, $height, $cropW, $cropH);

        // 8. Сохраняем под случайным именем. Имя от пользователя НЕ используется.
        $dir = BASE_PATH . '/public/uploads/' . $folder;
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            throw new RuntimeException('Не удалось создать папку для загрузок.');
        }
        $name = bin2hex(random_bytes(16)) . '.jpg';
        if (!imagejpeg($dst, $dir . '/' . $name, 85)) {
            throw new RuntimeException('Не удалось сохранить изображение.');
        }
        @chmod($dir . '/' . $name, 0644);

        return 'uploads/' . $folder . '/' . $name;
    }

    // Удалить ранее загруженный файл. Путь берётся из БД, поэтому проверяем его формат:
    // так нельзя заставить сервер удалить чужой файл.
    public static function delete($relativePath)
    {
        if (!is_string($relativePath) || !preg_match('#^uploads/(avatars|doctors)/[a-f0-9]{32}\.jpg$#', $relativePath)) {
            return;
        }
        $full = BASE_PATH . '/public/' . $relativePath;
        if (is_file($full)) {
            @unlink($full);
        }
    }
}
