# EAF Entrepreneurs Awareness Day 2026

Static landing page built with HTML5, CSS3 and vanilla JavaScript.

## Files

- `index.html` — page structure/content
- `css/style.css` — all styling/responsive design
- `js/config.js` — event, payment and endpoint configuration
- `js/script.js` — navigation, animations, payment modal, validation and submission
- `assets/` — logo, hero, leadership, committee and payment assets

## Before production

1. Replace `assets/payment/upi-qr-placeholder.png` with the official EAF UPI QR.
2. Set the real UPI ID in `js/config.js`.
3. Set `SITE_CONFIG.formEndpoint` to a secure backend/form endpoint that accepts `multipart/form-data`.
4. Replace dummy leadership/committee portraits with official photographs.
5. Replace `assets/logos/eaf-logo-source.jpg` with the official logo file if a cleaner source is available.
6. Test the form endpoint with a real payment screenshot upload before publishing.

## Important

This is a static frontend. It does not permanently store registrations by itself. The screenshot is sent with `FormData` only when a real `formEndpoint` is configured.

The demo deliberately refuses to claim successful storage when no endpoint is configured.

## Payment

The payment modal supports a configurable UPI deep link. The actual QR and UPI ID must be supplied by EAF; no fake payment details are included.

## Deploy

Upload the entire folder structure to Hostinger/cPanel/public_html. Keep the relative paths unchanged.

## Content source

Event information is based on the supplied EAF material and the meeting minutes provided with the project request. Where the source did not specify committee roles, the page shows names only rather than inventing designations.

Leadership:
- Dr. BALASUBRAHMANYA — VICE PRESIDENT
- SINGAM AMRUTHAM — PRESIDENT
- SUMA — TREASURER

Managing Committee names visible in the supplied material:
- NEELAKANTAPPA
- YOGINATH
- RAGHAVENDRA
- RITANJALI S

Portraits included in this package are dummy placeholders and must not be presented as official photographs.
