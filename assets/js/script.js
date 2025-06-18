/**
 * Nikoh Portali - JavaScript funksiyalar
 */

document.addEventListener('DOMContentLoaded', function() {
    // Form validatsiya
    const forms = document.querySelectorAll('form[data-validate="true"]');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });

    // Pasport input formatlash
    const passportInputs = document.querySelectorAll('input[data-validate="passport"]');
    passportInputs.forEach(function(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            if (value.length > 2) {
                value = value.substring(0, 2) + value.substring(2).replace(/[^0-9]/g, '');
            }
            if (value.length > 9) {
                value = value.substring(0, 9);
            }
            e.target.value = value;
        });
    });

    // Telefon input formatlash
    const phoneInputs = document.querySelectorAll('input[data-validate="phone"]');
    phoneInputs.forEach(function(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.startsWith('998')) {
                value = '+' + value;
            } else if (value.startsWith('0')) {
                value = '+998' + value.substring(1);
            } else if (value.length > 0 && !value.startsWith('+998')) {
                value = '+998' + value;
            }
            if (value.length > 13) {
                value = value.substring(0, 13);
            }
            e.target.value = value;
        });
    });

    // Loading animation
    const submitButtons = document.querySelectorAll('button[type="submit"]');
    submitButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            setTimeout(() => {
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Yuborilmoqda...';
            }, 100);
        });
    });
});

// Form validatsiya funksiyasi
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');

    inputs.forEach(function(input) {
        if (!input.value.trim()) {
            showError(input, 'Bu maydon majburiy');
            isValid = false;
        } else {
            hideError(input);
        }
    });

    return isValid;
}

// Xatolikni ko'rsatish
function showError(input, message) {
    input.classList.add('is-invalid');
    let feedback = input.parentNode.querySelector('.invalid-feedback');
    if (feedback) {
        feedback.textContent = message;
    }
}

// Xatolikni yashirish
function hideError(input) {
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
}