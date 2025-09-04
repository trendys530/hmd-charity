// كود لتحريك العدادات
function animateCounters() {
    const counters = document.querySelectorAll('.counter');
    const speed = 200; // السرعة بالمللي ثانية
    
    counters.forEach(counter => {
        const target = +counter.getAttribute('data-target');
        const count = +counter.innerText;
        const increment = target / speed;

        if (count < target) {
            counter.innerText = Math.ceil(count + increment);
            setTimeout(animateCounters, 1);
        } else {
            counter.innerText = target;
        }
    });
}

// تشغيل العدادات عند التمرير للقسم
function handleScroll() {
    const statsSection = document.querySelector('.achievements-stats');
    if (statsSection) {
        const sectionTop = statsSection.offsetTop;
        const sectionHeight = statsSection.offsetHeight;
        const windowHeight = window.innerHeight;
        const scrollY = window.scrollY;

        if (scrollY > (sectionTop + sectionHeight - windowHeight)) {
            animateCounters();
            window.removeEventListener('scroll', handleScroll);
        }
    }
}

// تهيئة الصفحة
document.addEventListener('DOMContentLoaded', function() {
    // إضافة مستمع لحدث التمرير
    window.addEventListener('scroll', handleScroll);
    
    // تفعيل القائمة المتحركة للهواتف
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const nav = document.querySelector('.nav');
    
    if (mobileMenuBtn && nav) {
        mobileMenuBtn.addEventListener('click', function() {
            nav.classList.toggle('active');
            this.classList.toggle('active');
        });
    }
});
