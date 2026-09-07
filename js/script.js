document.addEventListener("DOMContentLoaded", () => {
  const $ = (s) => document.querySelector(s);
  const header = $("#siteHeader");
  const nav = $("#mainNav");
  const menu = $(".menu-toggle");
  const modal = $("#paymentModal");
  const qr = $("#qrImage");
  const upiText = $("#upiText");
  const upiLink = $("#upiLink");
  const paymentStatus = $("#paymentStatus");
  const form = $("#registrationForm");
  const fields = $("#registrationFields");
  const screenshot = $("#paymentScreenshot");
  const preview = $("#preview");
  const message = $("#formMessage");
  const submitBtn = $("#submitBtn");
  const success = $("#successState");

  // Dynamic pricing elements
  const eventFacilitySelect = $("#eventFacility");
  const summaryRegFee = $("#summaryRegFee");
  const summaryFacilityFee = $("#summaryFacilityFee");
  const summaryTotalAmount = $("#summaryTotalAmount");
  const payBtnAmount = $("#payBtnAmount");
  const modalPayAmount = $("#modalPayAmount");
  const mobileRegisterAmount = $("#mobileRegisterAmount");

  let currentFacilityFee = 0;
  let currentTotalAmount = SITE_CONFIG.registrationFee || 500;

  function calculatePrice() {
    const baseFee = SITE_CONFIG.registrationFee || 500;
    const selectedFacility = eventFacilitySelect ? eventFacilitySelect.value : "";

    if (selectedFacility === "Standee (₹500)") {
      currentFacilityFee = 500;
    } else if (selectedFacility === "Show Table (₹500)") {
      currentFacilityFee = 500;
    } else if (selectedFacility === "Standee + Show Table (₹500 + ₹500)") {
      currentFacilityFee = 1000;
    } else if (selectedFacility === "Not Required") {
      currentFacilityFee = 0;
    } else {
      currentFacilityFee = 0;
    }

    currentTotalAmount = baseFee + currentFacilityFee;

    if (summaryRegFee) summaryRegFee.textContent = `₹${baseFee}`;
    if (summaryFacilityFee) summaryFacilityFee.textContent = `₹${currentFacilityFee}`;
    if (summaryTotalAmount) summaryTotalAmount.textContent = `₹${currentTotalAmount}`;
    if (payBtnAmount) payBtnAmount.textContent = `₹${currentTotalAmount}`;
    if (modalPayAmount) modalPayAmount.textContent = `₹${currentTotalAmount}`;
    if (mobileRegisterAmount) mobileRegisterAmount.textContent = `₹${currentTotalAmount}`;

    configurePayment();
  }

  function configurePayment() {
    if (qr) qr.src = PAYMENT_CONFIG.qrImage;
    if (upiText) upiText.textContent = PAYMENT_CONFIG.upiId;
    if (PAYMENT_CONFIG.upiId && !PAYMENT_CONFIG.upiId.includes("YOUR_OFFICIAL")) {
      const params = new URLSearchParams({
        pa: PAYMENT_CONFIG.upiId,
        pn: "EAF Entrepreneurs Awareness Forum",
        am: String(currentTotalAmount),
        cu: "INR"
      });
      if (upiLink) {
        upiLink.href = "upi://pay?" + params.toString();
        upiLink.hidden = false;
      }
    }
  }

  calculatePrice();

  if (eventFacilitySelect) {
    eventFacilitySelect.addEventListener("change", () => {
      calculatePrice();
      validateEventFacility(false);
    });
  }

  window.addEventListener("scroll", () => header && header.classList.toggle("scrolled", window.scrollY > 20));

  if (menu && nav) {
    menu.addEventListener("click", () => {
      const open = nav.classList.toggle("open");
      menu.setAttribute("aria-expanded", String(open));
    });
    nav.querySelectorAll("a").forEach(a => a.addEventListener("click", () => nav.classList.remove("open")));
  }

  document.querySelectorAll('a[href^="#"]').forEach(a => a.addEventListener("click", (e) => {
    const target = document.querySelector(a.getAttribute("href"));
    if (target) { e.preventDefault(); target.scrollIntoView({ behavior: "smooth", block: "start" }); }
  }));

  const observer = new IntersectionObserver(entries => entries.forEach(entry => {
    if (entry.isIntersecting) { entry.target.classList.add("visible"); observer.unobserve(entry.target); }
  }), { threshold: .12 });
  document.querySelectorAll(".reveal").forEach(el => observer.observe(el));

  function openModal() {
    if (modal) {
      modal.classList.add("open");
      modal.setAttribute("aria-hidden", "false");
      document.body.style.overflow = "hidden";
    }
  }
  function closeModal() {
    if (modal) {
      modal.classList.remove("open");
      modal.setAttribute("aria-hidden", "true");
      document.body.style.overflow = "";
    }
  }
  if ($("#openPayment")) $("#openPayment").addEventListener("click", openModal);
  if ($("#closePayment")) $("#closePayment").addEventListener("click", closeModal);
  if ($("#closePaymentBtn")) $("#closePaymentBtn").addEventListener("click", closeModal);
  document.addEventListener("keydown", e => { if (e.key === "Escape" && modal && modal.classList.contains("open")) closeModal(); });

  if ($("#copyUpi")) {
    $("#copyUpi").addEventListener("click", async () => {
      try {
        await navigator.clipboard.writeText(PAYMENT_CONFIG.upiId);
        $("#copyUpi").textContent = "Copied";
        setTimeout(() => $("#copyUpi").textContent = "Copy", 1200);
      } catch {
        alert("Copy is unavailable. Please copy the UPI ID manually.");
      }
    });
  }

  if ($("#paymentDone")) {
    $("#paymentDone").addEventListener("click", () => {
      if (PAYMENT_CONFIG.upiId.includes("YOUR_OFFICIAL")) {
        alert("Please configure the official EAF UPI ID and QR code before using payment.");
        return;
      }
      closeModal();
      if (paymentStatus) {
        paymentStatus.textContent = "Payment marked as completed";
        paymentStatus.style.color = "#16834b";
      }
      if (fields) fields.scrollIntoView({ behavior: "smooth", block: "center" });
    });
  }

  function setError(inputEl, errorId, msg) {
    const errorEl = document.getElementById(errorId);
    if (errorEl) errorEl.textContent = msg;
    if (inputEl) inputEl.classList.toggle("is-invalid", !!msg);
  }

  function clearError(inputEl, errorId) {
    setError(inputEl, errorId, "");
  }

  function validateFname(showError = true) {
    const el = form ? form.fname : null;
    const val = el ? el.value.trim() : "";
    if (!val || val.length < 2) {
      if (showError) setError(el, "error-fname", "Please enter your full name.");
      return false;
    }
    clearError(el, "error-fname");
    return true;
  }

  function validateMobile(showError = true) {
    const el = form ? form.mobile : null;
    const val = el ? el.value.trim() : "";
    const digitsOnly = val.replace(/\D/g, "");
    const isValid = /^[6-9]\d{9}$/.test(digitsOnly) && val.replace(/[\s\-\+\(\)]/g, "").length === digitsOnly.length;
    if (!isValid) {
      if (showError) setError(el, "error-mobile", "Please enter a valid 10-digit mobile number.");
      return false;
    }
    clearError(el, "error-mobile");
    return true;
  }

  function validateEmail(showError = true) {
    const el = form ? form.email : null;
    const val = el ? el.value.trim() : "";
    const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
    if (!val || !isValid) {
      if (showError) setError(el, "error-email", "Please enter a valid email address.");
      return false;
    }
    clearError(el, "error-email");
    return true;
  }

  function validateCompanyName(showError = true) {
    const el = form ? form.company_name : null;
    const val = el ? el.value.trim() : "";
    if (!val) {
      if (showError) setError(el, "error-company_name", "Please enter your company name.");
      return false;
    }
    clearError(el, "error-company_name");
    return true;
  }

  function validateCompanyType(showError = true) {
    const el = form ? form.company_type : null;
    const val = el ? el.value : "";
    if (!val) {
      if (showError) setError(el, "error-company_type", "Please select a company type.");
      return false;
    }
    clearError(el, "error-company_type");
    return true;
  }

  function validateEventFacility(showError = true) {
    const el = eventFacilitySelect;
    const val = el ? el.value : "";
    if (!val) {
      if (showError) setError(el, "error-event_facility", "Please select an event facility.");
      return false;
    }
    clearError(el, "error-event_facility");
    return true;
  }

  function validateAddress(showError = true) {
    const el = form ? form.address : null;
    const val = el ? el.value.trim() : "";
    if (!val || val.length < 5) {
      if (showError) setError(el, "error-address", "Please enter your address.");
      return false;
    }
    clearError(el, "error-address");
    return true;
  }

  function validateUpiId(showError = true) {
    const el = $("#upiInput");
    const val = el ? el.value.trim() : "";
    const isValid = /^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}$/.test(val);
    if (!val || !isValid) {
      if (showError) setError(el, "error-upi_id", "Please enter a valid UPI ID (e.g. name@upi).");
      return false;
    }
    clearError(el, "error-upi_id");
    return true;
  }

  function validateScreenshot(showError = true) {
    const el = screenshot;
    const file = el && el.files ? el.files[0] : null;
    if (!file) {
      if (showError) setError(el, "error-payment_screenshot", "Payment screenshot is required.");
      return false;
    }
    const maxBytes = (SITE_CONFIG.maxUploadSizeMB || 5) * 1024 * 1024;
    const allowedTypes = ["image/jpeg", "image/png", "image/webp"];
    const ext = file.name.split(".").pop().toLowerCase();
    const allowedExts = ["jpg", "jpeg", "png", "webp"];

    if (file.size > maxBytes) {
      if (showError) setError(el, "error-payment_screenshot", `File size must be under ${SITE_CONFIG.maxUploadSizeMB || 5}MB.`);
      return false;
    }
    if (!allowedTypes.includes(file.type) && !allowedExts.includes(ext)) {
      if (showError) setError(el, "error-payment_screenshot", "Please upload a valid JPG, PNG or WEBP image.");
      return false;
    }
    clearError(el, "error-payment_screenshot");
    return true;
  }

  if (form) {
    if (form.fname) form.fname.addEventListener("input", () => validateFname(false));
    if (form.mobile) form.mobile.addEventListener("input", () => validateMobile(false));
    if (form.email) form.email.addEventListener("input", () => validateEmail(false));
    if (form.company_name) form.company_name.addEventListener("input", () => validateCompanyName(false));
    if (form.company_type) form.company_type.addEventListener("change", () => validateCompanyType(false));
    if (form.address) form.address.addEventListener("input", () => validateAddress(false));
    const upiInput = $("#upiInput");
    if (upiInput) upiInput.addEventListener("input", () => validateUpiId(false));
  }

  if (screenshot) {
    screenshot.addEventListener("change", () => {
      if (preview) preview.innerHTML = "";
      if (!validateScreenshot(true)) return;

      const file = screenshot.files[0];
      const img = document.createElement("img");
      img.alt = "Payment screenshot preview";
      img.src = URL.createObjectURL(file);
      if (preview) preview.appendChild(img);
      if (message) message.textContent = "";
    });
  }

  function validate() {
    if (message) message.textContent = "";
    const validations = [
      { valid: validateFname(true), el: form.fname },
      { valid: validateMobile(true), el: form.mobile },
      { valid: validateEmail(true), el: form.email },
      { valid: validateCompanyName(true), el: form.company_name },
      { valid: validateCompanyType(true), el: form.company_type },
      { valid: validateEventFacility(true), el: eventFacilitySelect },
      { valid: validateAddress(true), el: form.address },
      { valid: validateUpiId(true), el: $("#upiInput") },
      { valid: validateScreenshot(true), el: screenshot }
    ];

    const firstInvalid = validations.find((v) => !v.valid);
    if (firstInvalid) {
      if (firstInvalid.el) firstInvalid.el.focus();
      if (message) message.textContent = "Please fill in all required fields correctly before submitting.";
      return false;
    }
    return true;
  }

  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      if (!validate()) return;

      if (!PAYMENT_CONFIG.upiId || PAYMENT_CONFIG.upiId.includes("YOUR_OFFICIAL")) {
        if (message) message.textContent = "Payment configuration is not complete. Add the official UPI ID in js/config.js.";
        return;
      }

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = "Submitting…";
      }

      try {
        const endpoint = SITE_CONFIG.formEndpoint || "api/register.php";
        const fd = new FormData(form);
        fd.append("event", "EAF Entrepreneurs Awareness Day 2026");

        const res = await fetch(endpoint, { method: "POST", body: fd });
        let data = null;
        try {
          data = await res.json();
        } catch {
          // Fallback if not JSON
        }

        if (!res.ok || (data && !data.success)) {
          throw new Error((data && data.message) ? data.message : "Submission failed");
        }

        if (SITE_CONFIG.successRedirect) {
          window.location.href = SITE_CONFIG.successRedirect;
        } else {
          // Populate success state details
          if (data) {
            if ($("#successRegId")) $("#successRegId").textContent = data.registration_id || "EAF2026";
            if ($("#successAttendance")) $("#successAttendance").textContent = data.attendance_type || "SEATED";
            if ($("#successSeatNo")) $("#successSeatNo").textContent = data.seat_number || "N/A";
            if ($("#successSeatRow")) {
              $("#successSeatRow").style.display = data.seat_number ? "block" : "none";
            }
            if ($("#successFacility")) $("#successFacility").textContent = data.event_facility || (eventFacilitySelect ? eventFacilitySelect.value : "Not Required");
            if ($("#successTotal")) $("#successTotal").textContent = `₹${data.total_amount || currentTotalAmount}`;
          }

          const regSection = document.querySelector(".register-section");
          if (regSection) regSection.hidden = true;
          if (success) {
            success.hidden = false;
            success.scrollIntoView({ behavior: "smooth" });
          }
        }
      } catch (err) {
        if (message) message.textContent = err.message || "We could not submit your registration. Please try again or contact EAF.";
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = "Submit Registration";
        }
      }
    });
  }
});
