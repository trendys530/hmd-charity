// نظام ترجمة بسيط (i18n)
const I18N = {
    current: (typeof localStorage !== 'undefined' && localStorage.getItem('lang')) || 'ar',
    locales: {},
};

const setDirection = (lang) => {
    const isRTL = lang === 'ar';
    if (document && document.documentElement) {
        document.documentElement.lang = lang;
        document.documentElement.dir = isRTL ? 'rtl' : 'ltr';
    }
};

const t = (key) => {
    const dict = I18N.locales[I18N.current] || {};
    // دعم المفاتيح المتشعبة مثل a.b.c
    return key.split('.').reduce((acc, part) => (acc && acc[part] !== undefined ? acc[part] : undefined), dict);
};

const applyTranslations = (root = document) => {
    if (!root) return;
    const nodes = root.querySelectorAll('[data-i18n]');
    nodes.forEach((el) => {
        const key = el.getAttribute('data-i18n');
        const val = t(key);
        if (val !== undefined && val !== null) {
            // إذا كان العنصر زر/رابط نحافظ على البنية
            if ('textContent' in el) {
                el.textContent = val;
            }
        }
    });
    // العنوان في <title data-i18n="...">
    const titleEl = document.querySelector('head title[data-i18n]');
    if (titleEl) {
        const titleKey = titleEl.getAttribute('data-i18n');
        const titleVal = t(titleKey);
        if (titleVal) titleEl.textContent = titleVal;
    }
};

const loadLocale = async (lang) => {
    if (I18N.locales[lang]) {
        I18N.current = lang;
        setDirection(lang);
        applyTranslations();
        return;
    }
    try {
        const res = await fetch(`locales/${lang}.json`, { cache: 'no-store' });
        const json = await res.json();
        I18N.locales[lang] = json;
        I18N.current = lang;
        setDirection(lang);
        applyTranslations();
    } catch (e) {
        console.error('Failed to load locale', lang, e);
    }
};

const switchLanguage = (lang) => {
    if (!lang || I18N.current === lang) return;
    try { localStorage.setItem('lang', lang); } catch (_) {}
    loadLocale(lang);
};

const wireLangButtons = (root = document) => {
    const buttons = root.querySelectorAll('.lang-btn[data-lang]');
    buttons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const lang = btn.getAttribute('data-lang');
            switchLanguage(lang);
        });
    });
};

// تفعيل القائمة المنسدلة للهواتف المحمولة
const initMobileMenu = () => {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const nav = document.querySelector('.nav');
    
    if (mobileMenuBtn && nav) {
        mobileMenuBtn.addEventListener('click', function() {
            this.classList.toggle('active');
            nav.classList.toggle('active');
            document.body.style.overflow = nav.classList.contains('active') ? 'hidden' : '';
        });

        // إغلاق القائمة عند النقر على رابط
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenuBtn.classList.remove('active');
                nav.classList.remove('active');
                document.body.style.overflow = '';
            });
        });
    }
};

// تأثير التمرير السلس للروابط
const initSmoothScroll = () => {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#' || targetId.startsWith('#')) {
                e.preventDefault();
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    const headerHeight = document.querySelector('.header').offsetHeight;
                    const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
};

// شريط التقدم
const initProgressBar = () => {
    const progressBar = document.getElementById('myBar');
    if (!progressBar) return;
    
    window.onscroll = function() {
        const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrolled = (winScroll / height) * 100;
        progressBar.style.width = scrolled + '%';
    };
};

// تأثير التمرير للهيدر
const initScrollHeader = () => {
    const header = document.querySelector('.header');
    if (!header) return;
    
    let lastScroll = 0;
    const headerHeight = header.offsetHeight;
    
    // إضافة مسافة للهيدر الثابت
    document.documentElement.style.setProperty('--header-height', headerHeight + 'px');
    document.body.style.paddingTop = headerHeight + 'px';
    
    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;
        
        // إضافة/إزالة كلاس التمرير
        if (currentScroll > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        // إظهار/إخفاء الهيدر عند التمرير
        if (currentScroll > lastScroll && currentScroll > headerHeight) {
            // التمرير لأسفل
            header.style.transform = 'translateY(-100%)';
        } else {
            // التمرير لأعلى
            header.style.transform = 'translateY(0)';
        }
        
        lastScroll = currentScroll;
    });
};

// تفعيل تأثيرات الظهور للروابط
const initNavLinkAnimations = () => {
    const navLinks = document.querySelectorAll('.nav-links li');
    navLinks.forEach((link, index) => {
        // إضافة كلاس visible بشكل متدرج
        setTimeout(() => {
            link.classList.add('visible');
        }, 100 * index);
    });
};

// تهيئة جميع الوظائف عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    // i18n: ضبط الاتجاه وتحميل اللغة الحالية
    setDirection(I18N.current);
    loadLocale(I18N.current);

    initMobileMenu();
    initSmoothScroll();
    initProgressBar();
    initScrollHeader();
    initNavLinkAnimations();

    // تأثير ظهور العناصر عند التمرير
    const animateOnScroll = () => {
        const elements = document.querySelectorAll('.animate-on-scroll');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const screenPosition = window.innerHeight / 1.3;
            
            if (elementPosition < screenPosition) {
                element.classList.add('animated');
            }
        });
    };

    window.addEventListener('scroll', animateOnScroll);
    animateOnScroll(); // تحقق عند التحميل

    // عداد الأرقام
    const animateValue = (obj, start, end, duration) => {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            obj.innerHTML = Math.floor(progress * (end - start) + start).toLocaleString();
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    };

    // تفعيل العداد عند التمرير للقسم
    const startCounter = () => {
        const achievementsSection = document.querySelector('.achievements-section');
        if (!achievementsSection) return;
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counters = document.querySelectorAll('.counter');
                    counters.forEach(counter => {
                        const target = +counter.getAttribute('data-target');
                        animateValue(counter, 0, target, 2000);
                    });
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        observer.observe(achievementsSection);
    };

    startCounter();

    // إرسال نموذج الاشتراك في النشرة البريدية
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            
            // هنا يمكنك إضافة كود إرسال البريد الإلكتروني
            console.log('تم الاشتراك بالبريد:', email);
            
            // إظهار رسالة نجاح
            alert('شكراً لاشتراكك في نشرتنا البريدية!');
            this.reset();
        });
    }

    // إضافة تأثير التحميل للصور
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.getAttribute('data-src');
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));
});

// إضافة تأثير التمرير لشريط التنقل
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (!navbar) return;
    if (window.scrollY > 50) {
        navbar.style.padding = '10px 0';
        navbar.style.backgroundColor = 'rgba(255, 255, 255, 0.98)';
        navbar.style.boxShadow = '0 2px 15px rgba(0, 0, 0, 0.1)';
    } else {
        navbar.style.padding = '15px 0';
        navbar.style.backgroundColor = 'rgba(255, 255, 255, 1)';
        navbar.style.boxShadow = '0 2px 15px rgba(0, 0, 0, 0.1)';
    }
});

// عند حقن الهيدر ديناميكياً، قم بربط أزرار اللغة وتطبيق الترجمة عليه
document.addEventListener('header:loaded', () => {
    wireLangButtons(document);
    applyTranslations(document);
});
