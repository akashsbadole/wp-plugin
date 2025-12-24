# Hospital Management System (HMS) - Developer Documentation

## Table of Contents

1.  [Introduction](#introduction)
2.  [Architecture Overview](#architecture-overview)
    - [File Structure](#file-structure)
    - [Core Classes](#core-classes)
    - [Database Schema](#database-schema)
    - [Hooks & Filters](#hooks--filters)
3.  [Custom Post Types (CPTs) & Taxonomies](#custom-post-types-cpts--taxonomies)
    - [List of CPTs](#list-of-cpts)
    - [List of Taxonomies](#list-of-taxonomies)
    - [Interacting with CPTs](#interacting-with-cpts)
4.  [Extending the Plugin](#extending-the-plugin)
    - [Adding New Modules](#adding-new-modules)
    - [Custom Fields & Forms API](#custom-fields--forms-api)
    - [Integration Gateways](#integration-gateways)
    - [Action & Filter Hooks](#action--filter-hooks)
5.  [Front-End Development (Patient Portal)](#front-end-development-patient-portal)
    - [Shortcodes](#shortcodes)
    - [Templates & Overrides](#templates--overrides)
    - [JavaScript & CSS](#javascript--css)
6.  [API Endpoints (AJAX)](#api-endpoints-ajax)
    - [Admin AJAX](#admin-ajax)
    - [Public AJAX](#public-ajax)
7.  [Security Best Practices](#security-best-practices)
    - [Nonce Verification](#nonce-verification)
    - [Capability Checks](#capability-checks)
    - [Input Sanitization & Output Escaping](#input-sanitization--output-escaping)
    - [SQL Injection Prevention](#sql-injection-prevention)
8.  [Coding Standards](#coding-standards)
9.  [Contributing](#contributing)

---

## 1. Introduction <a name="introduction"></a>

Welcome to the developer documentation for the Hospital Management System (HMS) WordPress plugin. This document is intended for developers who wish to understand the plugin's internal workings, extend its functionality, or integrate with it.

HMS is built with modern PHP practices, following WordPress coding standards. It is designed to be modular and extensible, allowing developers to add new features or customize existing ones with ease.

### Prerequisites

- Strong understanding of PHP and WordPress development.
- Familiarity with WordPress hooks (actions and filters).
- Knowledge of MySQL and WordPress database schema (`$wpdb`).
- Understanding of front-end technologies (HTML, CSS, JavaScript).

---

## 2. Architecture Overview <a name="architecture-overview"></a>

### 2.1. File Structure <a name="file-structure"></a>

The plugin is organized into a logical directory structure:

```
/hospital-management-system/
├── hospital-management-system.php  # Main plugin file. Includes all core classes.
├── unistall.php                      # Handles plugin uninstallation (drops tables).
├── assets/                           # Compiled assets (CSS, JS). Not used in this dev version.
├── includes/                         # Core PHP classes.
│   ├── class-hms-activator.php       # Handles plugin activation (table creation).
│   ├── class-hms-deactivator.php     # Handles plugin deactivation.
│   ├── class-hms.php                 # Main plugin class, loads everything.
│   ├── class-hms-custom-fields.php   # Manages dynamic custom fields.
│   ├── class-hms-white-label.php     # Handles white-label branding.
│   ├── class-hms-clinical-features.php # CPTs for clinical data.
│   ├── class-hms-reporting-analytics.php # Report generation logic.
│   ├── class-hms-multi-location.php  # Multi-location management logic.
│   ├── class-hms-patient-portal-mobile.php # PWA and mobile features.
│   ├── class-hms-telemedicine.php    # Telemedicine session management.
│   ├── class-hms-license-manager.php # Premium license management.
│   ├── api/
│   │   ├── class-hms-rest-api.php    # REST API endpoints (if any).
│   ├── abstracts/
│   │   ├── abstract-hms-gateway.php  # Base class for payment/sms/whatsapp gateways.
│   ├── gateways/
│   │   ├── class-hms-payment-gateway.php # Abstract payment gateway.
│   │   ├── class-hms-sms-gateway.php     # Abstract SMS gateway.
│   │   └── class-hms-whatsapp-gateway.php # Abstract WhatsApp gateway.
│   └── admin/                         # Admin-specific functionality.
│       ├── class-hms-admin.php        # Admin-specific hooks and helpers.
│       └── partials/                  # Reusable admin template parts.
├── admin/                            # Admin-facing templates, CSS, JS.
│   ├── templates/                     # Admin page templates.
│   │   ├── dashboard.php
│   │   ├── settings.php
│   │   └── ...
│   ├── css/
│   │   └── hms-admin.css
│   └── js/
│       └── hms-admin.js
├── public/                           # Public-facing templates, CSS, JS.
│   ├── templates/
│   │   ├── patient-portal.php
│   │   └── ...
│   ├── css/
│   │   └── hms-patient-portal.css
│   └── js/
│       └── hms-patient-portal.js
├── docs/                             # Documentation files.
└── tests/                            # PHPUnit tests.
    ├── bootstrap.php
    ├── test-sample.php
    └── ...
```

### 2.2. Core Classes <a name="core-classes"></a>

- **`HMS` (`includes/class-hms.php`):** The central class. It's a singleton that initializes all other components, defines constants, and sets up hooks. It's the entry point for the entire plugin.
- **`HMS_Activator` (`includes/class-hms-activator.php`):** Contains the `activate()` method, which is responsible for creating the plugin's database tables and setting up initial options upon plugin activation.
- **`HMS_Deactivator` (`includes/class-hms-deactivator.php`):** Contains the `deactivate()` method for any cleanup tasks upon deactivation (currently minimal).
- **Feature-Specific Classes (e.g., `HMS_Custom_Fields`, `HMS_Billing`):** These classes encapsulate the logic for specific features. For example, `HMS_Custom_Fields` handles the creation, rendering, and saving of dynamic fields. This modular approach makes the codebase easier to maintain and extend.

### 2.3. Database Schema <a name="database-schema"></a>

HMS uses custom database tables to store its data. All tables are prefixed with `wp_hms_` (or the site's database prefix) to avoid conflicts.

Key tables include:

- `wp_hms_patients`: Stores patient information.
- `wp_hms_appointments`: Stores appointment details.
- `wp_hms_bills`, `wp_hms_bill_items`: For billing and invoicing.
- `wp_hms_prescriptions`, `wp_hms_medicines`, `wp_hms_pharmacy_stock`: For pharmacy management.
- `wp_hms_medical_records`: For clinical notes and patient history.
- `wp_hms_staff`: For hospital staff details.
- `wp_hms_departments`, `wp_hms_services`: For hospital departments and services offered.
- `wp_hms_claims`: For insurance claims.
- `wp_hms_audit_log`: For tracking important actions within the system.

Refer to `includes/class-hms-activator.php` for the full `CREATE TABLE` statements and schema details.

### 2.4. Hooks & Filters <a name="hooks--filters"></a>

HMS leverages WordPress hooks extensively for extensibility.

- **Actions:** Used to trigger custom functionality at specific points in the WordPress execution lifecycle (e.g., `hms_patient_registered`, `hms_appointment_created`).
- **Filters:** Used to modify data before it is used or displayed (e.g., `hms_patient_data_before_save`, `hms_report_query_args`).

Developers can hook into these actions and filters to customize behavior without modifying core plugin files. See the [Action & Filter Hooks](#action--filter-hooks) section for a more detailed list.

---

## 3. Custom Post Types (CPTs) & Taxonomies <a name="custom-post-types-cpts--taxonomies"></a>

HMS uses Custom Post Types (CPTs) to manage various entities, leveraging WordPress's native content management capabilities.

### 3.1. List of CPTs <a name="list-of-cpts"></a>

- **`hms_patient`**: Represents a patient. (Note: Patient data is primarily stored in the `wp_hms_patients` table for performance and relational integrity, but a CPT might be used for public-facing profiles or if deep integration with WordPress core features is needed. The current implementation uses a custom table.)
- **`hms_appointment`**: Represents an appointment.
- **`hms_medical_record`**: Represents a clinical note or medical record entry.
- **`hms_prescription`**: Represents a prescription issued by a doctor.
- **`hms_medicine`**: Represents a medicine in the pharmacy inventory.
- **`hms_bill`**: Represents a bill generated for a patient.
- **`hms_telemedicine_session`**: Represents a scheduled telemedicine session.

These CPTs are registered in `includes/class-hms-clinical-features.php` (and other relevant feature classes). Each CPT has its own set of arguments, including `public`, `show_ui`, `show_in_menu`, `supports`, and `capability_type`.

### 3.2. List of Taxonomies <a name="list-of-taxonomies"></a>

- **`hms_department`**: Used to categorize staff, appointments, and services by department.
- **`hms_location`**: Used for multi-location management, allowing content to be filtered by hospital branch or location.

These taxonomies are registered in `includes/class-hms-multi-location.php` and are typically associated with the relevant CPTs.

### 3.3. Interacting with CPTs <a name="interacting-with-cpts"></a>

You can interact with HMS CPTs using standard WordPress functions:

- **Creating a Post:**
  ```php
  $appointment_id = wp_insert_post([
      'post_title'   => 'Appointment with Dr. Smith',
      'post_content' => 'Follow-up for fever.',
      'post_status'  => 'publish',
      'post_type'    => 'hms_appointment',
      'post_author'  => get_current_user_id(),
  ]);
  ```
- **Adding Custom Fields (Post Meta):**
  ```php
  update_post_meta($appointment_id, '_hms_patient_id', 123);
  update_post_meta($appointment_id, '_hms_appointment_datetime', '2025-09-25 10:00:00');
  ```
- **Querying Posts:**
  ```php
  $args = [
      'post_type'      => 'hms_appointment',
      'posts_per_page' => -1,
      'meta_query'     => [
          [
              'key'   => '_hms_patient_id',
              'value' => 123,
          ],
      ],
  ];
  $appointments_query = new WP_Query($args);
  ```

---

## 4. Extending the Plugin <a name="extending-the-plugin"></a>

HMS is designed to be extensible. You can add new features, modify existing ones, and integrate with third-party services.

### 4.1. Adding New Modules <a name="adding-new-modules"></a>

To add a new feature module (e.g., "Laboratory Management"):

1.  **Create a new class file:** `includes/class-hms-laboratory.php`.
2.  **Define your class:**

    ```php
    <?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly.
    }

    class HMS_Laboratory {

        public function __construct() {
            // Hook into 'init' to register CPTs/taxonomies.
            add_action( 'init', [ $this, 'register_cpts' ] );
            // Add other hooks for your module's functionality.
        }

        public function register_cpts() {
            // Register your 'hms_lab_test' CPT here.
            $labels = [
                'name'          => _x( 'Lab Tests', 'Post Type General Name', 'hms' ),
                // ... other labels
            ];
            $args = [
                'label'         => __( 'Lab Tests', 'hms' ),
                'description'   => __( 'Holds all lab tests', 'hms' ),
                'labels'        => $labels,
                'supports'      => [ 'title', 'editor' ],
                'hierarchical'  => false,
                'public'        => false, // Not publicly queryable on the front end
                'show_ui'       => true,
                'show_in_menu'  => 'hms', // Show under the main HMS menu
                'capability_type' => 'post',
                'has_archive'   => false,
                'rewrite'       => false,
            ];
            register_post_type( 'hms_lab_test', $args );
        }

        // Add other methods for your module's logic.
    }
    ```

3.  **Instantiate your class in the main `HMS` class:**
    In `includes/class-hms.php`, within the `define_constants()` or `includes()` method, add:
    ```php
    require_once HMS_PLUGIN_PATH . 'includes/class-hms-laboratory.php';
    // ...
    $this->laboratory = new HMS_Laboratory();
    ```

### 4.2. Custom Fields & Forms API <a name="custom-fields--forms-api"></a>

The `HMS_Custom_Fields` class (`includes/class-hms-custom-fields.php`) provides an API for dynamically adding fields to forms.

- **Registering a Field Group:**
  ```php
  // In your module's constructor or an 'init' hook.
  add_filter( 'hms_custom_field_groups', 'my_module_add_field_group' );
  function my_module_add_field_group( $groups ) {
      $groups[] = [
          'title'   => 'Lab Test Details',
          'context' => 'lab_test_form', // A unique context for your form
          'fields'  => [
              [
                  'label'       => 'Test Name',
                  'name'        => 'test_name',
                  'type'        => 'text',
                  'required'    => true,
                  'placeholder' => 'e.g., CBC',
              ],
              [
                  'label'       => 'Result Value',
                  'name'        => 'result_value',
                  'type'        => 'number',
              ],
          ],
      ];
      return $groups;
  }
  ```
- **Rendering Fields in Your Form:**
  ```php
  // In your form template file.
  $field_group = HMS_Custom_Fields::get_field_group_by_context( 'lab_test_form' );
  if ( $field_group ) {
      foreach ( $field_group['fields'] as $field ) {
          echo HMS_Custom_Fields::render_field( $field );
      }
  }
  ```
- **Saving Field Data:**
  ```php
  // In your form processing logic.
  $field_group = HMS_Custom_Fields::get_field_group_by_context( 'lab_test_form' );
  if ( $field_group ) {
      $custom_data = HMS_Custom_Fields::sanitize_submitted_data( $field_group['fields'], $_POST );
      // $custom_data now contains sanitized values like ['test_name' => '...', 'result_value' => '...']
      // Save this data to your post meta or custom table.
      update_post_meta( $post_id, '_hms_lab_test_data', $custom_data );
  }
  ```

### 4.3. Integration Gateways <a name="integration-gateways"></a>

HMS provides abstract classes for integrating with third-party services like payment gateways, SMS providers, and WhatsApp APIs.

- **Creating a Custom Payment Gateway:**

  1.  Create a new file: `includes/gateways/class-hms-my-custom-payment.php`.
  2.  Extend the abstract class:

      ```php
      <?php
      require_once HMS_PLUGIN_PATH . 'includes/gateways/class-hms-payment-gateway.php';

      class HMS_My_Custom_Payment extends HMS_Payment_Gateway {

          public $id = 'my_custom_payment';
          public $method_title = 'My Custom Gateway';
          public $has_fields = true; // If your gateway requires fields on the checkout form.

          public function __construct() {
              parent::__construct();
              $this->init_settings();
              $this->title = $this->get_option( 'title' );
              // Add other settings.
          }

          public function process_payment( $order_id ) {
              // Logic to process payment with the external API.
              // On success:
              //   update_post_meta( $order_id, '_payment_status', 'paid' );
              //   return ['result' => 'success'];
              // On failure:
              //   return ['result' => 'fail', 'redirect' => $redirect_url];
          }

          public function payment_fields() {
              // Echo HTML for any fields needed on the payment form (e.g., card number).
              echo '<p>Enter your custom payment details here.</p>';
          }
      }
      ```

  3.  Register your gateway:
      `php
    // In your module's class or via a filter.
    add_filter( 'hms_payment_gateways', 'add_my_custom_payment_gateway' );
    function add_my_custom_payment_gateway( $gateways ) {
        $gateways[] = 'HMS_My_Custom_Payment';
        return $gateways;
    }
    `
      The process is similar for SMS (`HMS_SMS_Gateway`) and WhatsApp (`HMS_WhatsApp_Gateway`) gateways.

### 4.4. Action & Filter Hooks <a name="action--filter-hooks"></a>

Here is a list of key hooks provided by HMS. You can use `add_action()` and `add_filter()` to interact with them.

#### Actions

- `hms_patient_registered( $patient_id, $submitted_data )`: Fired after a patient is successfully registered.
- `hms_appointment_created( $appointment_id, $appointment_data )`: Fired after a new appointment is created.
- `hms_appointment_status_updated( $appointment_id, $old_status, $new_status )`: Fired when an appointment's status is changed.
- `hms_prescription_created( $prescription_id, $prescription_data )`: Fired after a new prescription is created.
- `hms_bill_created( $bill_id, $bill_data )`: Fired after a new bill is generated.
- `hms_payment_processed( $payment_id, $payment_data, $gateway_id )`: Fired after a payment is successfully processed.
- `hms_before_render_admin_template( $template_name, $args )`: Fired before an admin template is rendered.
- `hms_after_render_admin_template( $template_name, $args )`: Fired after an admin template is rendered.

#### Filters

- `hms_patient_data_before_save( $data, $context )`: Filters patient data before it is saved to the database. `$context` can be 'registration', 'profile_edit'.
- `hms_appointment_data_before_save( $data )`: Filters appointment data before saving.
- `hms_bill_calculate_total( $total, $bill_items )`: Filters the calculated total amount for a bill.
- `hms_report_query_args( $args, $report_type )`: Filters the WP_Query arguments used for generating reports.
- `hms_custom_field_groups( $groups )`: Filters the list of registered custom field groups. (See [Custom Fields API](#custom-fields--forms-api)).
- `hms_payment_gateways( $gateways )`: Filters the list of available payment gateways.
- `hms_sms_gateways( $gateways )`: Filters the list of available SMS gateways.
- `hms_whatsapp_gateways( $gateways )`: Filters the list of available WhatsApp gateways.
- `hms_admin_template_path( $template_path, $template_name )`: Filters the path to an admin template file, allowing for overrides.

---

## 5. Front-End Development (Patient Portal) <a name="front-end-development-patient-portal"></a>

The patient portal is the public-facing side of HMS, allowing patients to interact with the system.

### 5.1. Shortcodes <a name="shortcodes"></a>

The portal is primarily accessed via shortcodes that can be placed on any WordPress page.

- **`[hms_patient_portal]`**: Renders the main patient portal dashboard. This shortcode dynamically shows different content based on whether the user is logged in or not (e.g., a login form for guests, or the dashboard for logged-in patients).
- **`[hms_appointment_booking]`**: Renders a standalone appointment booking form.
- **`[hms_telemedicine_join session_id="123"]`**: Renders a button or interface to join a specific telemedicine session.

These shortcodes are defined in `includes/class-hms-patient-portal.php`.

### 5.2. Templates & Overrides <a name="templates--overrides"></a>

The front-end uses template files located in `public/templates/`.

- **`login.php`**: Template for the patient login form.
- **`register.php`**: Template for the patient registration form.
- **`dashboard.php`**: Template for the patient dashboard.
- **`appointment-booking.php`**: Template for the appointment booking form.
- **`profile.php`**: Template for the patient profile management page.

**Theme Override:**
You can override any of these template files by copying them into your active theme's directory, within a `hms/` folder. For example, to override the dashboard:

1.  Copy `public/templates/dashboard.php` to `your-theme/hms/dashboard.php`.
2.  Modify the copied file. HMS will now load your theme's version instead of the plugin's default.

### 5.3. JavaScript & CSS <a name="javascript--css"></a>

- **CSS:** `public/css/hms-patient-portal.css` contains all the styles for the patient portal. It's enqueued on pages where HMS shortcodes are used.
- **JavaScript:** `public/js/hms-patient-portal.js` contains client-side logic, such as AJAX form submissions, date pickers, and dynamic UI updates.

**Adding Custom Scripts/Styles:**
If your custom module requires front-end assets, you can enqueue them conditionally:

```php
function my_module_enqueue_frontend_assets() {
    if ( is_page( 'patient-portal' ) || has_shortcode( 'hms_patient_portal' ) ) {
        wp_enqueue_style( 'my-module-style', HMS_PLUGIN_URL . 'public/css/my-module.css' );
        wp_enqueue_script( 'my-module-script', HMS_PLUGIN_URL . 'public/js/my-module.js', [ 'jquery' ], HMS_VERSION, true );
        wp_localize_script( 'my-module-script', 'MyModuleAjax', [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'my_module_nonce' ),
        ]);
    }
}
add_action( 'wp_enqueue_scripts', 'my_module_enqueue_frontend_assets' );
```

---

## 6. API Endpoints (AJAX) <a name="api-endpoints-ajax"></a>

HMS uses WordPress's AJAX API to handle asynchronous requests from both the admin and public-facing sides.

### 6.1. Admin AJAX <a name="admin-ajax"></a>

Admin AJAX requests are handled by functions hooked to `wp_ajax_{$action}`.

**Example: Saving a custom field group via AJAX.**
In `admin/js/hms-admin.js`:

```javascript
jQuery(document).ready(function ($) {
  $("#save-field-group").on("click", function (e) {
    e.preventDefault();
    var data = {
      action: "hms_save_custom_field_group",
      nonce: hms_ajax.nonce, // Passed via wp_localize_script
      group_data: $("#field-group-form").serialize(),
    };
    $.post(ajaxurl, data, function (response) {
      if (response.success) {
        alert("Field group saved!");
      } else {
        alert("Error: " + response.data);
      }
    });
  });
});
```

In your PHP class (e.g., `HMS_Custom_Fields`):

```php
public function __construct() {
    add_action( 'wp_ajax_hms_save_custom_field_group', [ $this, 'ajax_save_field_group' ] );
}

public function ajax_save_field_group() {
    check_ajax_referer( 'hms_admin_nonce', 'nonce' ); // Security check
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized' );
    }
    // Process $_POST['group_data'], sanitize, and save.
    // ...
    wp_send_json_success( 'Field group saved successfully.' );
}
```

### 6.2. Public AJAX <a name="public-ajax"></a>

Public AJAX requests (for the patient portal) are handled by functions hooked to `wp_ajax_nopriv_{$action}`.

**Example: Patient booking an appointment via AJAX.**
In `public/js/hms-patient-portal.js`:

```javascript
jQuery(document).ready(function ($) {
  $("#book-appointment-form").on("submit", function (e) {
    e.preventDefault();
    var data = {
      action: "hms_nopriv_book_appointment",
      nonce: hms_portal_ajax.nonce,
      form_data: $(this).serialize(),
    };
    $.post(ajaxurl, data, function (response) {
      if (response.success) {
        $("#booking-result").html("Appointment booked successfully!");
      } else {
        $("#booking-result").html("Error: " + response.data.message);
      }
    });
  });
});
```

In your PHP class (e.g., `HMS_Patient_Portal`):

```php
public function __construct() {
    add_action( 'wp_ajax_nopriv_hms_book_appointment', [ $this, 'ajax_book_appointment' ] );
    // Note: Also hook to 'wp_ajax_hms_book_appointment' if logged-in users can also book.
}

public function ajax_book_appointment() {
    check_ajax_referer( 'hms_portal_nonce', 'nonce' );
    // Process $_POST['form_data'], sanitize, validate, and create the appointment.
    // ...
    wp_send_json_success( [ 'message' => 'Appointment booked!' ] );
}
```

---

## 7. Security Best Practices <a name="security-best-practices"></a>

When developing for or extending HMS, it is crucial to follow security best practices to protect sensitive data.

### 7.1. Nonce Verification <a name="nonce-verification"></a>

Always use nonces for any action that modifies data (form submissions, AJAX requests, admin links).

- **Creating a nonce:** `wp_nonce_field( 'hms_delete_patient_' . $patient_id, 'delete_patient_nonce' )`
- **Verifying a nonce:** `check_admin_referer( 'hms_delete_patient_' . $patient_id, 'delete_patient_nonce' );` or `check_ajax_referer( 'hms_portal_nonce', 'nonce' );`

### 7.2. Capability Checks <a name="capability-checks"></a>

Ensure users have the appropriate permissions before performing an action.

- **Checking capabilities:** Use `current_user_can( 'capability' )`.
  ```php
  if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_hms_patients' ) ) {
      wp_die( 'You do not have sufficient permissions to perform this action.' );
  }
  ```
- **Define custom capabilities:** Use a plugin like "User Role Editor" or map capabilities to roles in your plugin's activation hook. HMS defines capabilities like `edit_hms_patients`, `delete_hms_bills`, etc.

### 7.3. Input Sanitization & Output Escaping <a name="input-sanitization--output-escaping"></a>

- **Sanitize Input:** Never trust user input. Always sanitize it before saving or using it.
  - **Text:** `sanitize_text_field( $_POST['user_name'] )`
  - **Email:** `sanitize_email( $_POST['user_email'] )`
  - **HTML:** `wp_kses_post( $_POST['content'] )` (for allowed HTML tags)
  - **Integers:** `intval( $_GET['post_id'] )`
- **Escape Output:** Always escape data before displaying it in HTML, JavaScript, or attributes.
  - **HTML:** `esc_html( $variable )`
  - **Attributes:** `esc_attr( $variable )`
  - **JavaScript:** `esc_js( $variable )`
  - **URLs:** `esc_url( $variable )`

### 7.4. SQL Injection Prevention <a name="sql-injection-prevention"></a>

When interacting with the database directly (using `$wpdb`), always use prepared statements.

**Incorrect (Vulnerable):**

```php
$patient_id = $_GET['patient_id'];
$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}hms_patients WHERE id = $patient_id" );
```

**Correct (Secure):**

```php
$patient_id = intval( $_GET['patient_id'] ); // Sanitize as integer.
$results = $wpdb->get_results( $wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}hms_patients WHERE id = %d",
    $patient_id
) );
// Use %s for strings, %d for integers, %f for floats.
```

---

## 8. Coding Standards <a name="coding-standards"></a>

All code for HMS must adhere to the [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/).

Key points:

- **PHP:**
  - Use single quotes for strings.
  - Use spaces for indentation (tabs are acceptable but spaces are preferred for consistency in many modern standards).
  - Braces should be on the same line for classes, functions, and control structures (K&R style).
  - Yoda conditions should not be used: `if ( true === $foo )` is incorrect. Use `if ( $foo === true )`.
  - Always use `<?php ?>` tags, not short tags `<? ?>`.
- **JavaScript:**
  - Use camelCase for variables and functions.
  - Use single quotes for strings.
- **HTML:**
  - Use lowercase for all tags and attributes.
  - Always quote attributes.
  - Use semantic HTML5 tags where appropriate.
- **Comments:**
  - Use phpDoc blocks for all classes, methods, and functions.
  - Use inline comments (`//`) to explain complex logic.

Consider using static analysis tools like PHP_CodeSniffer with the WordPress ruleset to automatically check your code for compliance.

---

## 9. Contributing <a name="contributing"></a>

We welcome contributions to the HMS plugin! If you would like to contribute, please follow these guidelines:

1.  **Fork the Repository:** Fork the official HMS repository on GitHub.
2.  **Create a Feature Branch:** Create a new branch for your feature or bugfix (e.g., `feature/add-lab-module`).
3.  **Follow Coding Standards:** Ensure your code adheres to the standards outlined in this document.
4.  **Write Tests:** If you add new functionality, please write corresponding PHPUnit tests to cover it.
5.  **Update Documentation:** If your changes add new features or modify existing ones, please update the relevant documentation (this file, user manual, inline code comments).
6.  **Submit a Pull Request:** Submit a pull request to the `develop` branch of the official repository. Provide a clear description of your changes and why they should be merged.

Thank you for helping to improve the Hospital Management System!
