<?php include 'includes/db.php'; ?>
<?php include 'includes/functions.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lens & Look - Интернет-магазин оптики</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- Slick Slider CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
    
    <!-- AOS Animation CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        /* Дополнительные стили для слайдера отзывов и FAQ */
        
        /* Блок отзывов */
        .reviews-section {
            padding: 60px 0;
            background: linear-gradient(135deg, #F8FAFF 0%, #FFFFFF 100%);
            border-radius: 32px;
            margin: 40px 0;
        }
        
        .reviews-title {
            text-align: center;
            font-size: 36px;
            margin-bottom: 50px;
            font-weight: 800;
            background: linear-gradient(135deg, #1E2A3E, #5F9EF0);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .review-slide {
            padding: 0 15px;
        }
        
        .review-card {
            background: white;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: row;
            align-items: center;
            min-height: 320px;
        }
        
        .review-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(95,158,240,0.15);
        }
        
        .review-image {
            flex: 0 0 200px;
            background: #F0F4FF;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .review-image img {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #5F9EF0;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        
        .review-content {
            flex: 1;
            padding: 30px 30px 30px 20px;
        }
        
        .review-name {
            font-size: 24px;
            font-weight: 700;
            color: #1E2A3E;
            margin-bottom: 15px;
            font-family: 'Manrope', sans-serif;
        }
        
        .review-text {
            font-size: 16px;
            line-height: 1.6;
            color: #5B6E8C;
            font-style: italic;
        }
        
        .review-rating {
            margin-bottom: 12px;
            color: #FFB800;
            letter-spacing: 2px;
        }
        
        /* Стили для FAQ аккордеона */
        .faq-section {
            padding: 60px 0;
            background: white;
            border-radius: 32px;
            margin: 40px 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        
        .faq-title {
            text-align: center;
            font-size: 36px;
            margin-bottom: 50px;
            font-weight: 800;
            color: #1E2A3E;
        }
        
        .faq-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .faq-item {
            background: #F8F9FC;
            margin-bottom: 16px;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s;
        }
        
        .faq-question {
            padding: 22px 30px;
            font-size: 18px;
            font-weight: 600;
            color: #1E2A3E;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s;
            font-family: 'Manrope', sans-serif;
        }
        
        .faq-question:hover {
            background: #EEF3FF;
        }
        
        .faq-question .icon {
            font-size: 24px;
            font-weight: 300;
            transition: transform 0.3s;
            color: #5F9EF0;
        }
        
        .faq-item.active .faq-question .icon {
            transform: rotate(45deg);
        }
        
        .faq-answer {
            max-height: 0;
            padding: 0 30px;
            color: #5B6E8C;
            line-height: 1.6;
            overflow: hidden;
            transition: all 0.3s ease-out;
        }
        
        .faq-item.active .faq-answer {
            max-height: 200px;
            padding: 0 30px 22px 30px;
        }
        
        /* Иконки-заглушки вместо эмодзи */
        .icon-placeholder {
            display: inline-block;
            width: 24px;
            height: 24px;
            background: #5F9EF0;
            mask-size: contain;
            -webkit-mask-size: contain;
            mask-repeat: no-repeat;
            -webkit-mask-repeat: no-repeat;
            mask-position: center;
            -webkit-mask-position: center;
        }
        
        /* Адаптив */
        @media (max-width: 768px) {
            .review-card {
                flex-direction: column;
                text-align: center;
            }
            .review-image {
                padding-top: 30px;
            }
            .review-content {
                padding: 20px;
            }
            .faq-question {
                font-size: 16px;
                padding: 18px 20px;
            }
            .reviews-title, .faq-title {
                font-size: 28px;
            }
        }
        
        /* Кастомные точки для Slick Slider */
        .slick-dots li button:before {
            font-size: 10px;
            color: #5F9EF0;
        }
        .slick-dots li.slick-active button:before {
            color: #3A7BD5;
        }
        
        .slick-prev:before, .slick-next:before {
            color: #5F9EF0;
            font-size: 28px;
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<main class="container">
    
    <!-- Hero-блок с анимацией AOS -->
    <div class="hero" data-aos="fade-up" data-aos-duration="800">
        <div class="hero-text">
            <h1>Идеальное зрение<br>начинается здесь</h1>
            <p>Очки, контактные линзы от ведущих брендов</p>
            <a href="catalog.php" class="btn">В каталог →</a>
        </div>
        <div class="hero-image">
            <img src="./assets/images/glasses_hero.png" alt="Модные очки">
        </div>
    </div>

    <!-- Новые поступления с анимацией -->
    <section data-aos="fade-up" data-aos-delay="100">
        <h2>Новые поступления</h2>
        <div class="products-grid">
            <?php
            $stmt = $pdo->query("SELECT * FROM products WHERE is_new = 1 LIMIT 4");
            while($product = $stmt->fetch(PDO::FETCH_ASSOC)):
            ?>
            <div class="product-card" onclick="window.location.href='product.php?id=<?= $product['id'] ?>'">
                <img src="assets/images/<?= $product['image'] ?>" onerror="this.src='https://placehold.co/300x200/5F9EF0/white?text=Product'">
                <div class="product-info">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <div class="price"><?= number_format($product['price'], 0, ',', ' ') ?> ₽</div>
                    <button class="btn add-to-cart" data-id="<?= $product['id'] ?>">В корзину</button>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- Популярное с анимацией -->
    <section data-aos="fade-up" data-aos-delay="200">
        <h2>Популярное</h2>
        <div class="products-grid">
            <?php
            $stmt = $pdo->query("SELECT * FROM products WHERE is_popular = 1 LIMIT 4");
            while($product = $stmt->fetch(PDO::FETCH_ASSOC)):
            ?>
            <div class="product-card" onclick="window.location.href='product.php?id=<?= $product['id'] ?>'">
                <img src="assets/images/<?= $product['image'] ?>" onerror="this.src='https://placehold.co/300x200/5F9EF0/white?text=Popular'">
                <div class="product-info">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <div class="price"><?= number_format($product['price'], 0, ',', ' ') ?> ₽</div>
                    <button class="btn add-to-cart" data-id="<?= $product['id'] ?>">Купить</button>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- ========== БЛОК ОТЗЫВОВ (Slick Slider) ========== -->
    <div class="reviews-section" data-aos="fade-up" data-aos-delay="100">
        <div class="container">
            <h2 class="reviews-title">Отзывы наших клиентов</h2>
            <div class="reviews-slider">
                
                <!-- Отзыв 1 -->
                <div class="review-slide">
                    <div class="review-card">
                        <div class="review-image">
                            <img src="assets/images/reviews/review6.jpg" alt="Анна С." onerror="this.src='https://placehold.co/140x140/5F9EF0/white?text=Анна'">
                        </div>
                        <div class="review-content">
                            <div class="review-rating">★★★★★</div>
                            <div class="review-name">Анна Смирнова</div>
                            <div class="review-text">"Заказала первые в жизни контактные линзы. Консультанты помогли с выбором, доставили очень быстро. Линзы комфортные, глаза не сохнут. Спасибо!"</div>
                        </div>
                    </div>
                </div>
                
                <!-- Отзыв 2 -->
                <div class="review-slide">
                    <div class="review-card">
                        <div class="review-image">
                            <img src="assets/images/reviews/review2.jpg" alt="Дмитрий К." onerror="this.src='https://placehold.co/140x140/5F9EF0/white?text=Дмитрий'">
                        </div>
                        <div class="review-content">
                            <div class="review-rating">★★★★★</div>
                            <div class="review-name">Дмитрий Ковалёв</div>
                            <div class="review-text">"Отличные солнцезащитные очки! Заказывал Polaroid, качество оригинал, цена приятно удивила. Буду заказывать ещё."</div>
                        </div>
                    </div>
                </div>
                
                <!-- Отзыв 3 -->
                <div class="review-slide">
                    <div class="review-card">
                        <div class="review-image">
                            <img src="assets/images/reviews/review3.jpg" alt="Елена М." onerror="this.src='https://placehold.co/140x140/5F9EF0/white?text=Елена'">
                        </div>
                        <div class="review-content">
                            <div class="review-rating">★★★★★</div>
                            <div class="review-name">Елена Михайлова</div>
                            <div class="review-text">"Очень довольна сервисом! Быстрая примерка на сайте, удобная оплата картой. Доставили за 2 дня. Рекомендую!"</div>
                        </div>
                    </div>
                </div>
                
                <!-- Отзыв 4 -->
                <div class="review-slide">
                    <div class="review-card">
                        <div class="review-image">
                            <img src="assets/images/reviews/review4.jpg" alt="Максим Т." onerror="this.src='https://placehold.co/140x140/5F9EF0/white?text=Максим'">
                        </div>
                        <div class="review-content">
                            <div class="review-rating">★★★★★</div>
                            <div class="review-name">Максим Тарасов</div>
                            <div class="review-text">"Покупал оправу для очков - огромный выбор. Качество отличное, ценник адекватный. Отдельное спасибо за подбор по лицу."</div>
                        </div>
                    </div>
                </div>
                
                <!-- Отзыв 5 -->
                <div class="review-slide">
                    <div class="review-card">
                        <div class="review-image">
                            <img src="assets/images/reviews/review5.jpg" alt="Ольга В." onerror="this.src='https://placehold.co/140x140/5F9EF0/white?text=Ольга'">
                        </div>
                        <div class="review-content">
                            <div class="review-rating">★★★★★</div>
                            <div class="review-name">Ольга Ветрова</div>
                            <div class="review-text">"Линзы Biotrue - лучшие! Сайт удобный, оплатила без проблем, курьер приехал точно вовремя. Буду заказывать только здесь."</div>
                        </div>
                    </div>
                </div>
                
                <!-- Отзыв 6 -->
                <div class="review-slide">
                    <div class="review-card">
                        <div class="review-image">
                            <img src="assets/images/reviews/review1.jpg" alt="Игорь Н." onerror="this.src='https://placehold.co/140x140/5F9EF0/white?text=Игорь'">
                        </div>
                        <div class="review-content">
                            <div class="review-rating">★★★★★</div>
                            <div class="review-name">Игорь Никитин</div>
                            <div class="review-text">"Заказывал сразу 2 пары солнцезащитных очков для себя и жены. Отличная упаковка, подарок внутри. Очень приятно!"</div>
                        </div>
                    </div>
                </div>
                
                <!-- Отзыв 7 -->
                <div class="review-slide">
                    <div class="review-card">
                        <div class="review-image">
                            <img src="assets/images/reviews/review7.jpg" alt="Татьяна П." onerror="this.src='https://placehold.co/140x140/5F9EF0/white?text=Татьяна'">
                        </div>
                        <div class="review-content">
                            <div class="review-rating">★★★★★</div>
                            <div class="review-name">Татьяна Павлова</div>
                            <div class="review-text">"Очень крутой магазин! Поддержка работает отлично, помогли с выбором линз для чувствительных глаз. Спасибо команде Lens & Look!"</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== БЛОК ВОПРОС-ОТВЕТ (FAQ ACCORDION) ========== -->
    <div class="faq-section" data-aos="fade-up" data-aos-delay="200">
        <div class="container">
            <h2 class="faq-title">Часто задаваемые вопросы</h2>
            <div class="faq-container">
                
                <div class="faq-item">
                    <div class="faq-question">
                        Как оформить заказ?
                        <span class="icon">+</span>
                    </div>
                    <div class="faq-answer">
                        Добавьте нужные товары в корзину, перейдите в корзину, нажмите "Оформить заказ", заполните контактные данные и выберите способ доставки. После этого наш менеджер свяжется с вами для подтверждения.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        Оплата на сайте
                        <span class="icon">+</span>
                    </div>
                    <div class="faq-answer">
                        Мы принимаем оплату банковскими картами (Visa, MasterCard, МИР), а также через Apple Pay и Google Pay. Все платежи защищены современными протоколами шифрования.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        Доставка
                        <span class="icon">+</span>
                    </div>
                    <div class="faq-answer">
                        Доставка по Москве и Санкт-Петербургу — 1-2 дня, по России — 3-7 дней. Стоимость доставки от 200 рублей, при заказе от 3000 рублей — бесплатно. Возможен самовывоз из пунктов выдачи.
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</main>

<?php include 'includes/footer.php'; ?>

<!-- Подключаем JS библиотеки -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="assets/js/main.js"></script>

<script>
    // Инициализация Slick Slider для отзывов
    $(document).ready(function(){
        $('.reviews-slider').slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            autoplay: true,
            autoplaySpeed: 4000,
            dots: true,
            arrows: true,
            infinite: true,
            speed: 500,
            responsive: [
                {
                    breakpoint: 768,
                    settings: {
                        arrows: false,
                        dots: true
                    }
                }
            ]
        });
        
        // Анимация AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
        
        // FAQ аккордеон
        $('.faq-question').click(function(){
            const parent = $(this).parent('.faq-item');
            parent.toggleClass('active');
            
            // Закрыть другие открытые вопросы (опционально)
            $('.faq-item').not(parent).removeClass('active');
        });
    });
</script>

</body>
</html>