# Hospital Management System (HMS) WordPress Plugin

A comprehensive hospital management system for WordPress that integrates patient management, billing, clinical workflows, pharmacy operations, and reporting.

## Features

### 1. Patient Management

- Patient registration with unique Patient ID generation
- Digital health records (medical history, allergies, previous visits)
- Appointment scheduling with doctors
- Queue management (token system for OPD)
- SMS/Email/WhatsApp reminders for appointments

### 2. Billing & Finance

- OPD/IPD/Emergency billing
- Automatic billing integration with all services
- Insurance/TPA integration with pre-authorization workflow
- Multi-mode payment processing (Cash, UPI, Cards, Net Banking)
- Financial reports (daily collection, outstanding dues, insurance claims)

### 3. Clinical Management

- Doctor's module with digital prescriptions and progress notes
- Nursing module for vitals recording and medication administration
- Lab/Radiology module for test ordering and result entry
- Operation Theatre module for surgery scheduling and consumables tracking

### 4. Pharmacy & Inventory

- Medicine dispensing for IPD & OPD patients
- Stock management with low-stock alerts
- Purchase order creation and vendor management

### 5. Administration & HR

- Staff management (Doctors, Nurses, Technicians, Admin)
- Attendance tracking and duty rosters
- Payroll integration framework
- Role-based access control

### 6. Insurance & Corporate Tie-ups

- Corporate patient management
- Cashless claim processing
- TPA/Insurance portal integration
- Claim tracking and settlement reports

### 7. Patient Engagement

- Patient portal with login authentication
- Online appointment booking
- Bill viewing and payment
- Access to prescriptions, test results, and medical records
- Telemedicine framework (ready for integration)

### 8. Reports & Analytics

- Patient census reports (daily/weekly/monthly)
- Revenue reports by department/doctor
- Insurance pending reports
- Pharmacy sales reports
- Disease/diagnosis statistics

### 9. Security & Compliance

- Role-based access control
- Comprehensive audit logs for billing and records
- Data backup and disaster recovery
- Compliance framework for HIPAA/GDPR/DISHA

## Installation

### Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher

### Steps

1.  Download the plugin ZIP file
2.  Go to your WordPress admin dashboard
3.  Navigate to **Plugins → Add New**
4.  Click **Upload Plugin** and select the downloaded ZIP file
5.  Install and activate the plugin
6.  The plugin will automatically create the necessary database tables and set up default settings

## Deployment Steps

### 1. Server Requirements

- Ensure your server meets the minimum requirements
- Recommended: PHP 7.4+, MySQL 8.0+, SSL certificate
- Set proper file permissions (755 for directories, 644 for files)

### 2. Database Configuration

- Create a dedicated database for WordPress if not already done
- Ensure database user has appropriate privileges
- Consider enabling database caching for better performance

### 3. WordPress Configuration

- Install WordPress (latest version recommended)
- Configure WordPress settings:
  - Set permalink structure to "Post name"
  - Configure time zone in Settings → General
  - Set up email sending (SMTP recommended)

### 4. Plugin Configuration

1.  After activating the HMS plugin:

    - Go to **Hospital Management → Settings**
    - Configure hospital details (name, address, contact info)
    - Set up currency and time zone
    - Configure notification settings (SMS, Email, WhatsApp)

2.  Set up user roles:

    - Create admin accounts with appropriate roles
    - Assign roles to existing staff members
    - Configure role-based access permissions

3.  Configure departments and staff:

    - Add hospital departments
    - Register staff members with their details
    - Set up work schedules and duty rosters

4.  Configure services and pricing:

    - Add consultation fees
    - Set up test and procedure pricing
    - Configure medicine inventory

### 5. Third-Party Integrations

1.  SMS Gateway:

    - Configure SMS gateway API credentials
    - Test SMS notifications

2.  Payment Gateway:

    - Set up payment gateway (Razorpay, PayU, Stripe, etc.)
    - Configure payment methods
    - Test payment processing

3.  Insurance Integration:

    - Add insurance providers
    - Configure TPA integration
    - Set up coverage rules

4.  Email Configuration:

    - Set up SMTP for reliable email delivery
    - Configure email templates

### 6. Security Hardening

1.  Implement SSL certificate (HTTPS)
2.  Set up regular backups:
    - Use a backup plugin (UpdraftPlus, BackupBuddy)
    - Schedule daily backups
    - Store backups off-site
3.  Implement security measures:
    - Install a security plugin (Wordfence, Sucuri)
    - Enable two-factor authentication for admins
    - Restrict login attempts
    - Implement IP whitelisting for admin access

### 7. Performance Optimization

1.  Implement caching:
    - Install a caching plugin (WP Rocket, W3 Total Cache)
    - Configure object caching (Redis or Memcached)
2.  Optimize images:
    - Use an image optimization plugin
    - Implement lazy loading
3.  Use a Content Delivery Network (CDN)

### 8. Testing

1.  Test all features:
    - Patient registration
    - Appointment booking
    - Billing workflows
    - Pharmacy operations
    - Report generation
2.  Test user roles and permissions
3.  Test payment processing
4.  Test notification systems
5.  Test backup and restore procedures

### 9. Go-Live Checklist

1.  Final data migration (if applicable)
2.  Train staff members
3.  Create user documentation
4.  Set up monitoring and alerts
5.  Schedule regular maintenance windows
6.  Implement disaster recovery plan

## Configuration

### Basic Settings

Navigate to **Hospital Management → Settings** to configure:

- Hospital information
- Currency and time zone
- Notification preferences
- Appointment settings

### User Management

1.  Go to **Hospital Management → Staff**
2.  Add staff members with appropriate roles
3.  Configure access permissions

### Department Setup

1.  Go to **Hospital Management → Departments**
2.  Add hospital departments
3.  Assign doctors to departments

### Service Configuration

1.  Go to **Hospital Management → Services**
2.  Set up consultation fees
3.  Configure test and procedure pricing

### Pharmacy Setup

1.  Go to **Hospital Management → Pharmacy**
2.  Add medicines to inventory
3.  Set up reorder levels
4.  Configure suppliers

## Usage

### For Administrators

1.  **Dashboard**: Overview of hospital operations
2.  **Patient Management**: Register patients, view records
3.  **Appointments**: Schedule and manage appointments
4.  **Billing**: Generate and manage bills
5.  **Pharmacy**: Manage inventory and dispensing
6.  **Reports**: Generate various reports
7.  **Settings**: Configure system settings

### For Doctors

1.  View patient appointments
2.  Record consultations and prescriptions
3.  Order tests and procedures
4.  View patient medical history

### For Nurses

1.  Record patient vitals
2.  Administer medications
3.  Update patient charts

### For Pharmacists

1.  Manage medicine inventory
2.  Dispense prescriptions
3.  Generate purchase orders

### For Billing Staff

1.  Generate patient bills
2.  Process payments
3.  Manage insurance claims

### For Patients

1.  Access patient portal
2.  Book appointments online
3.  View medical records
4.  Pay bills online

## Screenshots

_(Note: Actual screenshots would be included here in a real README)_

## Support

For support and documentation:

- Visit our support forum: [Plugin Support Forum]
- Documentation: [Plugin Documentation]
- Report bugs: [Bug Tracker]
- Feature requests: [Feature Requests]

## License

This plugin is licensed under the GPLv2 or later.

## Changelog

### 1.0.0

- Initial release
- Complete HMS functionality implementation
- Patient management system
- Integrated billing workflow
- Clinical management modules
- Pharmacy and inventory system
- Reporting and analytics
- Security and compliance features
