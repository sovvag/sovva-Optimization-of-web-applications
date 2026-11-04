// Анимация при скролле
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('.product-card, .hero').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(30px)';
    el.style.transition = 'all 0.6s ease';
    observer.observe(el);
});

// Работа с корзиной через fetch
document.querySelectorAll('.add-to-cart').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        e.stopPropagation();
        const productId = btn.dataset.id;
        
        const response = await fetch('cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=add&id=${productId}`
        });
        
        const data = await response.json();
        if (data.success) {
            updateCartCount(data.count);
            showNotification('Товар добавлен в корзину');
        }
    });
});

function updateCartCount(count) {
    const counter = document.querySelector('.cart-count');
    if (counter) counter.textContent = count;
}

function showNotification(msg) {
    const notif = document.createElement('div');
    notif.textContent = msg;
    notif.style.cssText = `
        position: fixed; bottom: 20px; right: 20px;
        background: #5F9EF0; color: white; padding: 12px 24px;
        border-radius: 40px; z-index: 1000; animation: fadeInUp 0.3s;
    `;
    document.body.appendChild(notif);
    setTimeout(() => notif.remove(), 2000);
}
