<?php 
include 'includes/db.php'; 
include 'includes/header.php'; 
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оплата - Lens & Look</title>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        .payment-hero {
            background: linear-gradient(135deg, #5F9EF0 0%, #3A7BD5 100%);
            border-radius: 32px;
            padding: 60px 48px;
            color: white;
            margin: 40px 0 50px;
            position: relative;
            overflow: hidden;
        }
        .payment-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 400px;
            height: 400px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }
        .payment-hero::after {
            content: '';
            position: absolute;
            bottom: -40%;
            left: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.03);
            border-radius: 50%;
        }
        .payment-hero h1 {
            font-family: 'Manrope', sans-serif;
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }
        .payment-hero p {
            font-size: 18px;
            opacity: 0.9;
            max-width: 600px;
            position: relative;
            z-index: 1;
            line-height: 1.6;
        }
        .payment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }
        .payment-card {
            background: white;
            border-radius: 24px;
            padding: 32px 28px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
            border: 1px solid #F0F4FF;
            position: relative;
        }
        .payment-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(95,158,240,0.15);
            border-color: #5F9EF0;
        }
        .payment-card .icon {
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
        .payment-card h3 {
            font-family: 'Manrope', sans-serif;
            font-size: 20px;
            margin-bottom: 10px;
            color: #1E2A3E;
        }
        .payment-card .description {
            color: #5B6E8C;
            line-height: 1.6;
            font-size: 14px;
        }
        .payment-card .badge {
            display: inline-block;
            background: #DCFCE7;
            color: #065F46;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin-top: 12px;
        }
        .payment-card .badge-blue {
            background: #DBEAFE;
            color: #1D4ED8;
        }
        .payment-card .badge-yellow {
            background: #FEF3C7;
            color: #92400E;
        }
        .payment-card .badge-purple {
            background: #EDE9FE;
            color: #5B21B6;
        }
        .payment-card .icons-row {
            display: flex;
            gap: 12px;
            margin-top: 12px;
            flex-wrap: wrap;
        }
        .payment-card .icons-row .mini-icon {
            background: #F8F9FC;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            color: #5B6E8C;
            font-weight: 500;
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
        .secure-banner {
            background: linear-gradient(135deg, #F0F4FF, #E8EDFF);
            border-radius: 24px;
            padding: 40px;
            text-align: center;
            margin: 40px 0;
            border: 1px solid #DBEAFE;
        }
        .secure-banner .icon-big {
            width: 64px;
            height: 64px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: #5F9EF0;
            box-shadow: 0 4px 20px rgba(95,158,240,0.15);
        }
        .secure-banner h3 {
            font-family: 'Manrope', sans-serif;
            font-size: 24px;
            color: #1E2A3E;
            margin-bottom: 8px;
        }
        .secure-banner p {
            color: #5B6E8C;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }
        .secure-banner .features {
            display: flex;
            gap: 30px;
            justify-content: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .secure-banner .features span {
            color: #5B6E8C;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .secure-banner .features span svg {
            color: #22C55E;
        }
        .faq-payment {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            margin: 40px 0;
        }
        .faq-payment h2 {
            font-family: 'Manrope', sans-serif;
            font-size: 28px;
            margin-bottom: 24px;
            color: #1E2A3E;
        }
        .faq-payment .faq-item {
            border-bottom: 1px solid #E1E8F0;
            padding: 16px 0;
        }
        .faq-payment .faq-item:last-child {
            border-bottom: none;
        }
        .faq-payment .faq-question {
            font-weight: 600;
            font-size: 16px;
            color: #1E2A3E;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: color 0.2s;
        }
        .faq-payment .faq-question:hover {
            color: #5F9EF0;
        }
        .faq-payment .faq-question .arrow {
            transition: transform 0.3s;
            font-size: 20px;
            color: #5B6E8C;
        }
        .faq-payment .faq-item.active .faq-question .arrow {
            transform: rotate(180deg);
        }
        .faq-payment .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, padding 0.3s ease;
            color: #5B6E8C;
            line-height: 1.6;
            font-size: 14px;
        }
        .faq-payment .faq-item.active .faq-answer {
            max-height: 200px;
            padding-top: 12px;
        }
        @media (max-width: 768px) {
            .payment-hero {
                padding: 40px 24px;
            }
            .payment-hero h1 {
                font-size: 28px;
            }
            .payment-grid {
                grid-template-columns: 1fr;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
            .secure-banner .features {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>

<main class="container">
    
    <!-- Hero-блок -->
    <div class="payment-hero" data-aos="fade-up" data-aos-duration="800">
        <h1>Безопасная оплата</h1>
        <p>Мы принимаем все популярные способы оплаты. Ваши данные надёжно защищены современными технологиями шифрования.</p>
    </div>

    <!-- Способы оплаты -->
    <h2 style="font-family: 'Manrope', sans-serif; font-size: 30px; margin-bottom: 20px;" data-aos="fade-up">Способы оплаты</h2>
    
    <div class="payment-grid">
        <!-- Банковские карты -->
        <div class="payment-card" data-aos="fade-up" data-aos-delay="100">
            <div class="icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                    <line x1="1" y1="10" x2="23" y2="10"/>
                </svg>
            </div>
            <h3>Банковские карты</h3>
            <div class="description">
                Принимаем все основные платёжные системы для быстрой и удобной оплаты.
            </div>
            <div class="icons-row">
                <span class="mini-icon">Visa</span>
                <span class="mini-icon">Mastercard</span>
                <span class="mini-icon">МИР</span>
                <span class="mini-icon">UnionPay</span>
            </div>
            <span class="badge">Самый популярный</span>
        </div>

        <!-- Электронные кошельки -->
        <div class="payment-card" data-aos="fade-up" data-aos-delay="200">
            <div class="icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v20M2 12h20"/>
                    <circle cx="12" cy="12" r="10"/>
                </svg>
            </div>
            <h3>Электронные кошельки</h3>
            <div class="description">
                Оплачивайте покупки через популярные электронные кошельки без комиссии.
            </div>
            <div class="icons-row">
                <span class="mini-icon">ЮMoney</span>
                <span class="mini-icon">Qiwi</span>
                <span class="mini-icon">WebMoney</span>
            </div>
            <span class="badge-blue">Без комиссии</span>
        </div>

        <!-- СБП -->
        <div class="payment-card" data-aos="fade-up" data-aos-delay="300">
            <div class="icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 6.5L12 13 2 6.5M22 6.5v12a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6.5M2 6.5L12 3l10 3.5"/>
                </svg>
            </div>
            <h3>СБП</h3>
            <div class="description">
                Система быстрых платежей — моментальное списание средств с вашего счёта.
            </div>
            <div class="icons-row">
                <span class="mini-icon">СБП</span>
                <span class="mini-icon">QR-код</span>
            </div>
            <span class="badge-yellow">Моментально</span>
        </div>

        <!-- Рассрочка -->
        <div class="payment-card" data-aos="fade-up" data-aos-delay="400">
            <div class="icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <h3>Рассрочка</h3>
            <div class="description">
                Покупайте товары в рассрочку без переплаты от надёжных банков-партнёров.
            </div>
            <div class="icons-row">
                <span class="mini-icon">СберБанк</span>
                <span class="mini-icon">Т-Банк</span>
                <span class="mini-icon">Альфа-Банк</span>
            </div>
            <span class="badge-purple">0% переплаты</span>
        </div>
    </div>

    <!-- Безопасность платежей -->
    <div class="secure-banner" data-aos="fade-up" data-aos-duration="800">
        <div class="icon-big">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <path d="M9 12l2 2 4-4"/>
            </svg>
        </div>
        <h3>Ваши данные под надёжной защитой</h3>
        <p>Мы используем современные протоколы шифрования для защиты ваших платёжных данных.</p>
        <div class="features">
            <span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                Шифрование данных
            </span>
            <span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                3D Secure
            </span>
            <span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                Безопасные платежи
            </span>
        </div>
    </div>

    <!-- Важная информация -->
    <div class="info-section" data-aos="fade-up" data-aos-delay="100">
        <h2>Важная информация об оплате</h2>
        <div class="info-grid">
            <div class="info-item">
                <h4>Комиссия</h4>
                <p>Мы не взимаем дополнительную комиссию за оплату банковскими картами и электронными кошельками.</p>
            </div>
            <div class="info-item">
                <h4>Возврат средств</h4>
                <p>Возврат денежных средств производится на ту же карту или счёт, с которого была произведена оплата. Срок возврата — от 3 до 10 рабочих дней.</p>
            </div>
            <div class="info-item">
                <h4>Чеки и документы</h4>
                <p>После оплаты вы получите электронный чек на указанный при оформлении email. Для юридических лиц предоставляются все необходимые документы.</p>
            </div>
            <div class="info-item">
                <h4>Конфиденциальность</h4>
                <p>Мы не храним данные ваших карт на своих серверах. Все платежи проходят через надёжные платёжные шлюзы.</p>
            </div>
        </div>
    </div>

    <!-- Часто задаваемые вопросы об оплате -->
    <div class="faq-payment" data-aos="fade-up" data-aos-delay="200">
        <h2>Часто задаваемые вопросы</h2>
        
        <div class="faq-item active">
            <div class="faq-question">
                Как происходит оплата банковской картой?
                <span class="arrow">▼</span>
            </div>
            <div class="faq-answer">
                Вы будете перенаправлены на защищённую страницу платёжного шлюза, где введёте данные карты. После подтверждения платежа вы вернётесь на наш сайт.
            </div>
        </div>
        
        <div class="faq-item">
            <div class="faq-question">
                Безопасно ли вводить данные карты на сайте?
                <span class="arrow">▼</span>
            </div>
            <div class="faq-answer">
                Да, все платежи проходят через защищённое соединение с использованием протокола TLS. Мы не храним данные ваших карт и не имеем к ним доступа.
            </div>
        </div>
        
        <div class="faq-item">
            <div class="faq-question">
                Как работает оплата через СБП?
                <span class="arrow">▼</span>
            </div>
            <div class="faq-answer">
                При выборе СБП на экране появится QR-код. Отсканируйте его в мобильном приложении банка и подтвердите платеж. Деньги спишутся мгновенно.
            </div>
        </div>
        
        <div class="faq-item">
            <div class="faq-question">
                Можно ли оплатить заказ частями?
                <span class="arrow">▼</span>
            </div>
            <div class="faq-answer">
                Да, вы можете оформить рассрочку от банков-партнёров. Узнайте условия у менеджера при оформлении заказа или на странице оформления.
            </div>
        </div>
        
        <div class="faq-item">
            <div class="faq-question">
                Как получить чек об оплате?
                <span class="arrow">▼</span>
            </div>
            <div class="faq-answer">
                Электронный чек будет отправлен на ваш email после успешной оплаты. Для юридических лиц мы также выставляем счёт и акт выполненных работ.
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