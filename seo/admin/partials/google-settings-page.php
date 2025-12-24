<?php
/**
 * Google Settings Page Template
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="seo-simple-google-settings">
    <div class="seo-simple-google-tabs">
        <button class="tab-button active" data-tab="analytics-tab"><?php _e( 'Analytics', 'seo-simple' ); ?></button>
        <button class="tab-button" data-tab="adsense-tab"><?php _e( 'AdSense', 'seo-simple' ); ?></button>
        <button class="tab-button" data-tab="search-console-tab"><?php _e( 'Search Console', 'seo-simple' ); ?></button>
        <button class="tab-button" data-tab="verification-tab"><?php _e( 'Site Verification', 'seo-simple' ); ?></button>
    </div>

    <form id="seo-simple-google-settings-form">
        <!-- Analytics Tab -->
        <div id="analytics-tab" class="seo-simple-google-tab active">
            <div class="seo-simple-google-section">
                <h3 class="seo-simple-google-section-header"><?php _e( 'Google Analytics Settings', 'seo-simple' ); ?></h3>
                <div class="seo-simple-google-section-content">
                    <table class="seo-simple-google-form-table">
                        <tr>
                            <th>
                                <label for="google-analytics-enabled"><?php _e( 'Enable Google Analytics', 'seo-simple' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="google-analytics-enabled" name="google_analytics_enabled" value="1" <?php checked( $google_analytics_enabled ); ?>>
                                <p class="description"><?php _e( 'Enable Google Analytics tracking on your website.', 'seo-simple' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="google-analytics-id"><?php _e( 'Tracking ID', 'seo-simple' ); ?></label>
                            </th>
                            <td>
                                <input type="text" id="google-analytics-id" name="google_analytics_id" value="<?php echo esc_attr( $google_analytics_id ); ?>" class="regular-text">
                                <p class="description"><?php _e( 'Enter your Google Analytics tracking ID (e.g., GA_MEASUREMENT_ID or UA-XXXXXXXX-X).', 'seo-simple' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="google-analytics-version"><?php _e( 'Analytics Version', 'seo-simple' ); ?></label>
                            </th>
                            <td>
                                <select id="google-analytics-version" name="google_analytics_version">
                                    <option value="ga4" <?php selected( $google_analytics_version, 'ga4' ); ?>><?php _e( 'Google Analytics 4 (GA4)', 'seo-simple' ); ?></option>
                                    <option value="universal" <?php selected( $google_analytics_version, 'universal' ); ?>><?php _e( 'Universal Analytics', 'seo-simple' ); ?></option>
                                </select>
                                <p class="description"><?php _e( 'Select the version of Google Analytics you are using.', 'seo-simple' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="google-analytics-anonymize-ip"><?php _e( 'Anonymize IP Addresses', 'seo-simple' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="google-analytics-anonymize-ip" name="google_analytics_anonymize_ip" value="1" <?php checked( $google_analytics_anonymize_ip ); ?>>
                                <p class="description"><?php _e( 'Enable IP anonymization to comply with privacy regulations like GDPR.', 'seo-simple' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="google-analytics-track-outbound"><?php _e( 'Track Outbound Links', 'seo-simple' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="google-analytics-track-outbound" name="google_analytics_track_outbound" value="1" <?php checked( $google_analytics_track_outbound ); ?>>
                                <p class="description"><?php _e( 'Automatically track clicks on outbound links.', 'seo-simple' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="google-analytics-ecommerce"><?php _e( 'E-commerce Tracking', 'seo-simple' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="google-analytics-ecommerce" name="google_analytics_ecommerce" value="1" <?php checked( $google_analytics_ecommerce ); ?>>
                                <p class="description"><?php _e( 'Enable e-commerce tracking for WooCommerce and Easy Digital Downloads.', 'seo-simple' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label><?php _e( 'Exclude User Roles', 'seo-simple' ); ?></label>
                            </th>
                            <td id="google-analytics-exclude-roles">
                                <?php foreach ( $user_roles as $role => $details ) : ?>
                                    <label>
                                        <input type="checkbox" name="google_analytics_exclude_roles[]" value="<?php echo esc_attr( $role ); ?>" <?php checked( in_array( $role, $google_analytics_exclude_roles ) ); ?>>
                                        <?php echo esc_html( $details['name'] ); ?>
                                    </label>
                                <?php endforeach; ?>
                                <p class="description"><?php _e( 'Select user roles to exclude from tracking.', 'seo-simple' ); ?></p>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="seo-simple-google-connection-test">
                        <button type="button" class="button seo-simple-connection-test" data-service="analytics">
                            <?php _e( 'Test Connection', 'seo-simple' ); ?>
                        </button>
                        <span class="seo-simple-google-connection-status"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- AdSense Tab -->
        <div id="adsense-tab" class="seo-simple-google-tab">
            <div class="seo-simple-google-section">
                <h3 class="seo-simple-google-section-header"><?php _e( 'Google AdSense Settings', 'seo-simple' ); ?></h3>
                <div class="seo-simple-google-section-content">
                    <table class="seo-simple-google-form-table">
                        <tr>
                            <th>
                                <label for="google-adsense-enabled"><?php _e( 'Enable Google AdSense', 'seo-simple' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="google-adsense-enabled" name="google_adsense_enabled" value="1" <?php checked( $google_adsense_enabled ); ?>>
                                <p class="description"><?php _e( 'Enable Google AdSense on your website.', 'seo-simple' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="google-adsense-id"><?php _e( 'AdSense Publisher ID', 'seo-simple' ); ?></label>
                            </th>
                            <td>
                                <input type="text" id="google-adsense-id" name="google_adsense_id" value="<?php echo esc_attr( $google_adsense_id ); ?>" class="regular-text">
                                <p class="description"><?php _e( 'Enter your Google AdSense Publisher ID (e.g., ca-pub-XXXXXXXXXXXXXXXX).', 'seo-simple' ); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Search Console Tab -->
        <div id="search-console-tab" class="seo-simple-google-tab">
            <div class="seo-simple-google-section">
                <h3 class="seo-simple-google-section-header"><?php _e( 'Google Search Console Settings', 'seo-simple' ); ?></h3>
                <div class="seo-simple-google-section-content">
                    <div class="seo-simple-google-authentication-status <?php echo $google_search_console_authenticated ? 'authenticated' : 'not-authenticated'; ?>">
                        <?php if ( $google_search_console_authenticated ) : ?>
                            <strong><?php _e( 'Authenticated', 'seo-simple' ); ?></strong>
                            <?php if ( ! empty( $google_search_console_site ) ) : ?>
                                <br><?php printf( __( 'Site: %s', 'seo-simple' ), '<code>' . esc_html( $google_search_console_site ) . '</code>' ); ?>
                            <?php endif; ?>
                        <?php else : ?>
                            <strong><?php _e( 'Not Authenticated', 'seo-simple' ); ?></strong>
                            <br><?php _e( 'Please authenticate with Google Search Console to access search performance data.', 'seo-simple' ); ?>
                        <?php endif; ?>
                    </div>

                    <table class="seo-simple-google-form-table">
                        <tr>
                            <th>
                                <label for="google-search-console-enabled"><?php _e( 'Enable Search Console', 'seo-simple' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="google-search-console-enabled" name="google_search_console_enabled" value="1" <?php checked( $google_search_console_enabled ); ?>>
                                <p class="description"><?php _e( 'Enable Google Search Console integration.', 'seo-simple' ); ?></p>
                            </td>
                        </tr>
                    </table>

                    <?php if ( $google_search_console_authenticated ) : ?>
                        <div class="seo-simple-google-connection-test">
                            <button type="button" class="button seo-simple-connection-test" data-service="search_console">
                                <?php _e( 'Test Connection', 'seo-simple' ); ?>
                            </button>
                            <button type="button" class="button" id="seo-simple-google-revoke-access">
                                <?php _e( 'Revoke Access', 'seo-simple' ); ?>
                            </button>
                            <span class="seo-simple-google-connection-status"></span>
                        </div>
                    <?php else : ?>
                        <div class="seo-simple-google-authentication">
                            <h4><?php _e( 'Authenticate with Google Search Console', 'seo-simple' ); ?></h4>
                            <p><?php _e( 'Click the button below to authenticate with Google Search Console and grant access to your search performance data.', 'seo-simple' ); ?></p>
                            <div class="auth-form">
                                <div class="form-field">
                                    <label for="google-search-console-site"><?php _e( 'Site URL', 'seo-simple' ); ?></label>
                                    <input type="text" id="google-search-console-site" name="google_search_console_site" value="<?php echo esc_url( home_url() ); ?>" class="regular-text">
                                </div>
                                <button type="button" class="button button-primary" id="seo-simple-google-authenticate">
                                    <?php _e( 'Authenticate', 'seo-simple' ); ?>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Site Verification Tab -->
        <div id="verification-tab" class="seo-simple-google-tab">
            <div class="seo-simple-google-section">
                <h3 class="seo-simple-google-section-header"><?php _e( 'Google Site Verification', 'seo-simple' ); ?></h3>
                <div class="seo-simple-google-section-content">
                    <table class="seo-simple-google-form-table">
                        <tr>
                            <th>
                                <label for="google-site-verification"><?php _e( 'Verification Code', 'seo-simple' ); ?></label>
                            </th>
                            <td>
                                <input type="text" id="google-site-verification" name="google_site_verification" value="<?php echo esc_attr( $google_site_verification ); ?>" class="regular-text">
                                <p class="description"><?php _e( 'Enter your Google site verification code. This will add a meta tag to your site header for verification purposes.', 'seo-simple' ); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="submit">
            <button type="submit" class="button button-primary"><?php _e( 'Save Settings', 'seo-simple' ); ?></button>
        </div>
    </form>
</div>