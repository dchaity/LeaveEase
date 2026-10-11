// ============================================
// LEAVEEASE - MAIN JAVASCRIPT
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================
    // ANIMATE NUMBERS
    // ============================================
    const statValues = document.querySelectorAll('.stat-card-value');
    statValues.forEach(el => {
        const target = parseInt(el.textContent);
        if (!isNaN(target) && target > 0) {
            animateNumber(el, target);
        }
    });
    
    // ============================================
    // AUTO-HIDE ALERTS
    // ============================================
    setTimeout(() => {
        document.querySelectorAll('.alert:not(.alert-permanent)').forEach(alert => {
            alert.style.transition = 'all 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);
    
    // ============================================
    // SCROLL TO TOP BUTTON
    // ============================================
    const scrollBtn = document.createElement('button');
    scrollBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
    scrollBtn.style.cssText = `
        position: fixed;
        bottom: 100px;
        right: 30px;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: white;
        border: 1px solid var(--gray-200);
        box-shadow: var(--shadow-lg);
        cursor: pointer;
        display: none;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        font-size: 1rem;
        z-index: 998;
        transition: all 0.3s ease;
    `;
    document.body.appendChild(scrollBtn);
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            scrollBtn.style.display = 'flex';
        } else {
            scrollBtn.style.display = 'none';
        }
    });
    
    scrollBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    
    scrollBtn.addEventListener('mouseenter', () => {
        scrollBtn.style.transform = 'translateY(-3px)';
        scrollBtn.style.boxShadow = 'var(--shadow-xl)';
    });
    
    scrollBtn.addEventListener('mouseleave', () => {
        scrollBtn.style.transform = 'translateY(0)';
        scrollBtn.style.boxShadow = 'var(--shadow-lg)';
    });
});

// ============================================
// NUMBER ANIMATION
// ============================================
function animateNumber(element, target, duration = 1000) {
    const start = 0;
    const increment = target / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 16);
}

// ============================================
// CONFETTI ANIMATION
// ============================================
function launchConfetti() {
    const container = document.createElement('div');
    container.className = 'confetti-container';
    document.body.appendChild(container);
    
    const colors = ['#10b981', '#34d399', '#6ee7b7', '#fbbf24', '#f59e0b', '#3b82f6', '#8b5cf6'];
    
    for (let i = 0; i < 150; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        confetti.style.left = Math.random() * 100 + '%';
        confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.width = (Math.random() * 10 + 5) + 'px';
        confetti.style.height = (Math.random() * 10 + 5) + 'px';
        confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
        confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
        confetti.style.animationDelay = (Math.random() * 2) + 's';
        confetti.style.transform = `rotate(${Math.random() * 360}deg)`;
        container.appendChild(confetti);
    }
    
    setTimeout(() => {
        container.style.transition = 'opacity 1s ease';
        container.style.opacity = '0';
        setTimeout(() => container.remove(), 1000);
    }, 5000);
}

// ============================================
// NOTIFICATION SOUND
// ============================================
function playNotificationSound() {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const notes = [523.25, 659.25, 783.99, 1046.50];
        
        notes.forEach((freq, index) => {
            setTimeout(() => {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.frequency.value = freq;
                osc.type = 'sine';
                gain.gain.value = 0.1;
                osc.start();
                osc.stop(audioCtx.currentTime + 0.15);
            }, index * 100);
        });
    } catch(e) {
        console.log('Audio not supported');
    }
}

// ============================================
// FORM VALIDATION
// ============================================
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = 'var(--danger)';
            input.style.animation = 'shake 0.5s ease';
            setTimeout(() => {
                input.style.animation = '';
            }, 500);
            isValid = false;
        } else {
            input.style.borderColor = '';
        }
    });
    
    return isValid;
}

// ============================================
// SHAKE ANIMATION
// ============================================
const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-10px); }
        75% { transform: translateX(10px); }
    }
`;
document.head.appendChild(style);

console.log('✅ LeaveEase Main JS Loaded');