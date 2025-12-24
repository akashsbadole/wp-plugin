<?php
/**
 * HMS Billing Class
 *
 * Handles billing, invoice generation, and payment processing.
 * This class primarily interacts with the custom `wp_hms_bills` and `wp_hms_bill_items` tables.
 *
 * @package    Hospital_Management_System
 * @subpackage Hospital_Management_System/includes
 * @author     HMS Development Team
 * @since      1.0.0
 */

class HMS_Billing {

    /**
     * Constructor.
     * Hooks into WordPress actions for AJAX handling.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // AJAX hooks for billing management
        add_action('wp_ajax_hms_create_bill', array($this, 'ajax_create_bill'));
        add_action('wp_ajax_hms_update_bill', array($this, 'ajax_update_bill'));
        add_action('wp_ajax_hms_get_bill', array($this, 'ajax_get_bill'));
        add_action('wp_ajax_hms_get_bills_list', array($this, 'ajax_get_bills_list'));
        add_action('wp_ajax_hms_delete_bill', array($this, 'ajax_delete_bill'));

        // AJAX hooks for bill item management
        add_action('wp_ajax_hms_create_bill_item', array($this, 'ajax_create_bill_item'));
        add_action('wp_ajax_hms_update_bill_item', array($this, 'ajax_update_bill_item'));
        add_action('wp_ajax_hms_get_bill_item', array($this, 'ajax_get_bill_item'));
        add_action('wp_ajax_hms_delete_bill_item', array($this, 'ajax_delete_bill_item'));
    }

    /**
     * Get a single bill by ID from the custom table.
     *
     * @since    1.0.0
     * @param    int       $bill_id            The bill's database ID.
     * @return   object|null                   The bill data object, or null if not found.
     */
    public function get_bill(int $bill_id): ?object {
        return HMS_Helper::hms_get_row('hms_bills', array('id' => $bill_id), array('%d'));
    }

    /**
     * Get all bills, with optional pagination, search, and filtering.
     *
     * @since    1.0.0
     * @param    string    $search             Optional. A search term to filter bills.
     * @param    string    $status             Optional. Filter by bill status.
     * @param    string    $patient_id         Optional. Filter by patient ID.
     * @param    string    $date_from          Optional. Filter by bill date from (Y-m-d).
     * @param    string    $date_to            Optional. Filter by bill date to (Y-m-d).
     * @param    int       $offset             Optional. The offset for pagination.
     * @param    int       $per_page           Optional. The number of bills to return per page.
     * @return   array                          An array of bill data objects.
     */
    public function get_bills(string $search = '', string $status = '', string $patient_id = '', string $date_from = '', string $date_to = '', int $offset = 0, int $per_page = 20): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_bills';
        
        $where_clauses = array();
        $where_values = array();

        if (!empty($search)) {
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where_clauses[] = "(bill_number LIKE %s OR bill_id LIKE %s OR patient_id IN (SELECT id FROM {$wpdb->prefix}hms_patients WHERE first_name LIKE %s OR last_name LIKE %s OR patient_id LIKE %s))";
            $where_values = array_fill(0, 5, $search_term);
        }
        if (!empty($status)) {
            $where_clauses[] = "payment_status = %s";
            $where_values[] = $status;
        }
        if (!empty($patient_id)) {
            $where_clauses[] = "patient_id = %d";
            $where_values[] = intval($patient_id);
        }
        if (!empty($date_from)) {
            $where_clauses[] = "issue_date >= %s";
            $where_values[] = $date_from . ' 00:00:00';
        }
        if (!empty($date_to)) {
            $where_clauses[] = "issue_date <= %s";
            $where_values[] = $date_to . ' 23:59:59';
        }

        $where_clause = '';
        if (!empty($where_clauses)) {
            $where_clause = "WHERE " . implode(' AND ', $where_clauses);
        }

        $query = "SELECT b.*, p.first_name, p.last_name, p.patient_id as patient_number 
                  FROM {$table_name} b 
                  LEFT JOIN {$wpdb->prefix}hms_patients p ON b.patient_id = p.id 
                  {$where_clause} 
                  ORDER BY b.issue_date DESC, b.id DESC";
        
        if (!empty($per_page)) {
            $query .= " LIMIT %d OFFSET %d";
            $values = array_merge($where_values, array($per_page, $offset));
        } else {
            $values = $where_values;
        }
        
        if (!empty($values)) {
            return $wpdb->get_results($wpdb->prepare($query, $values));
        } else {
            return $wpdb->get_results($query);
        }
    }

    /**
     * Get total count of bills for pagination.
     *
     * @since    1.0.0
     * @param    string    $search             Optional. A search term to filter bills.
     * @param    string    $status             Optional. Filter by bill status.
     * @param    string    $patient_id         Optional. Filter by patient ID.
     * @param    string    $date_from          Optional. Filter by bill date from (Y-m-d).
     * @param    string    $date_to            Optional. Filter by bill date to (Y-m-d).
     * @return   int                             The total number of bills.
     */
    public function get_bills_count(string $search = '', string $status = '', string $patient_id = '', string $date_from = '', string $date_to = ''): int {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_bills';

        $where_clauses = array();
        $where_values = array();

        if (!empty($search)) {
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where_clauses[] = "(bill_id LIKE %s OR patient_id IN (SELECT id FROM {$wpdb->prefix}hms_patients WHERE first_name LIKE %s OR last_name LIKE %s OR patient_id LIKE %s))";
            $where_values = array_fill(0, 4, $search_term);
        }
        if (!empty($status)) {
            $where_clauses[] = "status = %s";
            $where_values[] = $status;
        }
        if (!empty($patient_id)) {
            $where_clauses[] = "patient_id = %d";
            $where_values[] = intval($patient_id);
        }
        if (!empty($date_from)) {
            $where_clauses[] = "issue_date >= %s";
            $where_values[] = $date_from . ' 00:00:00';
        }
        if (!empty($date_to)) {
            $where_clauses[] = "issue_date <= %s";
            $where_values[] = $date_to . ' 23:59:59';
        }
        
        $where_clause = '';
        if (!empty($where_clauses)) {
            $where_clause = "WHERE " . implode(' AND ', $where_clauses);
        }

        $query = "SELECT COUNT(*) FROM {$table_name} {$where_clause}";
        return (int) $wpdb->get_var($wpdb->prepare($query, $where_values));
    }

    /**
     * Create a new bill.
     *
     * @since    1.0.0
     * @param    array     $data              An associative array of bill data.
     * @return   int|false                     The new bill's database ID, or false on failure.
     */
    public function create_bill(array $data) {
        global $wpdb;
        $table_name = 'hms_bills';

        // Basic validation
        if (empty($data['patient_id']) || empty($data['issue_date'])) {
            return false;
        }

        // Start transaction to prevent race conditions
        $wpdb->query('START TRANSACTION');
        
        try {
            // Get the last bill number to generate a new unique one
            $last_bill = $wpdb->get_row("SELECT bill_number FROM {$wpdb->prefix}{$table_name} ORDER BY id DESC LIMIT 1");
            $last_number = 0;
            
            if ($last_bill && !empty($last_bill->bill_number)) {
                // Extract numeric part from bill_number (e.g., BIL000002 -> 2)
                $last_number = intval(preg_replace('/[^0-9]/', '', $last_bill->bill_number));
            }
            
            // Generate new bill number with prefix
            $new_bill_number = 'BIL' . str_pad($last_number + 1, 6, '0', STR_PAD_LEFT);
            
            // Calculate amounts
            $total_amount = !empty($data['total_amount']) ? floatval($data['total_amount']) : 0.00;
            $discount_amount = !empty($data['discount_amount']) ? floatval($data['discount_amount']) : 0.00;
            $tax_percentage = !empty($data['tax_percentage']) ? floatval($data['tax_percentage']) : 0.00;
            $tax_amount = !empty($data['tax_amount']) ? floatval($data['tax_amount']) : ($total_amount * $tax_percentage / 100);
            $net_amount = $total_amount - $discount_amount + $tax_amount;
            $paid_amount = !empty($data['paid_amount']) ? floatval($data['paid_amount']) : 0.00;
            
            // Determine payment status based on amounts
            $payment_status = 'unpaid';
            if ($paid_amount >= $net_amount) {
                $payment_status = 'paid';
            } elseif ($paid_amount > 0) {
                $payment_status = 'partially_paid';
            }
            
            $bill_data = array(
                'bill_id'           => $new_bill_number,
                'bill_number'       => $new_bill_number,
                'patient_id'        => intval($data['patient_id']),
                'appointment_id'    => !empty($data['appointment_id']) ? intval($data['appointment_id']) : null,
                'issue_date'        => sanitize_text_field($data['issue_date']),
                'due_date'          => !empty($data['due_date']) ? sanitize_text_field($data['due_date']) : null,
                'total_amount'      => $total_amount,
                'discount_amount'   => $discount_amount,
                'tax_percentage'    => $tax_percentage,
                'tax_amount'        => $tax_amount,
                'net_amount'        => $net_amount,
                'paid_amount'       => $paid_amount,
                'payment_status'    => !empty($data['payment_status']) ? sanitize_text_field($data['payment_status']) : $payment_status,
                'notes'             => !empty($data['notes']) ? sanitize_textarea_field($data['notes']) : null,
                'created_by'        => get_current_user_id(),
                'created_at'        => current_time('mysql'),
            );

            $format = array('%s', '%s', '%d', '%d', '%s', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%s', '%d', '%s');
            
            $bill_id = HMS_Helper::hms_create($table_name, $bill_data, $format);

            if ($bill_id) {
                // Commit transaction
                $wpdb->query('COMMIT');
                HMS_Helper::log_action('Bill Created', 'Bill', $bill_id, json_encode($bill_data));
                return $bill_id;
            } else {
                // Rollback on failure
                $wpdb->query('ROLLBACK');
                return false;
            }
        } catch (Exception $e) {
            // Rollback on exception
            $wpdb->query('ROLLBACK');
            error_log('HMS: Error creating bill: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing bill.
     *
     * @since    1.0.0
     * @param    int       $bill_id            The bill's database ID.
     * @param    array     $data              An associative array of bill data to update.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function update_bill(int $bill_id, array $data) {
        $table_name = 'hms_bills';
        $where = array('id' => $bill_id);
        $where_format = array('%d');

        $existing_bill = $this->get_bill($bill_id);
        if (!$existing_bill) {
            return new WP_Error('bill_not_found', esc_html__('Bill not found.', 'hospital-management-system'));
        }
        
        $bill_data = array();
        $format = array();

        // Handle numeric fields
        $numeric_fields = array('total_amount', 'discount_amount', 'tax_amount', 'tax_percentage', 'net_amount', 'paid_amount');
        $integer_fields = array('patient_id', 'appointment_id');
        $text_fields = array('issue_date', 'due_date', 'payment_status', 'notes');
        
        foreach ($numeric_fields as $field) {
            if (isset($data[$field])) {
                $bill_data[$field] = floatval($data[$field]);
                $format[] = '%f';
            }
        }
        
        foreach ($integer_fields as $field) {
            if (isset($data[$field])) {
                $bill_data[$field] = !empty($data[$field]) ? intval($data[$field]) : null;
                $format[] = $bill_data[$field] !== null ? '%d' : '%s';
            }
        }
        
        foreach ($text_fields as $field) {
            if (isset($data[$field])) {
                if ($field === 'notes') {
                    $bill_data[$field] = sanitize_textarea_field($data[$field]);
                } else {
                    $bill_data[$field] = sanitize_text_field($data[$field]);
                }
                $format[] = '%s';
            }
        }
        
        // Recalculate net amount and payment status if amounts changed
        if (isset($data['total_amount']) || isset($data['discount_amount']) || isset($data['tax_amount']) || isset($data['paid_amount'])) {
            $total = isset($bill_data['total_amount']) ? $bill_data['total_amount'] : floatval($existing_bill->total_amount);
            $discount = isset($bill_data['discount_amount']) ? $bill_data['discount_amount'] : floatval($existing_bill->discount_amount);
            $tax = isset($bill_data['tax_amount']) ? $bill_data['tax_amount'] : floatval($existing_bill->tax_amount);
            $paid = isset($bill_data['paid_amount']) ? $bill_data['paid_amount'] : floatval($existing_bill->paid_amount);
            
            $net_amount = $total - $discount + $tax;
            $bill_data['net_amount'] = $net_amount;
            
            // Auto-update payment status based on amounts
            if (!isset($data['payment_status'])) {
                if ($paid >= $net_amount && $net_amount > 0) {
                    $bill_data['payment_status'] = 'paid';
                } elseif ($paid > 0) {
                    $bill_data['payment_status'] = 'partially_paid';
                } else {
                    $bill_data['payment_status'] = 'unpaid';
                }
                $format[] = '%s';
            }
            
            if (!in_array('net_amount', array_keys($bill_data))) {
                $format[] = '%f';
            }
        }
        
        if (empty($bill_data)) {
            return true; // No data to update
        }

        $updated = HMS_Helper::hms_update($table_name, $bill_data, $where, $format, $where_format);
        
        if ($updated !== false) {
            HMS_Helper::log_action('Bill Updated', 'Bill', $bill_id, json_encode($bill_data));
            return true;
        }
        return false;
    }

    /**
     * Delete a bill and its items.
     *
     * @since    1.0.0
     * @param    int       $bill_id            The bill's database ID.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function delete_bill(int $bill_id) {
        global $wpdb;
        $bill = $this->get_bill($bill_id);
        if (!$bill) {
            return new WP_Error('bill_not_found', esc_html__('Bill not found.', 'hospital-management-system'));
        }

        try {
            $wpdb->query('START TRANSACTION');

            // Delete bill items first
            $items_deleted = $wpdb->delete($wpdb->prefix . 'hms_bill_items', array('bill_id' => $bill_id), array('%d'));
            if ($items_deleted === false) {
                throw new Exception(esc_html__('Failed to delete bill items.', 'hospital-management-system'));
            }

            // Delete the bill
            $bill_deleted = HMS_Helper::hms_delete('hms_bills', array('id' => $bill_id), array('%d'));
            if ($bill_deleted === false) {
                throw new Exception(esc_html__('Failed to delete bill.', 'hospital-management-system'));
            }

            $wpdb->query('COMMIT');
            HMS_Helper::log_action('Bill Deleted', 'Bill', $bill_id, json_encode($bill));
            return true;

        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('bill_deletion_failed', $e->getMessage());
        }
    }


    // --- Bill Item Management ---

    /**
     * Get a single bill item by ID.
     *
     * @since    1.0.0
     * @param    int       $item_id            The bill item's database ID.
     * @return   object|null                   The bill item data object, or null if not found.
     */
    public function get_bill_item(int $item_id): ?object {
        return HMS_Helper::hms_get_row('hms_bill_items', array('id' => $item_id), array('%d'));
    }

    /**
     * Get all items for a specific bill.
     *
     * @since    1.0.0
     * @param    int       $bill_id            The bill's database ID.
     * @return   array                          An array of bill item data objects.
     */
    public function get_bill_items(int $bill_id): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_bill_items';
        $query = $wpdb->prepare("SELECT * FROM {$table_name} WHERE bill_id = %d ORDER BY id ASC", array($bill_id));
        return $wpdb->get_results($query);
    }

    /**
     * Get all payments for a specific bill.
     *
     * @since    1.0.0
     * @param    int       $bill_id            The bill's database ID.
     * @return   array                          An array of payment data objects.
     */
    public function get_bill_payments(int $bill_id): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_payments';
        $query = $wpdb->prepare("SELECT * FROM {$table_name} WHERE bill_id = %d ORDER BY payment_date DESC", array($bill_id));
        return $wpdb->get_results($query);
    }

    /**
     * Get bill with all related data (items, payments, patient info).
     *
     * @since    1.0.0
     * @param    int       $bill_id            The bill's database ID.
     * @return   object|null                   The complete bill data object, or null if not found.
     */
    public function get_bill_with_details(int $bill_id): ?object {
        $bill = $this->get_bill($bill_id);
        if (!$bill) {
            return null;
        }

        // Get bill items
        $bill->items = $this->get_bill_items($bill_id);
        
        // Get payments
        $bill->payments = $this->get_bill_payments($bill_id);
        
        // Get patient information
        $patient = HMS_Helper::hms_get_row('hms_patients', array('id' => $bill->patient_id), array('%d'));
        $bill->patient = $patient;
        
        return $bill;
    }

    /**
     * Add a new item to a bill.
     *
     * @since    1.0.0
     * @param    array     $data              An associative array of bill item data.
     * @return   int|false                     The new bill item's database ID, or false on failure.
     */
    public function create_bill_item(array $data) {
        $table_name = 'hms_bill_items';

        if (empty($data['bill_id']) || empty($data['description']) || !isset($data['quantity']) || !isset($data['price'])) {
            return false;
        }

        $item_data = array(
            'bill_id'        => intval($data['bill_id']),
            'description'    => sanitize_text_field($data['description']),
            'quantity'       => intval($data['quantity']),
            'price'          => floatval($data['price']),
            'created_at'     => current_time('mysql'),
        );

        $format = array('%d', '%s', '%d', '%f', '%s');
        
        $item_id = HMS_Helper::hms_create($table_name, $item_data, $format);

        if ($item_id) {
            // Optionally, update the parent bill's total_amount here
            // $this->update_bill_total_amount($data['bill_id']);
            HMS_Helper::log_action('Bill Item Created', 'Bill Item', $item_id, json_encode($item_data));
            return $item_id;
        }
        return false;
    }

    /**
     * Update an existing bill item.
     *
     * @since    1.0.0
     * @param    int       $item_id            The bill item's database ID.
     * @param    array     $data              An associative array of bill item data to update.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function update_bill_item(int $item_id, array $data) {
        $table_name = 'hms_bill_items';
        $where = array('id' => $item_id);
        $where_format = array('%d');

        $existing_item = $this->get_bill_item($item_id);
        if (!$existing_item) {
            return new WP_Error('bill_item_not_found', esc_html__('Bill item not found.', 'hospital-management-system'));
        }
        
        $item_data = array();
        $format = array();

        $fields_to_update = array('description', 'quantity', 'price');
        foreach ($fields_to_update as $field) {
            if (isset($data[$field])) {
                if ($field === 'description') {
                    $item_data[$field] = sanitize_text_field($data[$field]);
                } elseif ($field === 'quantity') {
                    $item_data[$field] = intval($data[$field]);
                } elseif ($field === 'price') {
                    $item_data[$field] = floatval($data[$field]);
                }
                if ($field === 'price') {
                    $format[] = '%f'; // Use %f for floats
                } else {
                    $format[] = '%s'; // Use %s for others
                }
            }
        }
        
        if (empty($item_data)) {
            return true; // No data to update
        }

        $updated = HMS_Helper::hms_update($table_name, $item_data, $where, $format, $where_format);
        
        if ($updated !== false) {
            // Optionally, update the parent bill's total_amount here
            // $this->update_bill_total_amount($existing_item->bill_id);
            HMS_Helper::log_action('Bill Item Updated', 'Bill Item', $item_id, json_encode($item_data));
            return true;
        }
        return false;
    }

    /**
     * Delete a bill item.
     *
     * @since    1.0.0
     * @param    int       $item_id            The bill item's database ID.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function delete_bill_item(int $item_id) {
        $existing_item = $this->get_bill_item($item_id);
        if (!$existing_item) {
            return new WP_Error('bill_item_not_found', esc_html__('Bill item not found.', 'hospital-management-system'));
        }

        $deleted = HMS_Helper::hms_delete('hms_bill_items', array('id' => $item_id), array('%d'));
        
        if ($deleted !== false) {
            // Optionally, update the parent bill's total_amount here
            // $this->update_bill_total_amount($existing_item->bill_id);
            HMS_Helper::log_action('Bill Item Deleted', 'Bill Item', $item_id, json_encode($existing_item));
            return true;
        }
        return false;
    }

    /**
     * Helper function to recalculate and update the total amount for a bill.
     * This would typically be called after an item is added, updated, or deleted.
     *
     * @since    1.0.0
     * @param    int       $bill_id            The bill's database ID.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    private function update_bill_total_amount(int $bill_id) {
        $bill_items = $this->get_bill_items($bill_id);
        $new_total = 0;
        foreach ($bill_items as $item) {
            $new_total += floatval($item->quantity) * floatval($item->price);
        }
        
        $updated = $this->update_bill($bill_id, array('total_amount' => $new_total));
        if (is_wp_error($updated)) {
            return $updated;
        }
        return true;
    }

    /**
     * Process a payment for a bill.
     *
     * @since    1.0.0
     * @param    int       $bill_id            The bill's database ID.
     * @param    array     $payment_data       Payment information.
     * @return   int|false                     The payment ID on success, false on failure.
     */
    public function process_payment(int $bill_id, array $payment_data) {
        global $wpdb;
        
        $bill = $this->get_bill($bill_id);
        if (!$bill) {
            return false;
        }
        
        $amount = floatval($payment_data['amount']);
        if ($amount <= 0) {
            return false;
        }
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        try {
            // Create payment record
            $payment_record = array(
                'bill_id' => $bill_id,
                'amount' => $amount,
                'payment_method' => sanitize_text_field($payment_data['payment_method']),
                'payment_date' => !empty($payment_data['payment_date']) ? sanitize_text_field($payment_data['payment_date']) : current_time('mysql'),
                'transaction_id' => !empty($payment_data['transaction_id']) ? sanitize_text_field($payment_data['transaction_id']) : null,
                'notes' => !empty($payment_data['notes']) ? sanitize_textarea_field($payment_data['notes']) : null,
                'created_by' => get_current_user_id(),
                'created_at' => current_time('mysql'),
            );
            
            $payment_format = array('%d', '%f', '%s', '%s', '%s', '%s', '%d', '%s');
            $payment_id = HMS_Helper::hms_create('hms_payments', $payment_record, $payment_format);
            
            if (!$payment_id) {
                throw new Exception('Failed to create payment record');
            }
            
            // Update bill paid amount
            $new_paid_amount = floatval($bill->paid_amount) + $amount;
            $net_amount = floatval($bill->net_amount);
            
            // Determine new payment status
            $payment_status = 'unpaid';
            if ($new_paid_amount >= $net_amount) {
                $payment_status = 'paid';
            } elseif ($new_paid_amount > 0) {
                $payment_status = 'partially_paid';
            }
            
            $update_result = $this->update_bill($bill_id, array(
                'paid_amount' => $new_paid_amount,
                'payment_status' => $payment_status
            ));
            
            if (is_wp_error($update_result)) {
                throw new Exception($update_result->get_error_message());
            }
            
            $wpdb->query('COMMIT');
            HMS_Helper::log_action('Payment Processed', 'Payment', $payment_id, json_encode($payment_record));
            return $payment_id;
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            error_log('HMS: Error processing payment: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate invoice HTML for a bill.
     *
     * @since    1.0.0
     * @param    int       $bill_id            The bill's database ID.
     * @param    bool      $for_print          Whether this is for printing.
     * @return   string|false                  The invoice HTML or false on failure.
     */
    public function generate_invoice_html(int $bill_id, bool $for_print = false) {
        $bill = $this->get_bill_with_details($bill_id);
        if (!$bill) {
            return false;
        }
        
        ob_start();
        
        // Hospital information
        $hospital_name = get_option('hms_hospital_name', 'Hospital Management System');
        $hospital_address = get_option('hms_hospital_address', '');
        $hospital_phone = get_option('hms_hospital_phone', '');
        $hospital_email = get_option('hms_hospital_email', '');
        
        ?>
        <div class="invoice-container" style="font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px;">
            <div class="invoice-header" style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px;">
                <h1 style="margin: 0; color: #333;"><?php echo esc_html($hospital_name); ?></h1>
                <?php if ($hospital_address): ?>
                    <p style="margin: 5px 0;"><?php echo esc_html($hospital_address); ?></p>
                <?php endif; ?>
                <p style="margin: 5px 0;">
                    <?php if ($hospital_phone): ?>
                        Tel: <?php echo esc_html($hospital_phone); ?>
                    <?php endif; ?>
                    <?php if ($hospital_email): ?>
                        | Email: <?php echo esc_html($hospital_email); ?>
                    <?php endif; ?>
                </p>
            </div>
            
            <div class="invoice-title" style="text-align: center; margin-bottom: 30px;">
                <h2 style="margin: 0; color: #333;">INVOICE</h2>
            </div>
            
            <div class="invoice-details" style="display: flex; justify-content: space-between; margin-bottom: 30px;">
                <div class="bill-to">
                    <h3 style="margin-bottom: 10px;">Bill To:</h3>
                    <?php if ($bill->patient): ?>
                        <p style="margin: 2px 0;"><strong><?php echo esc_html($bill->patient->first_name . ' ' . $bill->patient->last_name); ?></strong></p>
                        <p style="margin: 2px 0;">Patient ID: <?php echo esc_html($bill->patient->patient_id); ?></p>
                        <?php if (!empty($bill->patient->address)): ?>
                            <p style="margin: 2px 0;"><?php echo esc_html($bill->patient->address); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($bill->patient->contact_number)): ?>
                            <p style="margin: 2px 0;">Phone: <?php echo esc_html($bill->patient->contact_number); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <div class="invoice-info" style="text-align: right;">
                    <p style="margin: 2px 0;"><strong>Invoice #:</strong> <?php echo esc_html($bill->bill_number); ?></p>
                    <p style="margin: 2px 0;"><strong>Date:</strong> <?php echo date_i18n(get_option('date_format'), strtotime($bill->issue_date)); ?></p>
                    <?php if (!empty($bill->due_date)): ?>
                        <p style="margin: 2px 0;"><strong>Due Date:</strong> <?php echo date_i18n(get_option('date_format'), strtotime($bill->due_date)); ?></p>
                    <?php endif; ?>
                    <p style="margin: 2px 0;"><strong>Status:</strong> <?php echo esc_html(ucwords(str_replace('_', ' ', $bill->payment_status))); ?></p>
                </div>
            </div>
            
            <table class="invoice-items" style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
                <thead>
                    <tr style="background-color: #f8f9fa;">
                        <th style="border: 1px solid #dee2e6; padding: 12px; text-align: left;">Description</th>
                        <th style="border: 1px solid #dee2e6; padding: 12px; text-align: center;">Qty</th>
                        <th style="border: 1px solid #dee2e6; padding: 12px; text-align: right;">Unit Price</th>
                        <th style="border: 1px solid #dee2e6; padding: 12px; text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bill->items)): ?>
                        <?php foreach ($bill->items as $item): ?>
                            <tr>
                                <td style="border: 1px solid #dee2e6; padding: 12px;"><?php echo esc_html($item->description); ?></td>
                                <td style="border: 1px solid #dee2e6; padding: 12px; text-align: center;"><?php echo esc_html($item->quantity); ?></td>
                                <td style="border: 1px solid #dee2e6; padding: 12px; text-align: right;"><?php echo esc_html(number_format($item->price, 2)); ?></td>
                                <td style="border: 1px solid #dee2e6; padding: 12px; text-align: right;"><?php echo esc_html(number_format($item->quantity * $item->price, 2)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="border: 1px solid #dee2e6; padding: 12px; text-align: center;">No items found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="border: 1px solid #dee2e6; padding: 12px; text-align: right;"><strong>Subtotal:</strong></td>
                        <td style="border: 1px solid #dee2e6; padding: 12px; text-align: right;"><?php echo esc_html(number_format($bill->total_amount - $bill->tax_amount + $bill->discount_amount, 2)); ?></td>
                    </tr>
                    <?php if ($bill->discount_amount > 0): ?>
                        <tr>
                            <td colspan="3" style="border: 1px solid #dee2e6; padding: 12px; text-align: right;"><strong>Discount:</strong></td>
                            <td style="border: 1px solid #dee2e6; padding: 12px; text-align: right;">-<?php echo esc_html(number_format($bill->discount_amount, 2)); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if ($bill->tax_amount > 0): ?>
                        <tr>
                            <td colspan="3" style="border: 1px solid #dee2e6; padding: 12px; text-align: right;"><strong>Tax (<?php echo esc_html($bill->tax_percentage); ?>%):</strong></td>
                            <td style="border: 1px solid #dee2e6; padding: 12px; text-align: right;"><?php echo esc_html(number_format($bill->tax_amount, 2)); ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr style="background-color: #f8f9fa;">
                        <td colspan="3" style="border: 1px solid #dee2e6; padding: 12px; text-align: right;"><strong>Total Amount:</strong></td>
                        <td style="border: 1px solid #dee2e6; padding: 12px; text-align: right; font-size: 1.2em;"><strong><?php echo esc_html(number_format($bill->net_amount, 2)); ?></strong></td>
                    </tr>
                    <tr>
                        <td colspan="3" style="border: 1px solid #dee2e6; padding: 12px; text-align: right;"><strong>Amount Paid:</strong></td>
                        <td style="border: 1px solid #dee2e6; padding: 12px; text-align: right;"><?php echo esc_html(number_format($bill->paid_amount, 2)); ?></td>
                    </tr>
                    <tr style="background-color: #fff3cd;">
                        <td colspan="3" style="border: 1px solid #dee2e6; padding: 12px; text-align: right;"><strong>Balance Due:</strong></td>
                        <td style="border: 1px solid #dee2e6; padding: 12px; text-align: right; font-weight: bold; color: #856404;"><strong><?php echo esc_html(number_format($bill->net_amount - $bill->paid_amount, 2)); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
            
            <?php if (!empty($bill->notes)): ?>
                <div class="invoice-notes" style="margin-bottom: 30px;">
                    <h4>Notes:</h4>
                    <p><?php echo nl2br(esc_html($bill->notes)); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="invoice-footer" style="text-align: center; margin-top: 50px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                <p>Thank you for your business!</p>
            </div>
        </div>
        
        <?php if ($for_print): ?>
            <script>
                window.onload = function() {
                    window.print();
                };
            </script>
        <?php endif; ?>
        <?php
        
        return ob_get_clean();
    }


    // --- Automatic Billing Integration ---

    /**
     * Automatically generate a bill for a completed appointment.
     * This will check for applicable consultation fees and add them as bill items.
     *
     * @since    1.0.0
     * @param    int       $appointment_id    The appointment ID.
     * @return   int|false                     The new bill's database ID, or false on failure.
     */
    public function auto_generate_bill_for_appointment(int $appointment_id) {
        global $wpdb;
        $appointment_table = $wpdb->prefix . 'hms_appointments';
        $appointment = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$appointment_table} WHERE id = %d", $appointment_id));

        if (!$appointment || $appointment->status !== 'completed') {
            // Only generate bill for completed appointments
            return false;
        }

        // Check if a bill already exists for this appointment to avoid duplicates
        $existing_bill = HMS_Helper::hms_get_row('hms_bills', array('appointment_id' => $appointment_id), array('%d'));
        if ($existing_bill) {
            return $existing_bill->id; // Return existing bill ID
        }

        // Get doctor's consultation fee (this could be from staff table or a service fee table)
        $doctor_fee = $this->get_doctor_consultation_fee($appointment->doctor_id);
        $total_amount = $doctor_fee;

        $bill_data = array(
            'patient_id'     => $appointment->patient_id,
            'appointment_id' => $appointment_id,
            'issue_date'     => current_time('Y-m-d'),
            'total_amount'   => $total_amount,
            'paid_amount'    => 0.00,
            'payment_status' => 'unpaid',
            'status'         => 'unpaid',
            'created_by'     => get_current_user_id(),
        );

        $bill_id = $this->create_bill($bill_data);

        if ($bill_id) {
            // Add consultation fee as a bill item
            $item_data = array(
                'bill_id'     => $bill_id,
                'item_type'   => 'service', // Or 'consultation'
                /* translators: %s: Doctor name */
                'description' => sprintf(esc_html__('Consultation Fee - Dr. %s', 'hospital-management-system'), $this->get_doctor_name($appointment->doctor_id)),
                'quantity'    => 1,
                'unit_price'  => $doctor_fee,
                'total_price' => $doctor_fee,
            );
            $this->create_bill_item($item_data);
            HMS_Helper::log_action('Auto-generated Bill for Appointment', 'Bill', $bill_id, "Appointment ID: {$appointment_id}");
        }

        return $bill_id;
    }

    /**
     * Automatically generate a bill for a lab test.
     *
     * @since    1.0.0
     * @param    int       $patient_id        The patient ID.
     * @param    int       $lab_test_id       The lab test ID (from a tests table).
     * @param    int|null  $appointment_id    Optional. The appointment ID if linked.
     * @return   int|false                     The new bill's database ID, or false on failure.
     */
    public function auto_generate_bill_for_lab_test(int $patient_id, int $lab_test_id, ?int $appointment_id = null) {
        // Assuming a wp_hms_lab_tests or wp_hms_services table for test details
        $lab_test = HMS_Helper::hms_get_row('hms_services', array('id' => $lab_test_id, 'category' => 'Lab Test'), array('%d', '%s'));
        if (!$lab_test) {
            return false;
        }

        $total_amount = $lab_test->fee;

        $bill_data = array(
            'patient_id'     => $patient_id,
            'appointment_id' => $appointment_id,
            'issue_date'     => current_time('Y-m-d'),
            'total_amount'   => $total_amount,
            'paid_amount'    => 0.00,
            'payment_status' => 'unpaid',
            'status'         => 'unpaid',
            'created_by'     => get_current_user_id(),
        );

        $bill_id = $this->create_bill($bill_data);

        if ($bill_id) {
            $item_data = array(
                'bill_id'     => $bill_id,
                'item_type'   => 'lab_test',
                'item_id'     => $lab_test_id,
                'description' => $lab_test->name,
                'quantity'    => 1,
                'unit_price'  => $lab_test->fee,
                'total_price' => $lab_test->fee,
            );
            $this->create_bill_item($item_data);
            HMS_Helper::log_action('Auto-generated Bill for Lab Test', 'Bill', $bill_id, "Lab Test ID: {$lab_test_id}, Patient ID: {$patient_id}");
        }

        return $bill_id;
    }

    /**
     * Automatically generate a bill for dispensed medicine.
     *
     * @since    1.0.0
     * @param    int       $patient_id        The patient ID.
     * @param    int       $medicine_id       The medicine ID.
     * @param    int       $quantity          The quantity dispensed.
     * @param    int|null  $appointment_id    Optional. The appointment ID if linked.
     * @return   int|false                     The new bill's database ID, or false on failure.
     */
    public function auto_generate_bill_for_pharmacy(int $patient_id, int $medicine_id, int $quantity, ?int $appointment_id = null) {
        $medicine = HMS_Helper::hms_get_row('hms_medicines', array('id' => $medicine_id), array('%d'));
        if (!$medicine) {
            return false;
        }

        $unit_price = $medicine->selling_price;
        $total_price = $unit_price * $quantity;

        $bill_data = array(
            'patient_id'     => $patient_id,
            'appointment_id' => $appointment_id,
            'issue_date'     => current_time('Y-m-d'),
            'total_amount'   => $total_price,
            'paid_amount'    => 0.00,
            'payment_status' => 'unpaid',
            'status'         => 'unpaid',
            'created_by'     => get_current_user_id(),
        );

        $bill_id = $this->create_bill($bill_data);

        if ($bill_id) {
            $item_data = array(
                'bill_id'     => $bill_id,
                'item_type'   => 'medicine',
                'item_id'     => $medicine_id,
                'description' => $medicine->name,
                'quantity'    => $quantity,
                'unit_price'  => $unit_price,
                'total_price' => $total_price,
            );
            $this->create_bill_item($item_data);
            HMS_Helper::log_action('Auto-generated Bill for Pharmacy', 'Bill', $bill_id, "Medicine ID: {$medicine_id}, Patient ID: {$patient_id}");
        }

        return $bill_id;
    }

    /**
     * Helper function to get doctor's consultation fee.
     * This is a placeholder. In a real scenario, this might come from the staff table,
     * a department table, or a specific services/fees table.
     *
     * @since    1.0.0
     * @param    int       $doctor_id         The doctor's staff ID.
     * @return   float                          The consultation fee.
     */
    private function get_doctor_consultation_fee(int $doctor_id): float {
        $doctor = HMS_Helper::hms_get_row('hms_staff', array('id' => $doctor_id), array('%d'));
        // Assuming a 'consultation_fee' field in hms_staff table or a related meta table.
        // For now, returning a default value or fetching from a generic service.
        if ($doctor && isset($doctor->consultation_fee)) {
            return floatval($doctor->consultation_fee);
        }
        
        // Fallback: get a general 'Consultation' service fee
        $service = HMS_Helper::hms_get_row('hms_services', array('name' => 'General Consultation'), array('%s'));
        if ($service) {
            return floatval($service->fee);
        }

        return 500.00; // Default fallback fee
    }

    /**
     * Helper function to get doctor's name.
     *
     * @since    1.0.0
     * @param    int       $doctor_id         The doctor's staff ID.
     * @return   string                         The doctor's full name.
     */
    private function get_doctor_name(int $doctor_id): string {
        $doctor = HMS_Helper::hms_get_row('hms_staff', array('id' => $doctor_id), array('%d'));
        if ($doctor) {
            return trim($doctor->first_name . ' ' . $doctor->last_name);
        }
        return esc_html__('Unknown Doctor', 'hospital-management-system');
    }

    // --- Multi-Mode Payment Processing ---

    /**
     * Handle Cash Payment.
     * This is a straightforward wrapper for process_payment.
     *
     * @since    1.0.0
     * @param    int       $bill_id            The bill ID.
     * @param    float     $amount             The amount being paid.
     * @param    string    $notes              Optional. Additional notes.
     * @return   int|false|WP_Error            The new payment record ID, or false/WP_Error on failure.
     */
    public function process_cash_payment(int $bill_id, float $amount, string $notes = '') {
        return $this->process_payment($bill_id, $amount, 'cash', '', $notes);
    }

    /**
     * Handle UPI Payment.
     * This is a placeholder. Actual UPI integration would involve a third-party API.
     *
     * @since    1.0.0
     * @param    int       $bill_id            The bill ID.
     * @param    float     $amount             The amount being paid.
     * @param    string    $transaction_id     The UPI transaction ID.
     * @param    string    $notes              Optional. Additional notes.
     * @return   int|false|WP_Error            The new payment record ID, or false/WP_Error on failure.
     */
    public function process_upi_payment(int $bill_id, float $amount, string $transaction_id, string $notes = '') {
        // In a real scenario, you would verify the transaction_id with the UPI provider here
        // For now, we assume the transaction_id is valid and proceed.
        return $this->process_payment($bill_id, $amount, 'upi', $transaction_id, $notes);
    }

    /**
     * Handle Card Payment.
     * This is a placeholder. Actual card integration would involve a payment gateway (Razorpay, Stripe, etc.).
     *
     * @since    1.0.0
     * @param    int       $bill_id            The bill ID.
     * @param    float     $amount             The amount being paid.
     * @param    string    $transaction_id     The payment gateway transaction ID.
     * @param    string    $notes              Optional. Additional notes.
     * @return   int|false|WP_Error            The new payment record ID, or false/WP_Error on failure.
     */
    public function process_card_payment(int $bill_id, float $amount, string $transaction_id, string $notes = '') {
        // In a real scenario, you would verify the transaction_id with the payment gateway here
        // For now, we assume the transaction_id is valid and proceed.
        return $this->process_payment($bill_id, $amount, 'card', $transaction_id, $notes);
    }

    /**
     * Handle Net Banking Payment.
     * This is a placeholder. Actual Net Banking integration would involve a payment gateway.
     *
     * @since    1.0.0
     * @param    int       $bill_id            The bill ID.
     * @param    float     $amount             The amount being paid.
     * @param    string    $transaction_id     The payment gateway transaction ID.
     * @param    string    $notes              Optional. Additional notes.
     * @return   int|false|WP_Error            The new payment record ID, or false/WP_Error on failure.
     */
    public function process_net_banking_payment(int $bill_id, float $amount, string $transaction_id, string $notes = '') {
        // In a real scenario, you would verify the transaction_id with the payment gateway here
        // For now, we assume the transaction_id is valid and proceed.
        return $this->process_payment($bill_id, $amount, 'net_banking', $transaction_id, $notes);
    }

    /**
     * Get all payments for a specific bill.
     *
     * @since    1.0.0
     * @param    int       $bill_id            The bill ID.
     * @return   array                          An array of payment data objects.
     */
    public function get_payments_for_bill(int $bill_id): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_payments';
        $query = $wpdb->prepare("SELECT * FROM {$table_name} WHERE bill_id = %d ORDER BY payment_date DESC", array($bill_id));
        return $wpdb->get_results($query);
    }


    // --- Insurance Integration ---

    /**
     * Check if a patient has a valid insurance policy for a given provider.
     * This is a placeholder. A real implementation would check a patient_insurance table.
     *
     * @since    1.0.0
     * @param    int       $patient_id            The patient's ID.
     * @param    int       $insurance_provider_id The insurance provider's ID.
     * @return   bool|WP_Error                   True if valid, false otherwise, or WP_Error.
     */
    public function is_patient_insured(int $patient_id, int $insurance_provider_id) {
        // Placeholder: Check a hypothetical 'wp_hms_patient_insurance' table.
        // For now, we'll assume a patient is insured if an insurance_provider_id is passed.
        // In a real scenario, you'd check policy validity, coverage, etc.
        if ($insurance_provider_id > 0) {
            // Add more checks here, e.g., policy expiry date, coverage for specific services.
            return true;
        }
        return false;
    }

    /**
     * Calculate the insured amount and patient's co-pay for a bill.
     * This is a simplified version. Real-world logic is more complex (deductibles, co-insurance, coverage limits).
     *
     * @since    1.0.0
     * @param    int       $bill_id               The bill's ID.
     * @return   array|WP_Error                  An array with 'insured_amount' and 'patient_amount', or WP_Error.
     */
    public function calculate_insurance_breakdown(int $bill_id) {
        $bill = $this->get_bill($bill_id);
        if (!$bill) {
            return new WP_Error('invalid_bill', esc_html__('Invalid bill ID.', 'hospital-management-system'));
        }

        if (empty($bill->insurance_provider_id) || !$this->is_patient_insured($bill->patient_id, $bill->insurance_provider_id)) {
            // No insurance or not insured for this provider
            return array(
                'insured_amount' => 0.00,
                'patient_amount' => floatval($bill->total_amount),
                'status' => 'self_pay'
            );
        }

        // Placeholder logic: Assume 80% insured, 20% patient co-pay.
        // This should be replaced with actual rules based on the insurance plan.
        $total_amount = floatval($bill->total_amount);
        $insurance_coverage_percentage = 80.00; // Get this from insurance plan details
        $insured_amount = ($total_amount * $insurance_coverage_percentage) / 100;
        $patient_amount = $total_amount - $insured_amount;

        return array(
            'insured_amount' => $insured_amount,
            'patient_amount' => $patient_amount,
            'status' => 'insurance_applicable'
        );
    }

    /**
     * Update a bill with insurance details and set status for pre-authorization.
     *
     * @since    1.0.0
     * @param    int       $bill_id               The bill's ID.
     * @param    int       $insurance_provider_id The insurance provider's ID.
     * @param    string    $policy_number         The patient's policy number.
     * @return   bool|WP_Error                   True on success, WP_Error on failure.
     */
    public function apply_insurance_to_bill(int $bill_id, int $insurance_provider_id, string $policy_number) {
        $bill = $this->get_bill($bill_id);
        if (!$bill) {
            return new WP_Error('invalid_bill', esc_html__('Invalid bill ID.', 'hospital-management-system'));
        }

        if (!$this->is_patient_insured($bill->patient_id, $insurance_provider_id)) {
            return new WP_Error('patient_not_insured', esc_html__('Patient is not insured with this provider.', 'hospital-management-system'));
        }

        $breakdown = $this->calculate_insurance_breakdown($bill_id);
        if (is_wp_error($breakdown)) {
            return $breakdown;
        }

        $update_data = array(
            'insurance_provider_id' => $insurance_provider_id,
            'policy_number' => sanitize_text_field($policy_number),
            'insured_amount' => $breakdown['insured_amount'],
            'due_amount' => $breakdown['patient_amount'], // Patient only pays co-pay initially
            'status' => 'pending_preauth', // Mark as pending pre-authorization
        );

        $updated = $this->update_bill($bill_id, $update_data);

        if ($updated) {
            HMS_Helper::log_action('Insurance Applied to Bill', 'Bill', $bill_id, json_encode($update_data));
            return true;
        }
        return false;
    }
    
    /**
     * Simulate a pre-authorization check.
     * This would typically involve an API call to the TPA/Insurance portal.
     * For now, it's a placeholder that auto-approves after a "check".
     *
     * @since    1.0.0
     * @param    int       $bill_id               The bill's ID.
     * @return   bool|WP_Error                   True if pre-auth is approved, WP_Error otherwise.
     */
    public function check_pre_authorization(int $bill_id) {
        $bill = $this->get_bill($bill_id);
        if (!$bill || $bill->status !== 'pending_preauth') {
            return new WP_Error('invalid_bill_status', esc_html__('Bill is not pending pre-authorization.', 'hospital-management-system'));
        }

        // Placeholder: Simulate an API call to TPA/Insurance
        // In a real scenario, this would send bill details and wait for a response.
        // For now, we'll simulate an approval.
        $preauth_approved = true; // Assume approval

        if ($preauth_approved) {
            $this->update_bill($bill_id, array(
                'status' => 'preauth_approved'
            ));
            HMS_Helper::log_action('Pre-authorization Approved', 'Bill', $bill_id);
            return true;
        } else {
            // Handle rejection
            $this->update_bill($bill_id, array(
                'status' => 'preauth_rejected'
            ));
            HMS_Helper::log_action('Pre-authorization Rejected', 'Bill', $bill_id);
            return new WP_Error('preauth_rejected', esc_html__('Pre-authorization was rejected.', 'hospital-management-system'));
        }
    }


    // --- AJAX Handlers for Bills ---

    public function ajax_create_bill() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_bills')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        // Basic server-side validation
        if (empty($_POST['patient_id']) || empty($_POST['bill_date'])) {
            wp_send_json_error(esc_html__('Please fill in all required fields.', 'hospital-management-system'));
        }

        $data = array(
            'patient_id' => intval($_POST['patient_id']),
            'appointment_id' => !empty($_POST['appointment_id']) ? intval($_POST['appointment_id']) : null,
            'issue_date' => sanitize_text_field($_POST['issue_date']),
            'due_date' => !empty($_POST['due_date']) ? sanitize_text_field($_POST['due_date']) : null,
            'total_amount' => !empty($_POST['total_amount']) ? floatval($_POST['total_amount']) : 0.00,
            'discount_amount' => !empty($_POST['discount_amount']) ? floatval($_POST['discount_amount']) : 0.00,
            'tax_amount' => !empty($_POST['tax_amount']) ? floatval($_POST['tax_amount']) : 0.00,
            'tax_percentage' => !empty($_POST['tax_percentage']) ? floatval($_POST['tax_percentage']) : 0.00,
            'paid_amount' => !empty($_POST['paid_amount']) ? floatval($_POST['paid_amount']) : 0.00,
            'payment_method' => !empty($_POST['payment_method']) ? sanitize_text_field($_POST['payment_method']) : null,
            'payment_status' => !empty($_POST['payment_status']) ? sanitize_text_field($_POST['payment_status']) : 'unpaid',
            'status' => sanitize_text_field($_POST['status']),
            'insurance_provider_id' => !empty($_POST['insurance_provider_id']) ? intval($_POST['insurance_provider_id']) : null,
            'notes' => sanitize_textarea_field($_POST['notes']),
        );

        $bill_id = $this->create_bill($data);

        if (is_wp_error($bill_id)) {
            wp_send_json_error($bill_id->get_error_message());
        } elseif ($bill_id) {
            $bill = $this->get_bill($bill_id);
            wp_send_json_success(array(
                'message' => esc_html__('Bill created successfully.', 'hospital-management-system'),
                'bill' => $bill,
                'redirect_url' => admin_url('admin.php?page=hms_billing&message=' . urlencode(__('Bill created successfully.', 'hospital-management-system')) . '&type=success')
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to create bill.', 'hospital-management-system'));
        }
    }
    public function ajax_update_bill() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_bills')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $bill_id = intval($_POST['bill_id']);
        
        // Basic server-side validation
        if (empty($_POST['patient_id']) || empty($_POST['issue_date'])) {
            wp_send_json_error(esc_html__('Please fill in all required fields.', 'hospital-management-system'));
        }

        $data = array(
            'patient_id' => intval($_POST['patient_id']),
            'appointment_id' => !empty($_POST['appointment_id']) ? intval($_POST['appointment_id']) : null,
            'issue_date' => sanitize_text_field($_POST['issue_date']),
            'due_date' => !empty($_POST['due_date']) ? sanitize_text_field($_POST['due_date']) : null,
            'total_amount' => !empty($_POST['total_amount']) ? floatval($_POST['total_amount']) : 0.00,
            'discount_amount' => !empty($_POST['discount_amount']) ? floatval($_POST['discount_amount']) : 0.00,
            'tax_amount' => !empty($_POST['tax_amount']) ? floatval($_POST['tax_amount']) : 0.00,
            'tax_percentage' => !empty($_POST['tax_percentage']) ? floatval($_POST['tax_percentage']) : 0.00,
            'paid_amount' => !empty($_POST['paid_amount']) ? floatval($_POST['paid_amount']) : 0.00,
            'payment_method' => !empty($_POST['payment_method']) ? sanitize_text_field($_POST['payment_method']) : null,
            'payment_status' => sanitize_text_field($_POST['payment_status']),
            'status' => sanitize_text_field($_POST['status']),
            'insurance_provider_id' => !empty($_POST['insurance_provider_id']) ? intval($_POST['insurance_provider_id']) : null,
            'notes' => sanitize_textarea_field($_POST['notes']),
        );

        $updated = $this->update_bill($bill_id, $data);

        if (is_wp_error($updated)) {
            wp_send_json_error($updated->get_error_message());
        } elseif ($updated) {
            $bill = $this->get_bill($bill_id);
            wp_send_json_success(array(
                'message' => esc_html__('Bill updated successfully.', 'hospital-management-system'),
                'bill' => $bill,
                'redirect_url' => admin_url('admin.php?page=hms_billing&message=' . urlencode(__('Bill updated successfully.', 'hospital-management-system')) . '&type=success')
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to update bill.', 'hospital-management-system'));
        }
    }
    public function ajax_get_bill() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_bills')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }
        $bill_id = intval($_GET['bill_id']);
        $bill = $this->get_bill($bill_id);
        if ($bill) {
            wp_send_json_success($bill);
        } else {
            wp_send_json_error(esc_html__('Bill not found.', 'hospital-management-system'));
        }
    }
    public function ajax_get_bills_list() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_bills')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $args = array(
            'search' => isset($_GET['search']['value']) ? sanitize_text_field($_GET['search']['value']) : '',
            'status' => isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '',
            'patient_id' => isset($_GET['patient_id']) ? intval($_GET['patient_id']) : '',
            'date_from' => isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '',
            'date_to' => isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '',
            'offset' => isset($_GET['start']) ? intval($_GET['start']) : 0,
            'per_page' => isset($_GET['length']) ? intval($_GET['length']) : 20,
        );
        
        $bills = $this->get_bills($args['search'], $args['status'], $args['patient_id'], $args['date_from'], $args['date_to'], $args['offset'], $args['per_page']);
        $total_bills = $this->get_bills_count($args['search'], $args['status'], $args['patient_id'], $args['date_from'], $args['date_to']);
        
        $data = array();
        foreach ($bills as $bill) {
            // Get patient name
            $patient = HMS_Helper::hms_get_row('hms_patients', array('id' => $bill->patient_id), array('%d'));
            $patient_name = $patient ? $patient->first_name . ' ' . $patient->last_name : __('N/A', 'hospital-management-system');
            
            $data[] = array(
                'id' => $bill->id,
                'bill_id' => $bill->bill_id,
                'patient_name' => $patient_name,
                'issue_date' => date_i18n(get_option('date_format'), strtotime($bill->issue_date)),
                'total_amount' => number_format($bill->total_amount, 2),
                'paid_amount' => number_format($bill->paid_amount, 2),
                'payment_status' => $bill->payment_status,
                'status' => $bill->status,
                'actions' => '' // Action buttons will be added by JS
            );
        }

        wp_send_json(array(
            'draw' => intval($_GET['draw']),
            'recordsTotal' => $total_bills,
            'recordsFiltered' => $total_bills,
            'data' => $data
        ));
    }
    public function ajax_delete_bill() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('delete_bills')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }
        $bill_id = intval($_POST['bill_id']);
        $deleted = $this->delete_bill($bill_id);
        if (is_wp_error($deleted)) {
            wp_send_json_error($deleted->get_error_message());
        } elseif ($deleted) {
            wp_send_json_success(array('message' => esc_html__('Bill deleted successfully.', 'hospital-management-system')));
        } else {
            wp_send_json_error(esc_html__('Failed to delete bill.', 'hospital-management-system'));
        }
    }


    // --- AJAX Handlers for Bill Items ---

    /**
     * AJAX handler to create a new bill item.
     */
    public function ajax_create_bill_item() {
        check_ajax_referer('hms_admin_nonce', 'nonce'); // Using a generic nonce for simplicity
        if (!current_user_can('manage_bills')) { // Using manage_bills capability
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $data = array(
            'bill_id'     => intval($_POST['bill_id']),
            'description' => sanitize_text_field($_POST['description']),
            'quantity'    => intval($_POST['quantity']),
            'price'       => floatval($_POST['price']),
        );

        $item_id = $this->create_bill_item($data);

        if (is_wp_error($item_id)) {
            wp_send_json_error($item_id->get_error_message());
        } elseif ($item_id) {
            $item = $this->get_bill_item($item_id);
            wp_send_json_success(array(
                'message' => esc_html__('Bill item added successfully.', 'hospital-management-system'),
                'item'    => $item
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to add bill item.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to update an existing bill item.
     */
    public function ajax_update_bill_item() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_bills')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $item_id = intval($_POST['item_id']);
        $data = array(
            'description' => sanitize_text_field($_POST['description']),
            'quantity'    => intval($_POST['quantity']),
            'price'       => floatval($_POST['price']),
        );

        $updated = $this->update_bill_item($item_id, $data);

        if (is_wp_error($updated)) {
            wp_send_json_error($updated->get_error_message());
        } elseif ($updated) {
            $item = $this->get_bill_item($item_id);
            wp_send_json_success(array(
                'message' => esc_html__('Bill item updated successfully.', 'hospital-management-system'),
                'item'    => $item
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to update bill item.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to get a single bill item's details.
     */
    public function ajax_get_bill_item() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_bills')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }
        $item_id = intval($_GET['item_id']);
        $item = $this->get_bill_item($item_id);
        if ($item) {
            wp_send_json_success($item);
        } else {
            wp_send_json_error(esc_html__('Bill item not found.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to delete a bill item.
     */
    public function ajax_delete_bill_item() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_bills')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $item_id = intval($_POST['item_id']);
        
        $deleted = $this->delete_bill_item($item_id);

        if (is_wp_error($deleted)) {
            wp_send_json_error($deleted->get_error_message());
        } elseif ($deleted) {
            wp_send_json_success(array(
                'message' => esc_html__('Bill item deleted successfully.', 'hospital-management-system')
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to delete bill item.', 'hospital-management-system'));
        }
    }
}
