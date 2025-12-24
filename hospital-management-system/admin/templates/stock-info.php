<?php
/**
 * Stock information meta box template
 */
?>
<div class="stock-info">
    <div class="form-field">
        <label for="stock_quantity"><?php esc_html_e('Stock Quantity', 'hospital-management-system'); ?></label>
        <input type="number" id="stock_quantity" name="stock_quantity" value="<?php echo esc_attr($stock_quantity); ?>" min="0">
    </div>
    
    <div class="form-field">
        <label for="unit_price"><?php esc_html_e('Unit Price', 'hospital-management-system'); ?></label>
        <input type="number" id="unit_price" name="unit_price" value="<?php echo esc_attr($unit_price); ?>" step="0.01" min="0">
    </div>
    
    <div class="form-field">
        <label for="expiry_date"><?php esc_html_e('Expiry Date', 'hospital-management-system'); ?></label>
        <input type="date" id="expiry_date" name="expiry_date" value="<?php echo esc_attr($expiry_date); ?>">
    </div>
    
    <div class="form-field">
        <label for="reorder_level"><?php esc_html_e('Reorder Level', 'hospital-management-system'); ?></label>
        <input type="number" id="reorder_level" name="reorder_level" value="<?php echo esc_attr($reorder_level); ?>" min="0">
        <p class="description"><?php esc_html_e('Alert when stock falls below this level', 'hospital-management-system'); ?></p>
    </div>
    
    <div class="stock-actions">
        <button type="button" id="update-stock" class="button"><?php esc_html_e('Update Stock', 'hospital-management-system'); ?></button>
        <button type="button" id="create-purchase-order" class="button button-primary"><?php esc_html_e('Create Purchase Order', 'hospital-management-system'); ?></button>
    </div>
</div>