document.addEventListener("DOMContentLoaded", () => {
  initTheme();
  initNotification();
  initMobileMenu();
  initFAQ();
  initModal();
  initSlider();
  initAccessibility();
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
            successMsg.style.display = "block";
            // Pop up an alert as well to ensure they see it
            alert("Registration successful!");
            // Redirect to login page after a short delay
            setTimeout(() => {
                window.location.href = "login.html";
            }, 1500);
        }
    });

    // Reset Form
    regForm.addEventListener("reset", () => {
        successMsg.style.display = "none";
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

document.addEventListener("DOMContentLoaded", () => {
    if (typeof initStudents === 'function') initStudents();
    if (typeof initEvents === 'function') initEvents();
    if (typeof initFAQsData === 'function') initFAQsData();
    if (typeof initDashboardEvents === 'function') initDashboardEvents();
    if (typeof initAdminStats === 'function') initAdminStats();
});

// Admin stats
async function initAdminStats() {
    const adminStats = document.getElementById('dynamicAdminStats');
    if (!adminStats) return;
    const data = await fetchStudents();
    if(data && data.length > 0) {
        const total = data.length;
        const avgCgpa = (data.reduce((sum, s) => sum + s.cgpa, 0) / total).toFixed(2);
        const avgAtt = (data.reduce((sum, s) => sum + s.attendance, 0) / total).toFixed(1);
        adminStats.innerHTML = `<div class="card"><h3>Total Students</h3><p style="font-size: 2rem; color: var(--primary);">${total}</p></div>
        <div class="card"><h3>Average CGPA</h3><p style="font-size: 2rem; color: var(--primary);">${avgCgpa}</p></div>
        <div class="card"><h3>Average Attendance</h3><p style="font-size: 2rem; color: var(--primary);">${avgAtt}%</p></div>`;
    }
}

// Dashboard Events
async function initDashboardEvents() {
    const dashEvents = document.getElementById('dashboardUpcomingEvents');
    if (!dashEvents) return;
    const data = await fetchEvents();
    if (data && data.length > 0) {
        const upcoming = data.filter(e => e.status === 'Upcoming').sort((a, b) => new Date(a.date) - new Date(b.date)).slice(0, 3);
        let html = '<div style="display: flex; flex-direction: column; gap: 10px;">';
        upcoming.forEach(e => {
            html += `<div class="card" style="padding: 15px;"><strong>${escapeHTML(e.title)}</strong> - ${escapeHTML(e.date)}</div>`;
        });
        html += '</div>';
        dashEvents.innerHTML = html;
    }
}
