<?php
/**
 * Indexing Settings Page Template
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

<div class="seo-simple-indexing-settings">
    <form id="seo-simple-indexing-settings-form">
        <!-- Google Settings -->
        <div class="seo-simple-indexing-section">
            <h3 class="seo-simple-indexing-section-header"><?php _e( 'Google Indexing API', 'seo-simple' ); ?></h3>
            <div class="seo-simple-indexing-section-content">
                <table class="seo-simple-indexing-form-table">
                    <tr>
                        <th>
                            <label for="google-indexing-enabled"><?php _e( 'Enable Google Indexing', 'seo-simple' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="google-indexing-enabled" name="google_indexing_enabled" value="1" <?php checked( $google_enabled ); ?>>
                            <p class="description"><?php _e( 'Enable indexing with Google using the Indexing API.', 'seo-simple' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="google-indexing-api-key"><?php _e( 'API Key', 'seo-simple' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="google-indexing-api-key" name="google_indexing_api_key" value="<?php echo esc_attr( $google_api_key ); ?>" class="regular-text">
                            <p class="description"><?php _e( 'Enter your Google Indexing API key. Get one from Google Search Console.', 'seo-simple' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Bing Settings -->
        <div class="seo-simple-indexing-section">
            <h3 class="seo-simple-indexing-section-header"><?php _e( 'Bing Indexing API', 'seo-simple' ); ?></h3>
            <div class="seo-simple-indexing-section-content">
                <table class="seo-simple-indexing-form-table">
                    <tr>
                        <th>
                            <label for="bing-indexing-enabled"><?php _e( 'Enable Bing Indexing', 'seo-simple' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="bing-indexing-enabled" name="bing_indexing_enabled" value="1" <?php checked( $bing_enabled ); ?>>
                            <p class="description"><?php _e( 'Enable indexing with Bing using the Submit URL API.', 'seo-simple' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="bing-indexing-api-key"><?php _e( 'API Key', 'seo-simple' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="bing-indexing-api-key" name="bing_indexing_api_key" value="<?php echo esc_attr( $bing_api_key ); ?>" class="regular-text">
                            <p class="description"><?php _e( 'Enter your Bing Webmaster API key. Get one from Bing Webmaster Tools.', 'seo-simple' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Yandex Settings -->
        <div class="seo-simple-indexing-section">
            <h3 class="seo-simple-indexing-section-header"><?php _e( 'Yandex Indexing API', 'seo-simple' ); ?></h3>
            <div class="seo-simple-indexing-section-content">
                <table class="seo-simple-indexing-form-table">
                    <tr>
                        <th>
                            <label for="yandex-indexing-enabled"><?php _e( 'Enable Yandex Indexing', 'seo-simple' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="yandex-indexing-enabled" name="yandex_indexing_enabled" value="1" <?php checked( $yandex_enabled ); ?>>
                            <p class="description"><?php _e( 'Enable indexing with Yandex using the Webmaster API.', 'seo-simple' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="yandex-indexing-api-key"><?php _e( 'OAuth Token', 'seo-simple' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="yandex-indexing-api-key" name="yandex_indexing_api_key" value="<?php echo esc_attr( $yandex_api_key ); ?>" class="regular-text">
                            <p class="description"><?php _e( 'Enter your Yandex OAuth token.', 'seo-simple' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="yandex-indexing-user-id"><?php _e( 'User ID', 'seo-simple' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="yandex-indexing-user-id" name="yandex_indexing_user_id" value="<?php echo esc_attr( $yandex_user_id ); ?>" class="regular-text">
                            <p class="description"><?php _e( 'Enter your Yandex user ID.', 'seo-simple' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="yandex-indexing-host-id"><?php _e( 'Host ID', 'seo-simple' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="yandex-indexing-host-id" name="yandex_indexing_host_id" value="<?php echo esc_attr( $yandex_host_id ); ?>" class="regular-text">
                            <p class="description"><?php _e( 'Enter your Yandex host ID for this site.', 'seo-simple' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- General Settings -->
        <div class="seo-simple-indexing-section">
            <h3 class="seo-simple-indexing-section-header"><?php _e( 'General Settings', 'seo-simple' ); ?></h3>
            <div class="seo-simple-indexing-section-content">
                <table class="seo-simple-indexing-form-table">
                    <tr>
                        <th>
                            <label for="auto-indexing-enabled"><?php _e( 'Automatic Indexing', 'seo-simple' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="auto-indexing-enabled" name="auto_indexing_enabled" value="1" <?php checked( $auto_index ); ?>>
                            <p class="description"><?php _e( 'Automatically submit new and updated posts to search engines when published.', 'seo-simple' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="indexing-daily-limit"><?php _e( 'Daily Limit', 'seo-simple' ); ?></label>
                        </th>
                        <td>
                            <input type="number" id="indexing-daily-limit" name="indexing_daily_limit" value="<?php echo esc_attr( $indexing_limit ); ?>" class="small-text" min="1" max="10000">
                            <p class="description"><?php _e( 'Maximum number of submissions per day. Be conservative to avoid API limits.', 'seo-simple' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="submit">
            <button type="submit" class="button button-primary"><?php _e( 'Save Settings', 'seo-simple' ); ?></button>
        </div>
    </form>
</div>