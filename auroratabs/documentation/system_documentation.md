# AuroraTabs - System Documentation
**Capstone Project Technical Reference**

## 1. System Overview
**AuroraTabs** is a comprehensive tabulating and event management system designed for pageants, talent shows, and competitions. It streamlines the entire event lifecycle from organizer registration to live scoring and results calculation.

### 1.1 Technology Stack
*   **Backend**: PHP (Native/Vanilla)
*   **Database**: MySQL (Relational)
*   **Frontend**: HTML5, CSS3, JavaScript (jQuery), Bootstrap 5
*   **Architecture**: MVC-inspired (Model-View-Controller separation logic)

---

## 2. Database Architecture
The database is normalized to 3NF to ensure data integrity and minimize redundancy.

### 2.1 Core Entities
*   **Users & Roles (RBAC)**
    *   `users`: Stores authentication credentials, profile info, and `role_id`.
    *   `roles`: Defines system roles (Admin, Organizer, Judge, Tabulator).
    *   `permissions`: Granular access controls linked to roles and modules.
*   **Event Management**
    *   `competitions`: The central entity representing an event.
    *   `categories`: Segments of a competition (e.g., Swimsuit, Talent).
    *   `criteria`: Scoring metrics within meaningful categories (e.g., Poise, Impact).
*   **Participants**
    *   `candidates`: Contestants linked to competitions.
    *   `judges`: Scorers with specific access codes and passcodes.
    *   `judge_assignments`: Links judges to specific competitions.
*   **Scoring & Tabulation**
    *   `scores`: Individual score records (Judge -> Candidate -> Criteria).
    *   `formulas`: Custom calculation logic for weighted averages.
    *   `judge_locks`: Security mechanism to finalize scores per category.
*   **Voting & Finance**
    *   `vote_packages`: predefined bundles for purchasing votes.
    *   `transactions`: Financial audit trail (Stripe, GCash, PayPal).
    *   `votes`: Individual votes cast for candidates.

### 2.2 Security Features
*   **Soft Deletes**: Implemented via `is_deleted`, `deleted_at`, and `deleted_by` columns to prevent accidental data loss.
*   **Audit Logging**: `activity_logs` tracks all critical actions (Create, Update, Delete) with IP addresses.
*   **Role-Based Access**: Strict separation of duties between Organizers and Judges.

---

## 3. Core Modules & Workflows

### 3.1 Authentication Module
*   **Organizer Registration**: 
    1.  Multi-step wizard (Account -> Organization -> Review).
    2.  Password strength validation.
    3.  Email OTP verification.
*   **Login Flow**:
    *   **Audience**: Simple login for voting access.
    *   **Organizer**: Dashboard access for event management.
    *   **Judge**: specialized login using `Access Code` + `Passcode`.

### 3.2 Competition Management
*   **Setup Wizard**:
    1.  Define Competition Details (Title, Date, Venue).
    2.  Select Plan (Starter, Pro, Enterprise).
    3.  Configure Categories & Criteria.
*   **Status Lifecycle**: `Draft` -> `Upcoming` -> `Active` -> `Completed`.

### 3.3 Judging System
*   **Real-time Scoring**: Judges submit scores via a mobile-responsive interface.
*   **Locking Mechanism**: Judges must "Lock" their scores for a category to finalize them, preventing modification.
*   **Live Tabulation**: Admin dashboard updates in real-time as scores come in.

### 3.4 Voting System
*   **Vote Purchase**: 
    - Users select a package (e.g., 100 Votes for ₱50).
    - Payment processed via Gateway.
    - Votes credited to user account.
*   **Casting Votes**: Users allocate their available votes to candidates.
*   **Rate Limiting**: IP-based restrictions to prevent fraud (e.g., Max 1000 votes/IP/day).

---

## 4. System Configuration & Pricing

### 4.1 Pricing Engine
Dynamic pricing model managed by Super Admins:
*   `pricing_plans`: Defines tiers (e.g., Starter @ ₱10k, Pro @ ₱25k).
*   `plan_features`: Toggles capabilities like "Live TV Export" or "Whitelabeling".

### 4.2 Global Settings
Key-value store (`settings` table) for:
*   Payment Gateway Keys (Stripe Public/Secret Keys).
*   System Localization (Currency, Timezone).
*   Security Policies (Password complexity, Session timeout).

---

## 5. Security Protocols
1.  **Password Hashing**: Bcrypt used for all user passwords.
2.  **Session Management**: Secure session handling with inactivity timeouts.
3.  **Input Sanitation**: All inputs treated as untrusted; prepared statements used for SQL.
4.  **Access Control**: Middleware checks role permissions before loading admin pages.

---

## 6. Future Roadmap
*   **Offline Mode**: PWA support for judging without internet.
*   **Analytics**: Advanced visualization of voting trends.
*   **Multi-tenant**: SaaS structured support for multiple organizations.
