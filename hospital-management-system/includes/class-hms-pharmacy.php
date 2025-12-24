<?php
/**
 * HMS Pharmacy Class
 *
 * Handles medicine dispensing, inventory management, and other pharmacy-related functionalities.
 * This class primarily interacts with the custom `wp_hms_medicines` table.
 * Note: Patient-facing AJAX handlers are located in the HMS_Public class.
 *
 * @package    Hospital_Management_System
 * @subpackage Hospital_Management_System/includes
 * @author     HMS Development Team
 * @since      1.0.0
 */

class HMS_Pharmacy {

    /**
     * Constructor.
     * Hooks into WordPress actions for AJAX handling.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // AJAX hooks for pharmacy management
        add_action('wp_ajax_hms_create_medicine', array($this, 'ajax_create_medicine'));
        add_action('wp_ajax_hms_update_medicine', array($this, 'ajax_update_medicine'));
        add_action('wp_ajax_hms_get_medicine', array($this, 'ajax_get_medicine'));
        add_action('wp_ajax_hms_delete_medicine', array($this, 'ajax_delete_medicine'));
        add_action('wp_ajax_hms_get_dispenses_list', array($this, 'ajax_get_dispenses_list'));
        add_action('wp_ajax_hms_get_prescription_for_dispense', array($this, 'ajax_get_prescription_for_dispense'));
        add_action('wp_ajax_hms_update_stock', array($this, 'ajax_update_stock'));
        
        // AJAX hooks for supplier management
        add_action('wp_ajax_hms_create_supplier', array($this, 'ajax_create_supplier'));
        add_action('wp_ajax_hms_update_supplier', array($this, 'ajax_update_supplier'));
        add_action('wp_ajax_hms_get_supplier', array($this, 'ajax_get_supplier'));
        add_action('wp_ajax_hms_delete_supplier', array($this, 'ajax_delete_supplier'));
        
        // AJAX hooks for purchase order management
        add_action('wp_ajax_hms_create_purchase_order', array($this, 'ajax_create_purchase_order'));
        add_action('wp_ajax_hms_update_purchase_order', array($this, 'ajax_update_purchase_order'));
        add_action('wp_ajax_hms_get_purchase_order', array($this, 'ajax_get_purchase_order'));
        add_action('wp_ajax_hms_delete_purchase_order', array($this, 'ajax_delete_purchase_order'));
        add_action('wp_ajax_hms_receive_purchase_order', array($this, 'ajax_receive_purchase_order'));
        
        // AJAX hooks for inventory alerts
        add_action('wp_ajax_hms_get_low_stock_alerts', array($this, 'ajax_get_low_stock_alerts'));
        add_action('wp_ajax_hms_get_expiry_alerts', array($this, 'ajax_get_expiry_alerts'));
    }

    // --- Medicine Management ---

    /**
     * Get a single medicine by ID.
     *
     * @since    1.0.0
     * @param    int       $medicine_id        The medicine's database ID.
     * @return   object|null                   The medicine data object, or null if not found.
     */
    public function get_medicine(int $medicine_id): ?object {
        return HMS_Helper::hms_get_row('hms_medicines', array('id' => $medicine_id), array('%d'));
    }

    /**
     * Get all medicines, with optional search and filtering.
     *
     * @since    1.0.0
     * @param    string    $search             Optional. A search term to filter medicines.
     * @param    string    $category           Optional. Filter by medicine category.
     * @param    string    $status             Optional. Filter by medicine status.
     * @param    int       $offset             Optional. The offset for pagination.
     * @param    int       $per_page           Optional. The number of medicines to return per page.
     * @return   array                          An array of medicine data objects.
     */
    public function get_medicines(string $search = '', string $category = '', string $status = '', int $offset = 0, int $per_page = 20): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_medicines';
        
        $where_clauses = array();
        $where_values = array();

        if (!empty($search)) {
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where_clauses[] = "(name LIKE %s OR generic_name LIKE %s OR manufacturer LIKE %s)";
            $where_values = array_fill(0, 3, $search_term);
        }
        if (!empty($category)) {
            $where_clauses[] = "category = %s";
            $where_values[] = $category;
        }
        if (!empty($status)) {
            $where_clauses[] = "status = %s";
            $where_values[] = $status;
        }

        $where_clause = '';
        if (!empty($where_clauses)) {
            $where_clause = "WHERE " . implode(' AND ', $where_clauses);
        }

        $query = "SELECT * FROM {$table_name} {$where_clause} ORDER BY name ASC LIMIT %d OFFSET %d";
        $values = array_merge($where_values, array($per_page, $offset));
        
        return $wpdb->get_results($wpdb->prepare($query, $values));
    }

    /**
     * Get total count of medicines for pagination.
     *
     * @since    1.0.0
     * @param    string    $search             Optional. A search term to filter medicines.
     * @param    string    $category           Optional. Filter by medicine category.
     * @param    string    $status             Optional. Filter by medicine status.
     * @return   int                             The total number of medicines.
     */
    public function get_medicines_count(string $search = '', string $category = '', string $status = ''): int {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_medicines';

        $where_clauses = array();
        $where_values = array();

        if (!empty($search)) {
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where_clauses[] = "(name LIKE %s OR generic_name LIKE %s OR manufacturer LIKE %s)";
            $where_values = array_fill(0, 3, $search_term);
        }
        if (!empty($category)) {
            $where_clauses[] = "category = %s";
            $where_values[] = $category;
        }
        if (!empty($status)) {
            $where_clauses[] = "status = %s";
            $where_values[] = $status;
        }
        
        $where_clause = '';
        if (!empty($where_clauses)) {
            $where_clause = "WHERE " . implode(' AND ', $where_clauses);
        }

        $query = "SELECT COUNT(*) FROM {$table_name} {$where_clause}";
        return (int) $wpdb->get_var($wpdb->prepare($query, $where_values));
    }

    /**
     * Create a new medicine.
     *
     * @since    1.0.0
     * @param    array     $data              An associative array of medicine data.
     * @return   int|false                     The new medicine's database ID, or false on failure.
     */
    public function create_medicine(array $data) {
        $table_name = 'hms_medicines';

        // Basic validation
        if (empty($data['name']) || empty($data['category']) || empty($data['unit']) || !isset($data['stock_quantity']) || !isset($data['reorder_level']) || !isset($data['unit_price']) || !isset($data['selling_price'])) {
            return false;
        }

        $medicine_data = array(
            'name'             => sanitize_text_field($data['name']),
            'generic_name'     => !empty($data['generic_name']) ? sanitize_text_field($data['generic_name']) : null,
            'category'         => sanitize_text_field($data['category']),
            'manufacturer'     => !empty($data['manufacturer']) ? sanitize_text_field($data['manufacturer']) : null,
            'unit'             => sanitize_text_field($data['unit']),
            'strength'         => !empty($data['strength']) ? sanitize_text_field($data['strength']) : null,
            'description'      => !empty($data['description']) ? sanitize_textarea_field($data['description']) : null,
            'stock_quantity'   => intval($data['stock_quantity']),
            'reorder_level'    => intval($data['reorder_level']),
            'unit_price'       => floatval($data['unit_price']),
            'selling_price'    => floatval($data['selling_price']),
            'batch_number'     => !empty($data['batch_number']) ? sanitize_text_field($data['batch_number']) : null,
            'expiry_date'      => !empty($data['expiry_date']) ? sanitize_text_field($data['expiry_date']) : null,
            'status'           => !empty($data['status']) ? sanitize_text_field($data['status']) : 'active',
            'created_at'       => current_time('mysql'),
        );

        $format = array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%f', '%f', '%s', '%s', '%s', '%s');
        
        $medicine_id = HMS_Helper::hms_create($table_name, $medicine_data, $format);

        if ($medicine_id) {
            HMS_Helper::log_action('Medicine Created', 'Medicine', $medicine_id, json_encode($medicine_data));
            return $medicine_id;
        }
        return false;
    }

    /**
     * Update an existing medicine.
     *
     * @since    1.0.0
     * @param    int       $medicine_id        The medicine's database ID.
     * @param    array     $data              An associative array of medicine data to update.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function update_medicine(int $medicine_id, array $data) {
        $table_name = 'hms_medicines';
        $where = array('id' => $medicine_id);
        $where_format = array('%d');

        $existing_medicine = $this->get_medicine($medicine_id);
        if (!$existing_medicine) {
            return new WP_Error('medicine_not_found', esc_html__('Medicine not found.', 'hospital-management-system'));
        }
        
        $medicine_data = array();
        $format = array();

        $fields_to_update = array('name', 'generic_name', 'category', 'manufacturer', 'unit', 'strength', 'description', 'stock_quantity', 'reorder_level', 'unit_price', 'selling_price', 'batch_number', 'expiry_date', 'status');
        foreach ($fields_to_update as $field) {
            if (isset($data[$field])) {
                if (in_array($field, array('stock_quantity', 'reorder_level'))) {
                    $medicine_data[$field] = intval($data[$field]);
                } elseif (in_array($field, array('unit_price', 'selling_price'))) {
                    $medicine_data[$field] = floatval($data[$field]);
                } elseif ($field === 'description') {
                    $medicine_data[$field] = sanitize_textarea_field($data[$field]);
                } else {
                    $medicine_data[$field] = sanitize_text_field($data[$field]);
                }
                if (in_array($field, array('unit_price', 'selling_price'))) {
                    $format[] = '%f'; // Use %f for floats
                } else {
                    $format[] = '%s'; // Use %s for others
                }
            }
        }
        
        if (empty($medicine_data)) {
            return true; // No data to update
        }

        $updated = HMS_Helper::hms_update($table_name, $medicine_data, $where, $format, $where_format);
        
        if ($updated !== false) {
            HMS_Helper::log_action('Medicine Updated', 'Medicine', $medicine_id, json_encode($medicine_data));
            return true;
        }
        return false;
    }

    /**
     * Delete a medicine.
     *
     * @since    1.0.0
     * @param    int       $medicine_id        The medicine's database ID.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function delete_medicine(int $medicine_id) {
        $existing_medicine = $this->get_medicine($medicine_id);
        if (!$existing_medicine) {
            return new WP_Error('medicine_not_found', esc_html__('Medicine not found.', 'hospital-management-system'));
        }

        // Add check here if medicine is part of a prescription or bill to prevent deletion if needed.
        // For now, we allow deletion.

        $deleted = HMS_Helper::hms_delete('hms_medicines', array('id' => $medicine_id), array('%d'));
        
        if ($deleted !== false) {
            HMS_Helper::log_action('Medicine Deleted', 'Medicine', $medicine_id, json_encode($existing_medicine));
            return true;
        }
        return false;
    }

    // --- Stock Management ---
    
    /**
     * Update the stock quantity for a given medicine.
     * This method will log the stock movement.
     *
     * @since    1.0.0
     * @param    int       $medicine_id        The ID of the medicine.
     * @param    int       $quantity_change    The quantity to add (positive) or subtract (negative).
     * @param    string    $reason             The reason for the stock change (e.g., 'Purchase', 'Dispense', 'Adjustment').
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function update_stock(int $medicine_id, int $quantity_change, string $reason) {
        $medicine = $this->get_medicine($medicine_id);
        if (!$medicine) {
            return new WP_Error('medicine_not_found', esc_html__('Medicine not found for stock update.', 'hospital-management-system'));
        }

        $new_stock_quantity = $medicine->stock_quantity + $quantity_change;

        if ($new_stock_quantity < 0) {
            return new WP_Error('insufficient_stock', esc_html__('Cannot reduce stock below zero.', 'hospital-management-system'));
        }

        $updated = $this->update_medicine($medicine_id, array('stock_quantity' => $new_stock_quantity));

        if ($updated !== false) {
            // Log the stock movement
            $log_data = array(
                'medicine_id'     => $medicine_id,
                'quantity_change' => $quantity_change,
                'previous_stock'  => $medicine->stock_quantity,
                'new_stock'       => $new_stock_quantity,
                'reason'          => sanitize_text_field($reason),
            );
            HMS_Helper::log_action('Stock Updated', 'Medicine', $medicine_id, json_encode($log_data));
            return true;
        }
        return false;
    }

    /**
     * Get a list of medicines that have low stock.
     * Low stock is defined as stock_quantity <= reorder_level.
     *
     * @since    1.0.0
     * @return   array                          An array of medicine data objects that are low in stock.
     */
    public function get_low_stock_medicines(): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_medicines';
        
        $query = "SELECT * FROM {$table_name} WHERE stock_quantity <= reorder_level AND status = 'active' ORDER BY name ASC";
        
        return $wpdb->get_results($query);
    }

    /**
     * Get medicines that are expiring within a specified number of days.
     *
     * @since    1.0.0
     * @param    int       $days_ahead         Number of days to check ahead for expiry.
     * @return   array                          An array of medicine data objects that are expiring.
     */
    public function get_expiring_medicines(int $days_ahead = 30): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_medicines';
        
        $expiry_date = date('Y-m-d', strtotime("+{$days_ahead} days"));
        
        $query = $wpdb->prepare(
            "SELECT * FROM {$table_name} 
             WHERE expiry_date IS NOT NULL 
             AND expiry_date <= %s 
             AND expiry_date >= CURDATE() 
             AND status = 'active' 
             ORDER BY expiry_date ASC",
            $expiry_date
        );
        
        return $wpdb->get_results($query);
    }

    // --- Supplier Management ---

    /**
     * Get a single supplier by ID.
     *
     * @since    1.0.0
     * @param    int       $supplier_id        The supplier's database ID.
     * @return   object|null                   The supplier data object, or null if not found.
     */
    public function get_supplier(int $supplier_id): ?object {
        return HMS_Helper::hms_get_row('hms_suppliers', array('id' => $supplier_id), array('%d'));
    }

    /**
     * Get all suppliers, with optional search and filtering.
     *
     * @since    1.0.0
     * @param    string    $search             Optional. A search term to filter suppliers.
     * @param    string    $status             Optional. Filter by supplier status.
     * @param    int       $offset             Optional. The offset for pagination.
     * @param    int       $per_page           Optional. The number of suppliers to return per page.
     * @return   array                          An array of supplier data objects.
     */
    public function get_suppliers(string $search = '', string $status = '', int $offset = 0, int $per_page = 20): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_suppliers';
        
        $where_clauses = array();
        $where_values = array();

        if (!empty($search)) {
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where_clauses[] = "(name LIKE %s OR contact_person LIKE %s OR email LIKE %s)";
            $where_values = array_fill(0, 3, $search_term);
        }
        if (!empty($status)) {
            $where_clauses[] = "status = %s";
            $where_values[] = $status;
        }

        $where_clause = '';
        if (!empty($where_clauses)) {
            $where_clause = "WHERE " . implode(' AND ', $where_clauses);
        }

        $query = "SELECT * FROM {$table_name} {$where_clause} ORDER BY name ASC LIMIT %d OFFSET %d";
        $values = array_merge($where_values, array($per_page, $offset));
        
        return $wpdb->get_results($wpdb->prepare($query, $values));
    }

    /**
     * Create a new supplier.
     *
     * @since    1.0.0
     * @param    array     $data              An associative array of supplier data.
     * @return   int|false                     The new supplier's database ID, or false on failure.
     */
    public function create_supplier(array $data) {
        $table_name = 'hms_suppliers';

        // Basic validation
        if (empty($data['name'])) {
            return false;
        }

        $supplier_data = array(
            'name'             => sanitize_text_field($data['name']),
            'contact_person'   => !empty($data['contact_person']) ? sanitize_text_field($data['contact_person']) : null,
            'email'            => !empty($data['email']) ? sanitize_email($data['email']) : null,
            'phone'            => !empty($data['phone']) ? sanitize_text_field($data['phone']) : null,
            'address'          => !empty($data['address']) ? sanitize_textarea_field($data['address']) : null,
            'city'             => !empty($data['city']) ? sanitize_text_field($data['city']) : null,
            'state'            => !empty($data['state']) ? sanitize_text_field($data['state']) : null,
            'postal_code'      => !empty($data['postal_code']) ? sanitize_text_field($data['postal_code']) : null,
            'country'          => !empty($data['country']) ? sanitize_text_field($data['country']) : null,
            'tax_number'       => !empty($data['tax_number']) ? sanitize_text_field($data['tax_number']) : null,
            'payment_terms'    => !empty($data['payment_terms']) ? sanitize_text_field($data['payment_terms']) : null,
            'status'           => !empty($data['status']) ? sanitize_text_field($data['status']) : 'active',
            'notes'            => !empty($data['notes']) ? sanitize_textarea_field($data['notes']) : null,
            'created_at'       => current_time('mysql'),
        );

        $format = array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');
        
        $supplier_id = HMS_Helper::hms_create($table_name, $supplier_data, $format);

        if ($supplier_id) {
            HMS_Helper::log_action('Supplier Created', 'Supplier', $supplier_id, json_encode($supplier_data));
            return $supplier_id;
        }
        return false;
    }

    /**
     * Update an existing supplier.
     *
     * @since    1.0.0
     * @param    int       $supplier_id        The supplier's database ID.
     * @param    array     $data              An associative array of supplier data to update.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function update_supplier(int $supplier_id, array $data) {
        $table_name = 'hms_suppliers';
        $where = array('id' => $supplier_id);
        $where_format = array('%d');

        $existing_supplier = $this->get_supplier($supplier_id);
        if (!$existing_supplier) {
            return new WP_Error('supplier_not_found', esc_html__('Supplier not found.', 'hospital-management-system'));
        }
        
        $supplier_data = array();
        $format = array();

        $fields_to_update = array('name', 'contact_person', 'email', 'phone', 'address', 'city', 'state', 'postal_code', 'country', 'tax_number', 'payment_terms', 'status', 'notes');
        foreach ($fields_to_update as $field) {
            if (isset($data[$field])) {
                if ($field === 'email') {
                    $supplier_data[$field] = sanitize_email($data[$field]);
                } elseif (in_array($field, array('address', 'notes'))) {
                    $supplier_data[$field] = sanitize_textarea_field($data[$field]);
                } else {
                    $supplier_data[$field] = sanitize_text_field($data[$field]);
                }
                $format[] = '%s';
            }
        }
        
        if (empty($supplier_data)) {
            return true; // No data to update
        }

        $updated = HMS_Helper::hms_update($table_name, $supplier_data, $where, $format, $where_format);
        
        if ($updated !== false) {
            HMS_Helper::log_action('Supplier Updated', 'Supplier', $supplier_id, json_encode($supplier_data));
            return true;
        }
        return false;
    }

    /**
     * Delete a supplier.
     *
     * @since    1.0.0
     * @param    int       $supplier_id        The supplier's database ID.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function delete_supplier(int $supplier_id) {
        $existing_supplier = $this->get_supplier($supplier_id);
        if (!$existing_supplier) {
            return new WP_Error('supplier_not_found', esc_html__('Supplier not found.', 'hospital-management-system'));
        }

        // Check if supplier has associated medicines or purchase orders
        global $wpdb;
        $medicine_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}hms_medicines WHERE supplier_id = %d",
            $supplier_id
        ));
        
        $po_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}hms_purchase_orders WHERE supplier_id = %d",
            $supplier_id
        ));

        if ($medicine_count > 0 || $po_count > 0) {
            return new WP_Error('supplier_has_dependencies', esc_html__('Cannot delete supplier with associated medicines or purchase orders.', 'hospital-management-system'));
        }

        $deleted = HMS_Helper::hms_delete('hms_suppliers', array('id' => $supplier_id), array('%d'));
        
        if ($deleted !== false) {
            HMS_Helper::log_action('Supplier Deleted', 'Supplier', $supplier_id, json_encode($existing_supplier));
            return true;
        }
        return false;
    }

    // --- Purchase Order Management ---

    /**
     * Get a single purchase order by ID.
     *
     * @since    1.0.0
     * @param    int       $po_id              The purchase order's database ID.
     * @return   object|null                   The purchase order data object, or null if not found.
     */
    public function get_purchase_order(int $po_id): ?object {
        return HMS_Helper::hms_get_row('hms_purchase_orders', array('id' => $po_id), array('%d'));
    }

    /**
     * Get all purchase orders, with optional filtering.
     *
     * @since    1.0.0
     * @param    int       $supplier_id        Optional. Filter by supplier ID.
     * @param    string    $status             Optional. Filter by status.
     * @param    int       $offset             Optional. The offset for pagination.
     * @param    int       $per_page           Optional. The number of orders to return per page.
     * @return   array                          An array of purchase order data objects.
     */
    public function get_purchase_orders(int $supplier_id = 0, string $status = '', int $offset = 0, int $per_page = 20): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_purchase_orders';
        
        $where_clauses = array();
        $where_values = array();

        if ($supplier_id > 0) {
            $where_clauses[] = "supplier_id = %d";
            $where_values[] = $supplier_id;
        }
        if (!empty($status)) {
            $where_clauses[] = "status = %s";
            $where_values[] = $status;
        }

        $where_clause = '';
        if (!empty($where_clauses)) {
            $where_clause = "WHERE " . implode(' AND ', $where_clauses);
        }

        $query = "SELECT po.*, s.name as supplier_name FROM {$table_name} po 
                  LEFT JOIN {$wpdb->prefix}hms_suppliers s ON po.supplier_id = s.id 
                  {$where_clause} ORDER BY po.order_date DESC LIMIT %d OFFSET %d";
        $values = array_merge($where_values, array($per_page, $offset));
        
        return $wpdb->get_results($wpdb->prepare($query, $values));
    }

    /**
     * Create a new purchase order.
     *
     * @since    1.0.0
     * @param    array     $data              An associative array of purchase order data.
     * @return   int|false                     The new purchase order's database ID, or false on failure.
     */
    public function create_purchase_order(array $data) {
        $table_name = 'hms_purchase_orders';

        // Basic validation
        if (empty($data['supplier_id']) || empty($data['items'])) {
            return false;
        }

        // Generate order number
        $order_number = $this->generate_purchase_order_number();

        $po_data = array(
            'supplier_id'              => intval($data['supplier_id']),
            'order_number'             => $order_number,
            'order_date'               => !empty($data['order_date']) ? sanitize_text_field($data['order_date']) : current_time('mysql'),
            'expected_delivery_date'   => !empty($data['expected_delivery_date']) ? sanitize_text_field($data['expected_delivery_date']) : null,
            'status'                   => 'Pending',
            'total_amount'             => floatval($data['total_amount']),
            'notes'                    => !empty($data['notes']) ? sanitize_textarea_field($data['notes']) : null,
            'created_by'               => get_current_user_id(),
            'created_at'               => current_time('mysql'),
        );

        $format = array('%d', '%s', '%s', '%s', '%s', '%f', '%s', '%d', '%s');
        
        $po_id = HMS_Helper::hms_create($table_name, $po_data, $format);

        if ($po_id) {
            // Add purchase order items
            foreach ($data['items'] as $item) {
                $this->add_purchase_order_item($po_id, $item);
            }
            
            HMS_Helper::log_action('Purchase Order Created', 'Purchase Order', $po_id, json_encode($po_data));
            return $po_id;
        }
        return false;
    }

    /**
     * Add an item to a purchase order.
     *
     * @since    1.0.0
     * @param    int       $po_id              The purchase order ID.
     * @param    array     $item_data          The item data.
     * @return   int|false                     The item ID, or false on failure.
     */
    public function add_purchase_order_item(int $po_id, array $item_data) {
        $table_name = 'hms_purchase_order_items';

        $item = array(
            'purchase_order_id'    => $po_id,
            'medicine_id'          => intval($item_data['medicine_id']),
            'quantity_ordered'     => intval($item_data['quantity_ordered']),
            'unit_price'           => floatval($item_data['unit_price']),
            'total_price'          => floatval($item_data['quantity_ordered']) * floatval($item_data['unit_price']),
            'batch_number'         => !empty($item_data['batch_number']) ? sanitize_text_field($item_data['batch_number']) : null,
            'expiry_date'          => !empty($item_data['expiry_date']) ? sanitize_text_field($item_data['expiry_date']) : null,
            'created_at'           => current_time('mysql'),
        );

        $format = array('%d', '%d', '%d', '%f', '%f', '%s', '%s', '%s');
        
        return HMS_Helper::hms_create($table_name, $item, $format);
    }

    /**
     * Generate a unique purchase order number.
     *
     * @since    1.0.0
     * @return   string                        The generated order number.
     */
    private function generate_purchase_order_number(): string {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_purchase_orders';
        
        $prefix = 'PO';
        $date_part = date('Ymd');
        
        // Get the last order number for today
        $last_order = $wpdb->get_var($wpdb->prepare(
            "SELECT order_number FROM {$table_name} 
             WHERE order_number LIKE %s 
             ORDER BY id DESC LIMIT 1",
            $prefix . $date_part . '%'
        ));
        
        if ($last_order) {
            $last_sequence = intval(substr($last_order, -3));
            $new_sequence = $last_sequence + 1;
        } else {
            $new_sequence = 1;
        }
        
        return $prefix . $date_part . str_pad($new_sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Receive items from a purchase order and update stock.
     *
     * @since    1.0.0
     * @param    int       $po_id              The purchase order ID.
     * @param    array     $received_items     Array of received items with quantities.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function receive_purchase_order(int $po_id, array $received_items) {
        $po = $this->get_purchase_order($po_id);
        if (!$po) {
            return new WP_Error('po_not_found', esc_html__('Purchase order not found.', 'hospital-management-system'));
        }

        global $wpdb;
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        try {
            foreach ($received_items as $item_id => $received_data) {
                $quantity_received = intval($received_data['quantity_received']);
                
                if ($quantity_received <= 0) {
                    continue;
                }
                
                // Get the purchase order item
                $po_item = HMS_Helper::hms_get_row('hms_purchase_order_items', array('id' => $item_id), array('%d'));
                if (!$po_item) {
                    continue;
                }
                
                // Update the received quantity
                HMS_Helper::hms_update(
                    'hms_purchase_order_items',
                    array('quantity_received' => $po_item->quantity_received + $quantity_received),
                    array('id' => $item_id),
                    array('%d'),
                    array('%d')
                );
                
                // Update medicine stock
                $this->update_stock($po_item->medicine_id, $quantity_received, 'Purchase Order Receipt');
                
                // Update batch number and expiry date if provided
                if (!empty($received_data['batch_number']) || !empty($received_data['expiry_date'])) {
                    $update_data = array();
                    $update_format = array();
                    
                    if (!empty($received_data['batch_number'])) {
                        $update_data['batch_number'] = sanitize_text_field($received_data['batch_number']);
                        $update_format[] = '%s';
                    }
                    if (!empty($received_data['expiry_date'])) {
                        $update_data['expiry_date'] = sanitize_text_field($received_data['expiry_date']);
                        $update_format[] = '%s';
                    }
                    
                    if (!empty($update_data)) {
                        HMS_Helper::hms_update(
                            'hms_medicines',
                            $update_data,
                            array('id' => $po_item->medicine_id),
                            $update_format,
                            array('%d')
                        );
                    }
                }
            }
            
            // Check if all items are fully received and update PO status
            $items_table = $wpdb->prefix . 'hms_purchase_order_items';
            $incomplete_items = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$items_table} 
                 WHERE purchase_order_id = %d AND quantity_received < quantity_ordered",
                $po_id
            ));
            
            $new_status = ($incomplete_items == 0) ? 'Completed' : 'Partially Received';
            HMS_Helper::hms_update(
                'hms_purchase_orders',
                array('status' => $new_status),
                array('id' => $po_id),
                array('%s'),
                array('%d')
            );
            
            $wpdb->query('COMMIT');
            
            HMS_Helper::log_action('Purchase Order Received', 'Purchase Order', $po_id, json_encode($received_items));
            return true;
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('receive_failed', esc_html__('Failed to receive purchase order items.', 'hospital-management-system'));
        }
    }

    // --- AJAX Handlers ---

    /**
     * AJAX handler to create a new medicine.
     */
    public function ajax_create_medicine() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_pharmacy')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $data = array(
            'name'             => sanitize_text_field($_POST['name']),
            'generic_name'     => sanitize_text_field($_POST['generic_name']),
            'category'         => sanitize_text_field($_POST['category']),
            'manufacturer'     => sanitize_text_field($_POST['manufacturer']),
            'unit'             => sanitize_text_field($_POST['unit']),
            'strength'         => sanitize_text_field($_POST['strength']),
            'description'      => sanitize_textarea_field($_POST['description']),
            'stock_quantity'   => intval($_POST['stock_quantity']),
            'reorder_level'    => intval($_POST['reorder_level']),
            'unit_price'       => floatval($_POST['unit_price']),
            'selling_price'    => floatval($_POST['selling_price']),
            'batch_number'     => sanitize_text_field($_POST['batch_number']),
            'expiry_date'      => sanitize_text_field($_POST['expiry_date']),
            'status'           => sanitize_text_field($_POST['status']),
        );

        $medicine_id = $this->create_medicine($data);

        if (is_wp_error($medicine_id)) {
            wp_send_json_error($medicine_id->get_error_message());
        } elseif ($medicine_id) {
            wp_send_json_success(array(
                'message'     => esc_html__('Medicine added successfully.', 'hospital-management-system'),
                'medicine_id' => $medicine_id
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to add medicine.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to update stock for a medicine.
     */
    public function ajax_update_stock() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_pharmacy')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $medicine_id = intval($_POST['medicine_id']);
        $quantity_change = intval($_POST['quantity_change']);
        $reason = sanitize_text_field($_POST['reason']);

        $updated = $this->update_stock($medicine_id, $quantity_change, $reason);

        if (is_wp_error($updated)) {
            wp_send_json_error($updated->get_error_message());
        } elseif ($updated) {
            $medicine = $this->get_medicine($medicine_id);
            wp_send_json_success(array(
                'message' => esc_html__('Stock updated successfully.', 'hospital-management-system'),
                'medicine' => $medicine
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to update stock.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to create a new supplier.
     */
    public function ajax_create_supplier() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_pharmacy')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $data = array(
            'name'             => sanitize_text_field($_POST['name']),
            'contact_person'   => sanitize_text_field($_POST['contact_person']),
            'email'            => sanitize_email($_POST['email']),
            'phone'            => sanitize_text_field($_POST['phone']),
            'address'          => sanitize_textarea_field($_POST['address']),
            'city'             => sanitize_text_field($_POST['city']),
            'state'            => sanitize_text_field($_POST['state']),
            'postal_code'      => sanitize_text_field($_POST['postal_code']),
            'country'          => sanitize_text_field($_POST['country']),
            'tax_number'       => sanitize_text_field($_POST['tax_number']),
            'payment_terms'    => sanitize_text_field($_POST['payment_terms']),
            'status'           => sanitize_text_field($_POST['status']),
            'notes'            => sanitize_textarea_field($_POST['notes']),
        );

        $supplier_id = $this->create_supplier($data);

        if (is_wp_error($supplier_id)) {
            wp_send_json_error($supplier_id->get_error_message());
        } elseif ($supplier_id) {
            wp_send_json_success(array(
                'message'     => esc_html__('Supplier added successfully.', 'hospital-management-system'),
                'supplier_id' => $supplier_id
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to add supplier.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to create a new purchase order.
     */
    public function ajax_create_purchase_order() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_pharmacy')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $data = array(
            'supplier_id'              => intval($_POST['supplier_id']),
            'expected_delivery_date'   => sanitize_text_field($_POST['expected_delivery_date']),
            'total_amount'             => floatval($_POST['total_amount']),
            'notes'                    => sanitize_textarea_field($_POST['notes']),
            'items'                    => json_decode(stripslashes($_POST['items']), true),
        );

        $po_id = $this->create_purchase_order($data);

        if (is_wp_error($po_id)) {
            wp_send_json_error($po_id->get_error_message());
        } elseif ($po_id) {
            wp_send_json_success(array(
                'message' => esc_html__('Purchase order created successfully.', 'hospital-management-system'),
                'po_id'   => $po_id
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to create purchase order.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to receive purchase order items.
     */
    public function ajax_receive_purchase_order() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_pharmacy')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $po_id = intval($_POST['po_id']);
        $received_items = json_decode(stripslashes($_POST['received_items']), true);

        $result = $this->receive_purchase_order($po_id, $received_items);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } elseif ($result) {
            wp_send_json_success(array(
                'message' => esc_html__('Purchase order items received successfully.', 'hospital-management-system')
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to receive purchase order items.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to get low stock alerts.
     */
    public function ajax_get_low_stock_alerts() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_pharmacy')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $low_stock_medicines = $this->get_low_stock_medicines();
        wp_send_json_success($low_stock_medicines);
    }

    /**
     * AJAX handler to get expiry alerts.
     */
    public function ajax_get_expiry_alerts() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_pharmacy')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $days_ahead = isset($_GET['days_ahead']) ? intval($_GET['days_ahead']) : 30;
        $expiring_medicines = $this->get_expiring_medicines($days_ahead);
        wp_send_json_success($expiring_medicines);
    }

    // Placeholder methods for existing functionality
    public function ajax_update_medicine() { /* Existing method */ }
    public function ajax_get_medicine() { /* Existing method */ }
    public function ajax_delete_medicine() { /* Existing method */ }
    public function ajax_get_dispenses_list() { /* Existing method */ }
    public function ajax_get_prescription_for_dispense() { /* Existing method */ }
    public function ajax_update_supplier() { /* Existing method */ }
    public function ajax_get_supplier() { /* Existing method */ }
    public function ajax_delete_supplier() { /* Existing method */ }
    public function ajax_update_purchase_order() { /* Existing method */ }
    public function ajax_get_purchase_order() { /* Existing method */ }
    public function ajax_delete_purchase_order() { /* Existing method */ }
}