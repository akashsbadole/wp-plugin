# Hospital Management System (HMS) - User Manual

## Table of Contents

1.  [Introduction](#introduction)
2.  [Getting Started](#getting-started)
    - [System Requirements](#system-requirements)
    - [Installation & Activation](#installation--activation)
    - [Initial Configuration](#initial-configuration)
3.  [Administrator Guide](#administrator-guide)
    - [Dashboard Overview](#dashboard-overview)
    - [Managing Users & Roles](#managing-users--roles)
    - [Hospital Settings](#hospital-settings)
    - [Custom Fields Management](#custom-fields-management)
    - [White-Label Branding](#white-label-branding)
    - [License Management](#license-management)
    - [Generating Reports](#generating-reports)
4.  [Doctor Guide](#doctor-guide)
    - [Viewing Your Dashboard](#viewing-your-dashboard)
    - [Managing Appointments](#managing-appointments)
    - [Patient Records & History](#patient-records--history)
    - [Creating Prescriptions](#creating-prescriptions)
5.  [Nurse Guide](#nurse-guide)
    - [Patient Registration](#patient-registration)
    - [Recording Patient Vitals](#recording-patient-vitals)
    - [Managing Patient Data](#managing-patient-data)
    - [Logging Medication Administration](#logging-medication-administration)
6.  [Pharmacist Guide](#pharmacist-guide)
    - [Managing Pharmacy Inventory](#managing-pharmacy-inventory)
    - [Dispensing Prescriptions](#dispensing-prescriptions)
    - [Generating Pharmacy Reports](#generating-pharmacy-reports)
7.  [Patient Portal Guide](#patient-portal-guide)
    - [Registration & Login](#registration--login)
    - [Booking an Appointment](#booking-an-appointment)
    - [Viewing Your Medical Records](#viewing-your-medical-records)
    - [Managing Your Profile](#managing-your-profile)
    - [Viewing and Paying Bills](#viewing-and-paying-bills)
    - [Accessing Telemedicine](#accessing-telemedicine)
8.  [Frequently Asked Questions (FAQ)](#frequently-asked-questions-faq)
9.  [Troubleshooting & Support](#troubleshooting--support)

---

## 1. Introduction <a name="introduction"></a>

Welcome to the Hospital Management System (HMS), a comprehensive WordPress plugin designed to streamline and manage all aspects of hospital operations. From patient registration and appointment scheduling to billing, clinical records, and reporting, HMS provides an integrated solution for administrators, doctors, nurses, pharmacists, and patients.

This manual will guide you through the features and functionalities of the HMS, helping you to effectively utilize the system based on your role.

---

## 2. Getting Started <a name="getting-started"></a>

### 2.1. System Requirements <a name="system-requirements"></a>

Before installing HMS, ensure your server meets the following minimum requirements:

- **WordPress:** Version 5.0 or higher.
- **PHP:** Version 7.4 or higher.
- **MySQL:** Version 5.6 or higher (or MariaDB 10.1 or higher).
- **Web Server:** Apache or Nginx.
- **Memory:** PHP `memory_limit` set to at least 128M.
- **WordPress Memory:** `WP_MEMORY_LIMIT` set to at least 64M (defined in `wp-config.php`).

### 2.2. Installation & Activation <a name="installation--activation"></a>

1.  **Download the Plugin:** Obtain the `hospital-management-system.zip` file.
2.  **Upload to WordPress:**
    - Log in to your WordPress Admin Dashboard.
    - Navigate to **Plugins -> Add New**.
    - Click **Upload Plugin**.
    - Choose the `hospital-management-system.zip` file and click **Install Now**.
3.  **Activate the Plugin:**
    - After installation, click **Activate Plugin**.
    - Upon activation, HMS will automatically create the necessary database tables. You should see a new "HMS" menu item in your WordPress admin sidebar.

### 2.3. Initial Configuration <a name="initial-configuration"></a>

Before other users can start using the system, an administrator should perform the initial setup:

1.  Navigate to **HMS -> Settings**.
2.  **Hospital Details:**
    - Enter your hospital's name, address, contact information, and upload a logo.
3.  **General Settings:**
    - Set the default currency and timezone.
4.  **User Roles & Permissions:**
    - Verify that user roles (Doctor, Nurse, Pharmacist, etc.) have been created with the correct capabilities. You may need a role management plugin like "User Role Editor" for fine-grained control.
5.  **License Management (if applicable):**
    - If you are using a premium version, navigate to **HMS -> License Management** to enter and activate your license key.

---

## 3. Administrator Guide <a name="administrator-guide"></a>

As an Administrator, you have full control over the HMS. You manage settings, users, and oversee the entire system's operation.

### 3.1. Dashboard Overview <a name="dashboard-overview"></a>

Navigate to **HMS -> Dashboard** to get an overview of your hospital's key metrics:

- **Today's Appointments:** Number of appointments scheduled for the day.
- **New Patients:** Number of patients registered today.
- **Revenue Summary:** Today's collected revenue.
- **Recent Activities:** A log of recent actions taken within the system (e.g., new patient registered, appointment booked).
- **Quick Links:** Shortcuts to common tasks like adding a new patient or creating an appointment.

### 3.2. Managing Users & Roles <a name="managing-users--roles"></a>

1.  Navigate to **Users -> All Users**.
2.  **Add New User:**
    - Click **Add New**.
    - Fill in the username, email, and other details.
    - Assign the appropriate role (e.g., Doctor, Nurse, Pharmacist) from the "Role" dropdown.
    - Click **Add New User**.
3.  **Edit User Roles:**
    - You can use a plugin like "User Role Editor" to modify the capabilities of each role to suit your hospital's specific workflow.

### 3.3. Hospital Settings <a name="hospital-settings"></a>

Navigate to **HMS -> Settings** to configure global system options:

- **Hospital Details:** Name, address, logo.
- **General:** Currency, time format, date format.
- **Notifications:** Configure SMTP settings for reliable email delivery, and add API keys for SMS/WhatsApp gateways if used.
- **Integrations:** Add API keys for payment gateways (e.g., Razorpay, Stripe) if processing online payments.
- **Save Changes** after making any modifications.

### 3.4. Custom Fields Management <a name="custom-fields-management"></a>

HMS allows you to create custom fields for forms like patient registration to capture specific information.

1.  Navigate to **HMS -> Custom Fields**.
2.  **Add New Field Group:**
    - Click **Add New Field Group**.
    - Enter a title for the group (e.g., "Patient Registration Fields").
    - Select the context where this group should appear (e.g., "patient_registration").
3.  **Add Fields:**
    - Within the group, click **Add Custom Field**.
    - **Label:** The text displayed to the user (e.g., "Emergency Contact").
    - **Name:** A unique machine-readable name (e.g., `emergency_contact`).
    - **Type:** Choose the field type (Text, Textarea, Select, Checkbox, etc.).
    - **Required:** Check if this field must be filled out.
    - **Placeholder:** Add helper text for the input field.
    - Click **Save Field**.
4.  The custom fields will now appear on the selected forms.

### 3.5. White-Label Branding <a name="white-label-branding"></a>

You can customize the look and feel of the HMS admin area to match your hospital's branding.

1.  Navigate to **HMS -> White-Label Settings**.
2.  **Branding Options:**
    - **Hospital Name:** Overrides the default WordPress site name in the HMS context.
    - **Logo URL:** Enter the URL to your hospital's logo.
    - **Primary Color:** Choose a primary color for the HMS admin theme.
    - **Admin Footer Text:** Customize the text in the admin footer.
3.  Click **Save Branding Settings**. You may need to hard-refresh your browser (Ctrl+F5 or Cmd+Shift+R) to see the changes.

### 3.6. License Management <a name="license-management"></a>

If you are using a premium version of HMS, you need to activate your license to unlock premium features.

1.  Navigate to **HMS -> License Management**.
2.  **Activate License:**
    - Enter your license key in the provided field.
    - Click **Activate License**.
    - The system will connect to the license server to validate your key.
3.  **Status:** The page will display the status of your license (Active, Inactive, Expired).
4.  Premium features will be unlocked only with an active license.

### 3.7. Generating Reports <a name="generating-reports"></a>

HMS provides a powerful reporting module to analyze various aspects of your hospital.

1.  Navigate to **HMS -> Reports**.
2.  **Select Report Type:**
    - Choose the report you want to generate from the dropdown (e.g., "Patient Census", "Revenue Report", "Pharmacy Sales").
3.  **Set Filters:**
    - Select a date range (e.g., Last 7 Days, Last Month, or a custom range).
    - Some reports may have additional filters (e.g., Department, Doctor).
4.  **Generate Report:**
    - Click the **Generate Report** button.
    - The report data will be displayed in a table or chart format below the form.
5.  **Export Report:**
    - You can export the report data to CSV or Excel format using the export buttons provided.

---

## 4. Doctor Guide <a name="doctor-guide"></a>

As a Doctor, your primary interactions with HMS will involve managing your schedule, viewing patient information, and creating prescriptions.

### 4.1. Viewing Your Dashboard <a name="viewing-your-dashboard"></a>

Navigate to **HMS -> Dashboard**. Your view is tailored to show:

- **Today's Appointments:** A list of your appointments for the day, with time and patient name.
- **My Patients:** A quick link to the list of all patients assigned to you.
- **Pending Prescriptions:** Any prescriptions that need your attention.

### 4.2. Managing Appointments <a name="managing-appointments"></a>

1.  **View Appointments:** Navigate to **HMS -> Appointments** to see a list of all your appointments. You can filter by date (e.g., Today, Upcoming, Past).
2.  **Schedule New Appointment:**
    - Navigate to **HMS -> Appointments -> Add New**.
    - Select the patient from the dropdown or search for them.
    - Choose the date and time for the appointment.
    - Select the department (if applicable) and the reason for the visit.
    - Click **Save Appointment**.
3.  **Edit/Update Appointment Status:**
    - From the appointments list, click on an appointment to edit it.
    - You can change the date, time, or update the status (e.g., from "Scheduled" to "In Progress" or "Completed").

### 4.3. Patient Records & History <a name="patient-records--history"></a>

1.  **View Patient List:** Navigate to **HMS -> Patients** to see a list of all patients.
2.  **View Patient Details:** Click on a patient's name to view their comprehensive profile, which includes:
    - **Personal Information:** Demographics, contact details.
    - **Medical History:** Past diagnoses, allergies, surgeries.
    - **Vitals:** Historical vital signs recorded by nurses.
    - **Appointments:** All past and upcoming appointments.
    - **Bills:** Billing history.
    - **Prescriptions:** Current and past medications.
    - **Clinical Notes:** Notes from previous consultations.
3.  **Add Clinical Notes:** On the patient details page, find the section for clinical notes and add an entry to document the current consultation.

### 4.4. Creating Prescriptions <a name="creating-prescriptions"></a>

1.  **From Patient Details:** While viewing a patient's profile, find the "Prescriptions" section and click **Add New Prescription**.
2.  **Fill Prescription Details:**
    - **Medicine:** Search and select the medicine from the pharmacy inventory.
    - **Dosage:** Enter the dosage (e.g., "500mg").
    - **Frequency:** Specify how often it should be taken (e.g., "Twice a day").
    - **Duration:** Enter the duration of the course (e.g., "7 days").
    - **Quantity:** The total quantity to dispense (e.g., "14 tablets").
    - **Instructions:** Add any special instructions for the patient.
3.  **Save Prescription:** Click **Save Prescription**. The prescription will be added to the patient's record and will be visible to the pharmacist for dispensing.

---

## 5. Nurse Guide <a name="nurse-guide"></a>

As a Nurse, you play a crucial role in patient data management, vitals recording, and assisting doctors.

### 5.1. Patient Registration <a name="patient-registration"></a>

1.  Navigate to **HMS -> Patients -> Add New**.
2.  **Fill Patient Information:**
    - **Personal Details:** First name, last name, date of birth, gender, contact information (phone, email), address.
    - **Emergency Contact:** Name and phone number of an emergency contact.
    - **Insurance Details:** Insurance provider, policy number (if applicable).
    - **Custom Fields:** Fill in any additional fields that have been configured by the administrator (e.g., medical history, allergies).
3.  **Save Patient:** Click **Save Patient**. The patient is now registered in the system.

### 5.2. Recording Patient Vitals <a name="recording-patient-vitals"></a>

1.  Navigate to **HMS -> Patients** and select the patient whose vitals you need to record.
2.  On the patient's details page, find the "Vitals" section.
3.  **Add New Vitals Entry:**
    - Click **Add Vitals** or a similar button.
    - Enter the current readings:
      - Temperature (°C or °F)
      - Blood Pressure (Systolic/Diastolic)
      - Pulse (beats per minute)
      - Respiratory Rate (breaths per minute)
      - Oxygen Saturation (%)
      - Weight (kg or lbs)
      - Height (cm or ft/in)
    - Add any relevant notes.
4.  **Save Vitals:** Click **Save**. The entry will be timestamped and added to the patient's vitals history.

### 5.3. Managing Patient Data <a name="managing-patient-data"></a>

- **Edit Patient Information:** From the patient list, click "Edit" below a patient's name to update their details.
- **Search for a Patient:** Use the search bar on the **HMS -> Patients** page to quickly find a patient by name or ID.

### 5.4. Logging Medication Administration <a name="logging-medication-administration"></a>

When you administer medication to a patient as per a doctor's prescription, you should log it.

1.  Go to the patient's details page and navigate to the "Prescriptions" section.
2.  Find the active prescription that was administered.
3.  There should be an option like **Log Administration** or **Mark as Given**.
4.  **Log the Details:**
    - Enter the time and date of administration.
    - You may be able to record the batch number of the medicine if required.
    - Add any notes (e.g., "Patient took medication without issues").
5.  **Save Log:** This updates the prescription's status and creates an audit trail.

---

## 6. Pharmacist Guide <a name="pharmacist-guide"></a>

As a Pharmacist, you are responsible for managing the pharmacy inventory and dispensing medications accurately.

### 6.1. Managing Pharmacy Inventory <a name="managing-pharmacy-inventory"></a>

1.  **View Inventory:** Navigate to **HMS -> Pharmacy -> Medicines** to see a list of all medicines in stock.
2.  **Add New Medicine:**
    - Navigate to **HMS -> Pharmacy -> Add New Medicine**.
    - **Medicine Details:** Name, description, manufacturer.
    - **SKU/Barcode:** Unique stock keeping unit.
    - **Initial Quantity:** The current stock quantity.
    - **Unit:** (e.g., tablets, ml, vials).
    - **Expiry Date:** The expiry date of the batch.
    - **Batch Number:** The batch number of the medicine.
    - **Price:** The selling price per unit.
    - Click **Save Medicine**.
3.  **Update Stock:**
    - From the medicines list, you can edit a medicine to update its quantity (e.g., when new stock arrives or when stock is dispensed). This is often done automatically when dispensing prescriptions.

### 6.2. Dispensing Prescriptions <a name="dispensing-prescriptions"></a>

1.  **View Pending Prescriptions:** Navigate to **HMS -> Pharmacy -> Prescriptions**. This page shows a list of prescriptions that need to be dispensed.
2.  **Select a Prescription:** Click on a prescription to view its details (patient, medicine, dosage, quantity).
3.  **Dispense:**
    - Verify the prescription details.
    - Check the available stock for the medicine.
    - If everything is correct, find the **Dispense** button and click it.
    - The system will:
      - Update the prescription status to "Dispensed".
      - Automatically reduce the stock quantity of the medicine in the inventory.
      - Generate a bill for the medication (if not already part of a larger bill).
4.  **Print Label (Optional):** You may have an option to print a label for the dispensed medication with instructions for the patient.

### 6.3. Generating Pharmacy Reports <a name="generating-pharmacy-reports"></a>

1.  Navigate to **HMS -> Reports**.
2.  **Select "Pharmacy Sales Report"** from the report type dropdown.
3.  Set the desired date range.
4.  Click **Generate Report**. This will show a summary of medicines dispensed and total revenue for the pharmacy during the selected period.

---

## 7. Patient Portal Guide <a name="patient-portal-guide"></a>

The Patient Portal allows patients to interact with the hospital online for tasks like booking appointments and viewing their records.

### 7.1. Registration & Login <a name="registration--login"></a>

1.  **Accessing the Portal:** Your hospital will provide a link to the Patient Portal, which is typically a page on your hospital's website with the `[hms_patient_portal]` shortcode.
2.  **Registration:**
    - On the portal login page, click the "Register" link.
    - Fill in your details (Name, Date of Birth, Email, Phone Number, Address).
    - Create a username and password.
    - Submit the form. Your account will be created, and you may need to verify your email address.
3.  **Login:**
    - Return to the portal login page.
    - Enter your username and password.
    - Click "Log In" to access your dashboard.

### 7.2. Booking an Appointment <a name="booking-an-appointment"></a>

1.  From your patient dashboard, find and click the "Book Appointment" button.
2.  **Select Department:** Choose the relevant department (e.g., Cardiology, General Medicine).
3.  **Select Doctor:** Choose a doctor from the list of available doctors in that department.
4.  **Select Date & Time:** A calendar will show available slots. Click on your preferred date and time.
5.  **Provide Reason:** Briefly describe the reason for the appointment.
6.  **Confirm Booking:** Review your appointment details and click "Confirm Booking". A confirmation message will be displayed, and you may receive an email/SMS notification.

### 7.3. Viewing Your Medical Records <a name="viewing-your-medical-records"></a>

1.  From your dashboard, navigate to the "My Medical Records" section.
2.  You will find tabs or links to different types of records:
    - **Appointments:** View your upcoming and past appointment details.
    - **Prescriptions:** See a list of your current and past prescriptions, including medicine names and dosages.
    - **Test Results:** View reports of any lab tests or imaging you have undergone.
    - **Clinical Summaries:** Read summaries of your consultations with doctors.
3.  Click on any record to view more detailed information.

### 7.4. Managing Your Profile <a name="managing-your-profile"></a>

1.  From your dashboard, go to "My Profile".
2.  You can update your personal information (contact details, address) and change your password.
3.  Click "Save Changes" to update your profile.

### 7.5. Viewing and Paying Bills <a name="viewing-and-paying-bills"></a>

1.  Navigate to the "My Bills" section in your portal.
2.  You will see a list of your outstanding and paid bills.
3.  **View Bill Details:** Click on a bill to see an itemized list of charges (consultation fees, medicines, tests, etc.).
4.  **Pay Bill:**
    - For outstanding bills, there will be a "Pay Now" button.
    - Click it, and you will be redirected to a secure payment gateway.
    - Follow the instructions to complete the payment using your preferred method (Card, UPI, Net Banking).
    - After successful payment, the bill status will update to "Paid".

### 7.6. Accessing Telemedicine <a name="accessing-telemedicine"></a>

If you have a telemedicine appointment scheduled:

1.  Go to "My Appointments" in your portal.
2.  Find the upcoming telemedicine appointment.
3.  A few minutes before the scheduled time, a "Join Session" button will become active.
4.  Click "Join Session". This will open the video conferencing interface (e.g., Zoom, Jitsi Meet) in a new tab or window, allowing you to connect with your doctor remotely.

---

## 8. Frequently Asked Questions (FAQ) <a name="frequently-asked-questions-faq"></a>

**Q: I forgot my password. How can I reset it?**
**A:** On the Patient Portal login page, click the "Forgot Password?" link. Enter your registered email address, and you will receive instructions to reset your password. For admin/doctor/nurse/pharmacist users, use the WordPress "Lost your password?" link on the main login screen.

**Q: Can I book an appointment for someone else (e.g., a family member)?**
**A:** Currently, the Patient Portal allows you to book appointments only for yourself. For booking on behalf of someone else, please contact the hospital's reception.

**Q: How do I know if my payment was successful?**
**A:** After making a payment, you should see a confirmation message on the portal. You can also check the "My Bills" section; the status of the bill will change to "Paid" upon successful transaction. You will also receive a payment receipt via email.

**Q: I can't see my test results in the portal.**
**A:** Test results are typically uploaded by the hospital staff after they are processed. There might be a delay. If you don't see your results after a reasonable time, please contact the hospital's lab or records department.

**Q: Is my data secure in the HMS?**
**A:** Yes, the hospital takes data security very seriously. The system is designed to comply with data protection regulations (like HIPAA/GDPR). Your personal and medical information is encrypted and stored securely. Access to your data is restricted to authorized personnel only.

---

## 9. Troubleshooting & Support <a name="troubleshooting--support"></a>

If you encounter any issues while using the HMS, please try the following troubleshooting steps:

- **Clear Your Browser Cache and Cookies:** Sometimes, outdated files can cause display or functionality issues.
- **Try a Different Browser:** The issue might be specific to the browser you are using.
- **Check Your Internet Connection:** A stable internet connection is required for the portal and some admin functions.
- **Ensure JavaScript is Enabled:** HMS relies on JavaScript for many of its interactive features. Make sure it is enabled in your browser.

**For Administrators:**

- **Check Plugin Conflicts:** If an admin page is not working, try deactivating other plugins temporarily to see if there is a conflict.
- **Review Server Error Logs:** Your hosting provider's error logs can provide more details about critical errors.

**Contacting Support:**
If the issue persists, please contact your hospital's IT support team or the HMS plugin support team. When contacting support, please provide:

- Your role in the system (Admin, Doctor, etc.).
- A detailed description of the problem.
- Steps to reproduce the issue.
- Any error messages you received.
- Screenshots (if applicable).

For developer-related issues or extending the plugin, please refer to the **Developer Documentation**.
