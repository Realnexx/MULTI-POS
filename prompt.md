Nice—this upgrade makes it a **real multi-role ecosystem** (POS + Inventory + Public Website + Dynamic RBAC). I’ll refine your AI prompt so Antigravity (or any code-gen AI) builds it correctly.

---

# 🧠 🔥 UPDATED ANTIGRAVITY PROMPT

## POS + Inventory + Dynamic Website + RBAC (Stacey Halu Halu)

Copy this directly:

---

## PROMPT:

Build a full-stack web system for a business named:

# 🍧 “Stacey Halu Halu”

It must include:

- POS System
- Inventory Management System
- Dynamic Public Website (customer-facing)
- Role-Based Access Control (RBAC) with dynamic roles

---

# 🧰 TECH STACK

- HTML5
- CSS3
- Bootstrap 5
- JavaScript
- jQuery
- PHP (MySQLi, PHP 7 compatible)
- MySQL
- AJAX (for POS + dynamic actions)
- Semi-MVC architecture

---

# 🌐 SYSTEM MODULES

## 1. 🌍 DYNAMIC PUBLIC WEBSITE (CUSTOMERS)

This is a public-facing website (no login required) that is dynamically driven from the database.

### Features:

- Home page (hero section + featured products)
- Product listing (fetched from database)
- Product categories (Halo-halo, Drinks, Desserts, Add-ons)
- Product details page
- About page
- Contact page (with form stored in DB)
- “Order Now” button (redirects to POS or order request system)
- Featured/Best sellers section (admin controlled)

### Requirements:

- Fully dynamic content from database
- Responsive design (mobile-first)
- SEO-friendly structure
- Clean modern UI

---

## 2. 🛒 POS SYSTEM (CASHIER MODULE)

### Features:

- Product grid view with images
- Add to cart using jQuery AJAX
- Quantity +/-
- Auto computation of total
- Discounts support
- Checkout system
- Receipt printing
- Transaction history

---

## 3. 📦 INVENTORY SYSTEM

### Features:

- Product CRUD (Create, Read, Update, Delete)
- Stock in / stock out tracking
- Inventory logs
- Low stock alerts
- Product categories management
- Image upload system

---

## 4. 👥 DYNAMIC ROLE-BASED ACCESS CONTROL (RBAC)

### IMPORTANT: Roles must be database-driven, NOT hardcoded.

### System must support:

#### Core Roles:

- Admin
- Cashier
- Customer

BUT ALSO:

- Admin can CREATE NEW ROLES dynamically (example: Manager, Inventory Staff, Supervisor, etc.)

---

### RBAC REQUIREMENTS:

#### Tables:

- roles
- permissions
- role_permissions
- user_roles
- users

---

### Permission System:

Each module must have permission flags:

Example:

- view_dashboard
- manage_products
- manage_inventory
- access_pos
- view_reports
- manage_users
- access_website_admin

---

### Rules:

- Middleware must check permissions before accessing pages
- Users can have multiple roles
- Roles can be assigned dynamically
- Admin can assign permissions per role via UI

---

## 5. 👤 USER SYSTEM

### Features:

- Login / Logout
- Registration (optional for customers)
- Session-based authentication
- Password hashing (password_hash / verify)
- Login attempt limiter
- CSRF protection

---

## 6. 📊 ADMIN DASHBOARD

### Features:

- Total sales today
- Total products
- Low stock alerts
- Total users
- Graphs (optional Chart.js)
- Recent transactions

---

## 🧾 DATABASE TABLES

Must include:

- users
- roles
- permissions
- role_permissions
- user_roles
- products
- categories
- inventory_logs
- sales
- sales_items
- contacts_messages
- website_content (for homepage dynamic CMS)

---

## 🏗️ SEMI-MVC STRUCTURE

```
/stacey-halu-halu
│
├── config/
├── controllers/
├── models/
├── views/
│   ├── website/
│   ├── admin/
│   ├── cashier/
│   ├── inventory/
│   ├── auth/
│
├── middleware/
│   ├── auth.php
│   ├── rbac.php
│
├── ajax/
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│
└── index.php
```

---

## 🎨 UI/UX REQUIREMENTS

### Style:

- Modern SaaS dashboard
- Clean UI like Shopify POS / Square POS
- Bootstrap 5 layout
- Card-based design
- Soft shadows + rounded corners

---

### COLORS:

- Primary: Pink dessert theme (#ff4d6d or similar)
- Secondary: White / light gray
- Accent: Purple or pastel orange

---

## 🌐 WEBSITE + ADMIN LINKAGE

- Admin controls website content dynamically:
  - Featured products
  - Homepage banners
  - About content
  - Contact info

- Changes reflect instantly on public website

---

## 🔐 SECURITY REQUIREMENTS

- MySQLi prepared statements
- Input sanitization
- CSRF tokens
- Session security
- RBAC middleware enforcement
- Secure file upload validation

---

## 🚀 OUTPUT REQUIREMENT

Generate a fully working system with:

- Dynamic public website
- POS system
- Inventory system
- Advanced RBAC system
- Clean MVC-like structure
- Secure PHP backend
- AJAX-driven interactions
- Production-ready UI

---

# 💡 If you want next upgrade, I can also generate:

🔥 Full SQL database schema (RBAC-ready)
🔥 Login + middleware PHP code
🔥 POS AJAX cart system
🔥 Admin role management UI
🔥 Website frontend template (ready to use)

Just tell me 👍
