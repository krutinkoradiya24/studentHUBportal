/**
 * Unified Client-Side Application Script (js/script.js)
 * Centralizes ALL JavaScript logic across the system into a single file:
 * 1. Password Visibility Toggle (Show / Hide eye buttons)
 * 2. Real-Time Registration Validation & AJAX Form Submission
 * 3. Password Strength Meter Analysis
 * 4. Login Submission State & UX Spinner
 * 5. Automated Test Suite Interactive Runner & DOM Renderer
 */

document.addEventListener('DOMContentLoaded', () => {
    initPasswordVisibilityToggles();
    initRegistrationForm();
    initLoginForm();
    initTestDashboard();
});

/**
 * -------------------------------------------------------------
 * 1. Show/Hide Password Toggle Functionality
 * -------------------------------------------------------------
 */
function initPasswordVisibilityToggles() {
    const toggleButtons = document.querySelectorAll('.btn-toggle-password');

    toggleButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            if (!targetInput) return;

            const eyeShow = btn.querySelector('.eye-show');
            const eyeHide = btn.querySelector('.eye-hide');

            if (targetInput.type === 'password') {
                targetInput.type = 'text';
                if (eyeShow) eyeShow.classList.add('hidden');
                if (eyeHide) eyeHide.classList.remove('hidden');
            } else {
                targetInput.type = 'password';
                if (eyeShow) eyeShow.classList.remove('hidden');
                if (eyeHide) eyeHide.classList.add('hidden');
            }
        });
    });
}

/**
 * -------------------------------------------------------------
 * 2. Registration Form Validation, Strength Meter & Submission
 * -------------------------------------------------------------
 */
function initRegistrationForm() {
    const form = document.getElementById('registerForm');
    if (!form) return;

    const fullNameInput        = document.getElementById('full_name');
    const usernameInput        = document.getElementById('username');
    const emailInput           = document.getElementById('email');
    const passwordInput        = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const submitBtn            = document.getElementById('registerBtn');
    const btnSpinner           = document.getElementById('btnSpinner');
    const alertContainer       = document.getElementById('alertContainer');

    const meterFill = document.getElementById('meterFill');
    const meterText = document.getElementById('meterText');
    const meterHint = document.getElementById('meterHint');

    const emailRegex    = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/;
    const usernameRegex = /^[a-zA-Z0-9_]{3,50}$/;

    if (fullNameInput) {
        fullNameInput.addEventListener('input', () => validateFullName(false));
    }
    if (usernameInput) {
        usernameInput.addEventListener('input', () => validateUsername(false));
    }
    if (emailInput) {
        emailInput.addEventListener('input', () => validateEmail(false));
    }
    if (passwordInput) {
        passwordInput.addEventListener('input', () => {
            updatePasswordStrength(passwordInput.value);
            validatePassword(false);
            if (confirmPasswordInput && confirmPasswordInput.value.length > 0) {
                validateConfirmPassword(false);
            }
        });
    }
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', () => validateConfirmPassword(false));
    }

    function setFieldError(field, errorId, message) {
        const group = document.getElementById(`group-${field}`);
        const feedback = document.getElementById(errorId);
        if (group) {
            group.classList.remove('has-success');
            group.classList.add('has-error');
        }
        if (feedback) {
            feedback.textContent = message;
        }
    }

    function clearFieldError(field, errorId) {
        const group = document.getElementById(`group-${field}`);
        const feedback = document.getElementById(errorId);
        if (group) {
            group.classList.remove('has-error');
            group.classList.add('has-success');
        }
        if (feedback) {
            feedback.textContent = '';
        }
    }

    function validateFullName(showEmptyError = true) {
        const val = fullNameInput.value.trim();
        if (val === '') {
            if (showEmptyError) setFieldError('full_name', 'error-full_name', 'Full Name is required.');
            return false;
        }
        if (val.length < 2) {
            setFieldError('full_name', 'error-full_name', 'Full Name must be at least 2 characters.');
            return false;
        }
        clearFieldError('full_name', 'error-full_name');
        return true;
    }

    function validateUsername(showEmptyError = true) {
        const val = usernameInput.value.trim();
        if (val === '') {
            if (showEmptyError) setFieldError('username', 'error-username', 'Username is required.');
            return false;
        }
        if (!usernameRegex.test(val)) {
            setFieldError('username', 'error-username', 'Username must be 3-50 letters, numbers, or underscores.');
            return false;
        }
        clearFieldError('username', 'error-username');
        return true;
    }

    function validateEmail(showEmptyError = true) {
        const val = emailInput.value.trim();
        if (val === '') {
            if (showEmptyError) setFieldError('email', 'error-email', 'Email Address is required.');
            return false;
        }
        if (!emailRegex.test(val)) {
            setFieldError('email', 'error-email', 'Please enter a valid email address.');
            return false;
        }
        clearFieldError('email', 'error-email');
        return true;
    }

    function validatePassword(showEmptyError = true) {
        const val = passwordInput.value;
        if (val === '') {
            if (showEmptyError) setFieldError('password', 'error-password', 'Password is required.');
            return false;
        }
        if (val.length < 6) {
            setFieldError('password', 'error-password', 'Password must be at least 6 characters long.');
            return false;
        }
        clearFieldError('password', 'error-password');
        return true;
    }

    function validateConfirmPassword(showEmptyError = true) {
        const pVal = passwordInput.value;
        const cVal = confirmPasswordInput.value;
        if (cVal === '') {
            if (showEmptyError) setFieldError('confirm_password', 'error-confirm_password', 'Please confirm your password.');
            return false;
        }
        if (pVal !== cVal) {
            setFieldError('confirm_password', 'error-confirm_password', 'Passwords do not match.');
            return false;
        }
        clearFieldError('confirm_password', 'error-confirm_password');
        return true;
    }

    function updatePasswordStrength(password) {
        if (!meterFill || !meterText) return;

        if (!password || password.length === 0) {
            meterFill.className = 'meter-bar-fill';
            meterText.textContent = 'None';
            meterText.style.color = 'var(--text-dim)';
            if (meterHint) meterHint.textContent = 'Min. 6 characters';
            return;
        }

        let score = 0;
        if (password.length >= 6) score++;
        if (password.length >= 10) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;

        meterFill.className = 'meter-bar-fill';

        if (password.length < 6 || score <= 2) {
            meterFill.classList.add('weak');
            meterText.textContent = 'Weak';
            meterText.style.color = 'var(--color-error)';
            if (meterHint) meterHint.textContent = 'Add uppercase, numbers, or symbols';
        } else if (score === 3 || score === 4) {
            meterFill.classList.add('medium');
            meterText.textContent = 'Medium';
            meterText.style.color = 'var(--color-warning)';
            if (meterHint) meterHint.textContent = 'Good! Add a symbol to strengthen';
        } else {
            meterFill.classList.add('strong');
            meterText.textContent = 'Strong';
            meterText.style.color = 'var(--color-success)';
            if (meterHint) meterHint.textContent = 'Great password!';
        }
    }

    function showAlert(type, message) {
        if (!alertContainer) return;
        alertContainer.className = `alert-container ${type === 'success' ? 'has-success' : 'has-error'}`;
        const iconSvg = type === 'success'
            ? `<svg class="alert-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`
            : `<svg class="alert-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`;

        alertContainer.innerHTML = `
            <div class="alert alert-${type}" role="alert">
                ${iconSvg}
                <span class="alert-text">${escapeHtml(message)}</span>
            </div>
        `;
    }

    function hideAlert() {
        if (alertContainer) {
            alertContainer.className = 'alert-container hidden';
            alertContainer.innerHTML = '';
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const isNameValid    = validateFullName(true);
        const isUserValid    = validateUsername(true);
        const isEmailValid   = validateEmail(true);
        const isPassValid    = validatePassword(true);
        const isConfirmValid = validateConfirmPassword(true);

        const hasEmptyFields = (
            fullNameInput.value.trim() === '' ||
            usernameInput.value.trim() === '' ||
            emailInput.value.trim() === '' ||
            passwordInput.value === '' ||
            confirmPasswordInput.value === ''
        );

        if (hasEmptyFields) {
            showAlert('error', 'Please fill in all required fields.');
            return;
        }

        if (!isEmailValid) {
            showAlert('error', 'Please enter a valid email address.');
            return;
        }

        if (!isConfirmValid) {
            showAlert('error', 'Passwords do not match.');
            return;
        }

        if (!isNameValid || !isUserValid || !isPassValid) {
            showAlert('error', 'Please correct the highlighted errors before submitting.');
            return;
        }

        submitBtn.disabled = true;
        const btnText = submitBtn.querySelector('.btn-text');
        const origBtnText = btnText ? btnText.textContent : 'Create Account';
        if (btnText) btnText.textContent = 'Processing Registration...';
        if (btnSpinner) btnSpinner.classList.remove('hidden');
        hideAlert();

        try {
            const formData = new FormData(form);
            const registerEndpoint = form.getAttribute('action') || (window.location.pathname.includes('/php/') ? 'register.php' : 'php/register.php');

            const response = await fetch(registerEndpoint, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showAlert('success', data.message || 'Registration successful!');
                form.reset();
                updatePasswordStrength('');

                document.querySelectorAll('.form-group').forEach(g => {
                    g.classList.remove('has-success', 'has-error');
                });

                setTimeout(() => {
                    const targetRedirect = window.location.pathname.includes('/php/')
                        ? (data.redirect || 'login.php?registered=1')
                        : ('php/' + (data.redirect || 'login.php?registered=1'));
                    window.location.href = targetRedirect;
                }, 1800);
            } else {
                const errorMessage = data.message || 'Registration failed. Please check your inputs.';
                showAlert('error', errorMessage);

                if (data.errors) {
                    if (data.errors.full_name) setFieldError('full_name', 'error-full_name', data.errors.full_name);
                    if (data.errors.username) setFieldError('username', 'error-username', data.errors.username);
                    if (data.errors.email) setFieldError('email', 'error-email', data.errors.email);
                    if (data.errors.password) setFieldError('password', 'error-password', data.errors.password);
                    if (data.errors.confirm_password) setFieldError('confirm_password', 'error-confirm_password', data.errors.confirm_password);
                }

                submitBtn.disabled = false;
                if (btnText) btnText.textContent = origBtnText;
                if (btnSpinner) btnSpinner.classList.add('hidden');
            }
        } catch (networkError) {
            console.error('Registration fetch error:', networkError);
            showAlert('error', 'A network or server communication error occurred. Please try again.');
            submitBtn.disabled = false;
            if (btnText) btnText.textContent = origBtnText;
            if (btnSpinner) btnSpinner.classList.add('hidden');
        }
    });
}

/**
 * -------------------------------------------------------------
 * 3. Login Form Submit Spinner UX
 * -------------------------------------------------------------
 */
function initLoginForm() {
    const loginForm = document.getElementById('loginForm');
    if (!loginForm) return;

    loginForm.addEventListener('submit', () => {
        const btn = document.getElementById('loginBtn');
        if (btn) {
            btn.disabled = true;
            const text = btn.querySelector('.btn-text');
            const spinner = btn.querySelector('.btn-spinner');
            if (text) text.textContent = 'Signing in...';
            if (spinner) spinner.classList.remove('hidden');
        }
    });
}

/**
 * -------------------------------------------------------------
 * 4. Interactive Test Dashboard Runner (Live AJAX Testing)
 * -------------------------------------------------------------
 */
function initTestDashboard() {
    const rerunBtn = document.getElementById('rerunTestsBtn');
    if (!rerunBtn) return;

    const runBtnText  = document.getElementById('runBtnText');
    const testSpinner = document.getElementById('testSpinner');

    rerunBtn.addEventListener('click', async () => {
        rerunBtn.disabled = true;
        if (runBtnText) runBtnText.textContent = 'Running 7 Tests...';
        if (testSpinner) testSpinner.classList.remove('hidden');

        try {
            const testEndpoint = window.location.pathname.includes('/php/')
                ? 'test_dashboard.php?ajax=1'
                : 'php/test_dashboard.php?ajax=1';

            const res = await fetch(testEndpoint, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            const tbody = document.getElementById('testTableBody');
            if (!tbody) return;
            tbody.innerHTML = '';

            let passCount = 0;
            let failCount = 0;

            data.results.forEach(t => {
                if (t.status === 'PASS') passCount++;
                else failCount++;

                const tr = document.createElement('tr');
                tr.id = `row-test-${t.id}`;
                tr.innerHTML = `
                    <td><strong>${t.id}</strong></td>
                    <td>
                        <strong>${escapeHtml(t.name)}</strong>
                        <small style="color: var(--text-dim); display: block; margin-top: 4px;">${escapeHtml(t.details)}</small>
                    </td>
                    <td>
                        <pre class="code-snippet">${escapeHtml(t.input)}</pre>
                    </td>
                    <td>
                        <span style="color: #93c5fd;">${escapeHtml(t.expected)}</span>
                    </td>
                    <td>
                        <pre class="code-snippet" style="max-width: 200px;">${escapeHtml(t.actual)}</pre>
                    </td>
                    <td style="text-align: center;">
                        <span class="test-badge ${t.status.toLowerCase()}">${t.status}</span>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            const totalEl  = document.getElementById('totalCount');
            const passedEl = document.getElementById('passedCount');
            const failedEl = document.getElementById('failedCount');

            if (totalEl) totalEl.textContent = data.results.length;
            if (passedEl) passedEl.textContent = passCount;
            if (failedEl) failedEl.textContent = failCount;

        } catch (err) {
            console.error('Error executing automated tests:', err);
            alert('Failed to execute automated tests. Please ensure MySQL is running.');
        } finally {
            rerunBtn.disabled = false;
            if (runBtnText) runBtnText.textContent = 'Run All 7 Automated Tests';
            if (testSpinner) testSpinner.classList.add('hidden');
        }
    });
}

/**
 * Utility: HTML Escaping
 */
function escapeHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}
