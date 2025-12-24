# Hospital Management System (HMS) - User Acceptance Testing (UAT) Plan

## 1. Introduction

This document outlines the User Acceptance Testing (UAT) plan for the Hospital Management System (HMS) plugin. The purpose of UAT is to verify that the system meets the business requirements and is ready for deployment. This plan focuses on testing the system from the perspective of end-users performing their daily tasks.

### 1.1. Objectives

- To ensure the HMS plugin functions correctly and intuitively for all intended user roles.
- To validate that all major features and workflows work as expected in a near-production environment.
- To identify any usability issues, bugs, or discrepancies from the expected behavior.
- To gather feedback from actual users on the system's functionality and user experience.

### 1.2. Scope

- **In Scope:**
  - Core functionalities for all user roles: Administrator, Doctor, Nurse, Pharmacist, Patient (via portal).
  - Key workflows: Patient Registration, Appointment Scheduling, Billing, Prescription Management, Pharmacy Stock, Reporting.
  - Integration between different modules (e.g., Appointment to Billing, Prescription to Pharmacy).
  - Custom Fields & Forms usage.
  - White-Label Branding appearance.
  - Multi-Location Management (if applicable).
- **Out of Scope:**
  - Performance and load testing (covered under Performance Testing).
  - Security penetration testing (covered under Security Testing).
  - Third-party API integrations beyond their basic configured functionality (e.g., actual SMS/Payment gateway transactions unless using a sandbox).
  - Server and infrastructure setup.

### 1.3. Roles and Responsibilities

- **Test Lead:** Oversees the UAT process, creates test cases, and compiles the final report.
- **Testers (End-Users):** Individuals representing each user role (Admin, Doctor, etc.) who will execute the test cases.
- **Developer(s):** On standby to fix any critical bugs found during UAT.
- **Project Manager:** Facilitates communication and ensures UAT is completed on schedule.

## 2. User Roles and Test Environments

### 2.1. User Roles

1.  **Administrator:** Manages system-wide settings, user roles, and views global reports.
2.  **Doctor:** Manages patient appointments, views patient history, creates prescriptions.
3.  **Nurse:** Records patient vitals, manages patient data, assists doctors.
4.  **Pharmacist:** Manages pharmacy stock, dispenses prescriptions, views pharmacy reports.
5.  **Patient (via Portal):** Registers, books appointments, views bills and medical records, accesses telemedicine.

### 2.2. Test Environment

- A staging server that mirrors the production environment as closely as possible.
- The latest version of the HMS plugin is installed and configured.
- Sample data is populated (patients, staff, appointments, medicines, etc.).
- Access credentials are provided to each tester for their respective role.

## 3. Test Cases

The following test cases are organized by user role. Testers should execute the cases for their assigned role and report results (Pass/Fail/Block) with detailed comments for failures.

---

### 3.1. Administrator Test Cases

| ID     | Test Case Description          | Test Steps                                                                                                                                                                                                                                                 | Expected Result                                                                                                                                                                   | Actual Result | Status | Comments |
| ------ | ------------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------- | ------ | -------- |
| ADM-01 | Login to Admin Dashboard       | 1. Navigate to the WordPress login page.<br>2. Enter valid admin credentials.<br>3. Click "Log In".                                                                                                                                                        | The user is successfully logged in and redirected to the WordPress Admin Dashboard. The main "HMS" menu is visible in the sidebar.                                                |               |        |          |
| ADM-02 | Navigate and Verify HMS Menu   | 1. Click on the main "HMS" menu item in the sidebar.<br>2. Verify all submenu items are present (Dashboard, Patients, Appointments, etc.).<br>3. Click on "White-Label Settings".                                                                          | The HMS submenu expands, showing all expected items. The "White-Label Settings" page loads without errors.                                                                        |               |        |          |
| ADM-03 | Configure White-Label Branding | 1. Go to HMS -> White-Label Settings.<br>2. Change the "Hospital Name", "Primary Color", and upload a test logo.<br>3. Click "Save Branding Settings".<br>4. Navigate back to the admin dashboard and a front-end page (if available).                     | A success message is displayed. The admin dashboard header/footer (if applicable) reflects the new hospital name and logo. The primary color styling is applied.                  |               |        |          |
| ADM-04 | Manage User Roles              | 1. Go to Users -> All Users.<br>2. Edit a user assigned to a custom role (e.g., Doctor).<br>3. Verify the role-specific capabilities are correctly assigned (this may require checking the database or a capabilities management plugin if one is used).   | The user's role is displayed correctly. The capabilities associated with that role are active.                                                                                    |               |        |          |
| ADM-05 | View Global Reports            | 1. Go to HMS -> Reports.<br>2. Select a report type (e.g., "Patient Census").<br>3. Enter a date range.<br>4. Click "Generate Report".                                                                                                                     | The report page displays data in a table or chart format. The data is relevant to the selected report type and date range. Export buttons (CSV/Excel) are present and functional. |               |        |          |
| ADM-06 | Manage Custom Fields           | 1. Go to HMS -> Custom Fields.<br>2. Create a new custom field for "Patient Registration".<br>3. Fill in all required fields (Label, Name, Type).<br>4. Click "Add Custom Field".<br>5. Verify the new field appears in the "Existing Custom Fields" list. | A success message is shown. The new custom field is listed correctly with the details provided.                                                                                   |               |        |          |

---

### 3.2. Doctor Test Cases

| ID     | Test Case Description     | Test Steps                                                                                                                                                                                                                                                | Expected Result                                                                                                                                        | Actual Result | Status | Comments |
| ------ | ------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------ | ------------- | ------ | -------- |
| DOC-01 | View Dashboard            | 1. Login as a Doctor.<br>2. Navigate to the HMS Dashboard.                                                                                                                                                                                                | The dashboard loads, displaying relevant widgets (e.g., "Today's Appointments", "My Patients").                                                        |               |        |          |
| DOC-02 | View Patient List         | 1. Go to HMS -> Patients.<br>2. Verify the list of patients is displayed.                                                                                                                                                                                 | A list of all patients is shown, with relevant columns (Name, ID, Last Visit, etc.).                                                                   |               |        |          |
| DOC-03 | View Patient Details      | 1. From the patient list, click on a patient's name to view their details.<br>2. Verify all patient information, medical history, and custom fields are displayed correctly.                                                                              | The patient details page loads, showing comprehensive information including demographics, medical records, and any custom fields defined for patients. |               |        |          |
| DOC-04 | Schedule New Appointment  | 1. Go to HMS -> Appointments.<br>2. Click "Add New Appointment".<br>3. Select a patient, date, time, and reason.<br>4. Save the appointment.                                                                                                              | The appointment is successfully created. A confirmation message is shown. The new appointment appears in the appointment list.                         |               |        |          |
| DOC-05 | Create Prescription       | 1. View a patient's details page.<br>2. Find the section for prescriptions (e.g., "Prescriptions" tab or a button to add one).<br>3. Click "Add New Prescription".<br>4. Select medicine(s), dosage, duration, and quantity.<br>5. Save the prescription. | The prescription is created and saved. It appears in the patient's prescription history. The prescription status is "Active" or similar.               |               |        |          |
| DOC-06 | View Today's Appointments | 1. From the Doctor Dashboard or HMS -> Appointments, filter for today's date.                                                                                                                                                                             | A list of all appointments scheduled for the current day for this doctor is displayed.                                                                 |               |        |          |
| DOC-07 | Edit Appointment Status   | 1. From the appointment list, select an existing appointment.<br>2. Change its status (e.g., from "Scheduled" to "Completed" or "No Show").<br>3. Save changes.                                                                                           | The appointment status is updated successfully. The change is reflected in the appointment list.                                                       |               |        |          |

---

### 3.3. Nurse Test Cases

| ID     | Test Case Description                  | Test Steps                                                                                                                                                                                                                            | Expected Result                                                                                                                 | Actual Result | Status | Comments |
| ------ | -------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------- | ------------- | ------ | -------- |
| NUR-01 | Register New Patient                   | 1. Go to HMS -> Patients -> Add New.<br>2. Fill in all mandatory patient details (Name, DOB, Contact).<br>3. Fill in any visible custom fields.<br>4. Save the patient.                                                               | The patient is successfully registered. A success message is displayed. The new patient appears in the patient list.            |               |        |          |
| NUR-02 | Record Patient Vitals                  | 1. View an existing patient's details page.<br>2. Find the "Vitals" section or a form to add new vitals.<br>3. Enter new vitals (Temperature, BP, Pulse, etc.).<br>4. Save the vitals entry.                                          | The vitals are recorded and saved. The new entry is added to the patient's vitals history, visible on their details page.       |               |        |          |
| NUR-03 | Edit Patient Information               | 1. From the patient list, select a patient to edit.<br>2. Modify some patient details (e.g., phone number, address).<br>3. Save changes.                                                                                              | The patient information is updated successfully. The changes are reflected on the patient details page and in the patient list. |               |        |          |
| NUR-04 | View and Log Medication Administration | 1. View a patient's details page and access their active prescriptions.<br>2. For a prescribed medication, find an option to log administration (e.g., "Mark as Given").<br>3. Log the administration time and any notes.<br>4. Save. | The medication administration is logged. The prescription's status or a log shows that the dose was administered.               |               |        |          |
| NUR-05 | Search for a Patient                   | 1. Go to the HMS -> Patients page.<br>2. Use the search bar to search for a patient by name or ID.                                                                                                                                    | The search results display the correct patient(s) matching the search query.                                                    |               |        |          |

---

### 3.4. Pharmacist Test Cases

| ID     | Test Case Description                  | Test Steps                                                                                                                                                               | Expected Result                                                                                                                                                                             | Actual Result | Status | Comments |
| ------ | -------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------- | ------ | -------- |
| PHA-01 | View Pharmacy Dashboard/Stock Overview | 1. Login as a Pharmacist.<br>2. Navigate to the HMS Pharmacy section or Dashboard.                                                                                       | The page loads, showing an overview of pharmacy stock, low-stock alerts, or pending prescriptions.                                                                                          |               |        |          |
| PHA-02 | View Medicine Inventory                | 1. Go to HMS -> Pharmacy -> Medicines (or similar).<br>2. Verify the list of all medicines in stock is displayed.                                                        | A list of medicines is shown with columns for Name, SKU, Quantity in Stock, etc.                                                                                                            |               |        |          |
| PHA-03 | Add New Medicine to Stock              | 1. Go to HMS -> Pharmacy -> Add New Medicine (or Purchase Orders).<br>2. Enter details for a new medicine (Name, SKU, Initial Quantity, Manufacturer, etc.).<br>3. Save. | The new medicine is added to the inventory. The stock quantity is correctly set. The medicine appears in the medicines list.                                                                |               |        |          |
| PHA-04 | View Pending Prescriptions             | 1. Go to HMS -> Pharmacy -> Prescriptions (or a queue).<br>2. Verify a list of prescriptions awaiting dispensing is shown.                                               | A list of "Active" or "Pending" prescriptions is displayed, including patient name and medication details.                                                                                  |               |        |          |
| PHA-05 | Dispense a Prescription                | 1. From the pending prescriptions list, select a prescription to dispense.<br>2. Verify the medication and quantity.<br>3. Click "Dispense" or similar action.           | The prescription status changes to "Dispensed". The corresponding medicine stock in the inventory is reduced by the dispensed quantity. The prescription is moved out of the pending queue. |               |        |          |
| PHA-06 | Generate Pharmacy Sales Report         | 1. Go to HMS -> Reports.<br>2. Select "Pharmacy Sales Report".<br>3. Enter a date range.<br>4. Generate the report.                                                      | The report displays a list of medicines dispensed and total sales/revenue for the selected period.                                                                                          |               |        |          |

---

### 3.5. Patient (via Portal) Test Cases

| ID     | Test Case Description               | Test Steps                                                                                                                                                                                                                                   | Expected Result                                                                                                                                          | Actual Result | Status | Comments |
| ------ | ----------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------- | ------ | -------- |
| PAT-01 | Patient Registration                | 1. Navigate to the Patient Portal registration page (e.g., via a shortcoded page).<br>2. Fill in all required registration details.<br>3. Submit the form.                                                                                   | The patient account is created. A success message is shown. The patient may need to verify their email.                                                  |               |        |          |
| PAT-02 | Patient Login                       | 1. Navigate to the Patient Portal login page.<br>2. Enter valid credentials (username/password).<br>3. Click "Log In".                                                                                                                       | The patient is successfully logged into their portal dashboard.                                                                                          |               |        |          |
| PAT-03 | View Personal Profile               | 1. From the patient dashboard, navigate to "My Profile" or similar.<br>2. Verify all personal details are displayed correctly.                                                                                                               | The patient's profile information is shown accurately.                                                                                                   |               |        |          |
| PAT-04 | Update Personal Information         | 1. From the profile page, edit a piece of information (e.g., phone number, address).<br>2. Save changes.                                                                                                                                     | The profile information is updated successfully. A confirmation message is displayed.                                                                    |               |        |          |
| PAT-05 | Book a New Appointment              | 1. From the dashboard, click "Book Appointment".<br>2. Select a department (if applicable), doctor, date, and time from available slots.<br>3. Provide a reason for the visit.<br>4. Confirm and book the appointment.                       | The appointment is successfully booked. A confirmation message is displayed. The new appointment appears in the "My Appointments" section of the portal. |               |        |          |
| PAT-06 | View Upcoming and Past Appointments | 1. Navigate to the "My Appointments" section of the portal.                                                                                                                                                                                  | A list of all upcoming and past appointments for the patient is displayed, with dates, times, and doctor names.                                          |               |        |          |
| PAT-07 | View Medical Records                | 1. Navigate to the "My Medical Records" section.<br>2. Verify access to prescriptions, test results, and visit summaries.                                                                                                                    | The patient can view a list or summary of their medical records. They can click on individual records to see more details.                               |               |        |          |
| PAT-08 | View and Pay Bills                  | 1. Navigate to the "My Bills" section.<br>2. Verify a list of outstanding and paid bills is shown.<br>3. Select an outstanding bill.<br>4. View the bill details and proceed to payment (if payment gateway is integrated and in test mode). | Bill details are shown accurately. If payment is tested, the payment process completes, and the bill status updates to "Paid".                           |               |        |          |
| PAT-09 | Access Telemedicine Session         | 1. From an upcoming appointment that is a telemedicine session, click a "Join Session" link (available at the time of the appointment).<br>2. Verify the video conference interface loads (or placeholder for it).                           | The patient is redirected to the telemedicine session. The video interface (or a placeholder indicating it would be here) loads.                         |               |        |          |

---

## 4. UAT Execution and Reporting

### 4.1. Entry Criteria

- The HMS plugin is fully deployed and configured in the staging environment.
- Sample data is populated.
- All testers have been trained on the test cases and the system.
- The UAT plan is finalized and approved.

### 4.2. Execution Process

1.  Testers will be given access to the staging environment with their designated user roles.
2.  Testers will execute the test cases assigned to their role.
3.  For each test case, testers will record the "Actual Result", "Status" (Pass/Fail/Block), and any "Comments".
4.  Any bugs or issues found will be reported in a bug tracking system (e.g., GitHub Issues, Jira) with detailed steps to reproduce, screenshots (if applicable), and severity.
5.  The Test Lead will monitor progress, facilitate communication, and triage reported issues.

### 4.3. Exit Criteria

- All critical and high-priority test cases have passed.
- All major blockers have been resolved and verified.
- No major usability concerns that would prevent go-live remain unaddressed.
- The UAT summary report is complete and signed off by stakeholders.

### 4.4. Reporting

- A daily or weekly status report will be shared with stakeholders during the UAT period.
- A final UAT summary report will be created at the end of the testing period, including:
  - Summary of test case execution (pass/fail/block percentages).
  - List of all identified defects, their status, and resolution.
  - Overall assessment of the system's readiness for production.
  - Recommendations for go-live or further actions.

## 5. Sign-off

The UAT is considered complete and successful upon sign-off by the Project Manager and key stakeholders.

| Role                                  | Name | Signature | Date |
| ------------------------------------- | ---- | --------- | ---- |
| Project Manager                       |      |           |      |
| Lead Tester / End-User Representative |      |           |      |
| Development Lead                      |      |           |      |
