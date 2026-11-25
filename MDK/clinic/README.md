# Офтальмологическая клиника: CRM-система (курсовой проект)

## Запуск в OpenServer
1. Папка проекта: `OpenServer\domains\clinic`, корень сайта = папка `public`
2. PHP 7.4 или выше, MySQL 5.7+ / MariaDB 10+
3. В phpMyAdmin по очереди выполнить файлы из `database/`:
   `schema.sql` -> `seed.sql` -> `seed_users.sql` -> `seed_schedule.sql` -> `seed_demo.sql`
   Если база уже была создана раньше: вместо `schema.sql` выполнить `migration_02_appointments.sql` (один раз).
4. Проверить `.env` (логин и пароль MySQL)

## Тестовые аккаунты (пароль у всех: Test1234)
| Роль | Email | После входа |
|---|---|---|
| Пациент | patient@clinic.local | /doctors |
| Администратор | admin@clinic.local | /schedule |
| Руководитель | manager@clinic.local | /schedule |
| Системный администратор | sysadmin@clinic.local | /sysadmin/roles |

## Страницы
| Адрес | Кто | Что |
|---|---|---|
| /doctors, /doctors/{slug} | все | список врачей и карточка (один шаблон) |
| /my-appointments | пациент | мои записи, отмена |
| /schedule | администратор, руководитель | таблица расписания |
| /patients | администратор, руководитель | картотека пациентов |
| /profile | все роли | профиль, фото, смена пароля |
| /manager/doctors | руководитель | врачи: добавить, изменить, фото |
| /manager/work-schedule | руководитель | график работы (смены) |
| /manager/reports | руководитель | отчёты, выгрузка CSV |
| /sysadmin/roles | сисадмин | матрица прав ролей |
| /sysadmin/users | сисадмин | аккаунты: создать, роль, блокировка, пароль |
| /sysadmin/audit | сисадмин | журнал действий |
| /sitemap.xml | поисковики | карта сайта (из базы данных) |

## Структура (MVC)
- `public/`: единственная папка, доступная из браузера
- `app/Core/`: ядро (маршрутизатор, БД, вход, защита)
- `app/Models/`: работа с базой данных
- `app/Views/`: HTML-шаблоны
- `app/Controllers/`: логика страниц
- `config/`: настройки и маршруты (`routes.php`)
