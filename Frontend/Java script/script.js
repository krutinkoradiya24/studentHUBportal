function initPageAuthGuard() {
  const currentPath = window.location.pathname.toLowerCase();
  const protectedPages = [
    'dashboard.html',
    'admin.html',
    'profile.html',
    'attendance.html',
    'course.html',
    'assignments.html',
    'results.html',
    'fee_receipt.html',
    'counselling.html',
    'e-library.html'
  ];

  const isProtected = protectedPages.some(page => currentPath.endsWith(page));
  if (!isProtected) return;

  let user = null;
  try {
    user = JSON.parse(localStorage.getItem("student_user") || sessionStorage.getItem("student_user") || "null");
  } catch(e) {}

  if (!user || !user.id || !user.full_name) {
    try {
      localStorage.removeItem("student_user");
      sessionStorage.removeItem("student_user");
    } catch(e) {}
    window.location.replace("login.html?error=access_denied");
  }
}

// Check immediately on script execution
initPageAuthGuard();

// Re-check on browser Back / Forward cache navigation (bfcache)
window.addEventListener("pageshow", initPageAuthGuard);
window.addEventListener("popstate", initPageAuthGuard);

document.addEventListener("DOMContentLoaded", () => {
  initPageAuthGuard();
  initTheme();
  initNotification();
  initMobileMenu();
  initFAQ();
  initModal();
  initSlider();
  initAccessibility();
  initLoginForm();
  initUserProfile();
  initLogout();
  initDbTable();
});

/* ==================================================
   1. LIGHT / DARK THEME
================================================== */
function initTheme() {
  // Check localStorage first so it applies to ALL pages
  const savedTheme = localStorage.getItem("theme");
  if (savedTheme === "dark") {
    document.documentElement.classList.add("dark-theme");
  }

  const themeBtn = document.getElementById("theme-toggle");
  if (!themeBtn) return;

  if (savedTheme === "dark") {
    updateThemeIcon(themeBtn, "dark");
  }

  themeBtn.addEventListener("click", () => {
    document.documentElement.classList.toggle("dark-theme");
    const isDark = document.documentElement.classList.contains("dark-theme");

    localStorage.setItem("theme", isDark ? "dark" : "light");
    updateThemeIcon(themeBtn, isDark ? "dark" : "light");
  });
}

function updateThemeIcon(btn, theme) {
  if (theme === "dark") {
    btn.innerHTML = '<i class="fas fa-sun"></i>';
    btn.setAttribute("aria-label", "Switch to light mode");
  } else {
    btn.innerHTML = '<i class="fas fa-moon"></i>';
    btn.setAttribute("aria-label", "Switch to dark mode");
  }
}

/* ==================================================
   2. NOTIFICATION BANNER
================================================== */
function initNotification() {
  const banner = document.getElementById("notification-banner");
  const closeBtn = document.getElementById("close-banner");

  if (banner && closeBtn) {
    closeBtn.addEventListener("click", () => {
      banner.classList.add("hidden");
      setTimeout(() => {
        banner.remove();
      }, 500); // Wait for transition
    });
  }
}

/* ==================================================
   3. HAMBURGER MENU (MOBILE NAVIGATION)
================================================== */
function initMobileMenu() {
  const sidebar = document.getElementById("sidebar");
  const hamburgerBtn = document.getElementById("hamburger-btn");
  const closeSidebarBtn = document.getElementById("close-sidebar");

  if (sidebar && hamburgerBtn) {
    hamburgerBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      sidebar.classList.add("active");
      hamburgerBtn.setAttribute("aria-expanded", "true");
    });

    if (closeSidebarBtn) {
      closeSidebarBtn.addEventListener("click", () => {
        sidebar.classList.remove("active");
        hamburgerBtn.setAttribute("aria-expanded", "false");
      });
    }

    // Close when clicking outside
    document.addEventListener("click", (e) => {
      if (
        sidebar.classList.contains("active") &&
        !sidebar.contains(e.target) &&
        e.target !== hamburgerBtn
      ) {
        sidebar.classList.remove("active");
        hamburgerBtn.setAttribute("aria-expanded", "false");
      }
    });
  }

  // Retain existing notice icon clickable logic globally
  const notificationIcon = document.querySelector(".notification-icon");
  if (notificationIcon) {
    notificationIcon.style.cursor = "pointer";
    notificationIcon.addEventListener("click", () => {
      window.location.href = "notice.html";
    });
  }

  // Retain existing user profile logic globally
  const userProfile = document.querySelector(".user-profile");
  if (userProfile) {
    userProfile.style.cursor = "pointer";
    userProfile.addEventListener("click", () => {
      window.location.href = "profile.html";
    });
  }

  // Toggle Password in auth pages
  const togglePassword = document.getElementById("toggle-password");
  const passwordInput = document.getElementById("password");
  if (togglePassword && passwordInput) {
    togglePassword.addEventListener("click", () => {
      const type =
        passwordInput.getAttribute("type") === "password" ? "text" : "password";
      passwordInput.setAttribute("type", type);
      togglePassword.classList.toggle("fa-eye");
      togglePassword.classList.toggle("fa-eye-slash");
    });
  }
}

/* ==================================================
   4. DYNAMIC FAQ
================================================== */
function initFAQ() {
  const faqQuestions = document.querySelectorAll(".faq-question");
  if (faqQuestions.length === 0) return;

  faqQuestions.forEach((question) => {
    question.addEventListener("click", () => {
      const faqItem = question.closest('.faq-item');
      const isExpanded = question.getAttribute("aria-expanded") === "true";
      const icon = question.querySelector('.faq-icon');

      // Close all other FAQs
      document.querySelectorAll('.faq-item').forEach((item) => {
        const q = item.querySelector('.faq-question');
        const i = item.querySelector('.faq-icon');
        q.setAttribute("aria-expanded", "false");
        item.classList.remove("open");
        if (i) {
          i.classList.remove("fa-minus");
          i.classList.add("fa-plus");
        }
      });

      if (!isExpanded) {
        question.setAttribute("aria-expanded", "true");
        faqItem.classList.add("open");
        if (icon) {
          icon.classList.remove("fa-plus");
          icon.classList.add("fa-minus");
        }
      }
    });
  });
}

/* ==================================================
   5. MODAL
================================================== */
function initModal() {
  const modalOpenBtns = document.querySelectorAll(".open-modal-btn");
  const modalCloseBtns = document.querySelectorAll(".modal-close");
  const modals = document.querySelectorAll(".modal-overlay");

  if (modalOpenBtns.length === 0 || modals.length === 0) return;

  modalOpenBtns.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      const targetId = btn.getAttribute("data-target");
      const modal = document.getElementById(targetId);
      if (modal) {
        modal.classList.add("active");
        modal.setAttribute("aria-hidden", "false");
        // Set focus to modal for accessibility
        const closeBtn = modal.querySelector(".modal-close");
        if (closeBtn) closeBtn.focus();
      }
    });
  });

  modalCloseBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      const modal = btn.closest(".modal-overlay");
      if (modal) {
        modal.classList.remove("active");
        modal.setAttribute("aria-hidden", "true");
      }
    });
  });

  // Close on click outside
  modals.forEach((modal) => {
    modal.addEventListener("click", (e) => {
      if (e.target === modal) {
        modal.classList.remove("active");
        modal.setAttribute("aria-hidden", "true");
      }
    });
  });

  // Close on Escape key
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      modals.forEach((modal) => {
        if (modal.classList.contains("active")) {
          modal.classList.remove("active");
          modal.setAttribute("aria-hidden", "true");
        }
      });
    }
  });
}

/* ==================================================
   6. IMAGE / CONTENT SLIDER
================================================== */
function initSlider() {
  const slider = document.querySelector(".slider-wrapper");
  if (!slider) return;

  const slides = document.querySelectorAll(".slide");
  const prevBtn = document.querySelector(".slider-prev");
  const nextBtn = document.querySelector(".slider-next");
  const dotsContainer = document.querySelector(".slider-dots");

  if (slides.length === 0) return;

  let currentIndex = 0;
  let autoSlideInterval;

  // Create dots
  slides.forEach((_, index) => {
    const dot = document.createElement("button");
    dot.classList.add("dot");
    dot.setAttribute("aria-label", "Go to slide " + (index + 1));
    if (index === 0) dot.classList.add("active");

    dot.addEventListener("click", () => {
      goToSlide(index);
    });
    dotsContainer.appendChild(dot);
  });

  const dots = document.querySelectorAll(".dot");

  function updateSlider() {
    slider.style.transform = "translateX(-" + currentIndex * 100 + "%)";
    dots.forEach((dot, index) => {
      if (index === currentIndex) dot.classList.add("active");
      else dot.classList.remove("active");
    });
  }

  function nextSlide() {
    currentIndex = (currentIndex + 1) % slides.length;
    updateSlider();
  }

  function prevSlide() {
    currentIndex = (currentIndex - 1 + slides.length) % slides.length;
    updateSlider();
  }

  function goToSlide(index) {
    currentIndex = index;
    updateSlider();
  }

  if (nextBtn) nextBtn.addEventListener("click", nextSlide);
  if (prevBtn) prevBtn.addEventListener("click", prevSlide);

  function startAutoSlide() {
    autoSlideInterval = setInterval(nextSlide, 3000);
  }
  function stopAutoSlide() {
    clearInterval(autoSlideInterval);
  }

  startAutoSlide();

  const sliderContainer = document.querySelector(".slider-container");
  sliderContainer.addEventListener("mouseover", stopAutoSlide);
  sliderContainer.addEventListener("mouseout", startAutoSlide);
}

/* ==================================================
   7. ACCESSIBILITY ENHANCEMENTS
================================================== */
function initAccessibility() {
  // Add focus outlines correctly
  const interactiveElements = document.querySelectorAll(
    "a, button, input, select, textarea",
  );
  interactiveElements.forEach((el) => {
    // Ensure proper attributes
    if (
      !el.getAttribute("aria-label") &&
      el.tagName === "BUTTON" &&
      el.innerHTML.includes("fa-")
    ) {
      el.setAttribute("aria-label", "Button action");
    }
  });
}

/* ==================================================
   6. REGISTRATION FORM VALIDATION
================================================== */
document.addEventListener("DOMContentLoaded", () => {
    const regForm = document.getElementById("registrationForm");
    if (!regForm) return;

    const nameInput = document.getElementById("fullName");
    const emailInput = document.getElementById("email");
    const mobileInput = document.getElementById("mobile");
    const passInput = document.getElementById("password");
    const confirmInput = document.getElementById("confirmPassword");
    const courseSelect = document.getElementById("course");
    const yearSelect = document.getElementById("year");
    const termsCheck = document.getElementById("terms");
    const genderRadios = document.getElementsByName("gender");
    
    const passStrength = document.getElementById("passwordStrength");
    const strengthBarContainer = document.querySelector(".strength-bar-container");
    const strengthBar = document.getElementById("strengthBar");
    const confirmMatch = document.getElementById("confirmMatch");
    const successMsg = document.getElementById("successMessage");

    // Regular Expressions
    const nameRegex = /^[A-Za-z ]{2,50}$/;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const mobileRegex = /^[6-9][0-9]{9}$/;
    // Password: Min 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special character
    const passRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

    // Helper function to show error
    function showError(input, errorId, show) {
        const errorEl = document.getElementById(errorId);
        if (show) {
            input.classList.add("is-invalid");
            input.classList.remove("is-valid");
            input.setAttribute("aria-invalid", "true");
            errorEl.style.display = "block";
        } else {
            input.classList.remove("is-invalid");
            input.classList.add("is-valid");
            input.setAttribute("aria-invalid", "false");
            errorEl.style.display = "none";
        }
    }

    // Password Strength
    passInput.addEventListener("input", () => {
        const val = passInput.value;
        strengthBarContainer.style.display = "block";
        
        let strength = 0;
        if (val.length >= 8) strength += 1;
        if (/[A-Z]/.test(val)) strength += 1;
        if (/[a-z]/.test(val)) strength += 1;
        if (/[0-9]/.test(val)) strength += 1;
        if (/[@$!%*?&]/.test(val)) strength += 1;

        passStrength.className = "password-strength";
        if (val.length === 0) {
            passStrength.textContent = "";
            strengthBarContainer.style.display = "none";
            strengthBar.style.width = "0%";
        } else if (strength <= 2) {
            passStrength.textContent = "Password Strength: Weak";
            passStrength.classList.add("weak");
            strengthBar.style.width = "33%";
            strengthBar.style.backgroundColor = "var(--danger)";
        } else if (strength <= 4) {
            passStrength.textContent = "Password Strength: Medium";
            passStrength.classList.add("medium");
            strengthBar.style.width = "66%";
            strengthBar.style.backgroundColor = "var(--warning)";
        } else {
            passStrength.textContent = "Password Strength: Strong";
            passStrength.classList.add("strong");
            strengthBar.style.width = "100%";
            strengthBar.style.backgroundColor = "var(--success)";
        }
        
        // Re-check match if confirm password has value
        if (confirmInput.value) checkMatch();
    });

    // Confirm Password Match
    function checkMatch() {
        const val = confirmInput.value;
        const confirmError = document.getElementById("confirmError");
        if (val === passInput.value && val.length > 0) {
            confirmInput.classList.remove("is-invalid");
            confirmInput.classList.add("is-valid");
            confirmInput.setAttribute("aria-invalid", "false");
            confirmError.style.display = "none";
            confirmMatch.style.display = "block";
            return true;
        } else if (val.length > 0) {
            confirmInput.classList.add("is-invalid");
            confirmInput.classList.remove("is-valid");
            confirmInput.setAttribute("aria-invalid", "true");
            confirmError.style.display = "block";
            confirmMatch.style.display = "none";
            return false;
        }
        return false;
    }
    
    confirmInput.addEventListener("input", checkMatch);

    // Form Submit
    regForm.addEventListener("submit", (e) => {
        e.preventDefault();
        let isValid = true;
        successMsg.style.display = "none";

        // Name Validation
        if (!nameRegex.test(nameInput.value.trim())) {
            showError(nameInput, "nameError", true);
            isValid = false;
        } else {
            showError(nameInput, "nameError", false);
        }

        // Email Validation
        if (!emailRegex.test(emailInput.value.trim())) {
            showError(emailInput, "emailError", true);
            isValid = false;
        } else {
            showError(emailInput, "emailError", false);
        }

        // Mobile Validation
        if (!mobileRegex.test(mobileInput.value.trim())) {
            showError(mobileInput, "mobileError", true);
            isValid = false;
        } else {
            showError(mobileInput, "mobileError", false);
        }

        // Password Validation
        if (!passRegex.test(passInput.value)) {
            showError(passInput, "passwordError", true);
            isValid = false;
        } else {
            showError(passInput, "passwordError", false);
        }

        // Confirm Validation
        if (!checkMatch()) {
            isValid = false;
        }

        // Course Select Validation
        if (courseSelect.value === "") {
            showError(courseSelect, "courseError", true);
            isValid = false;
        } else {
            showError(courseSelect, "courseError", false);
        }

        // Year Select Validation
        if (yearSelect.value === "") {
            showError(yearSelect, "yearError", true);
            isValid = false;
        } else {
            showError(yearSelect, "yearError", false);
        }

        // Gender Radio Validation
        let genderSelected = false;
        for (const radio of genderRadios) {
            if (radio.checked) genderSelected = true;
        }
        const genderErrorEl = document.getElementById("genderError");
        if (!genderSelected) {
            genderErrorEl.style.display = "block";
            isValid = false;
        } else {
            genderErrorEl.style.display = "none";
        }

        // Terms Checkbox Validation
        if (!termsCheck.checked) {
            showError(termsCheck, "termsError", true);
            isValid = false;
        } else {
            showError(termsCheck, "termsError", false);
        }

        if (isValid) {
            const errorMsgEl = document.getElementById("errorMessage");
            const regBtn = document.getElementById("registerBtn") || regForm.querySelector("button[type='submit']");
            const originalBtnText = regBtn ? regBtn.innerHTML : "Register Account";

            if (regBtn) {
                regBtn.disabled = true;
                regBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving to Database...';
            }
            if (errorMsgEl) errorMsgEl.style.display = "none";
            successMsg.style.display = "none";

            let selectedGender = "Other";
            for (const radio of genderRadios) {
                if (radio.checked) selectedGender = radio.value;
            }

            const payload = {
                action: "register",
                fullName: nameInput.value.trim(),
                email: emailInput.value.trim(),
                mobile: mobileInput.value.trim(),
                password: passInput.value,
                course: courseSelect.value,
                year: yearSelect.value,
                gender: selectedGender
            };

            fetch(getAuthApiUrl(), {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (regBtn) {
                    regBtn.disabled = false;
                    regBtn.innerHTML = originalBtnText;
                }

                if (data.success) {
                    successMsg.innerHTML = '<i class="fas fa-check-circle"></i> ' + (data.message || "Registration completed successfully! Redirecting to login...");
                    successMsg.style.display = "block";
                    sessionStorage.setItem("registered_email", payload.email);
                    setTimeout(() => {
                        window.location.href = "login.html?registered=1";
                    }, 1200);
                } else {
                    if (errorMsgEl) {
                        errorMsgEl.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + (data.message || "Registration failed.");
                        errorMsgEl.style.display = "block";
                    } else {
                        alert(data.message || "Registration failed.");
                    }
                }
            })
            .catch(err => {
                console.error("Registration error:", err);
                if (regBtn) {
                    regBtn.disabled = false;
                    regBtn.innerHTML = originalBtnText;
                }
                if (errorMsgEl) {
                    errorMsgEl.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Cannot connect to database server. Please ensure XAMPP Apache & MySQL are running.';
                    errorMsgEl.style.display = "block";
                } else {
                    alert("Cannot connect to server. Please ensure XAMPP MySQL and Apache are running.");
                }
            });
        }
    });

    // Reset Form
    regForm.addEventListener("reset", () => {
        successMsg.style.display = "none";
        const errorMsgEl = document.getElementById("errorMessage");
        if (errorMsgEl) errorMsgEl.style.display = "none";
        document.querySelectorAll(".error-message").forEach(el => el.style.display = "none");
        confirmMatch.style.display = "none";
        strengthBarContainer.style.display = "none";
        passStrength.textContent = "";
        
        document.querySelectorAll(".is-invalid, .is-valid").forEach(el => {
            el.classList.remove("is-invalid", "is-valid");
            el.setAttribute("aria-invalid", "false");
        });
    });
});

/* ==================================================
   API URL RESOLVER (Works with Apache & Live-Server)
================================================== */
function getAuthApiUrl() {
    // If opened via Apache: e.g. http://localhost/STUDENTPORTAL/Frontend/...
    if (window.location.pathname.includes('/STUDENTPORTAL/')) {
        return '../php/auth.php';
    }
    // If opened via live-server: e.g. http://127.0.0.1:8080/html/...
    return 'http://localhost/STUDENTPORTAL/Frontend/php/auth.php';
}

/* ==================================================
   STUDENT LOGIN HANDLER
================================================== */
function initLoginForm() {
    const loginForm = document.getElementById("loginForm");
    if (!loginForm) return;

    const emailInput = document.getElementById("loginEmail");
    const passwordInput = document.getElementById("password");
    const loginAlert = document.getElementById("loginAlert");
    const loginBtn = document.getElementById("loginBtn") || loginForm.querySelector("button[type='submit']");

    // Pre-fill email if redirected from registration
    const prefillEmail = sessionStorage.getItem("registered_email");
    if (prefillEmail && emailInput && !emailInput.value) {
        emailInput.value = prefillEmail;
    }

    // Show registration or logout success banner if query param present
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get("registered") === "1" && loginAlert) {
        loginAlert.style.display = "block";
        loginAlert.style.background = "#10b981";
        loginAlert.style.color = "#ffffff";
        loginAlert.innerHTML = '<i class="fas fa-check-circle"></i> Registration successful! You can now log in with your credentials.';
    } else if ((urlParams.get("message") === "logged_out" || urlParams.get("logged_out") === "1") && loginAlert) {
        loginAlert.style.display = "block";
        loginAlert.style.background = "#10b981";
        loginAlert.style.color = "#ffffff";
        loginAlert.innerHTML = '<i class="fas fa-check-circle"></i> You have been safely logged out.';
    } else if ((urlParams.get("error") === "access_denied" || urlParams.get("error") === "admin_required") && loginAlert) {
        loginAlert.style.display = "block";
        loginAlert.style.background = "#fee2e2";
        loginAlert.style.color = "#991b1b";
        loginAlert.style.border = "1.5px solid #ef4444";
        loginAlert.style.boxShadow = "0 4px 14px rgba(239, 68, 68, 0.25)";
        loginAlert.style.borderRadius = "8px";
        loginAlert.style.padding = "12px 16px";
        loginAlert.innerHTML = '<i class="fas fa-shield-alt" style="font-size: 1.15rem; color: #dc2626; margin-right: 8px;"></i> <strong>ACCESS DENIED (403):</strong> Dashboard access denied after logout. Please log in first.';
    } else if (urlParams.get("error") === "session_timeout" && loginAlert) {
        loginAlert.style.display = "block";
        loginAlert.style.background = "#fef3c7";
        loginAlert.style.color = "#92400e";
        loginAlert.style.border = "1.5px solid #f59e0b";
        loginAlert.style.boxShadow = "0 4px 14px rgba(245, 158, 11, 0.25)";
        loginAlert.style.borderRadius = "8px";
        loginAlert.style.padding = "12px 16px";
        loginAlert.innerHTML = '<i class="fas fa-hourglass-end" style="font-size: 1.15rem; color: #d97706; margin-right: 8px;"></i> <strong>SESSION TIMEOUT:</strong> Session expired due to inactivity. Redirection to login.';
    }

    loginForm.addEventListener("submit", (e) => {
        e.preventDefault();
        const identifier = emailInput ? emailInput.value.trim() : "";
        const password = passwordInput ? passwordInput.value : "";

        if (!identifier || !password) {
            showLoginAlert("Please enter both your email/mobile and password.", "error");
            return;
        }

        const originalBtnText = loginBtn ? loginBtn.innerHTML : "Login to Account";
        if (loginBtn) {
            loginBtn.disabled = true;
            loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking Database...';
        }

        const portalRole = (document.getElementById("loginRole") ? document.getElementById("loginRole").value : "Student");

        fetch(getAuthApiUrl(), {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                action: "login",
                identifier: identifier,
                password: password,
                portal_role: portalRole
            })
        })
        .then(res => res.json())
        .then(data => {
            if (loginBtn) {
                loginBtn.disabled = false;
                loginBtn.innerHTML = originalBtnText;
            }

            if (data.success) {
                // If attempting to log into Admin portal as a Student, reject!
                if (portalRole === 'Admin' && data.user.role !== 'Admin') {
                    showLoginAlert('<i class="fas fa-user-lock"></i> Access Denied: This account does not have Administrator privileges. Please use the Student Login option.', "error");
                    return;
                }

                // If attempting to log into Student portal as an Admin, reject!
                if (portalRole === 'Student' && data.user.role === 'Admin') {
                    showLoginAlert('<i class="fas fa-user-shield"></i> Access Denied: This is an Administrator account. Please select the Administrator tab to sign in.', "error");
                    return;
                }

                showLoginAlert('<i class="fas fa-check-circle"></i> ' + (data.message || "Login successful! Redirecting..."), "success");
                // Save authenticated user
                localStorage.setItem("student_user", JSON.stringify(data.user));
                sessionStorage.setItem("student_user", JSON.stringify(data.user));
                setTimeout(() => {
                    if (data.user && data.user.role === 'Admin') {
                        window.location.href = "admin.html";
                    } else {
                        window.location.href = "dashboard.html";
                    }
                }, 1000);
            } else {
                showLoginAlert('<i class="fas fa-exclamation-circle"></i> ' + (data.message || "Invalid credentials."), "error");
            }
        })
        .catch(err => {
            console.error("Login error:", err);
            if (loginBtn) {
                loginBtn.disabled = false;
                loginBtn.innerHTML = originalBtnText;
            }
            showLoginAlert('<i class="fas fa-exclamation-triangle"></i> Cannot connect to database server. Please ensure XAMPP Apache & MySQL are running.', "error");
        });
    });

    function showLoginAlert(msg, type) {
        if (!loginAlert) {
            alert(msg);
            return;
        }
        loginAlert.style.display = "block";
        loginAlert.innerHTML = msg;
        if (type === "success") {
            loginAlert.style.background = "#10b981";
            loginAlert.style.color = "#ffffff";
        } else {
            loginAlert.style.background = "#ef4444";
            loginAlert.style.color = "#ffffff";
        }
    }
}

/* ==================================================
   USER PROFILE SYNC (Dashboard & Profile pages)
================================================== */
function initUserProfile() {
    const rawUser = localStorage.getItem("student_user") || sessionStorage.getItem("student_user");
    if (!rawUser) return;

    try {
        const user = JSON.parse(rawUser);
        if (!user || !user.full_name) return;

        // Properly capitalize student full name
        const cleanName = user.full_name.trim();
        const formattedName = cleanName.replace(/\b\w/g, c => c.toUpperCase());

        // Update header user-profile
        document.querySelectorAll(".user-profile span").forEach(el => {
            el.textContent = formattedName;
        });

        // Update avatar image with exact matching name
        document.querySelectorAll(".user-profile img, .card img[alt='Profile']").forEach(img => {
            img.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(formattedName)}&background=2563eb&color=fff&size=120`;
        });

        // Update dashboard welcome header
        const welcomeH1 = document.querySelector(".dashboard-header h1");
        if (welcomeH1) {
            const firstName = formattedName.split(" ")[0];
            welcomeH1.textContent = `Welcome Back, ${firstName} 👋`;
        }

        // Update student info text in dashboard
        const welcomeP = document.querySelector(".dashboard-header p");
        if (welcomeP) {
            const course = user.course || "B.Tech";
            const year = user.year ? `Year ${user.year}` : "Semester 1";
            welcomeP.textContent = `Student ID: STD20260${user.id || '1'} | ${course} | ${year}`;
        }

        // Update Profile Page Heading (Matching Avatar Initials)
        const profileHeading = document.getElementById("profileName") || document.querySelector(".card div h1") || document.querySelector(".content-wrapper .card h1");
        if (profileHeading) {
            profileHeading.textContent = formattedName;
        }

        // Update Profile Page Course
        const profileCourse = document.getElementById("profileCourse") || document.querySelector(".card div > p[style*='font-size: 1.1rem']");
        if (profileCourse) {
            const cName = user.course || "B.Tech Computer Science Engineering";
            profileCourse.textContent = cName.includes("Engineering") ? cName : `${cName} Engineering`;
        }

        // Update Profile Page ID and Contact details
        const profileIdEl = document.getElementById("profileStudentId");
        if (profileIdEl) profileIdEl.textContent = `STD20260${user.id || '1'}`;

        const profileEmailEl = document.getElementById("profileEmail");
        if (profileEmailEl && user.email) profileEmailEl.textContent = user.email;

        const profilePhoneEl = document.getElementById("profilePhone");
        if (profilePhoneEl && user.mobile) profilePhoneEl.textContent = user.mobile;

        const profileSemesterEl = document.getElementById("profileSemester");
        if (profileSemesterEl && user.year) profileSemesterEl.textContent = `Year ${user.year}`;

        // Fallback for general cards
        document.querySelectorAll(".card div").forEach(div => {
            const label = div.querySelector("p");
            const val = div.querySelectorAll("p")[1];
            if (label && val) {
                if (label.textContent.includes("Email Address") && user.email) {
                    val.textContent = user.email;
                } else if (label.textContent.includes("Phone Number") && user.mobile) {
                    val.textContent = user.mobile;
                } else if (label.textContent.includes("Student ID") && user.id) {
                    val.textContent = `STD20260${user.id}`;
                }
            }
        });

        // Ensure NO admin details or admin links are shown for students
        if (user.role !== 'Admin') {
            document.querySelectorAll("a[href*='admin.html'], a[href*='admin_dashboard.php']").forEach(el => el.remove());
        }

        // Display access restricted notice if redirected from restricted admin page
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get("error") === "admin_required") {
            const notifBanner = document.getElementById("notification-banner");
            if (notifBanner) {
                notifBanner.style.display = "block";
                notifBanner.style.background = "#fee2e2";
                notifBanner.style.borderLeft = "4px solid #ef4444";
                notifBanner.style.color = "#991b1b";
                notifBanner.innerHTML = '<span><i class="fas fa-shield-alt" style="color: #ef4444; margin-right: 8px;"></i> <strong>Access Restricted:</strong> Administrator details and control panels are not accessible to Student accounts.</span>';
            }
        }
    } catch (e) {
        console.error("Error parsing student user profile:", e);
    }
}

/* ==================================================
   LOGOUT CONFIRMATION MODAL POPUP
================================================== */
let pendingLogoutAction = null;

function getOrCreateLogoutModal() {
    let modalOverlay = document.getElementById("logoutModalOverlay");
    if (!modalOverlay) {
        modalOverlay = document.createElement("div");
        modalOverlay.id = "logoutModalOverlay";
        modalOverlay.className = "logout-modal-overlay";
        modalOverlay.setAttribute("aria-hidden", "true");
        modalOverlay.setAttribute("role", "dialog");
        modalOverlay.setAttribute("aria-modal", "true");
        modalOverlay.setAttribute("aria-labelledby", "logoutModalTitle");

        modalOverlay.innerHTML = `
            <div class="logout-modal-card">
                <button type="button" class="logout-modal-close" id="logoutModalCloseBtn" aria-label="Close dialog">
                    <i class="fas fa-times"></i>
                </button>
                <div class="logout-modal-icon-wrap">
                    <div class="logout-modal-icon-pulse"></div>
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                <h3 id="logoutModalTitle" class="logout-modal-title">Confirm Sign Out</h3>
                <p class="logout-modal-desc">
                    Are you sure you want to log out of your session? You will need to sign in again to access the portal.
                </p>
                <div class="logout-modal-actions">
                    <button type="button" class="logout-modal-btn logout-modal-btn-cancel" id="logoutModalCancelBtn">
                        <i class="fas fa-arrow-left"></i> Stay Logged In
                    </button>
                    <button type="button" class="logout-modal-btn logout-modal-btn-confirm" id="logoutModalConfirmBtn">
                        <i class="fas fa-sign-out-alt"></i> Yes, Log Out
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(modalOverlay);

        const closeModal = () => {
            modalOverlay.classList.remove("active");
            modalOverlay.setAttribute("aria-hidden", "true");
            pendingLogoutAction = null;
        };

        const closeBtn = modalOverlay.querySelector("#logoutModalCloseBtn");
        const cancelBtn = modalOverlay.querySelector("#logoutModalCancelBtn");
        const confirmBtn = modalOverlay.querySelector("#logoutModalConfirmBtn");

        if (closeBtn) closeBtn.addEventListener("click", closeModal);
        if (cancelBtn) cancelBtn.addEventListener("click", closeModal);

        modalOverlay.addEventListener("click", (e) => {
            if (e.target === modalOverlay) closeModal();
        });

        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape" && modalOverlay.classList.contains("active")) {
                closeModal();
            }
        });

        if (confirmBtn) {
            confirmBtn.addEventListener("click", () => {
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging out...';
                try {
                    localStorage.removeItem("student_user");
                    sessionStorage.removeItem("student_user");
                } catch (err) {}

                if (typeof pendingLogoutAction === "function") {
                    pendingLogoutAction();
                } else {
                    const redirect = window.location.pathname.includes('/Frontend/') ? 'login.html?message=logged_out' : 'login.php?message=logged_out';
                    window.location.href = redirect;
                }
            });
        }
    }
    return modalOverlay;
}

function showLogoutConfirmation(actionCallback) {
    pendingLogoutAction = actionCallback;
    const modal = getOrCreateLogoutModal();
    const confirmBtn = modal.querySelector("#logoutModalConfirmBtn");
    if (confirmBtn) {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '<i class="fas fa-sign-out-alt"></i> Yes, Log Out';
    }
    modal.classList.add("active");
    modal.setAttribute("aria-hidden", "false");
    const cancelBtn = modal.querySelector("#logoutModalCancelBtn");
    if (cancelBtn) {
        setTimeout(() => cancelBtn.focus(), 60);
    }
}

function initLogout() {
    // 1. Intercept any logout link or anchor pointing to logout
    document.addEventListener("click", (e) => {
        // If click is inside modal itself, do not intercept
        if (e.target.closest("#logoutModalOverlay")) return;

        const logoutTarget = e.target.closest(".logout-link, .logout-btn, a[href*='logout'], button[data-logout]");
        if (logoutTarget) {
            // If inside a form with action containing logout, let form submit handler take precedence
            if (logoutTarget.closest("form[action*='logout']")) return;

            e.preventDefault();
            const targetHref = window.location.pathname.includes('/Frontend/') ? "login.html?message=logged_out" : "login.php?message=logged_out";

            showLogoutConfirmation(() => {
                try {
                    localStorage.removeItem("student_user");
                    sessionStorage.removeItem("student_user");
                    fetch(getAuthApiUrl() + "?action=logout");
                } catch (err) {}
                window.location.href = targetHref;
            });
        }
    });

    // 2. Intercept secure POST logout forms (e.g. in admin_dashboard.php & student_dashboard.php)
    document.addEventListener("submit", (e) => {
        const form = e.target;
        if (form && form.action && (form.action.includes("logout.php") || form.action.includes("logout"))) {
            e.preventDefault();
            showLogoutConfirmation(() => {
                HTMLFormElement.prototype.submit.call(form);
            });
        }
    });
}

/* ==================================================
   DATABASE VIEWER TABLE (Admin Dashboard)
================================================== */
function initDbTable() {
    const tbody = document.getElementById("dbUsersBody");
    const refreshBtn = document.getElementById("refreshDbBtn");
    if (!tbody) return;

    function loadUsers() {
        tbody.innerHTML = '<tr><td colspan="8" style="padding: 20px; text-align: center; color: var(--text-muted);"><i class="fas fa-spinner fa-spin"></i> Fetching records from MySQL database...</td></tr>';

        fetch(getAuthApiUrl() + "?action=get_users")
            .then(res => res.json())
            .then(data => {
                if (!data.success || !data.users || data.users.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" style="padding: 25px; text-align: center; color: var(--text-muted); font-size: 1rem;">No registered students found in database. <a href="registration.html" style="color: var(--primary); text-decoration: underline;">Register a student here</a></td></tr>';
                    return;
                }

                tbody.innerHTML = data.users.map(u => `
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 12px 14px; font-weight: 600;">#${u.id}</td>
                        <td style="padding: 12px 14px; font-weight: 500;">${escapeHtml(u.full_name)}</td>
                        <td style="padding: 12px 14px;"><a href="mailto:${escapeHtml(u.email)}" style="color: var(--primary);">${escapeHtml(u.email)}</a></td>
                        <td style="padding: 12px 14px;">${escapeHtml(u.mobile || '-')}</td>
                        <td style="padding: 12px 14px;"><span class="badge badge-primary">${escapeHtml(u.course || 'BTech')}</span></td>
                        <td style="padding: 12px 14px;">${escapeHtml(u.year ? 'Year ' + u.year : '-')}</td>
                        <td style="padding: 12px 14px;">${u.role === 'Admin' ? '<span class="badge" style="background: #4f46e5; color: #fff; padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 700;">Admin</span>' : '<span class="badge" style="background: #0284c7; color: #fff; padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 700;">Student</span>'}</td>
                        <td style="padding: 12px 14px; color: var(--text-muted); font-size: 0.85rem;">${escapeHtml(u.created_at || '-')}</td>
                    </tr>
                `).join('');
            })
            .catch(err => {
                console.error("Failed to load DB table:", err);
                tbody.innerHTML = '<tr><td colspan="8" style="padding: 20px; text-align: center; color: #ef4444;"><i class="fas fa-exclamation-triangle"></i> Error connecting to database. Ensure MySQL is running in XAMPP.</td></tr>';
            });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
    }

    loadUsers();
    if (refreshBtn) refreshBtn.addEventListener("click", loadUsers);
}


