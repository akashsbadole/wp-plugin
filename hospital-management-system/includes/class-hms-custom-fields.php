<?php

/**
 * HMS Custom Fields & Forms
 *
 * This class handles the creation, management, and rendering of dynamic custom fields
 * for various modules within the Hospital Management System.
 *
 * @package HospitalManagementSystem
 * @subpackage Includes
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class HMS_Custom_Fields {

    /**
     * The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
        $this->hooks();
    }

    /**
     * Register the hooks for the custom fields functionality.
     */
    private function hooks() {
        add_action( 'admin_menu', array( $this, 'add_custom_fields_admin_page' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'wp_ajax_hms_save_custom_field', array( $this, 'ajax_save_custom_field' ) );
        add_action( 'wp_ajax_hms_delete_custom_field', array( $this, 'ajax_delete_custom_field' ) );
        add_action( 'wp_ajax_hms_get_custom_fields', array( $this, 'ajax_get_custom_fields' ) );
        // Hook into forms to render custom fields
        add_action( 'hms_patient_registration_form_after', array( $this, 'render_patient_registration_fields' ) );
        add_action( 'hms_appointment_form_after', array( $this, 'render_appointment_fields' ) );
        add_action( 'hms_prescription_form_after', array( $this, 'render_prescription_fields' ) );
        add_action( 'hms_billing_form_after', array( $this, 'render_billing_fields' ) );
        // Hook into save processes to save custom field data
        add_action( 'hms_save_patient_data', array( $this, 'save_patient_custom_data' ), 10, 2 );
        add_action( 'hms_save_appointment_data', array( $this, 'save_appointment_custom_data' ), 10, 2 );
        add_action( 'hms_save_prescription_data', array( $this, 'save_prescription_custom_data' ), 10, 2 );
        add_action( 'hms_save_bill_data', array( $this, 'save_bill_custom_data' ), 10, 2 );
    }

    /**
     * Add the admin page for managing custom fields.
     */
    public function add_custom_fields_admin_page() {
        add_submenu_page(
            'hms-dashboard', // Parent slug
            'Custom Fields Management', // Page title
            'Custom Fields', // Menu title
            'manage_options', // Capability
            'hms-custom-fields', // Menu slug
            array( $this, 'render_custom_fields_admin_page' ) // Callback function
        );
    }

    /**
     * Render the admin page for custom fields.
     */
    public function render_custom_fields_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p>Manage dynamic custom fields for various forms in the Hospital Management System.</p>

            <div id="hms-custom-fields-container">
                <h2>Add New Custom Field</h2>
                <form id="hms-custom-field-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="field_context">Context</label></th>
                            <td>
                                <select name="field_context" id="field_context" required>
                                    <option value="">Select Context</option>
                                    <option value="patient_registration">Patient Registration</option>
                                    <option value="appointment">Appointment</option>
                                    <option value="prescription">Prescription</option>
                                    <option value="billing">Billing</option>
                                    <option value="reports">Reports</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="field_label">Field Label</label></th>
                            <td><input type="text" name="field_label" id="field_label" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="field_name">Field Name (Slug)</label></th>
                            <td><input type="text" name="field_name" id="field_name" class="regular-text" required pattern="[a-z0-9_]+" title="Only lowercase letters, numbers, and underscores are allowed."></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="field_type">Field Type</label></th>
                            <td>
                                <select name="field_type" id="field_type" required>
                                    <option value="">Select Type</option>
                                    <option value="text">Text</option>
                                    <option value="textarea">Textarea</option>
                                    <option value="number">Number</option>
                                    <option value="email">Email</option>
                                    <option value="date">Date</option>
                                    <option value="select">Select Dropdown</option>
                                    <option value="checkbox">Checkbox</option>
                                    <option value="radio">Radio Buttons</option>
                                    <option value="file">File Upload</option>
                                </select>
                            </td>
                        </tr>
                        <tr id="field_options_row" style="display: none;">
                            <th scope="row"><label for="field_options">Options (for Select/Radio/Checkbox)</label></th>
                            <td><textarea name="field_options" id="field_options" class="large-text" rows="3" placeholder="Enter options, one per line. For checkboxes, each line is a checkbox option."></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="field_required">Required</label></th>
                            <td><input type="checkbox" name="field_required" id="field_required" value="1"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="field_placeholder">Placeholder</label></th>
                            <td><input type="text" name="field_placeholder" id="field_placeholder" class="regular-text"></td>
                        </tr>
                         <tr>
                            <th scope="row"><label for="field_default_value">Default Value</label></th>
                            <td><input type="text" name="field_default_value" id="field_default_value" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="field_description">Description</label></th>
                            <td><input type="text" name="field_description" id="field_description" class="regular-text"></td>
                        </tr>
                         <tr>
                            <th scope="row"><label for="field_order">Order</label></th>
                            <td><input type="number" name="field_order" id="field_order" class="small-text" value="0"></td>
                        </tr>
                    </table>
                    <?php submit_button( 'Add Custom Field', 'primary', 'submit_custom_field' ); ?>
                </form>
            </div>

            <hr>

            <div id="hms-existing-custom-fields-container">
                <h2>Existing Custom Fields</h2>
                <div id="hms-fields-list-container">
                    <!-- Fields will be loaded here via AJAX -->
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Enqueue admin scripts and styles for the custom fields page.
     *
     * @param string $hook The current admin page.
     */
    public function enqueue_admin_assets( $hook ) {
        // Only load on our custom fields page
        if ( 'toplevel_page_hms-custom-fields' !== $hook && 'hospital-management-system_page_hms-custom-fields' !== $hook ) {
            return;
        }

        wp_enqueue_style( $this->plugin_name . '-custom-fields-admin', plugin_dir_url( __FILE__ ) . '../admin/css/hms-custom-fields.css', array(), $this->version, 'all' );
        wp_enqueue_script( $this->plugin_name . '-custom-fields-admin', plugin_dir_url( __FILE__ ) . '../admin/js/hms-custom-fields.js', array( 'jquery' ), $this->version, false );
        wp_localize_script( $this->plugin_name . '-custom-fields-admin', 'hms_custom_fields_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'hms_custom_fields_nonce' ),
        ) );
    }

    /**
     * AJAX handler to save a custom field.
     */
    public function ajax_save_custom_field() {
        check_ajax_referer( 'hms_custom_fields_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'You do not have sufficient permissions to perform this action.' );
        }

        $field_data = array(
            'context'     => sanitize_text_field( $_POST['field_context'] ),
            'label'       => sanitize_text_field( $_POST['field_label'] ),
            'name'        => sanitize_title( $_POST['field_name'] ), // Ensure it's a valid slug
            'type'        => sanitize_text_field( $_POST['field_type'] ),
            'options'     => ! empty( $_POST['field_options'] ) ? sanitize_textarea_field( $_POST['field_options'] ) : '',
            'required'    => isset( $_POST['field_required'] ) ? 1 : 0,
            'placeholder' => sanitize_text_field( $_POST['field_placeholder'] ),
            'default'     => sanitize_text_field( $_POST['field_default_value'] ),
            'description' => sanitize_text_field( $_POST['field_description'] ),
            'order'       => intval( $_POST['field_order'] ),
        );

        // Basic validation
        if ( empty( $field_data['context'] ) || empty( $field_data['label'] ) || empty( $field_data['name'] ) || empty( $field_data['type'] ) ) {
            wp_send_json_error( 'Required fields are missing.' );
        }

        $existing_fields = get_option( 'hms_custom_fields', array() );
        
        // Check if field name already exists in the same context
        foreach ($existing_fields as $key => $field) {
            if ($field['context'] === $field_data['context'] && $field['name'] === $field_data['name']) {
                // Update existing field
                $existing_fields[$key] = $field_data;
                update_option( 'hms_custom_fields', $existing_fields );
                wp_send_json_success( 'Custom field updated successfully!' );
            }
        }
        
        // Add new field
        $existing_fields[] = $field_data;
        update_option( 'hms_custom_fields', $existing_fields );

        wp_send_json_success( 'Custom field saved successfully!' );
    }

    /**
     * AJAX handler to delete a custom field.
     */
    public function ajax_delete_custom_field() {
        check_ajax_referer( 'hms_custom_fields_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'You do not have sufficient permissions to perform this action.' );
        }

        $context = sanitize_text_field( $_POST['context'] );
        $name    = sanitize_title( $_POST['name'] );

        if ( empty( $context ) || empty( $name ) ) {
            wp_send_json_error( 'Missing context or field name.' );
        }

        $existing_fields = get_option( 'hms_custom_fields', array() );
        foreach ( $existing_fields as $key => $field ) {
            if ( $field['context'] === $context && $field['name'] === $name ) {
                unset( $existing_fields[$key] );
                // Re-index array
                $existing_fields = array_values($existing_fields);
                update_option( 'hms_custom_fields', $existing_fields );
                wp_send_json_success( 'Custom field deleted successfully!' );
            }
        }

        wp_send_json_error( 'Custom field not found.' );
    }

    /**
     * AJAX handler to get all custom fields, optionally filtered by context.
     */
    public function ajax_get_custom_fields() {
        check_ajax_referer( 'hms_custom_fields_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'You do not have sufficient permissions.' );
        }

        $context = isset( $_POST['context'] ) ? sanitize_text_field( $_POST['context'] ) : '';
        $all_fields = get_option( 'hms_custom_fields', array() );

        if ( $context ) {
            $filtered_fields = array_filter( $all_fields, function( $field ) use ( $context ) {
                return $field['context'] === $context;
            });
            // Sort by order
            usort($filtered_fields, function($a, $b) {
                return $a['order'] - $b['order'];
            });
            wp_send_json_success( $filtered_fields );
        }

        // Sort all fields by context then order
        usort($all_fields, function($a, $b) {
            if ($a['context'] != $b['context']) {
                return strcmp($a['context'], $b['context']);
            }
            return $a['order'] - $b['order'];
        });
        wp_send_json_success( $all_fields );
    }

    /**
     * Get custom fields for a specific context.
     *
     * @param string $context The context (e.g., 'patient_registration').
     * @return array An array of custom field definitions.
     */
    public function get_custom_fields( $context ) {
        $all_fields = get_option( 'hms_custom_fields', array() );
        $context_fields = array_filter( $all_fields, function( $field ) use ( $context ) {
            return $field['context'] === $context;
        } );
        usort($context_fields, function($a, $b) {
            return $a['order'] - $b['order'];
        });
        return $context_fields;
    }

    /**
     * Render custom fields for the patient registration form.
     *
     * @param int $patient_id The patient ID (if editing).
     */
    public function render_patient_registration_fields( $patient_id = 0 ) {
        $this->render_fields( 'patient_registration', $patient_id, 'hms_patient_custom' );
    }

    /**
     * Render custom fields for the appointment form.
     *
     * @param int $appointment_id The appointment ID (if editing).
     */
    public function render_appointment_fields( $appointment_id = 0 ) {
        $this->render_fields( 'appointment', $appointment_id, 'hms_appointment_custom' );
    }

    /**
     * Render custom fields for the prescription form.
     *
     * @param int $prescription_id The prescription ID (if editing).
     */
    public function render_prescription_fields( $prescription_id = 0 ) {
        $this->render_fields( 'prescription', $prescription_id, 'hms_prescription_custom' );
    }

    /**
     * Render custom fields for the billing form.
     *
     * @param int $bill_id The bill ID (if editing).
     */
    public function render_billing_fields( $bill_id = 0 ) {
        $this->render_fields( 'billing', $bill_id, 'hms_bill_custom' );
    }

    /**
     * Generic method to render fields for a given context.
     *
     * @param string $context The field context.
     * @param int    $item_id The ID of the item being edited (e.g., patient_id).
     * @param string $meta_key_prefix The prefix for the meta key.
     */
    private function render_fields( $context, $item_id, $meta_key_prefix ) {
        $fields = $this->get_custom_fields( $context );
        if ( empty( $fields ) ) {
            return;
        }

        echo '<h3>Additional Information</h3>';
        echo '<table class="form-table hms-custom-fields-table">';
        foreach ( $fields as $field ) {
            $meta_key = $meta_key_prefix . '_' . $field['name'];
            $current_value = $item_id ? get_post_meta( $item_id, $meta_key, true ) : $field['default'];

            echo '<tr>';
            echo '<th scope="row"><label for="' . esc_attr( $meta_key ) . '">' . esc_html( $field['label'] ) . ( $field['required'] ? ' <span class="required">*</span>' : '' ) . '</label></th>';
            echo '<td>';
            $this->render_field_input( $field, $meta_key, $current_value );
            if ( ! empty( $field['description'] ) ) {
                echo '<p class="description">' . esc_html( $field['description'] ) . '</p>';
            }
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    /**
     * Render a single field input based on its type.
     *
     * @param array  $field The field definition.
     * @param string $name The input name.
     * @param mixed  $value The current value.
     */
    private function render_field_input( $field, $name, $value ) {
        $required = $field['required'] ? 'required' : '';
        $placeholder = ! empty( $field['placeholder'] ) ? 'placeholder="' . esc_attr( $field['placeholder'] ) . '"' : '';

        switch ( $field['type'] ) {
            case 'text':
            case 'email':
            case 'number':
            case 'date':
                echo '<input type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="regular-text" ' . $placeholder . ' ' . $required . '>';
                break;
            case 'textarea':
                echo '<textarea name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" rows="3" class="large-text" ' . $placeholder . ' ' . $required . '>' . esc_textarea( $value ) . '</textarea>';
                break;
            case 'select':
                $options_array = ! empty( $field['options'] ) ? explode( "\n", trim( $field['options'] ) ) : array();
                echo '<select name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" ' . $required . '>';
                echo '<option value="">' . esc_htmlesc_html__( 'Select an option', 'hospital-management-system' ) . '</option>';
                foreach ( $options_array as $option ) {
                    $option = trim( $option );
                    if ( ! empty( $option ) ) {
                        echo '<option value="' . esc_attr( $option ) . '" ' . selected( $value, $option, false ) . '>' . esc_html( $option ) . '</option>';
                    }
                }
                echo '</select>';
                break;
            case 'checkbox':
                // For simplicity, if options exist, treat as multiple checkboxes.
                // If no options, it's a single checkbox.
                $options_array = ! empty( $field['options'] ) ? explode( "\n", trim( $field['options'] ) ) : array();
                if ( ! empty( $options_array ) ) {
                    $current_values = is_array( $value ) ? $value : ( ! empty( $value ) ? array( $value ) : array() );
                    foreach ( $options_array as $option_key => $option_label ) {
                        $option_label = trim( $option_label );
                        $option_value = sanitize_title( $option_label ); // Or use $option_key
                        $input_name = $name . '[]';
                        echo '<label><input type="checkbox" name="' . esc_attr( $input_name ) . '" id="' . esc_attr( $name . '_' . $option_value ) . '" value="' . esc_attr( $option_value ) . '" ' . checked( in_array( $option_value, $current_values ), true, false ) . ' ' . $required . '> ' . esc_html( $option_label ) . '</label><br>';
                    }
                } else {
                    echo '<label><input type="checkbox" name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" value="1" ' . checked( $value, 1, false ) . ' ' . $required . '> ' . esc_htmlesc_html__( 'Yes', 'hospital-management-system' ) . '</label>';
                }
                break;
            case 'radio':
                $options_array = ! empty( $field['options'] ) ? explode( "\n", trim( $field['options'] ) ) : array();
                foreach ( $options_array as $option ) {
                    $option = trim( $option );
                    if ( ! empty( $option ) ) {
                        echo '<label><input type="radio" name="' . esc_attr( $name ) . '" id="' . esc_attr( $name . '_' . sanitize_title( $option ) ) . '" value="' . esc_attr( $option ) . '" ' . checked( $value, $option, false ) . ' ' . $required . '> ' . esc_html( $option ) . '</label><br>';
                    }
                }
                break;
            case 'file':
                echo '<input type="file" name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" ' . $required . '>';
                if ( ! empty( $value ) ) {
                    $file_url = wp_get_attachment_url( $value );
                    if ( $file_url ) {
                        echo '<br><a href="' . esc_url( $file_url ) . '" target="_blank">View Current File</a>';
                        echo '<input type="hidden" name="' . esc_attr( $name . '_existing_id' ) . '" value="' . esc_attr( $value ) . '>';
                    }
                }
                break;
            default:
                echo '<input type="text" name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="regular-text" ' . $placeholder . ' ' . $required . '>';
                break;
        }
    }

    /**
     * Save custom data for a patient.
     *
     * @param int   $patient_id The patient ID.
     * @param array $data The submitted data.
     */
    public function save_patient_custom_data( $patient_id, $data ) {
        $this->save_custom_data( 'patient_registration', $patient_id, $data, 'hms_patient_custom' );
    }

    /**
     * Save custom data for an appointment.
     *
     * @param int   $appointment_id The appointment ID.
     * @param array $data The submitted data.
     */
    public function save_appointment_custom_data( $appointment_id, $data ) {
        $this->save_custom_data( 'appointment', $appointment_id, $data, 'hms_appointment_custom' );
    }

    /**
     * Save custom data for a prescription.
     *
     * @param int   $prescription_id The prescription ID.
     * @param array $data The submitted data.
     */
    public function save_prescription_custom_data( $prescription_id, $data ) {
        $this->save_custom_data( 'prescription', $prescription_id, $data, 'hms_prescription_custom' );
    }

    /**
     * Save custom data for a bill.
     *
     * @param int   $bill_id The bill ID.
     * @param array $data The submitted data.
     */
    public function save_bill_custom_data( $bill_id, $data ) {
        $this->save_custom_data( 'billing', $bill_id, $data, 'hms_bill_custom' );
    }

    /**
     * Generic method to save custom data.
     *
     * @param string $context The field context.
     * @param int    $item_id The ID of the item (e.g., post ID).
     * @param array  $submitted_data All data submitted from the form.
     * @param string $meta_key_prefix The prefix for the meta key.
     */
    private function save_custom_data( $context, $item_id, $submitted_data, $meta_key_prefix ) {
        $fields = $this->get_custom_fields( $context );
        if ( empty( $fields ) ) {
            return;
        }

        foreach ( $fields as $field ) {
            $meta_key = $meta_key_prefix . '_' . $field['name'];
            $input_name = $meta_key; // This matches the 'name' attribute in the form

            if ( isset( $submitted_data[ $input_name ] ) ) {
                $value = $submitted_data[ $input_name ];

                // Handle file uploads
                if ( $field['type'] === 'file' ) {
                    if ( ! empty( $_FILES[ $input_name ]['name'] ) ) {
                        require_once( ABSPATH . 'wp-admin/includes/file.php' );
                        require_once( ABSPATH . 'wp-admin/includes/media.php' );
                        require_once( ABSPATH . 'wp-admin/includes/image.php' );

                        $attachment_id = media_handle_upload( $input_name, $item_id );
                        if ( is_wp_error( $attachment_id ) ) {
                            // Log error or handle it
                        } else {
                            update_post_meta( $item_id, $meta_key, $attachment_id );
                        }
                    } elseif ( isset( $submitted_data[ $input_name . '_existing_id' ] ) ) {
                        // Keep existing file if no new one is uploaded
                        update_post_meta( $item_id, $meta_key, intval( $submitted_data[ $input_name . '_existing_id' ] ) );
                    }
                } else {
                    // Sanitize based on type
                    if ( is_array( $value ) ) { // For checkboxes
                        $sanitized_value = array_map( 'sanitize_text_field', $value );
                    } elseif ( $field['type'] === 'textarea' ) {
                        $sanitized_value = sanitize_textarea_field( $value );
                    } elseif ( $field['type'] === 'number' ) {
                        $sanitized_value = floatval( $value );
                    } elseif ( $field['type'] === 'email' ) {
                        $sanitized_value = sanitize_email( $value );
                    } else {
                        $sanitized_value = sanitize_text_field( $value );
                    }
                    update_post_meta( $item_id, $meta_key, $sanitized_value );
                }
            } elseif ( $field['type'] === 'checkbox' && empty( $field['options'] ) ) {
                 // Single checkbox that was not checked
                 delete_post_meta( $item_id, $meta_key );
            } elseif ( $field['type'] === 'file' && isset( $submitted_data[ $input_name . '_existing_id' ] ) ) {
                 // File field, keep existing if no new file uploaded
                 update_post_meta( $item_id, $meta_key, intval( $submitted_data[ $input_name . '_existing_id' ] ) );
            }
        }
    }
}
