* {
  box-sizing: border-box;
  font-family: Arial, sans-serif;
}

body {
  margin: 0;
  background: #f4f8f2;
  color: #222;
}

/* =========================
   HEADER
========================= */
header {
  position: relative;
  overflow: hidden;
  background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 45%, #66bb6a 100%);
  color: white;
  padding: 18px 24px;
}

/* decorative circles */
header::before,
header::after {
  content: "";
  position: absolute;
  border-radius: 50%;
  opacity: 0.18;
  z-index: 0;
}

header::before {
  width: 220px;
  height: 220px;
  background: #c8e6c9;
  top: -70px;
  right: -40px;
}

header::after {
  width: 160px;
  height: 160px;
  background: #a5d6a7;
  bottom: -60px;
  left: 180px;
}

/* extra wave layer */
.header-wave {
  position: absolute;
  right: -30px;
  top: 0;
  width: 320px;
  height: 100%;
  opacity: 0.16;
  z-index: 0;
  pointer-events: none;
}

.header-wave::before,
.header-wave::after {
  content: "";
  position: absolute;
  border: 2px solid white;
  border-left: none;
  border-bottom: none;
  border-radius: 50%;
}

.header-wave::before {
  width: 220px;
  height: 220px;
  right: 20px;
  top: -40px;
  transform: rotate(18deg);
}

.header-wave::after {
  width: 160px;
  height: 160px;
  right: 90px;
  top: 35px;
  transform: rotate(18deg);
}

/* dotted texture */
.header-dots {
  position: absolute;
  left: 110px;
  top: 10px;
  width: 140px;
  height: 70px;
  z-index: 0;
  opacity: 0.18;
  background-image: radial-gradient(white 1.5px, transparent 1.5px);
  background-size: 16px 16px;
}

.header-wrap {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  gap: 16px;
}

.site-logo {
  width: 74px;
  height: 74px;
  object-fit: contain;
  background: rgba(255,255,255,0.95);
  border-radius: 14px;
  padding: 5px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.header-text h1 {
  margin: 0;
  font-size: 30px;
  letter-spacing: 0.4px;
}

.header-text p {
  margin: 5px 0 0;
  font-size: 15px;
  color: #f1fff1;
}

/* =========================
   NAVIGATION
========================= */
nav {
  background: #1b5e20;
  padding: 12px;
  text-align: center;
}

nav a {
  display: inline-block;
  margin: 5px;
  padding: 10px 14px;
  border-radius: 6px;
  background: white;
  color: #1b5e20;
  text-decoration: none;
  font-weight: bold;
  transition: 0.2s ease;
}

nav a:hover {
  background: #dcedc8;
  transform: translateY(-1px);
}

/* =========================
   LAYOUT
========================= */
.container {
  width: 92%;
  max-width: 1250px;
  margin: 20px auto;
}

.box {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 3px 10px rgba(0,0,0,0.08);
  margin-bottom: 20px;
}

.msg {
  padding: 12px;
  margin-bottom: 15px;
  border-radius: 8px;
  background: #f1f8e9;
  border-left: 5px solid #689f38;
}

/* =========================
   FORMS
========================= */
label {
  font-weight: bold;
  display: block;
  margin-bottom: 5px;
  color: #1b5e20;
}

input,
textarea,
select {
  width: 100%;
  padding: 10px;
  margin: 8px 0 14px;
  border: 1px solid #bbb;
  border-radius: 6px;
  font-size: 14px;
}

textarea {
  min-height: 100px;
  resize: vertical;
}

button {
  background: #2e7d32;
  color: white;
  border: none;
  padding: 12px 18px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: bold;
  transition: 0.2s ease;
}

button:hover {
  background: #1b5e20;
  transform: translateY(-1px);
}

/* =========================
   PRODUCT CARDS
========================= */
.card-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 18px;
  margin-top: 15px;
}

.card {
  border: 1px solid #d7e9d5;
  border-radius: 12px;
  padding: 15px;
  background: #fcfffb;
  box-shadow: 0 1px 6px rgba(0,0,0,0.06);
}

.card img {
  width: 100%;
  height: 210px;
  object-fit: cover;
  border-radius: 10px;
  margin-bottom: 10px;
  border: 1px solid #ddd;
}

.card h3 {
  margin-top: 0;
  color: #1b5e20;
}

.card p {
  margin: 6px 0;
}

/* =========================
   FILTERS
========================= */
.filter-grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 12px;
}

/* =========================
   SUMMARY BOX
========================= */
.summary-box {
  background: #f1f8e9;
  border: 1px solid #c5e1a5;
  padding: 14px;
  border-radius: 10px;
  margin-top: 15px;
}

.summary-box p {
  margin: 8px 0;
}

/* =========================
   TABLES
========================= */
table {
  width: 100%;
  border-collapse: collapse;
  overflow-x: auto;
  display: block;
}

th,
td {
  border: 1px solid #cfd8dc;
  padding: 10px;
  text-align: left;
  min-width: 120px;
}

th {
  background: #e8f5e9;
  color: #1b5e20;
}

/* =========================
   INVOICE BUTTON
========================= */
.invoice-btn {
  display: inline-block;
  background: #1565c0;
  color: white;
  padding: 8px 12px;
  text-decoration: none;
  border-radius: 6px;
  margin-top: 8px;
}

.invoice-btn:hover {
  background: #0d47a1;
}

/* =========================
   ADMIN SUMMARY
========================= */
.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 15px;
  margin-top: 15px;
}

.summary-card {
  background: #f9fff8;
  border: 1px solid #d7e9d5;
  border-radius: 10px;
  padding: 15px;
  box-shadow: 0 1px 6px rgba(0,0,0,0.05);
}

.summary-card h3 {
  margin: 0 0 10px 0;
  color: #1b5e20;
  font-size: 18px;
}

.summary-card p {
  margin: 0;
  font-size: 22px;
  font-weight: bold;
  color: #2e7d32;
}

/* =========================
   ACTION LINKS
========================= */
.action-link {
  display: inline-block;
  margin: 3px 4px 3px 0;
  padding: 6px 10px;
  border-radius: 5px;
  text-decoration: none;
  color: white;
  font-size: 13px;
}

.approve-link {
  background: #2e7d32;
}

.delete-link {
  background: #c62828;
}

.status-yes {
  color: green;
  font-weight: bold;
}

.status-no {
  color: #ef6c00;
  font-weight: bold;
}

.order-img {
  width: 70px;
  height: 55px;
  object-fit: cover;
  border-radius: 6px;
  border: 1px solid #ccc;
}

/* =========================
   RESPONSIVE
========================= */
@media (max-width: 900px) {
  .filter-grid {
    grid-template-columns: 1fr 1fr;
  }

  .header-text h1 {
    font-size: 24px;
  }

  .site-logo {
    width: 64px;
    height: 64px;
  }
}

@media (max-width: 600px) {
  .filter-grid {
    grid-template-columns: 1fr;
  }

  .header-wrap {
    flex-direction: row;
    align-items: center;
  }

  .header-text h1 {
    font-size: 20px;
  }

  .header-text p {
    font-size: 13px;
  }

  nav a {
    display: block;
    margin: 8px auto;
    width: 90%;
  }
}