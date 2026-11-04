<?php 
include 'includes/db.php'; 
include 'includes/header.php'; 
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Доставка - Lens & Look</title>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        .delivery-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 32px;
            padding: 60px 48px;
            color: white;
            margin: 40px 0 50px;
            position: relative;
            overflow: hidden;
        }
        .delivery-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 400px;
            height: 400px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }
        .delivery-hero::after {
            content: '';
            position: absolute;
            bottom: -40%;
            left: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.03);
            border-radius: 50%;
        }
        .delivery-hero h1 {
            font-family: 'Manrope', sans-serif;
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }
        .delivery-hero p {
            font-size: 18px;
            opacity: 0.9;
            max-width: 600px;
            position: relative;
            z-index: 1;
            line-height: 1.6;
        }
        .delivery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }
        .delivery-card {
            background: white;
            border-radius: 24px;
            padding: 32px 28px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
            border: 1px solid #F0F4FF;
        }
        .delivery-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(95,158,240,0.15);
            border-color: #5F9EF0;
        }
        .delivery-card .icon {
            width: 56px;
            height: 56px;
            background: #F0F4FF;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            color: #5F9EF0;
        }
        .delivery-card h3 {
            font-family: 'Manrope', sans-serif;
            font-size: 20px;
            margin-bottom: 10px;
            color: #1E2A3E;
        }
        .delivery-card .price {
            font-size: 24px;
            font-weight: 700;
            color: #5F9EF0;
            margin: 12px 0 8px;
        }
        .delivery-card .time {
            color: #5B6E8C;
            font-size: 14px;
            margin-bottom: 12px;
        }
        .delivery-card .description {
            color: #5B6E8C;
            line-height: 1.6;
            font-size: 14px;
        }
        .delivery-card .badge {
            display: inline-block;
            background: #DCFCE7;
            color: #065F46;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin-top: 12px;
        }
        .info-section {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            margin: 40px 0;
        }
        .info-section h2 {
            font-family: 'Manrope', sans-serif;
            font-size: 28px;
            margin-bottom: 24px;
            color: #1E2A3E;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }
        .info-item {
            padding: 20px;
            background: #F8F9FC;
            border-radius: 16px;
            transition: all 0.3s;
        }
        .info-item:hover {
            background: #F0F4FF;
        }
        .info-item h4 {
            font-family: 'Manrope', sans-serif;
            font-size: 16px;
            margin-bottom: 8px;
            color: #1E2A3E;
        }
        .info-item p {
            color: #5B6E8C;
            font-size: 14px;
            line-height: 1.6;
        }
        .info-item .highlight {
            color: #5F9EF0;
            font-weight: 600;
        }
        .faq-delivery {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            margin: 40px 0;
        }
        .faq-delivery h2 {
            font-family: 'Manrope', sans-serif;
            font-size: 28px;
            margin-bottom: 24px;
            color: #1E2A3E;
        }
        .faq-delivery .faq-item {
            border-bottom: 1px solid #E1E8F0;
            padding: 16px 0;
        }
        .faq-delivery .faq-item:last-child {
            border-bottom: none;
        }
        .faq-delivery .faq-question {
            font-weight: 600;
            font-size: 16px;
            color: #1E2A3E;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: color 0.2s;
        }
        .faq-delivery .faq-question:hover {
            color: #5F9EF0;
        }
        .faq-delivery .faq-question .arrow {
            transition: transform 0.3s;
            font-size: 20px;
            color: #5B6E8C;
        }
        .faq-delivery .faq-item.active .faq-question .arrow {
            transform: rotate(180deg);
        }
        .faq-delivery .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, padding 0.3s ease;
            color: #5B6E8C;
            line-height: 1.6;
            font-size: 14px;
        }
        .faq-delivery .faq-item.active .faq-answer {
            max-height: 200px;
            padding-top: 12px;
        }
        .free-delivery-banner {
            background: linear-gradient(135deg, #DCFCE7, #BBF7D0);
            border-radius: 24px;
            padding: 40px;
            text-align: center;
            margin: 40px 0;
        }
        .free-delivery-banner h3 {
            font-family: 'Manrope', sans-serif;
            font-size: 28px;
            color: #065F46;
            margin-bottom: 8px;
        }
        .free-delivery-banner p {
            color: #065F46;
            font-size: 16px;
            opacity: 0.8;
        }
        .free-delivery-banner .amount {
            font-size: 36px;
            font-weight: 800;
            color: #065F46;
            display: block;
            margin: 8px 0;
        }
        .city-list {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 16px;
        }
        .city-tag {
            background: #F0F4FF;
            color: #5B6E8C;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            transition: all 0.2s;
        }
        .city-tag:hover {
            background: #5F9EF0;
            color: white;
        }
        @media (max-width: 768px) {
            .delivery-hero {
                padding: 40px 24px;
            }
            .delivery-hero h1 {
                font-size: 28px;
            }
            .delivery-grid {
                grid-template-columns: 1fr;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
            .free-delivery-banner {
                padding: 24px;
            }
            .free-delivery-banner h3 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>

<main class="container">
    
    <!-- Hero-блок -->
    <div class="delivery-hero" data-aos="fade-up" data-aos-duration="800">
        <h1>Доставка по всей России</h1>
        <p>Мы доставляем заказы в любой уголок страны. Выберите удобный способ получения — и ваш заказ будет у вас в кратчайшие сроки.</p>
    </div>

    <!-- Способы доставки -->
    <h2 style="font-family: 'Manrope', sans-serif; font-size: 30px; margin-bottom: 20px;" data-aos="fade-up">Способы доставки</h2>
    
    <div class="delivery-grid">
        <!-- Курьерская доставка -->
        <div class="delivery-card" data-aos="fade-up" data-aos-delay="100">
            <div class="icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 3h15v13H1z"/>
                    <path d="M5 16v4h11v-4"/>
                    <circle cx="6" cy="20" r="2"/>
                    <circle cx="17" cy="20" r="2"/>
                    <path d="M16 8h3l3 4v4h-6V8z"/>
                </svg>
            </div>
            <h3>Курьерская доставка</h3>
            <div class="price">300 ₽</div>
            <div class="time">Срок: 1-2 дня</div>
            <div class="description">
                Доставка курьером до двери. Удобный способ получения заказа без необходимости куда-то ехать.
            </div>
            <span class="badge">Самый популярный</span>
        </div>

        <!-- СДЭК -->
        <div class="delivery-card" data-aos="fade-up" data-aos-delay="200">
            <div class="icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
            </div>
            <h3>Пункты выдачи СДЭК</h3>
            <div class="price">200 ₽</div>
            <div class="time">Срок: 2-3 дня</div>
            <div class="description">
                Более 1000 пунктов выдачи по всей России. Заберите заказ в удобном для вас месте.
            </div>
            <span class="badge">Экономия</span>
        </div>

        <!-- Почта России -->
        <div class="delivery-card" data-aos="fade-up" data-aos-delay="300">
            <div class="icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
            </div>
            <h3>Почта России</h3>
            <div class="price">от 250 ₽</div>
            <div class="time">Срок: 3-7 дней</div>
            <div class="description">
                Экономичный способ доставки в отдалённые регионы. Отслеживание заказа по трек-номеру.
            </div>
        </div>

        <!-- Экспресс-доставка -->
        <div class="delivery-card" data-aos="fade-up" data-aos-delay="400">
            <div class="icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <h3>Экспресс-доставка</h3>
            <div class="price">500 ₽</div>
            <div class="time">Срок: 4-6 часов</div>
            <div class="description">
                Срочная доставка в день заказа. Доступна в Москве и Санкт-Петербурге.
            </div>
            <span class="badge">Срочно</span>
        </div>
    </div>

    <!-- Бесплатная доставка -->
    <div class="free-delivery-banner" data-aos="fade-up" data-aos-duration="800">
        <h3>Бесплатная доставка</h3>
        <span class="amount">от 5 000 ₽</span>
        <p>При заказе на сумму от 5 000 рублей доставка абсолютно бесплатна!</p>
    </div>

    <!-- Города доставки -->
    <div class="info-section" data-aos="fade-up">
        <h2>Города доставки</h2>
        <p style="color: #5B6E8C; margin-bottom: 16px;">Мы доставляем в следующие города и регионы:</p>
        <div class="city-list">
            <span class="city-tag">Москва</span>
            <span class="city-tag">Санкт-Петербург</span>
            <span class="city-tag">Казань</span>
            <span class="city-tag">Екатеринбург</span>
            <span class="city-tag">Новосибирск</span>
            <span class="city-tag">Краснодар</span>
            <span class="city-tag">Ростов-на-Дону</span>
            <span class="city-tag">Нижний Новгород</span>
            <span class="city-tag">Челябинск</span>
            <span class="city-tag">Самара</span>
            <span class="city-tag">Омск</span>
            <span class="city-tag">Волгоград</span>
            <span class="city-tag">Уфа</span>
            <span class="city-tag">Красноярск</span>
            <span class="city-tag">Пермь</span>
            <span class="city-tag">Воронеж</span>
            <span class="city-tag">Саратов</span>
            <span class="city-tag">Тюмень</span>
            <span class="city-tag">Тольятти</span>
            <span class="city-tag">Барнаул</span>
            <span class="city-tag">Иркутск</span>
            <span class="city-tag">Хабаровск</span>
            <span class="city-tag">Владивосток</span>
        </div>
        <p style="color: #5B6E8C; margin-top: 16px; font-size: 14px;">Доставка в другие города уточняйте у менеджера.</p>
    </div>

    <!-- Дополнительная информация -->
    <div class="info-section" data-aos="fade-up" data-aos-delay="100">
        <h2>Важная информация</h2>
        <div class="info-grid">
            <div class="info-item">
                <h4>Сроки доставки</h4>
                <p>Мы стараемся обрабатывать заказы в день оформления. Ориентировочные сроки указаны для каждого способа доставки.</p>
            </div>
            <div class="info-item">
                <h4>Отслеживание заказа</h4>
                <p>После отправки вы получите трек-номер для отслеживания. Статус заказа доступен в <a href="account.php" style="color: #5F9EF0; text-decoration: none;">личном кабинете</a>.</p>
            </div>
            <div class="info-item">
                <h4>Условия возврата</h4>
                <p>Вы можете вернуть товар в течение 14 дней. Подробнее на странице <a href="returns.php" style="color: #5F9EF0; text-decoration: none;">возврата</a>.</p>
            </div>
            <div class="info-item">
                <h4>Контакты</h4>
                <p>По вопросам доставки звоните: <span class="highlight">8-800-555-01-01</span><br>Или пишите: <span class="highlight">info@lenslook.ru</span></p>
            </div>
        </div>
    </div>

    <!-- Часто задаваемые вопросы о доставке -->
    <div class="faq-delivery" data-aos="fade-up" data-aos-delay="200">
        <h2>Часто задаваемые вопросы</h2>
        
        <div class="faq-item active">
            <div class="faq-question">
                Как долго обрабатывается заказ?
                <span class="arrow">▼</span>
            </div>
            <div class="faq-answer">
                Заказы обрабатываются в течение 1-2 рабочих дней. В день отправки вы получите уведомление на email с трек-номером.
            </div>
        </div>
        
        <div class="faq-item">
            <div class="faq-question">
                Можно ли изменить адрес доставки после оформления заказа?
                <span class="arrow">▼</span>
            </div>
            <div class="faq-answer">
                Да, если заказ ещё не был отправлен. Свяжитесь с нашим менеджером по телефону 8-800-555-01-01 или в чате на сайте.
            </div>
        </div>
        
        <div class="faq-item">
            <div class="faq-question">
                Как узнать статус моего заказа?
                <span class="arrow">▼</span>
            </div>
            <div class="faq-answer">
                Вы можете отслеживать статус заказа в <a href="account.php" style="color: #5F9EF0; text-decoration: none;">личном кабинете</a> или по трек-номеру, который мы пришлём вам на email.
            </div>
        </div>
        
        <div class="faq-item">
            <div class="faq-question">
                Есть ли самовывоз?
                <span class="arrow">▼</span>
            </div>
            <div class="faq-answer">
                Да, вы можете забрать заказ из нашего офиса в Москве. Адрес: г. Москва, ул. Тверская, д. 15. Режим работы: пн-пт с 10:00 до 20:00.
            </div>
        </div>
        
        <div class="faq-item">
            <div class="faq-question">
                Что делать, если заказ пришёл с браком?
                <span class="arrow">▼</span>
            </div>
            <div class="faq-answer">
                В случае обнаружения брака, пожалуйста, свяжитесь с нами в течение 7 дней после получения. Мы бесплатно заменим товар или вернём деньги.
            </div>
        </div>
    </div>

</main>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    // Инициализация AOS анимаций
    AOS.init({
        duration: 600,
        once: true,
        offset: 50
    });

    // FAQ аккордеон
    document.querySelectorAll('.faq-question').forEach(function(question) {
        question.addEventListener('click', function() {
            var item = this.parentElement;
            var isActive = item.classList.contains('active');
            
            // Закрываем все
            document.querySelectorAll('.faq-item').forEach(function(el) {
                el.classList.remove('active');
            });
            
            // Открываем текущий, если он был закрыт
            if (!isActive) {
                item.classList.add('active');
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>