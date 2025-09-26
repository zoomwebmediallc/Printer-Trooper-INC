// --- Guest Cart Logic ---
// Key for localStorage
const GUEST_CART_KEY = 'printershop_guest_cart';
const GUEST_CART_EXPIRY_KEY = 'printershop_guest_cart_expiry';
const CART_EXPIRY_DAYS = 7;

// Utility: Get cart from localStorage
function getGuestCart() {
    const expiry = localStorage.getItem(GUEST_CART_EXPIRY_KEY);
    if (expiry && new Date().getTime() > parseInt(expiry)) {
        localStorage.removeItem(GUEST_CART_KEY);
        localStorage.removeItem(GUEST_CART_EXPIRY_KEY);
        return [];
    }
    const cart = localStorage.getItem(GUEST_CART_KEY);
    return cart ? JSON.parse(cart) : [];
}

// Utility: Save cart to localStorage with expiry
function saveGuestCart(cart) {
    localStorage.setItem(GUEST_CART_KEY, JSON.stringify(cart));
    const expiry = new Date().getTime() + CART_EXPIRY_DAYS * 24 * 60 * 60 * 1000;
    localStorage.setItem(GUEST_CART_EXPIRY_KEY, expiry.toString());
}

// Utility: Get total count
function getGuestCartCount() {
    return getGuestCart().reduce((sum, item) => sum + item.quantity, 0);
}

// Update cart badge in nav
function updateCartBadge() {
    const badge = document.querySelector('.cart-badge');
    if (badge) {
        const count = getGuestCartCount();
        // Only override when guest cart has items. Otherwise, keep server-rendered value.
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'inline-block';
        }
    }
}

// Add to cart handler for guest
function handleGuestAddToCart(e) {
    if (e.target.matches('.add-to-cart-form button')) {
        e.preventDefault();
        const form = e.target.closest('form');
        const productId = form.querySelector('input[name="product_id"]').value;
        const quantity = parseInt(form.querySelector('input[name="quantity"]').value) || 1;
        let cart = getGuestCart();
        const idx = cart.findIndex(item => item.product_id == productId);
        if (idx > -1) {
            cart[idx].quantity += quantity;
        } else {
            cart.push({ product_id: productId, quantity });
        }
        saveGuestCart(cart);
        updateCartBadge();
        // Optionally show a message
        alert('Product added to cart!');
    }
}

// Attach add-to-cart handler on DOMContentLoaded
document.addEventListener('DOMContentLoaded', function () {
    updateCartBadge();
    if (document.body) {
        document.body.addEventListener('submit', handleGuestAddToCart);
    }
    // Contact form validation
    const contactForm = document.querySelector('form.contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function (e) {
            // Clear previous errors
            contactForm.querySelectorAll('.field-error').forEach(el => el.remove());
            let valid = true;

            const nameEl = contactForm.querySelector('#name');
            const emailEl = contactForm.querySelector('#email');
            const subjectEl = contactForm.querySelector('#subject');
            const messageEl = contactForm.querySelector('#message');

            const addError = (el, msg) => {
                valid = false;
                const err = document.createElement('div');
                err.className = 'field-error';
                err.textContent = msg;
                err.style.color = '#c0392b';
                err.style.fontSize = '0.85rem';
                err.style.marginTop = '4px';
                if (el && el.parentElement) {
                    el.parentElement.appendChild(err);
                }
            };

            const name = (nameEl?.value || '').trim();
            if (name.length < 2) addError(nameEl, 'Please enter your name (min 2 characters).');

            const email = (emailEl?.value || '').trim();
            const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            if (!emailOk) addError(emailEl, 'Please enter a valid email address.');

            const subject = (subjectEl?.value || '').trim();
            if (subject.length < 3) addError(subjectEl, 'Please enter a subject (min 3 characters).');

            const message = (messageEl?.value || '').trim();
            if (message.length < 10) addError(messageEl, 'Please enter a message (min 10 characters).');

            if (!valid) {
                e.preventDefault();
                // Focus first invalid field
                const firstErr = contactForm.querySelector('.field-error');
                if (firstErr && firstErr.previousElementSibling && typeof firstErr.previousElementSibling.focus === 'function') {
                    firstErr.previousElementSibling.focus();
                }
            }
        });
    }
});
// Preloader
window.addEventListener('load', function () {
    setTimeout(function () {
        const preloader = document.getElementById('preloader');
        if (preloader) preloader.classList.add('hide');
    }, 1000);
});

// Set minimum date to today - only if date field exists
document.addEventListener('DOMContentLoaded', function () {
    const dateField = document.getElementById('date');
    if (dateField) {
        const today = new Date().toISOString().split('T')[0];
        dateField.setAttribute('min', today);
    }
});

// (Removed) Appointment page toggles and optional AJAX handler to keep code lean.

// Smooth animations on scroll
window.addEventListener('scroll', function () {
    const cards = document.querySelectorAll('.service-card');
    cards.forEach(card => {
        const cardTop = card.getBoundingClientRect().top;
        const cardVisible = cardTop < window.innerHeight - 100;

        if (cardVisible) {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }
    });
});

// Hero Slider Functionality (guarded for pages without hero)
let currentSlide = 0;
const slides = document.querySelectorAll('.hero-slide');
const dots = document.querySelectorAll('.hero-nav-dot');

function showSlide(index) {
    if (!slides.length) return;
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));
    if (slides[index]) slides[index].classList.add('active');
    if (dots[index]) dots[index].classList.add('active');
    currentSlide = index;
}

function nextSlide() {
    if (!slides.length) return;
    const next = (currentSlide + 1) % slides.length;
    showSlide(next);
}

// Auto slide every 5 seconds only if slides exist
if (slides.length) {
    setInterval(nextSlide, 5000);
}

// Dot navigation (safe if empty)
dots.forEach((dot, index) => {
    dot.addEventListener('click', () => showSlide(index));
});

// Product Carousel Functionality (guarded for pages without carousel)
const productTrack = document.getElementById('productTrack');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');
let currentPosition = 0;
const cardWidth = 370; // 350px + 20px gap
const hasCarousel = !!(productTrack && prevBtn && nextBtn);
let visibleCards = hasCarousel ? Math.floor(window.innerWidth / cardWidth) : 0;
let totalCards = hasCarousel ? document.querySelectorAll('.product-card').length : 0;
let maxPosition = hasCarousel ? Math.max(0, (totalCards - visibleCards) * cardWidth) : 0;

function updateCarousel() {
    if (!hasCarousel) return;
    productTrack.style.transform = `translateX(-${currentPosition}px)`;
    // Update button states
    prevBtn.style.opacity = currentPosition === 0 ? '0.5' : '1';
    nextBtn.style.opacity = currentPosition >= maxPosition ? '0.5' : '1';
}

if (hasCarousel) {
    prevBtn.addEventListener('click', () => {
        if (currentPosition > 0) {
            currentPosition = Math.max(0, currentPosition - cardWidth);
            updateCarousel();
        }
    });

    nextBtn.addEventListener('click', () => {
        if (currentPosition < maxPosition) {
            currentPosition = Math.min(maxPosition, currentPosition + cardWidth);
            updateCarousel();
        }
    });
}

// FAQ Functionality
document.querySelectorAll('.faq-item').forEach(item => {
    const question = item.querySelector('.faq-question');
    const answer = item.querySelector('.faq-answer');
    if (!question || !answer) return;

    question.addEventListener('click', () => {
        const isActive = item.classList.contains('active');

        // Close all FAQ items
        document.querySelectorAll('.faq-item').forEach(faq => {
            faq.classList.remove('active');
            const a = faq.querySelector('.faq-answer');
            if (a) a.classList.remove('active');
        });

        // Open clicked item if it wasn't active
        if (!isActive) {
            item.classList.add('active');
            answer.classList.add('active');
        }
    });
});

// Mobile Menu Toggle
const mobileToggle = document.getElementById('mobileToggle');
const navMenu = document.getElementById('navMenu');

if (mobileToggle && navMenu) {
    mobileToggle.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        const icon = mobileToggle.querySelector('i');
        if (icon) {
            icon.className = navMenu.classList.contains('active') ? 'fas fa-times' : 'fas fa-bars';
        }
    });
}

// Header scroll effect
window.addEventListener('scroll', () => {
    const header = document.getElementById('header');
    if (!header) return;
    if (window.scrollY > 100) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Initialize carousel on page load
window.addEventListener('load', () => {
});