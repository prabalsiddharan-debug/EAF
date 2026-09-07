// EAF website configuration.
// Replace only the values marked for configuration before production.

const SITE_CONFIG = {
  eventDate: "26 September 2026",
  venue: "TERIGE BHAVANA, 11 BLOCK, 2ND STAGE, NAAGARABHAVI, BENGALURU, KARNATAKA 560072",
  phone: "+91 9743334433",
  email: "connect.eaf@gmail.com",
  registrationFee: 500,

  // Add your real backend / form endpoint here.
  formEndpoint: "api/register.php",

  // Optional success redirect. Leave blank to use the built-in success screen.
  successRedirect: "",

  maxUploadSizeMB: 5
};

const PAYMENT_CONFIG = {
  // Replace with the official EAF UPI ID.
  upiId: "9901155111@sbi",
  amount: 500,
  qrImage: "assets/payment/QRC0de.jpeg"
};
