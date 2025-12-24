<?php
/**
 * Roles Settings Page Template
 *
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get current settings
$settings = $this->roles->get_settings();
$wordpress_roles = $this->roles->get_wordpress_roles();
$custom_roles = $this->roles->get_custom_roles();
$statistics = $this->roles->get_role_statistics();

// Show settings errors
settings_errors( 'seo_simple_role_settings' );
?>

<div class="wrap seo-simple-roles-settings">
    <h1><?php _e( 'SEO Role Manager', 'seo-simple' ); ?></h1>
    
    <p><?php _e( 'Manage user roles and capabilities for SEO functionality. Configure which user roles can access different SEO features.', 'seo-simple' ); ?></p>
    
    <form method="post" action="">
        <?php wp_nonce_field( 'seo_simple_role_settings' ); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e( 'Role Capabilities', 'seo-simple' ); ?></th>
                <td>
                    <p><?php _e( 'Configure which user roles have access to specific SEO features:', 'seo-simple' ); ?></p>
                    
                    <table class="seo-simple-capabilities-table">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" class="seo-simple-toggle-all">
                                    <?php _e( 'Capability', 'seo-simple' ); ?>
                                </th>
                                <?php foreach ( $wordpress_roles as $role_name => $display_name ) : ?>
                                <th><?php echo esc_html( $display_name ); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $settings as $capability => $roles ) : ?>
                            <tr>
                                <td>
                                    <div class="capability-name">
                                        <?php echo esc_html( $this->roles->get_capability_label( $capability ) ); ?>
                                    </div>
                                    <div class="capability-description">
                                        <?php echo esc_html( $this->roles->get_capability_description( $capability ) ); ?>
                                    </div>
                                </td>
                                <?php foreach ( $wordpress_roles as $role_name => $display_name ) : ?>
                                <td>
                                    <input type="checkbox" 
                                           name="<?php echo esc_attr( $capability ); ?>[<?php echo esc_attr( $role_name ); ?>]" 
                                           value="1" 
                                           <?php checked( isset( $roles[$role_name] ) ? $roles[$role_name] : false ); ?>>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
    
    <hr>
    
    <div class="seo-simple-role-statistics">
        <h2><?php _e( 'Role Statistics', 'seo-simple' ); ?></h2>
        <p><?php _e( 'Overview of SEO capabilities assigned to each role and the number of users with each role:', 'seo-simple' ); ?></p>
        
        <table class="seo-simple-role-statistics-table">
            <thead>
                <tr>
                    <th><?php _e( 'Role', 'seo-simple' ); ?></th>
                    <th><?php _e( 'Users', 'seo-simple' ); ?></th>
                    <th><?php _e( 'SEO Capabilities', 'seo-simple' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $statistics as $role_name => $role_data ) : ?>
                <tr>
                    <td><?php echo esc_html( $role_data['name'] ); ?></td>
                    <td class="user-count"><?php echo $role_data['user_count']; ?></td>
                    <td class="capability-count">
                        <?php 
                        $capability_count = count( array_filter( $role_data['capabilities'] ) );
                        echo $capability_count . ' / ' . count( $role_data['capabilities'] );
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ( ! empty( $custom_roles ) ) : ?>
    <div class="seo-simple-custom-roles">
        <h2><?php _e( 'Custom Roles', 'seo-simple' ); ?></h2>
        <p><?php _e( 'Manage custom roles created for SEO functionality:', 'seo-simple' ); ?></p>
        
        <table class="seo-simple-custom-roles-table">
            <thead>
                <tr>
                    <th><?php _e( 'Role Name', 'seo-simple' ); ?></th>
                    <th><?php _e( 'Users', 'seo-simple' ); ?></th>
                    <th><?php _e( 'Actions', 'seo-simple' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $custom_roles as $role_name => $display_name ) : ?>
                <tr>
                    <td><?php echo esc_html( $display_name ); ?></td>
                    <td><?php echo count( get_users( array( 'role' => $role_name ) ) ); ?></td>
                    <td class="role-actions">
                        <a href="<?php echo esc_url( admin_url( 'users.php?role=' . $role_name ) ); ?>" class="button">
                            <?php _e( 'View Users', 'seo-simple' ); ?>
                        </a>
                        <a href="#" class="seo-simple-remove-role" 
                           data-role-name="<?php echo esc_attr( $role_name ); ?>" 
                           data-display-name="<?php echo esc_attr( $display_name ); ?>">
                            <?php _e( 'Remove Role', 'seo-simple' ); ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <div class="seo-simple-actions">
        <h2><?php _e( 'Actions', 'seo-simple' ); ?></h2>
        
        <p>
            <button type="button" class="button seo-simple-add-role">
                <?php _e( 'Add Custom Role', 'seo-simple' ); ?>
            </button>
            
            <button type="button" class="button seo-simple-reset-roles">
                <?php _e( 'Reset to Defaults', 'seo-simple' ); ?>
            </button>
        </p>
        
        <p class="description">
            <?php _e( 'Reset to Defaults: Restore all role capabilities to their default settings. This will override any custom changes you have made.', 'seo-simple' ); ?>
        </p>
    </div>
</div>
