<?php
/**
 * Fix Bill Net Amount Script
 *
 * This script fixes any inconsistencies in bill net amounts
 * by recalculating them based on total amount and payments.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Check if user has permission
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'hospital-management-system'));
}

?>

<div class="wrap">
    <h1><?php esc_html_e('Fix Bill Net Amount', 'hospital-management-system'); ?></h1>
    
    <div class="notice notice-info">
        <p><?php esc_html_e('This tool will recalculate and fix any inconsistencies in bill net amounts.', 'hospital-management-system'); ?></p>
    </div>

    <?php
    global $wpdb;
    $bills_table = $wpdb->prefix . 'hms_bills';
    
    // Handle form submission
    if (isset($_POST['fix_bill_amounts']) && wp_verify_nonce($_POST['fix_bill_nonce'], 'hms_fix_bill_amounts')) {
        
        echo '<div class="notice notice-info"><p>' . esc_html__('Starting bill amount fix process...', 'hospital-management-system') . '</p></div>';
        
        try {
            // Get all bills that might have inconsistent amounts
            $bills = $wpdb->get_results("SELECT * FROM $bills_table WHERE total_amount > 0");
            
            $fixed_count = 0;
            $errors = array();
            
            foreach ($bills as $bill) {
                // Calculate what the due amount should be
                $total_amount = (float) $bill->total_amount;
                $paid_amount = (float) ($bill->paid_amount ?? 0);
                $insurance_covered = (float) ($bill->insurance_covered ?? 0);
                
                $calculated_due = $total_amount - $paid_amount - $insurance_covered;
                $calculated_due = max(0, $calculated_due); // Ensure it's not negative
                
                $current_due = (float) $bill->due_amount;
                
                // Check if there's a discrepancy
                if (abs($calculated_due - $current_due) > 0.01) { // Allow for small floating point differences
                    
                    // Update the bill
                    $update_result = $wpdb->update(
                        $bills_table,
                        array(
                            'due_amount' => $calculated_due,
                            'net_amount' => $calculated_due,
                            'updated_at' => current_time('mysql')
                        ),
                        array('id' => $bill->id),
                        array('%f', '%f', '%s'),
                        array('%d')
                    );
                    
                    if ($update_result !== false) {
                        $fixed_count++;
                    } else {
                        $errors[] = sprintf(
                            /* translators: %1$s: bill ID, %2$s: error message */
                            esc_html__('Failed to update bill %1$s: %2$s', 'hospital-management-system'),
                            $bill->bill_id,
                            $wpdb->last_error
                        );
                    }
                }
            }
            
            if (empty($errors)) {
                echo '<div class="notice notice-success"><p>' . 
                     sprintf(
                         /* translators: %d: number of bills fixed */
                         esc_html__('Successfully fixed %d bills with amount discrepancies.', 'hospital-management-system'),
                         $fixed_count
                     ) . 
                     '</p></div>';
            } else {
                echo '<div class="notice notice-warning"><p>' . 
                     sprintf(
                         /* translators: %d: number of bills fixed */
                         esc_html__('Fixed %d bills, but encountered some errors:', 'hospital-management-system'),
                         $fixed_count
                     ) . 
                     '</p><ul>';
                foreach ($errors as $error) {
                    echo '<li>' . esc_html($error) . '</li>';
                }
                echo '</ul></div>';
            }
            
            // Log the operation
            error_log('HMS: Bill net amount fix completed. Fixed: ' . $fixed_count . ' bills');
            
        } catch (Exception $e) {
            echo '<div class="notice notice-error"><p>' . 
                 sprintf(
                     /* translators: %s: error message */
                     esc_html__('Fix operation failed with error: %s', 'hospital-management-system'),
                     esc_html($e->getMessage())
                 ) . 
                 '</p></div>';
            
            // Log the error
            error_log('HMS: Bill net amount fix failed: ' . $e->getMessage());
        }
    }
    
    // Get current bill statistics
    $total_bills = $wpdb->get_var("SELECT COUNT(*) FROM $bills_table");
    $bills_with_issues = $wpdb->get_results("
        SELECT 
            id, bill_id, total_amount, paid_amount, insurance_covered, due_amount,
            (total_amount - COALESCE(paid_amount, 0) - COALESCE(insurance_covered, 0)) as calculated_due
        FROM $bills_table 
        WHERE total_amount > 0
        HAVING ABS(calculated_due - due_amount) > 0.01
        ORDER BY id DESC
        LIMIT 10
    ");
    
    $issues_count = count($bills_with_issues);
    ?>
    
    <!-- Statistics Cards -->
    <div class="stats-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
        <div class="stat-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; text-align: center;">
            <h3 style="margin: 0 0 10px 0; color: #0073aa;"><?php echo number_format($total_bills); ?></h3>
            <p style="margin: 0; color: #666;"><?php esc_html_e('Total Bills', 'hospital-management-system'); ?></p>
        </div>
        
        <div class="stat-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; text-align: center;">
            <h3 style="margin: 0 0 10px 0; color: <?php echo $issues_count > 0 ? '#d63638' : '#00a32a'; ?>;">
                <?php echo number_format($issues_count); ?>
            </h3>
            <p style="margin: 0; color: #666;"><?php esc_html_e('Bills with Issues', 'hospital-management-system'); ?></p>
        </div>
    </div>
    
    <?php if ($issues_count > 0): ?>
        <!-- Bills with Issues -->
        <div class="issues-section" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin-bottom: 20px;">
            <h2><?php esc_html_e('Bills with Amount Discrepancies', 'hospital-management-system'); ?></h2>
            <p><?php esc_html_e('The following bills have discrepancies between their calculated and stored due amounts:', 'hospital-management-system'); ?></p>
            
            <div style="overflow-x: auto;">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Bill ID', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Total Amount', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Paid Amount', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Insurance Covered', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Current Due', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Calculated Due', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Difference', 'hospital-management-system'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bills_with_issues as $bill): ?>
                            <tr>
                                <td><strong><?php echo esc_html($bill->bill_id); ?></strong></td>
                                <td><?php echo esc_html(number_format($bill->total_amount, 2)); ?></td>
                                <td><?php echo esc_html(number_format($bill->paid_amount ?? 0, 2)); ?></td>
                                <td><?php echo esc_html(number_format($bill->insurance_covered ?? 0, 2)); ?></td>
                                <td style="color: #d63638;"><?php echo esc_html(number_format($bill->due_amount, 2)); ?></td>
                                <td style="color: #00a32a;"><?php echo esc_html(number_format($bill->calculated_due, 2)); ?></td>
                                <td style="font-weight: bold; color: <?php echo $bill->calculated_due > $bill->due_amount ? '#d63638' : '#0073aa'; ?>;">
                                    <?php echo esc_html(number_format($bill->calculated_due - $bill->due_amount, 2)); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (count($bills_with_issues) >= 10): ?>
                <p style="margin-top: 15px; color: #666; font-style: italic;">
                    <?php esc_html_e('Showing first 10 bills with issues. There may be more.', 'hospital-management-system'); ?>
                </p>
            <?php endif; ?>
        </div>
        
        <!-- Fix Form -->
        <div class="fix-form" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin-bottom: 20px;">
            <h2><?php esc_html_e('Fix Bill Amounts', 'hospital-management-system'); ?></h2>
            <p><?php esc_html_e('Click the button below to automatically fix all bill amount discrepancies. This will recalculate due amounts based on total amount minus paid amount and insurance coverage.', 'hospital-management-system'); ?></p>
            
            <form method="post" action="">
                <?php wp_nonce_field('hms_fix_bill_amounts', 'fix_bill_nonce'); ?>
                <p>
                    <input type="submit" name="fix_bill_amounts" class="button button-primary" 
                           value="<?php esc_attr_e('Fix All Bill Amounts', 'hospital-management-system'); ?>" 
                           onclick="return confirm('<?php esc_attr_e('Are you sure you want to fix all bill amounts? This will update the due amounts for bills with discrepancies.', 'hospital-management-system'); ?>');">
                </p>
            </form>
        </div>
        
    <?php else: ?>
        <!-- No Issues -->
        <div style="background: #f0f8f0; border: 1px solid #c3e6c3; border-radius: 4px; padding: 20px; margin-top: 20px; text-align: center;">
            <h3 style="color: #00a32a; margin: 0 0 10px 0;">
                <span class="dashicons dashicons-yes-alt" style="margin-right: 8px;"></span>
                <?php esc_html_e('All Bill Amounts are Correct', 'hospital-management-system'); ?>
            </h3>
            <p style="margin: 0;"><?php esc_html_e('No discrepancies found in bill amounts. All calculations are consistent.', 'hospital-management-system'); ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Information Section -->
    <div class="info-section" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin-top: 20px;">
        <h2><?php esc_html_e('How Bill Amount Calculation Works', 'hospital-management-system'); ?></h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            <div>
                <h4><?php esc_html_e('Formula:', 'hospital-management-system'); ?></h4>
                <div style="background: #f9f9f9; padding: 15px; border-radius: 4px; font-family: monospace;">
                    Due Amount = Total Amount - Paid Amount - Insurance Covered
                </div>
            </div>
            
            <div>
                <h4><?php esc_html_e('What this tool does:', 'hospital-management-system'); ?></h4>
                <ul>
                    <li><?php esc_html_e('Identifies bills with calculation discrepancies', 'hospital-management-system'); ?></li>
                    <li><?php esc_html_e('Recalculates due amounts using the correct formula', 'hospital-management-system'); ?></li>
                    <li><?php esc_html_e('Updates both due_amount and net_amount fields', 'hospital-management-system'); ?></li>
                    <li><?php esc_html_e('Ensures amounts are never negative', 'hospital-management-system'); ?></li>
                </ul>
            </div>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: #f0f6ff; border-left: 4px solid #0073aa;">
            <h4><?php esc_html_e('When to use this tool:', 'hospital-management-system'); ?></h4>
            <ul>
                <li><?php esc_html_e('After importing bill data from external sources', 'hospital-management-system'); ?></li>
                <li><?php esc_html_e('If you notice incorrect due amounts in bills', 'hospital-management-system'); ?></li>
                <li><?php esc_html_e('After manual database modifications', 'hospital-management-system'); ?></li>
                <li><?php esc_html_e('As part of regular data maintenance', 'hospital-management-system'); ?></li>
            </ul>
        </div>
    </div>
</div>

<style>
.stats-cards {
    margin: 20px 0;
}

.stat-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.wp-list-table th,
.wp-list-table td {
    padding: 8px 12px;
}

@media (max-width: 768px) {
    .stats-cards {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .wp-list-table {
        font-size: 12px;
    }
    
    .wp-list-table th,
    .wp-list-table td {
        padding: 6px 8px;
    }
}
</style>