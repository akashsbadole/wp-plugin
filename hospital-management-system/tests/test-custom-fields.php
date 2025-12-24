<?php
/**
 * Tests for the HMS_Custom_Fields class.
 *
 * @package HospitalManagementSystem
 * @group   hms-custom-fields
 */

class HMS_Custom_Fields_Test extends WP_UnitTestCase {

    /**
     * Instance of the HMS_Custom_Fields class.
     *
     * @var HMS_Custom_Fields
     */
    protected $hms_custom_fields;

    /**
     * Setup the test environment.
     */
    public function setUp() {
        parent::setUp();
        // Get the global HMS instance and access the custom fields object.
        // This assumes the main plugin file instantiates the classes and makes them globally accessible.
        // For a real test suite, you might need a more robust way to get the instance.
        global $hms;
        if ( isset( $hms->custom_fields ) ) {
            $this->hms_custom_fields = $hms->custom_fields;
        } else {
            // Fallback: manually instantiate if not available globally for some reason.
            // This is less ideal as it doesn't test the actual integration.
            $this->hms_custom_fields = new HMS_Custom_Fields( 'hospital-management-system', '1.0.0' );
        }
        // Clear any existing custom fields before each test.
        delete_option( 'hms_custom_fields' );
    }

    /**
     * Test adding a new custom field via AJAX.
     */
    public function test_ajax_save_custom_field_add() {
        $this->_setRole( 'administrator' );

        $_POST['nonce'] = wp_create_nonce( 'hms_custom_fields_nonce' );
        $_POST['action'] = 'hms_save_custom_field';
        $_POST['field_context'] = 'patient_registration';
        $_POST['field_label'] = 'Test Field';
        $_POST['field_name'] = 'test_field';
        $_POST['field_type'] = 'text';
        $_POST['field_required'] = '1';
        $_POST['field_order'] = '1';

        try {
            $this->_handleAjax( 'hms_save_custom_field' );
        } catch ( WPAjaxDieStopException $e ) {
            // We expect this to be thrown, so we can inspect the response.
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertTrue( $response['success'] );
        $this->assertEquals( 'Custom field saved successfully!', $response['data'] );

        // Verify the field was saved in the database.
        $saved_fields = get_option( 'hms_custom_fields', array() );
        $this->assertCount( 1, $saved_fields );
        $this->assertEquals( 'patient_registration', $saved_fields[0]['context'] );
        $this->assertEquals( 'Test Field', $saved_fields[0]['label'] );
        $this->assertEquals( 'test_field', $saved_fields[0]['name'] );
    }

    /**
     * Test updating an existing custom field via AJAX.
     */
    public function test_ajax_save_custom_field_update() {
        $this->_setRole( 'administrator' );

        // First, add a field.
        $initial_field = array(
            'context'     => 'patient_registration',
            'label'       => 'Original Field',
            'name'        => 'original_field',
            'type'        => 'text',
            'required'    => 0,
            'placeholder' => '',
            'default'     => '',
            'description' => '',
            'order'       => 1,
        );
        update_option( 'hms_custom_fields', array( $initial_field ) );

        // Now, update it.
        $_POST['nonce'] = wp_create_nonce( 'hms_custom_fields_nonce' );
        $_POST['action'] = 'hms_save_custom_field';
        $_POST['field_context'] = 'patient_registration';
        $_POST['field_label'] = 'Updated Field'; // Changed label
        $_POST['field_name'] = 'original_field'; // Same name
        $_POST['field_type'] = 'textarea'; // Changed type
        $_POST['field_required'] = '1';
        $_POST['field_order'] = '1';

        try {
            $this->_handleAjax( 'hms_save_custom_field' );
        } catch ( WPAjaxDieStopException $e ) {
        }

        $response = json_decode( $this->_last_response, true );
        $this->assertTrue( $response['success'] );
        $this->assertEquals( 'Custom field updated successfully!', $response['data'] );

        // Verify the field was updated.
        $saved_fields = get_option( 'hms_custom_fields', array() );
        $this->assertCount( 1, $saved_fields );
        $this->assertEquals( 'Updated Field', $saved_fields[0]['label'] );
        $this->assertEquals( 'textarea', $saved_fields[0]['type'] );
    }


    /**
     * Test deleting a custom field via AJAX.
     */
    public function test_ajax_delete_custom_field() {
        $this->_setRole( 'administrator' );

        // Add a field first.
        $field_to_delete = array(
            'context'     => 'appointment',
            'label'       => 'Delete Me',
            'name'        => 'delete_me',
            'type'        => 'select',
            'required'    => 0,
            'options'     => "opt1\nopt2",
            'placeholder' => '',
            'default'     => '',
            'description' => '',
            'order'       => 1,
        );
        update_option( 'hms_custom_fields', array( $field_to_delete ) );

        $_POST['nonce'] = wp_create_nonce( 'hms_custom_fields_nonce' );
        $_POST['action'] = 'hms_delete_custom_field';
        $_POST['context'] = 'appointment';
        $_POST['name'] = 'delete_me';

        try {
            $this->_handleAjax( 'hms_delete_custom_field' );
        } catch ( WPAjaxDieStopException $e ) {
        }

        $response = json_decode( $this->_last_response, true );
        $this->assertTrue( $response['success'] );
        $this->assertEquals( 'Custom field deleted successfully!', $response['data'] );

        // Verify the field was deleted.
        $saved_fields = get_option( 'hms_custom_fields', array() );
        $this->assertCount( 0, $saved_fields );
    }

    /**
     * Test getting custom fields for a specific context.
     */
    public function test_get_custom_fields_for_context() {
        // Add multiple fields for different contexts.
        $fields = array(
            array(
                'context' => 'patient_registration', 'name' => 'p_field1', 'label' => 'P Field 1', 'order' => 2
            ),
            array(
                'context' => 'patient_registration', 'name' => 'p_field2', 'label' => 'P Field 2', 'order' => 1
            ),
            array(
                'context' => 'appointment', 'name' => 'a_field1', 'label' => 'A Field 1', 'order' => 1
            ),
        );
        update_option( 'hms_custom_fields', $fields );

        $patient_fields = $this->hms_custom_fields->get_custom_fields( 'patient_registration' );
        $this->assertCount( 2, $patient_fields );
        // Check if they are ordered correctly.
        $this->assertEquals( 'P Field 2', $patient_fields[0]['label'] );
        $this->assertEquals( 'P Field 1', $patient_fields[1]['label'] );

        $appointment_fields = $this->hms_custom_fields->get_custom_fields( 'appointment' );
        $this->assertCount( 1, $appointment_fields );
        $this->assertEquals( 'A Field 1', $appointment_fields[0]['label'] );

        $billing_fields = $this->hms_custom_fields->get_custom_fields( 'billing' );
        $this->assertCount( 0, $billing_fields );
    }

    /**
     * Test the nonce check for AJAX handlers.
     */
    public function test_ajax_nonce_check_failure() {
        $this->_setRole( 'administrator' );

        $_POST['action'] = 'hms_save_custom_field';
        $_POST['field_context'] = 'patient_registration';
        // Intentionally omit or set an incorrect nonce.
        $_POST['nonce'] = 'incorrect_nonce';

        try {
            $this->_handleAjax( 'hms_save_custom_field' );
            $this->fail( 'Expected WPAjaxDieStopException for nonce failure.' );
        } catch ( WPAjaxDieStopException $e ) {
            $response = json_decode( $this->_last_response, true );
            $this->assertFalse( $response['success'] );
            $this->assertStringContainsString( 'Nonce check failed', $response['data'] );
        }
    }

    /**
     * Test the capability check for AJAX handlers.
     */
    public function test_ajax_capability_check_failure() {
        $this->_setRole( 'subscriber' ); // User without 'manage_options'.

        $_POST['nonce'] = wp_create_nonce( 'hms_custom_fields_nonce' );
        $_POST['action'] = 'hms_save_custom_field';
        $_POST['field_context'] = 'patient_registration';

        try {
            $this->_handleAjax( 'hms_save_custom_field' );
            $this->fail( 'Expected WPAjaxDieStopException for capability failure.' );
        } catch ( WPAjaxDieStopException $e ) {
            $response = json_decode( $this->_last_response, true );
            $this->assertFalse( $response['success'] );
            $this->assertStringContainsString( 'You do not have sufficient permissions', $response['data'] );
        }
    }
}
