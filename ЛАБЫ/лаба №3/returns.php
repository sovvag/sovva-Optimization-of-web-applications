<?php 
include 'includes/db.php'; 
include 'includes/header.php'; 
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Возврат и гарантия - Lens & Look</title>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        .returns-hero {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            border-radius: 32px;
            padding: 50px 48px;
            color: white;
            margin: 40px 0 50px;
            position: relative;
            overflow: hidden;
        }
        .returns-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 400px;
            height: 400px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }
        .returns-hero h1 {
            font-family: 'Manrope', sans-serif;
            font-size: 38px;
            font-weight: 800;
            margin-bottom: 12px;
            position: relative;
            z-index: 1;
        }
        .returns-hero p {
            font-size: 17px;
            opacity: 0.9;
            max-width: 500px;
            position: relative;
            z-index: 1;
            line-height: 1.6;
        }
        .returns-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin: 40px 0;
        }
        .returns-card {
            background: white;
            border-radius: 24px;
            padding: 32px 28px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
            border: 1px solid #F0F4FF;
        }
        .returns-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 40px rgba(79,70,229,0.12);
            border-color: #4F46E5;
        }
        .returns-card .icon {
            width: 48px;
            height: 48px;
            background: #EEF2FF;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            color: #4F46E5;
        }
        .returns-card h3 {
            font-family: 'Manrope', sans-serif;
            font-size: 20px;
            margin-bottom: 8px;
            color: #1E2A3E;
        }
        .returns-card .subtitle {
            color: #5B6E8C;
            font-size: 14px;
            margin-bottom: 12px;
        }
        .returns-card .description {
            color: #5B6E8C;
            line-height: 1.7;
            font-size: 14px;
        }
        .returns-card .highlight-number {
            font-size: 36px;
            font-weight: 800;
            color: #4F46E5;
            display: block;
            margin: 8px 0 4px;
        }
        .returns-card .highlight-text {
            color: #4F46E5;
            font-weight: 600;
        }
        .returns-card ul {
            list-style: none;
            padding: 0;
            margin: 12px 0 0;
        }
        .returns-card ul li {
            padding: 6px 0;
            color: #5B6E8C;
            font-size: 14px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            line-height: 1.5;
        }
        .returns-card ul li svg {
            flex-shrink: 0;
            margin-top: 2px;
            color: #22C55E;
        }
        .rule-card {
            background: #FAFAFA;
            border-radius: 16px;
            padding: 20px 24px;
            margin-top: 16px;
            border-left: 4px solid #4F46E5;
        }
        .rule-card p {
            color: #1E2A3E;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }
        .rule-card .label {
            font-weight: 600;
            color: #4F46E5;
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
            font-size: 24px;
            margin-bottom: 20px;
            color: #1E2A3E;
        }
        .info-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .step-item {
            text-align: center;
            padding: 20px;
            background: #F8F9FC;
            border-radius: 16px;
            transition: all 0.3s;
        }
        .step-item:hover {
            background: #EEF2FF;
        }
        .step-item .step-number {
            width: 40px;
            height: 40px;
            background: #4F46E5;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
            margin: 0 auto 12px;
        }
        .step-item h4 {
            font-family: 'Manrope', sans-serif;
            font-size: 15px;
            margin-bottom: 6px;
            color: #1E2A3E;
        }
        .step-item p {
            color: #5B6E8C;
            font-size: 13px;
            line-height: 1.5;
        }
        .contact-block {
            background: #F8F9FC;
            border-radius: 24px;
            padding: 32px 40px;
            margin: 40px 0;
            text-align: center;
            border: 1px solid #E1E8F0;
        }
        .contact-block h3 {
            font-family: 'Manrope', sans-serif;
            font-size: 20px;
            margin-bottom: 8px;
            color: #1E2A3E;
        }
        .contact-block p {
            color: #5B6E8C;
            font-size: 14px;
            line-height: 1.6;
        }
        .contact-block .contact-info {
            display: flex;
            gap: 30px;
            justify-content: center;
            margin-top: 16px;
            flex-wrap: wrap;
        }
        .contact-block .contact-info span {
            color: #1E2A3E;
            font-weight: 500;
            font-size: 15px;
        }
        .contact-block .contact-info .highlight {
            color: #4F46E5;
        }
        @media (max-width: 768px) {
            .returns-hero {
                padding: 32px 24px;
            }
            .returns-hero h1 {
                font-size: 28px;
            }
            .returns-grid {
                grid-template-columns: 1fr;
            }
            .info-steps {
                grid-template-columns: 1fr;
            }
            .contact-block .contact-info {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>

<main class="container">
    
    <!-- Hero-блок -->
    <div class="returns-hero" data-aos="fade-up" data-aos-duration="800">
        <h1>Возврат и гарантия</h1>
        <p>Мы заботимся о вашем комфорте. Если товар не подошёл — верните его без лишних вопросов.</p>
    </div>

    <!-- Основные карточки -->
    <div class="returns-grid">
        <!-- Сроки возврата -->
        <div class="returns-card" data-aos="fade-up" data-aos-delay="100">
            <div class="icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <h3>Сроки возврата</h3>
            <div class="subtitle">У вас есть время на раздумья</div>
            <span class="highlight-number">14</span>
            <div class="description">
                дней с момента получения товара для возврата или обмена.
            </div>
            <div class="rule-card">
                <p><span class="label">Важно:</span> Срок начинает отсчитываться со дня получения заказа, а не с момента оформления.</p>
            </div>
        </div>

        <!-- Условия -->
        <div class="returns-card" data-aos="fade-up" data-aos-delay="200">
            <div class="icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <h3>Условия возврата</h3>
            <div class="subtitle">Что нужно знать</div>
            <ul>
                <li>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    Товар должен быть в <span class="highlight-text">первозданном виде</span>
                </li>
                <li>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    Сохранены <span class="highlight-text">ярлыки и упаковка</span>
                </li>
                <li>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    Товар не был в <span class="highlight-text">использовании</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Гарантия -->
    <div class="returns-card" data-aos="fade-up" data-aos-delay="300" style="margin-top: 0;">
        <div class="icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <path d="M9 12l2 2 4-4"/>
            </svg>
        </div>
        <h3>Гарантия на товары</h3>
        <div class="subtitle">Мы уверены в качестве</div>
        <div style="display: flex; align-items: baseline; gap: 8px; margin: 8px 0;">
            <span class="highlight-number">12</span>
            <span style="font-size: 20px; font-weight: 600; color: #1E2A3E;">месяцев</span>
        </div>
        <div class="description">
            Гарантия действует на все товары. При обнаружении производственного брака мы бесплатно заменим товар или вернём деньги.
        </div>
        <div class="rule-card" style="border-left-color: #22C55E;">
            <p><span class="label" style="color: #22C55E;">Гарантия покрывает:</span> производственные дефекты, неисправности оправы, отслоение покрытия линз.</p>
        </div>
    </div>

    <!-- Как вернуть товар -->
    <div class="info-section" data-aos="fade-up" data-aos-delay="400">
        <h2>Как вернуть товар</h2>
        <div class="info-steps">
            <div class="step-item">
                <div class="step-number">1</div>
                <h4>Свяжитесь с нами</h4>
                <p>Напишите нам на почту или позвоните, сообщите номер заказа и причину возврата.</p>
            </div>
            <div class="step-item">
                <div class="step-number">2</div>
                <h4>Упакуйте товар</h4>
                <p>Соберите товар в заводскую упаковку с сохранением ярлыков и бирок.</p>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <h4>Отправьте возврат</h4>
                <p>Мы предоставим адрес для отправки. Вы можете использовать любую удобную службу доставки.</p>
            </div>
            <div class="step-item">
                <div class="step-number">4</div>
                <h4>Получите возврат</h4>
                <p>После проверки товара мы вернём деньги на ту же карту или счёт в течение 3-10 рабочих дней.</p>
            </div>
        </div>
    </div>

    <!-- Контакты -->
    <div class="contact-block" data-aos="fade-up" data-aos-delay="500">
        <h3>Остались вопросы?</h3>
        <p>Наши менеджеры помогут вам с оформлением возврата или ответят на любые вопросы</p>
        <div class="contact-info">
            <span class="highlight">8-800-555-01-01</span>
            <span class="highlight">returns@lenslook.ru</span>
            <span> Пн-Пт: 10:00 - 20:00</span>
        </div>
    </div>

</main>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 600,
        once: true,
        offset: 50
    });
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>