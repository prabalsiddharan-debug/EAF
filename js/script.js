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

  function configurePayment() {
    qr.src = PAYMENT_CONFIG.qrImage;
    upiText.textContent = PAYMENT_CONFIG.upiId;
    if (PAYMENT_CONFIG.upiId && !PAYMENT_CONFIG.upiId.includes("YOUR_OFFICIAL")) {
      const params = new URLSearchParams({
        pa: PAYMENT_CONFIG.upiId,
        pn: "EAF Entrepreneurs Awareness Forum",
        am: String(PAYMENT_CONFIG.amount),
        cu: "INR"
      });
      upiLink.href = "upi://pay?" + params.toString();
      upiLink.hidden = false;
    }
  }
  configurePayment();

  window.addEventListener("scroll", () => header.classList.toggle("scrolled", window.scrollY > 20));

  menu.addEventListener("click", () => {
    const open = nav.classList.toggle("open");
    menu.setAttribute("aria-expanded", String(open));
  });
  nav.querySelectorAll("a").forEach(a => a.addEventListener("click", () => nav.classList.remove("open")));

  document.querySelectorAll('a[href^="#"]').forEach(a => a.addEventListener("click", (e) => {
    const target = document.querySelector(a.getAttribute("href"));
    if (target) { e.preventDefault(); target.scrollIntoView({behavior:"smooth", block:"start"}); }
  }));

  const observer = new IntersectionObserver(entries => entries.forEach(entry => {
    if (entry.isIntersecting) { entry.target.classList.add("visible"); observer.unobserve(entry.target); }
  }), {threshold:.12});
  document.querySelectorAll(".reveal").forEach(el => observer.observe(el));

  function openModal() { modal.classList.add("open"); modal.setAttribute("aria-hidden","false"); document.body.style.overflow="hidden"; }
  function closeModal() { modal.classList.remove("open"); modal.setAttribute("aria-hidden","true"); document.body.style.overflow=""; }
  $("#openPayment").addEventListener("click", openModal);
  $("#closePayment").addEventListener("click", closeModal);
  $("#closePaymentBtn").addEventListener("click", closeModal);
  document.addEventListener("keydown", e => { if(e.key === "Escape" && modal.classList.contains("open")) closeModal(); });

  $("#copyUpi").addEventListener("click", async () => {
    try { await navigator.clipboard.writeText(PAYMENT_CONFIG.upiId); $("#copyUpi").textContent = "Copied"; setTimeout(()=>$("#copyUpi").textContent="Copy",1200); }
    catch { alert("Copy is unavailable. Please copy the UPI ID manually."); }
  });

  $("#paymentDone").addEventListener("click", () => {
    if (PAYMENT_CONFIG.upiId.includes("YOUR_OFFICIAL")) {
      alert("Please configure the official EAF UPI ID and QR code before using payment.");
      return;
    }
    closeModal();
    paymentStatus.textContent = "Payment marked as completed";
    paymentStatus.style.color = "#16834b";
    fields.scrollIntoView({behavior:"smooth", block:"center"});
  });

  screenshot.addEventListener("change", () => {
    preview.innerHTML = "";
    const file = screenshot.files[0];
    if (!file) return;
    const max = SITE_CONFIG.maxUploadSizeMB * 1024 * 1024;
    if (file.size > max) {
      screenshot.value = "";
      message.textContent = `Please upload a file smaller than ${SITE_CONFIG.maxUploadSizeMB} MB.`;
      return;
    }
    if (!file.type.startsWith("image/")) {
      screenshot.value = "";
      message.textContent = "Please upload a JPG, JPEG, PNG or WEBP image.";
      return;
    }
    const img = document.createElement("img");
    img.alt = "Payment screenshot preview";
    img.src = URL.createObjectURL(file);
    preview.appendChild(img);
    message.textContent = "";
  });

  function validate() {
    message.textContent = "";
    const mobile = form.mobile.value.replace(/\D/g, "");
    if (mobile.length < 10) { message.textContent = "Please enter a valid mobile number."; form.mobile.focus(); return false; }
    if (!form.checkValidity()) { form.reportValidity(); return false; }
    if (!screenshot.files[0]) { message.textContent = "Please upload your payment screenshot."; return false; }
    return true;
  }

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!validate()) return;
    if (!PAYMENT_CONFIG.upiId || PAYMENT_CONFIG.upiId.includes("YOUR_OFFICIAL")) {
      message.textContent = "Payment configuration is not complete. Add the official UPI ID in js/config.js.";
      return;
    }
    if (!SITE_CONFIG.formEndpoint) {
      message.textContent = "Demo mode: add your formEndpoint in js/config.js to enable real submission.";
      return;
    }
    submitBtn.disabled = true;
    submitBtn.textContent = "Submitting…";
    try {
      const fd = new FormData(form);
      fd.append("event", "EAF Entrepreneurs Awareness Day 2026");
      fd.append("amount", String(SITE_CONFIG.registrationFee));
      const res = await fetch(SITE_CONFIG.formEndpoint, {method:"POST", body:fd});
      if (!res.ok) throw new Error("Submission failed");
      if (SITE_CONFIG.successRedirect) window.location.href = SITE_CONFIG.successRedirect;
      else {
        document.querySelector(".register-section").hidden = true;
        success.hidden = false;
        success.scrollIntoView({behavior:"smooth"});
      }
    } catch (err) {
      message.textContent = "We could not submit your registration. Please try again or contact EAF.";
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = "Submit Registration";
    }
  });
});
