Perfect ✅
Let’s turn your raw issue list into a **clear, professional development task document** — formatted as **a structured issue tracker** with explanations, goals, and developer action points for each item.
I’ve also expanded each point with context and developer instructions to make it ready for ticketing (e.g., in Jira, ClickUp, or GitHub Issues).

---

# 🏥 **Hospital Management System (HMS) WordPress Plugin – Technical Task Document**

---

## **0. Plugin Activation & Database Initialization**

### **Issue:**

When activating the plugin, all database tables for the HMS system are not being created properly.

### **Expected Behavior:**

* On plugin activation, `register_activation_hook` should create all necessary HMS tables automatically (patients, doctors, appointments, billing, pharmacy, lab tests, etc.).
* Database schema should match the structure defined in `readme.md`.
* Menu hierarchy (main menu → submenu → nested submenu) should render correctly under “Hospital Management System”.

### **Developer Tasks:**

* Review activation hook (`hms_install()` or equivalent).
* Add SQL table creation logic for all missing entities.
* Validate database versioning & upgrade paths.
* Ensure all submenus load in correct order using `add_menu_page()` and `add_submenu_page()`.

---

## **1. New Prescription Page**

### **Issue:**

Doctor field (“Prescribing Doctor:”) is not displaying available doctors, though doctors exist in the system.

### **Expected Behavior:**

* “Prescribing Doctor” dropdown should be auto-populated with all active doctors.
* Field should auto-fill doctor based on **Associated Appointment** if applicable.

### **Developer Tasks:**

* Check function that populates doctor list (possibly `get_all_doctors()`).
* Debug SQL query or WPDB call.
* Ensure `appointment_id` → `doctor_id` mapping works.

---

## **2. Medical Record Display**

### **Issue:**

The “All Medical Records” list does not show patient names properly.

### **Expected Behavior:**

* Each medical record row should start with **Patient Name** followed by **Diagnosis/Disease Name**.

### **Developer Tasks:**

* Update `hms_medical_records_list()` query to join patient table and fetch `first_name` + `last_name`.
* Adjust list rendering template.

---

## **3. Patient Registration Redirection**

### **Issue:**

After submitting registration (`page=hms_patient_registration`), the screen goes blank and does not redirect to `page=hms_patients`.

### **Expected Behavior:**

* On successful patient creation, redirect to `hms_patients` listing page.
* Show success message (`wp_redirect()` with `admin_url()`).

### **Developer Tasks:**

* Fix `wp_redirect()` logic.
* Check for output buffer or unexpected echo before redirect.

---

## **4. Medical History Page Design**

### **Issue:**

Design inconsistency on `page=hms_patient_medical_history&patient_id=1`.

### **Expected Behavior:**

* Buttons styled uniformly with HMS theme.
* Layout spacing and alignment consistent across pages.

### **Developer Tasks:**

* Apply consistent Bootstrap/WordPress admin CSS.
* Ensure consistent button styles (`button-primary`, `button-secondary`).
* Align input fields and data tables.

---

## **5. Department Dropdown Missing**

### **Issue:**

Department dropdown not loading on appointment or form pages.

### **Expected Behavior:**

* Dropdown should show department list.
* If empty, auto-seed default departments (Cardiology, Neurology, Oncology, etc.).

### **Developer Tasks:**

* Check data source (DB table `wp_hms_departments`).
* Add fallback seed script to populate default departments on activation.
* Fix `SELECT` query and populate dynamically.

### **Additional Issue:**

* On `page=hms_appointment_details`, submission redirects to blank dashboard.
* Appointment not saved or displayed in `hms_appointments`.

### **Fix:**

* Validate form handling & data insertion.
* Ensure appointment list query fetches recent appointments.

---

## **6. Billing & Invoice Data Not Displayed**

### **Issue:**

Billing page and invoice details not fetching data.

### **Expected Behavior:**

* `page=hms_billing` and `page=hms_bill_details` should show:

  * Bill items
  * Payment details
  * Additional info

### **Developer Tasks:**

* Review queries for fetching invoice data.
* Fix join statements to match billing → patient → invoice.
* Test edit & view functionality.

---

## **7. Remove Unnecessary Page**

### **Task:**

Delete redundant submenu page `page=hms_view_invoice` — not required.

---

## **8. Pharmacy Module Not Working**

### **Issue:**

Pharmacy dashboard throws error:

```
WordPress database error: [Table 'wp_hms_pharmacy_dispenses' doesn't exist]
Warning: Attempt to read property "ID" on string
```

### **Expected Behavior:**

* `page=hms_pharmacy` should display list of dispensed medicines without errors.

### **Developer Tasks:**

* Create missing table `wp_hms_pharmacy_dispenses`.
* Fix property handling (`$row->ID` validation).
* Ensure CRUD (dispense, edit, delete) operations work.

---

## **9. Insurance Management CRUD**

### **Issue:**

“Add New Provider” form not functional — CRUD operations not saving data.

### **Expected Behavior:**

* Form submission should insert/update records in `wp_hms_insurance_providers`.
* List page should show added providers.

### **Developer Tasks:**

* Check form `POST` handler.
* Fix SQL insertion logic and sanitize inputs.
* Validate success/failure messages.

---

## **10. Lab & Radiology Tests**

### **Issue:**

Lab test order form (`page=hms_lab_tests`) not working and poor UI.

### **Expected Behavior:**

* CRUD (Add, Edit, Delete) should work.
* Design consistent with HMS UI.
* “Order New Test” submission should save in database.

### **Developer Tasks:**

* Validate database table (`wp_hms_lab_tests`).
* Improve layout (Bootstrap forms, spacing, labels).
* Verify `wpdb->insert()` logic and redirect properly.

---

## **11. Clinical Page Blank**

### **Issue:**

`page=hms_clinical` loads empty.

### **Expected Behavior:**

* Clinical dashboard should load lab reports, prescriptions, and patient summary.

### **Developer Tasks:**

* Check include file mapping for `hms_clinical`.
* Load correct template file (`admin/templates/clinical.php`).
* Verify database joins.

---

## **12. Patient Queue Management**

### **Issue:**

`page=hms_queue` blank or malfunctioning.

### **Expected Behavior:**

* Show queue list of patients awaiting consultation.
* Allow staff to manage queue order.

### **Developer Tasks:**

* Validate data source table.
* Add UI for queue number, patient name, doctor, status.
* Ensure AJAX refresh works (optional enhancement).

---

## **13. Staff Details Page**

### **Task:**

Check and confirm `page=hms_staff_details` loads staff data properly.

### **Developer Tasks:**

* Validate queries and ensure join with user table.
* Ensure profile pictures and contact details load.

---

## **14. Reports Page**

### **Issue:**

Reports not generating — “An error occurred while generating the report.”

### **Expected Behavior:**

* HMS Reports should render data visualizations or CSV exports.

### **Developer Tasks:**

* Debug report generation PHP file.
* Fix SQL syntax or missing tables.
* Add error handling and fallback message.

---

## **15. Export HMS Data**

### **Enhancement:**

Add checkbox-based export system for admin to select modules for data export.

### **Expected Behavior:**

* Admin selects modules (Patients, Appointments, Billing, etc.).
* CSV/Excel export per module.

### **Developer Tasks:**

* Implement bulk export handler using `wp_ajax`.
* Use `fputcsv()` or PHPExcel library for generation.

---

## **16. Settings Page**

### **Issue:**

`page=hms-settings` incomplete or non-functional.

### **Expected Behavior:**

* Settings page should include:

  * General settings (hospital name, logo, contact)
  * Currency, timezone, date format
  * Notifications (email/SMS)
  * User role management
  * White-label branding options

### **Developer Tasks:**

* Build structured settings page using `settings_fields()` + `update_option()`.
* Add save & reset buttons.
* Organize in tabs (General, Notifications, Advanced).

---

## **17. Code Cleanup**

### **Task:**

Remove deprecated or unused files/functions (e.g. extra pages, commented-out legacy code).

### **Developer Tasks:**

* Identify unused includes/templates.
* Remove dead code safely.
* Optimize with PHPCS + WordPress coding standards.

---

### ✅ **Final Deliverables**

* Fully functional HMS plugin with all modules (Patients, Appointments, Billing, Pharmacy, Clinical, Lab, Insurance, Queue, Reports, Settings).
* Consistent UI design ( WP Admin style).
* Working CRUD + redirect flows.
* Clean and modular code structure.

---
