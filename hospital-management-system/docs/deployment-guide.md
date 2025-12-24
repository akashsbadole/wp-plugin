# Hospital Management System (HMS) - Deployment Guide

This guide provides a step-by-step process for deploying the Hospital Management System (HMS) plugin to a live production environment. Following these steps will ensure a smooth and successful go-live.

## Table of Contents

1.  [Pre-Deployment Checklist](#pre-deployment-checklist)
2.  [Server Requirements & Setup](#server-requirements--setup)
    - [Server Requirements Check](#server-requirements-check)
    - [Database Configuration](#database-configuration)
    - [WordPress Configuration](#wordpress-configuration)
3.  [Plugin Deployment](#plugin-deployment)
4.  [Plugin Configuration](#plugin-configuration)
    - [Initial Setup Wizard](#initial-setup-wizard)
    - [Manual Configuration](#manual-configuration)
5.  [Third-Party Integration Setup](#third-party-integration-setup)
    - [Payment Gateways](#payment-gateways)
    - [SMS/WhatsApp Gateways](#smswhatsapp-gateways)
    - [Email (SMTP)](#email-smtp)
6.  [Security Hardening](#security-hardening)
7.  [Performance Optimization](#performance-optimization)
8.  [Final Testing Checklist](#final-testing-checklist)
9.  [Go-Live Checklist](#go-live-checklist)
    - [Data Migration (if applicable)](#data-migration-if-applicable)
    - [User Training](#user-training)
    - [Go-Live Procedures](#go-live-procedures)
    - [Post-Launch Monitoring](#post-launch-monitoring)

---

## 1. Pre-Deployment Checklist <a name="pre-deployment-checklist"></a>

Before you begin the deployment process, ensure you have completed the following:

- [ ] **Staging Environment:** All features have been thoroughly tested on a staging environment that mirrors the production server.
- [ ] **Backup:** A full backup of the production website (files and database) has been taken.
- [ ] **Plugin Version:** You have the final, stable version of the HMS plugin ready for deployment.
- [ ] **SSL Certificate:** An SSL certificate is installed and active on the production site to ensure secure data transmission.
- [ ] **Domain & DNS:** The domain name is pointed to the production server, and DNS settings are configured.
- [ ] **Access Credentials:** You have all necessary credentials:
  - FTP/SFTP or SSH access to the server.
  - cPanel/Plesk or other hosting control panel access.
  - WordPress admin access.
  - Database access (phpMyAdmin or similar).
- [ ] **Third-Party Accounts:** You have API keys and credentials for all third-party services (payment gateways, SMS, etc.).

---

## 2. Server Requirements & Setup <a name="server-requirements--setup"></a>

### 2.1. Server Requirements Check <a name="server-requirements-check"></a>

Verify that your production server meets or exceeds the minimum requirements for HMS and WordPress.

- **PHP Version:** 7.4 or higher (8.0 or higher recommended for better performance and security).
- **MySQL Version:** 5.6 or higher (or MariaDB 10.1 or higher).
- **WordPress Version:** 5.0 or higher.
- **PHP Extensions:** Ensure the following PHP extensions are enabled:
  - `mysqli` or `mysqlnd` (for database connectivity).
  - `curl` (for API calls to third-party services).
  - `gd` or `imagick` (for image processing, if used).
  - `mbstring` (for multi-byte string handling).
  - `xml` (for certain WordPress functionalities).
  - `zip` (for plugin updates and backups).
- **PHP Memory Limit:** `memory_limit` in `php.ini` should be at least `128M`. `256M` is recommended.
- **PHP Max Execution Time:** `max_execution_time` in `php.ini` should be at least `60` seconds. Some operations (like report generation) might require more.
- **PHP Upload Max Filesize:** `upload_max_filesize` and `post_max_size` should be sufficient for any file uploads (e.g., patient documents, reports). `32M` is a good starting point.
- **Web Server:** Apache (with `mod_rewrite` enabled) or Nginx.

**How to Check:**
You can check PHP settings by creating a `phpinfo.php` file in your WordPress root directory with the content `<?php phpinfo(); ?>`. Access it via your browser (`https://yourdomain.com/phpinfo.php`) and remember to delete it afterward.

### 2.2. Database Configuration <a name="database-configuration"></a>

- **Create a Dedicated Database:** It's best practice to use a dedicated database user for WordPress with limited privileges.
  ```sql
  CREATE DATABASE hms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  CREATE USER 'hms_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';
  GRANT ALL PRIVILEGES ON hms_db.* TO 'hms_user'@'localhost';
  FLUSH PRIVILEGES;
  ```
- **Update `wp-config.php`:** Ensure your `wp-config.php` file has the correct database credentials:
  ```php
  define('DB_NAME', 'hms_db');
  define('DB_USER', 'hms_user');
  define('DB_PASSWORD', 'STRONG_PASSWORD');
  define('DB_HOST', 'localhost');
  define('DB_CHARSET', 'utf8mb4');
  define('DB_COLLATE', 'utf8mb4_unicode_ci');
  ```

### 2.3. WordPress Configuration <a name="wordpress-configuration"></a>

- **Install WordPress:** If not already installed, run the WordPress installation script.
- **Update `wp-config.php` Security Keys:** Generate and add unique security keys. You can get new keys from [https://api.wordpress.org/secret-key/1.1/salt/](https://api.wordpress.org/secret-key/1.1/salt/).
  ```php
  define('AUTH_KEY',         'put your unique phrase here');
  define('SECURE_AUTH_KEY',  'put your unique phrase here');
  define('LOGGED_IN_KEY',    'put your unique phrase here');
  define('NONCE_KEY',        'put your unique phrase here');
  define('AUTH_SALT',        'put your unique phrase here');
  define('SECURE_AUTH_SALT', 'put your unique phrase here');
  define('LOGGED_IN_SALT',   'put your unique phrase here');
  define('NONCE_SALT',       'put your unique phrase here');
  ```
- **Configure WordPress File Permissions:**
  - **Folders:** `755` (drwxr-xr-x)
  - **Files:** `644` (-rw-r--r--)
  - **`wp-config.php`:** `600` (-rw-------) for maximum security.
- **Configure Permalinks:** Go to **Settings -> Permalinks** in your WordPress admin and select a "Pretty" permalink structure (e.g., "Post name"). This is crucial for the patient portal and other features to work correctly.

---

## 3. Plugin Deployment <a name="plugin-deployment"></a>

There are two primary ways to deploy the HMS plugin:

### Method 1: Via WordPress Admin (Recommended for most users)

1.  **Download the Plugin:** Obtain the `hospital-management-system.zip` file.
2.  **Navigate to Admin:** Log in to your WordPress Admin Dashboard.
3.  **Upload Plugin:**
    - Go to **Plugins -> Add New**.
    - Click **Upload Plugin**.
    - Choose the `hospital-management-system.zip` file and click **Install Now**.
4.  **Activate the Plugin:** After installation, click **Activate Plugin**.

### Method 2: Via FTP/SFTP (Manual Upload)

1.  **Unzip the Plugin:** Unzip the `hospital-management-system.zip` file on your local computer.
2.  **Connect to Server:** Use an FTP/SFTP client (like FileZilla, Cyberduck) to connect to your production server.
3.  **Upload Folder:** Navigate to the `/wp-content/plugins/` directory on your server.
4.  **Upload Files:** Upload the unzipped `hospital-management-system` folder to the `/wp-content/plugins/` directory.
5.  **Activate the Plugin:** Log in to your WordPress Admin Dashboard, go to **Plugins**, find "Hospital Management System (HMS)" in the list, and click **Activate**.

Upon activation, HMS will automatically create its database tables. You should see a new "HMS" menu item in the WordPress admin sidebar.

---

## 4. Plugin Configuration <a name="plugin-configuration"></a>

After activation, you need to configure HMS for your hospital's specific needs.

### 4.1. Initial Setup Wizard (if available) <a name="initial-setup-wizard"></a>

If HMS includes a setup wizard, follow the on-screen instructions to configure basic settings like hospital name, currency, and default user roles.

### 4.2. Manual Configuration <a name="manual-configuration"></a>

Navigate to **HMS -> Settings** and configure the following sections:

- **Hospital Details:**
  - **Hospital Name:** Enter the official name of your hospital.
  - **Address, Phone, Email:** Enter your hospital's contact information.
  - **Logo:** Upload your hospital's logo. This will be used in invoices, reports, and the patient portal.
- **General Settings:**
  - **Currency:** Select the default currency for billing.
  - **Timezone:** Select your hospital's timezone to ensure accurate timestamps for appointments and records.
  - **Date & Time Format:** Choose the preferred formats for displaying dates and times.
- **User Roles & Permissions:**
  - Verify that the HMS-specific user roles (Doctor, Nurse, Pharmacist, etc.) have been created.
  - Use a role management plugin (e.g., "User Role Editor") to fine-tune capabilities for each role if necessary. Ensure that users have the correct level of access.
- **License Management (if applicable):**
  - Navigate to **HMS -> License Management**.
  - Enter your license key and activate it to unlock premium features.

---

## 5. Third-Party Integration Setup <a name="third-party-integration-setup"></a>

This is a critical step for enabling features like online payments, SMS notifications, and reliable email delivery.

### 5.1. Payment Gateways <a name="payment-gateways"></a>

Navigate to **HMS -> Settings -> Integrations**.

1.  **Select Gateway:** Choose your payment gateway (e.g., Razorpay, Stripe, PayU) from the list.
2.  **Configure Gateway:**
    - Enter the API keys/test keys provided by your payment gateway provider.
    - Set the payment mode to "Test" initially to perform test transactions.
    - Configure other settings like success/failure URLs, if required.
3.  **Test Transactions:** Perform a few test transactions in "Test" mode to ensure the integration is working correctly.
4.  **Go Live:** Once tested, switch the payment mode to "Live" and replace test keys with live API keys.

### 5.2. SMS/WhatsApp Gateways <a name="smswhatsapp-gateways"></a>

Navigate to **HMS -> Settings -> Notifications**.

1.  **SMS Gateway:**
    - Select your SMS provider (e.g., Twilio, Plivo, MSG91).
    - Enter your API credentials (Account SID, Auth Token, API Key, etc.).
    - Configure the sender ID.
    - Send a test SMS to verify the setup.
2.  **WhatsApp Gateway:**
    - Select your WhatsApp provider (e.g., Twilio WhatsApp, Facebook Graph API).
    - Enter the required API credentials and phone number ID.
    - Send a test WhatsApp message to verify.

### 5.3. Email (SMTP) <a name="email-smtp"></a>

To ensure reliable email delivery (especially for appointment reminders and notifications), it's highly recommended to use SMTP.

1.  **Install an SMTP Plugin:** Install and activate a popular SMTP plugin like "WP Mail SMTP" or "Easy WP SMTP".
2.  **Configure the Plugin:**
    - Go to the plugin's settings page (e.g., **Settings -> WP Mail SMTP**).
    - Choose your mailer (e.g., SMTP, SendGrid, Mailgun).
    - Enter your SMTP server details (host, port, encryption, username, password). You can get these from your hosting provider or a transactional email service.
3.  **Send a Test Email:** Use the plugin's test email feature to confirm that emails are being sent and received correctly.

---

## 6. Security Hardening <a name="security-hardening"></a>

Protecting patient data is paramount. Follow these steps to secure your HMS installation.

- **Strong Passwords:** Enforce strong password policies for all WordPress users, especially administrators and staff.
- **Two-Factor Authentication (2FA):** Implement 2FA for all admin and staff accounts. Use a plugin like "Google Authenticator" or "Wordfence Security".
- **Limit Login Attempts:** Use a security plugin to limit login attempts and block brute-force attacks.
- **Security Plugin:** Install and configure a comprehensive security plugin like "Wordfence Security" or "Sucuri Security". Use it to:
  - Enable a Web Application Firewall (WAF).
  - Scan for malware and vulnerabilities.
  - Monitor file integrity.
- **File Permissions:** Double-check that file permissions are set correctly as described in [Section 2.3](#wordpress-configuration).
- **Disable File Editing in WordPress Admin:** Add the following to your `wp-config.php` to prevent users from editing theme and plugin files from the admin dashboard, which is a common attack vector.
  ```php
  define('DISALLOW_FILE_EDIT', true);
  ```
- **Protect `wp-config.php`:** Move the `wp-config.php` file one directory above your WordPress root if your server setup allows it. If not, protect it using `.htaccess` (for Apache) or server configuration.
- **Use HTTPS:** Ensure your entire site runs on HTTPS. Install an SSL certificate and force HTTPS redirects.
- **Regular Backups:** Set up automated, regular backups of your website files and database. Store backups in a secure, off-site location. Use a plugin like "UpdraftPlus" or "VaultPress (Jetpack)".
- **Data Encryption at Rest:** HMS may have features for encrypting sensitive data in the database. Ensure this is enabled and configured correctly. (Consult the specific HMS documentation for this feature).

---

## 7. Performance Optimization <a name="performance-optimization"></a>

A fast and responsive system is crucial for user satisfaction, especially in a busy hospital environment.

- **Caching:**
  - **Object Caching:** Enable a persistent object caching backend like Redis or Memcached. This significantly speeds up database queries and dynamic content generation. You may need a plugin like "Redis Object Cache" and server-side configuration.
  - **Page Caching:** Use a WordPress caching plugin like "WP Rocket" or "W3 Total Cache". This creates static HTML files of your pages, reducing server load.
  - **Browser Caching:** Ensure browser caching is enabled on your server (via `.htaccess` or Nginx config) to serve static assets (CSS, JS, images) faster for returning visitors.
- **Content Delivery Network (CDN):** Use a CDN (e.g., Cloudflare, StackPath) to serve static assets (images, CSS, JS) from servers closer to your users, reducing latency. This also offloads traffic from your origin server.
- **Database Optimization:**
  - **Indexing:** Ensure all database tables, especially HMS tables, have proper indexes for common queries. (This should be handled by the plugin, but it's good to be aware).
  - **Regular Cleaning:** Periodically clean up your WordPress database by deleting spam comments, trashed posts, and transients. A plugin like "WP-Optimize" can help.
- **Image Optimization:**
  - Optimize all images before uploading them. Use tools like TinyPNG or ImageOptim.
  - Use a WordPress plugin like "Smush" or "ShortPixel" to automatically compress images without significant quality loss.
  - Serve images in next-gen formats like WebP if browser support is available.
- **Minification:**
  - Minify CSS and JavaScript files to reduce their size. Caching plugins often include this feature.
  - Enable Gzip or Brotli compression on your server to compress text-based assets before sending them to the browser.
- **Upgrade Hosting:** If, after all optimizations, the site is still slow, consider upgrading to a better hosting plan (e.g., from shared hosting to VPS or managed WordPress hosting).

---

## 8. Final Testing Checklist <a name="final-testing-checklist"></a>

Before going live, perform a comprehensive round of testing on the production server to replicate real-world usage.

- [ ] **Admin Area Functionality:**
  - Can you log in as an Administrator?
  - Can you navigate all HMS admin pages without errors?
  - Can you create a new patient, edit their details, and view their profile?
  - Can you book, edit, and manage appointments?
  - Can a doctor create a prescription?
  - Can a pharmacist dispense medicine and see stock levels update?
  - Can you generate a sample report and export it?
- [ ] **Patient Portal Functionality:**
  - Can a new user register on the patient portal?
  - Can an existing patient log in?
  - Can a patient book an appointment?
  - Can a patient view their profile, appointments, and (sample) medical records?
  - Can a patient see a sample bill and test the payment gateway (in test mode)?
- [ ] **Third-Party Integrations:**
  - Do appointment reminder emails/SMS/WhatsApp messages get sent correctly? (Test with a non-patient phone number/email).
  - Does the payment gateway integration work end-to-end? (Use test credentials).
- [ ] **Performance Check:**
  - Is the admin dashboard responsive?
  - Do patient portal pages load quickly?
  - Use tools like [Google PageSpeed Insights](https://pagespeed.web.dev/) or [GTmetrix](https://gtmetrix.com/) to check front-end performance.
- [ ] **Security Check:**
  - Try accessing sensitive URLs directly (e.g., `wp-config.php`).
  - Check if non-admin users can access admin-only pages.
  - Ensure all pages are served over HTTPS.
- [ ] **Cross-Browser Compatibility:**
  - Test key functionalities (admin login, patient portal) on major browsers (Chrome, Firefox, Safari, Edge).

---

## 9. Go-Live Checklist <a name="go-live-checklist"></a>

This is the final phase of making the HMS system live for all users.

### 9.1. Data Migration (if applicable) <a name="data-migration-if-applicable"></a>

If you are migrating from an old system, this is a critical step. **ALWAYS PERFORM MIGRATION ON A STAGING COPY FIRST.**

- [ ] **Data Mapping:** Create a detailed map of fields from the old database to the new HMS database structure.
- [ ] **Migration Script:** Develop a script (PHP, Python, etc.) to extract data from the old system, transform it to match the new schema, and load it into the HMS tables.
- [ ] **Data Cleaning:** Clean and de-duplicate data during the migration process.
- [ ] **Data Validation:** After migration, thoroughly check a sample of records to ensure data integrity (e.g., patient names, appointment dates, bill amounts).
- [ ] **Backup:** Take a final, complete backup of the production system _before_ importing the migrated data.
- [ ] **Import:** Import the migrated data into the live HMS database.
- [ ] **Final Verification:** Spot-check several patients, their appointments, and bills in the live system to confirm the migration was successful.

### 9.2. User Training <a name="user-training"></a>

A successful go-live depends on users being comfortable with the new system.

- [ ] **Role-Based Training Sessions:** Conduct separate training sessions for different user groups (Admins, Doctors, Nurses, Pharmacists, Receptionists).
- [ ] **Training Materials:** Provide user manuals (the `docs/user-manual.md` file), quick reference guides, and video tutorials if possible.
- [ ] **Hands-On Practice:** Allow users to practice their workflows in a controlled training environment (or the staging site) before go-live.
- [ ] **Q&A Session:** Dedicate time for a question-and-answer session to address any concerns.

### 9.3. Go-Live Procedures <a name="go-live-procedures"></a>

1.  **Choose Go-Live Time:** Select a low-traffic period (e.g., a weekend or late at night) to minimize disruption.
2.  **Final Backup:** Take one last complete backup of the live website.
3.  **Communicate Downtime:** Inform all staff and, if necessary, patients about the scheduled downtime.
4.  **Deploy Final Code:** If there are any last-minute code changes, deploy them now.
5.  **Put Site in Maintenance Mode (Optional but Recommended):** Use a maintenance mode plugin to display a "We are upgrading" message to users.
6.  **Perform Data Migration (if applicable):** If migrating from an old system, perform the final data import now.
7.  **Final Configuration Checks:** Do a quick run-through of the [Plugin Configuration](#plugin-configuration) and [Third-Party Integration](#third-party-integration-setup) steps.
8.  **Disable Maintenance Mode:** Take the site out of maintenance mode.
9.  **Announce Go-Live:** Communicate to all staff that the new HMS system is now live.

### 9.4. Post-Launch Monitoring <a name="post-launch-monitoring"></a>

The work isn't over after go-live. Close monitoring is essential.

- [ ] **System Health:** Monitor server resources (CPU, memory, disk space, database load) closely for the first few days. Use server monitoring tools or your hosting provider's dashboard.
- [ ] **Error Logs:** Regularly check the WordPress debug log (`/wp-content/debug.log`) and server error logs for any PHP errors, warnings, or notices.
- [ ] **User Feedback:** Establish a channel (e.g., email, helpdesk system) for users to report issues, ask questions, and provide feedback. Respond to issues promptly.
- [ ] **Transaction Monitoring:** Keep an eye on key transactions: are patients registering? Are appointments being booked? Are payments being processed successfully?
- [ ] **Performance Monitoring:** Continue to monitor page load times and overall system responsiveness.
- [ ] **Backup Verification:** Verify that your automated backups are running successfully and can be restored.

---

Congratulations! By following this guide, you have successfully deployed the Hospital Management System. Be prepared to provide ongoing support and make iterative improvements based on user feedback and system performance.
