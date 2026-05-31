// ============================================
// BGAI - Main JavaScript
// ============================================

// ---- Mobile Menu ----
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    const overlay = document.getElementById('mobileMenuOverlay');
    menu.classList.toggle('show');
    overlay.classList.toggle('show');
    document.body.style.overflow = menu.classList.contains('show') ? 'hidden' : '';
}

// ---- Search Bar ----
function toggleSearch() {
    const searchBar = document.getElementById('searchBar');
    searchBar.classList.toggle('active');
    if (searchBar.classList.contains('active')) {
        searchBar.querySelector('input').focus();
    }
}

// ---- Country Selector ----
function toggleCountryDropdown() {
    const dropdown = document.getElementById('countryDropdown');
    dropdown.classList.toggle('show');
}

function changeCountry(country, currency) {
    fetch((window.APP_URL || '') + '/api/country.php?country=' + country + '&currency=' + currency)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('currentCurrency').textContent = data.currency;
                document.getElementById('countryDropdown').classList.remove('show');
                location.reload();
            }
        })
        .catch(() => {
            // Fallback
            location.href = (window.APP_URL || '') + '/api/country.php?country=' + country + '&currency=' + currency;
        });
}

// Close dropdowns on click outside
document.addEventListener('click', function(e) {
    const countrySelector = document.getElementById('countrySelector');
    if (countrySelector && !countrySelector.contains(e.target)) {
        document.getElementById('countryDropdown').classList.remove('show');
    }
});

// ---- Sticky Header ----
window.addEventListener('scroll', function() {
    const header = document.getElementById('mainHeader');
    if (header) {
        header.classList.toggle('scrolled', window.scrollY > 50);
    }
});

// ---- Scroll Reveal Animation ----
const revealElements = document.querySelectorAll('.reveal');
const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            revealObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

revealElements.forEach(el => revealObserver.observe(el));

// ---- Newsletter ----
function handleNewsletter(e) {
    e.preventDefault();
    const input = e.target.querySelector('input');
    const email = input.value.trim();
    
    if (!email || !email.includes('@')) {
        showToast('Please enter a valid email address', 'error');
        return;
    }
    
    fetch((window.APP_URL || '') + '/api/newsletter.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({email: email})
    })
    .then(r => r.json())
    .then(data => {
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) input.value = '';
    })
    .catch(() => {
        showToast('Something went wrong. Please try again.', 'error');
    });
}

// ---- Toast Notifications ----
function showToast(message, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    
    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        info: 'fas fa-info-circle'
    };
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <i class="${icons[type] || icons.info}"></i>
        <span class="toast-message">${message}</span>
        <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:var(--gray-400);padding:4px;">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    container.appendChild(toast);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// ---- Auto-hide flash messages ----
setTimeout(() => {
    const flash = document.getElementById('flashMessage');
    if (flash) {
        flash.style.opacity = '0';
        flash.style.transition = 'opacity 0.3s ease';
        setTimeout(() => flash.remove(), 300);
    }
}, 5000);

// ---- Smooth scroll for anchor links ----
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

// ---- Image lazy loading fallback ----
document.querySelectorAll('img').forEach(img => {
    img.addEventListener('error', function() {
        if (!this.dataset.retried) {
            this.dataset.retried = '1';
            const placeholder = document.createElement('div');
            placeholder.className = 'product-placeholder';
            placeholder.innerHTML = '<i class="fas fa-gem"></i>';
            this.replaceWith(placeholder);
        }
    });
});

// ---- Keyboard shortcuts ----
document.addEventListener('keydown', function(e) {
    // ESC to close modals/menus
    if (e.key === 'Escape') {
        document.getElementById('mobileMenu')?.classList.remove('show');
        document.getElementById('mobileMenuOverlay')?.classList.remove('show');
        document.getElementById('searchBar')?.classList.remove('active');
        document.body.style.overflow = '';
        document.getElementById('countryDropdown')?.classList.remove('show');
    }
    // Ctrl+K for search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        toggleSearch();
    }
});

console.log('%c✦ BGAI - Brilliance, Gems of Australia International ✦', 'font-size:14px; color:#d4af37; font-weight:bold; background:#0a0a0a; padding:8px 16px; border-radius:4px;');
