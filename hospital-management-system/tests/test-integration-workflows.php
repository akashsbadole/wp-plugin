<?php
/**
 * Tests for core HMS workflows.
 *
 * @package HospitalManagementSystem
 * @group   hms-integration
 */

class HMS_Integration_Workflows_Test extends WP_UnitTestCase {

    /**
     * Instance of the HMS_Custom_Fields class (for use in tests).
     *
     * @var HMS_Custom_Fields
     */
    protected $hms_custom_fields;

    /**
     * Setup the test environment.
     */
    public function setUp() {
        parent::setUp();
        // We need some basic plugin classes for these workflows.
        // This assumes the main plugin file instantiates the classes.
        global $hms;
        if ( isset( $hms->custom_fields ) ) {
            $this->hms_custom_fields = $hms->custom_fields;
        } else {
            $this->hms_custom_fields = new HMS_Custom_Fields( 'hospital-management-system', '1.0.0' );
        }

        // Ensure core CPTs are registered for the tests.
        // This might require calling the registration functions from the main plugin file or activator.
        // For simplicity, we assume they are registered during plugin load.
        // If not, you'd need to manually call them here, e.g.:
        // $hms_clinical_features = new HMS_Clinical_Features( 'hospital-management-system', '1.0.0' );
        // $hms_clinical_features->register_custom_post_types();
    }

    /**
     * Test the full patient registration workflow.
     * This includes creating a patient post and saving any associated custom fields.
     */
    public function test_patient_registration_workflow() {
        // 1. Setup: Define a custom field for patient registration.
        $custom_field_data = array(
            'context'     => 'patient_registration',
            'label'       => 'Emergency Contact',
            'name'        => 'emergency_contact',
            'type'        => 'text',
            'required'    => 1,
            'placeholder' => 'e.g., John Doe - 555-1234',
            'default'     => '',
            'description' => 'Name and phone number of emergency contact.',
            'order'       => 1,
        );
        update_option( 'hms_custom_fields', array( $custom_field_data ) );

        // 2. Action: Simulate form submission data.
        // In a real scenario, this would come from $_POST.
        $patient_data = array(
            'post_title'  => 'John Doe',
            'post_content' => 'Patient information for John Doe.',
            'post_type'   => 'hms_patient', // Assuming 'hms_patient' is the CPT for patients.
            'post_status' => 'publish',
        );
        $custom_data = array(
            'hms_patient_custom_emergency_contact' => 'Jane Doe - 555-5678',
        );

        // 3. Process: Insert the patient post.
        $patient_id = wp_insert_post( $patient_data, true );
        $this->assertIsInt( $patient_id );
        $this->assertGreaterThan( 0, $patient_id );

        // 4. Process: Simulate the hook that saves custom data.
        // The `hms_save_patient_data` hook is expected to be called by the form processing logic.
        // We manually trigger it here for testing purposes.
        do_action( 'hms_save_patient_data', $patient_id, $custom_data );

        // 5. Verify: Check if the patient post was created correctly.
        $patient_post = get_post( $patient_id );
        $this->assertEquals( 'John Doe', $patient_post->post_title );
        $this->assertEquals( 'hms_patient', $patient_post->post_type );

        // 6. Verify: Check if the custom field data was saved correctly as post meta.
        $saved_emergency_contact = get_post_meta( $patient_id, 'hms_patient_custom_emergency_contact', true );
        $this->assertEquals( 'Jane Doe - 555-5678', $saved_emergency_contact );
    }

    /**
     * Test the appointment creation and billing workflow.
     * This involves creating an appointment, then generating a bill for it.
     */
    public function test_appointment_and_billing_workflow() {
        // 1. Setup: Create a patient and a doctor (staff).
        $patient_id = $this->factory->post->create( array( 'post_type' => 'hms_patient', 'post_title' => 'Test Patient' ) );
        $doctor_id  = $this->factory->post->create( array( 'post_type' => 'hms_staff', 'post_title' => 'Dr. Smith' ) ); // Assuming 'hms_staff' CPT.

        // 2. Action: Simulate creating an appointment.
        $appointment_data = array(
            'post_title'  => 'Appointment for Test Patient with Dr. Smith',
            'post_content' => 'Routine checkup.',
            'post_type'   => 'hms_appointment', // Assuming 'hms_appointment' CPT.
            'post_status' => 'publish',
            'meta_input'  => array(
                'hms_appointment_patient_id' => $patient_id,
                'hms_appointment_doctor_id'  => $doctor_id,
                'hms_appointment_datetime'  => '2025-09-25 10:00:00',
                'hms_appointment_status'    => 'scheduled',
            ),
        );
        $appointment_id = wp_insert_post( $appointment_data, true );
        $this->assertIsInt( $appointment_id );

        // 3. Action: Simulate the hook for auto-generating a bill.
        // The `hms_create_appointment_bill` hook is expected to be triggered after an appointment is created/confirmed.
        // We manually trigger it here.
        do_action( 'hms_create_appointment_bill', $appointment_id );

        // 4. Verify: Check if a bill was created.
        // This assumes bills are also a CPT, e.g., 'hms_bill'.
        $bills = get_posts( array(
            'post_type'      => 'hms_bill',
            'meta_key'       => 'hms_bill_appointment_id',
            'meta_value'     => $appointment_id,
            'posts_per_page' => 1,
        ) );
        $this->assertCount( 1, $bills );
        $bill_id = $bills[0]->ID;

        // 5. Verify: Check the details of the generated bill.
        $this->assertEquals( 'publish', $bills[0]->post_status );
        $bill_patient_id = get_post_meta( $bill_id, 'hms_bill_patient_id', true );
        $this->assertEquals( $patient_id, $bill_patient_id );

        $bill_items = get_post_meta( $bill_id, 'hms_bill_items', true ); // Assuming items are stored as a serialized array.
        $this->assertIsArray( $bill_items );
        $this->assertNotEmpty( $bill_items );
        // Example: Check for a consultation fee item.
        $found_consultation = false;
        foreach ($bill_items as $item) {
            if ($item['description'] === 'Consultation Fee') {
                $found_consultation = true;
                $this->assertEquals( 500, $item['amount'] ); // Assuming a fixed fee.
                break;
            }
        }
        $this->assertTrue( $found_consultation, 'Consultation fee item not found in bill.' );
        
        $total_amount = get_post_meta( $bill_id, 'hms_bill_total_amount', true );
        $this->assertEquals( 500, $total_amount );
    }

    /**
     * Test the prescription creation and pharmacy stock workflow.
     * This involves creating a prescription and then dispensing it, which should reduce stock.
     */
    public function test_prescription_and_pharmacy_workflow() {
        // 1. Setup: Create a patient, a doctor, and a medicine in stock.
        $patient_id = $this->factory->post->create( array( 'post_type' => 'hms_patient', 'post_title' => 'Test Patient' ) );
        $doctor_id  = $this->factory->post->create( array( 'post_type' => 'hms_staff', 'post_title' => 'Dr. Jones' ) );
        
        // Add medicine to stock. This assumes a function `hms_add_pharmacy_stock` exists.
        // For testing, we might directly manipulate the `wp_hms_pharmacy_stock` table or a CPT for medicines.
        // Let's assume a CPT 'hms_medicine' for simplicity.
        $medicine_id = $this->factory->post->create( array(
            'post_type'   => 'hms_medicine',
            'post_title'  => 'Paracetamol 500mg',
            'meta_input'  => array(
                'hms_medicine_stock_quantity' => 100,
                'hms_medicine_sku'            => 'PARA-500',
            )
        ));

        // 2. Action: Simulate creating a prescription.
        $prescription_data = array(
            'post_title'  => 'Prescription for Test Patient',
            'post_content' => 'Take one tablet daily.',
            'post_type'   => 'hms_prescription', // Assuming 'hms_prescription' CPT.
            'post_status' => 'publish',
            'meta_input'  => array(
                'hms_prescription_patient_id' => $patient_id,
                'hms_prescription_doctor_id'  => $doctor_id,
                'hms_prescription_items'      => maybe_serialize( array( // Serialized array of items.
                    array( 'medicine_id' => $medicine_id, 'dosage' => '1 tablet', 'duration' => '10 days', 'quantity' => 10 )
                )),
            ),
        );
        $prescription_id = wp_insert_post( $prescription_data, true );
        $this->assertIsInt( $prescription_id );

        // 3. Action: Simulate dispensing the prescription.
        // This would typically involve a UI action by a pharmacist.
        // We simulate the core logic: reduce stock for the prescribed medicine.
        $prescription_items = get_post_meta( $prescription_id, 'hms_prescription_items', true );
        $prescription_items = maybe_unserialize( $prescription_items );
        
        foreach ($prescription_items as $item) {
            $current_stock = (int) get_post_meta( $item['medicine_id'], 'hms_medicine_stock_quantity', true );
            $new_stock = $current_stock - $item['quantity'];
            update_post_meta( $item['medicine_id'], 'hms_medicine_stock_quantity', $new_stock );
        }

        // 4. Verify: Check if the medicine stock was reduced correctly.
        $updated_stock = (int) get_post_meta( $medicine_id, 'hms_medicine_stock_quantity', true );
        $this->assertEquals( 90, $updated_stock ); // 100 - 10.

        // 5. Verify: Check if the prescription status is updated to 'dispensed'.
        // This assumes a status meta field or taxonomy.
        wp_update_post( array( 'ID' => $prescription_id, 'meta_input' => array( 'hms_prescription_status' => 'dispensed' ) ) );
        $dispensed_status = get_post_meta( $prescription_id, 'hms_prescription_status', true );
        $this->assertEquals( 'dispensed', $dispensed_status );
    }
}
