<?php
/*
Plugin Name: Listagram for Wordpress
Description: Connects Wordpress to your Listagram.com account
Author: Listagram.com
Author URI: https://www.listagram.com
Version: 0.2
*/

/* Prevent direct file access */
defined( 'ABSPATH' ) or exit;

/* Set our plugin URL for asset handling */
define('LISTAGRAM_PLUGIN_URL', plugins_url('/', __FILE__));

/* Check for whether WooCommerce is installed */
function listagram_is_woocommerce_installed() {
    return class_exists('WooCommerce');
}

/* Checks for whether this is a woocommmerce page (excl. cart & checkout) */
function listagram_is_woocommerce_page() {
    return is_woocommerce();
}

/* Hook to add our Listagram admin panel menu */
add_action('admin_menu', 'listagram_menu');

/* Adds the Listagram administration menu */
function listagram_menu() {
    add_menu_page(
        'Listagram',
        'Listagram',
        'edit_pages',
        'listagram-settings',
        'listagram_settings_page',
        LISTAGRAM_PLUGIN_URL . 'assets/img/icon.png'
    );
}

if(is_admin()) {
    /* Add a stylesheet in the admin panel */
    wp_enqueue_style(
        'listagram_admin',
        LISTAGRAM_PLUGIN_URL . 'assets/css/listagram.css',
        array(),
        '1'
    );
}

function listagram_get_software_name() {
    if(listagram_is_woocommerce_installed()) {
        return 'WooCommerce';
    } else {
        return 'WordPress';
    }
}

/* Prints the settings page in the administration */
function listagram_settings_page() {

?>
    <div class="wrap">
        <img src="<?php echo LISTAGRAM_PLUGIN_URL . 'assets/img/listagram-logo.png'; ?>" />

        <h2>Listagram for <?php echo listagram_get_software_name(); ?></h2>

        <p>This module connects your <?php echo listagram_get_software_name(); ?> installation with your Listagram.com account. Go to the <a href="https://www.listagram.com/account/installation/">installation page</a> in your account to find your <b>Account Token</b>.</p>
        
        <?php if(get_option('listagram_token') == ''): ?>
        <div class="listagram-account-warning">
            <h3>You need a Listagram.com account to use this plugin</h3>
            <p>
                Listagram is a list building tool which increases conversions
                by turning newsletter signups into a game! In order to use
                Listagram in your <?php echo listagram_get_software_name(); ?> site you need an
                an account. If you don't have an account you can create one below.
            </p>
            <p>
                <a class="button-primary" href="https://www.listagram.com/register/" target="_blank">Create account</a>
                &nbsp;&nbsp;
                <a class="button-secondary" href="https://www.listagram.com/pricing" target="_blank">Pricing</a>
        </div>
        <?php endif; ?>

        <form method="post" action="options.php">
            <?php settings_fields( 'listagram-settings-group' ); ?>
            <?php do_settings_sections( 'listagram-settings-group' ); ?>
            <table class="form-table listagram-form-table">
                <tr valign="top">
                    <th scope="row">Account Token</th>
                    <td scope="row">
                        <input type="text" name="listagram_token" class="account-token-input" value="<?php echo esc_attr(get_option('listagram_token')); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Enabled</th>
                    <td scope="row">
                        <fieldset>
                            <legend class="screen-reader-text"><span>Enabled</span></legend><label for="listagram_enabled">
                            <input <?php if (esc_attr(get_option('listagram_enabled')) == '1'): ?> checked="checked" <?php endif; ?> name="listagram_enabled" type="checkbox" id="listagram_enabled" value="1">
                            This will install Listagram in your blog.</label>
                        </fieldset>
                    </td>
                </tr>

                <?php if(listagram_is_woocommerce_installed()): ?>
                <tr valign="top">
                    <th scope="row">Enabled in shop (WooCommerce)</th>
                    <td scope="row">
                        <fieldset>
                            <legend class="screen-reader-text"><span>Enabled</span></legend><label for="listagram_enabled_woocommerce">
                            <input <?php if (esc_attr(get_option('listagram_enabled_woocommerce')) == '1'): ?> checked="checked" <?php endif; ?> name="listagram_enabled_woocommerce" type="checkbox" id="listagram_enabled_woocommerce" value="1">
                            This will install Listagram in your WooCommerce (shop, category, product) pages.</label>
                        </fieldset>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Enabled in shop cart page (WooCommerce)</th>
                    <td scope="row">
                        <fieldset>
                            <legend class="screen-reader-text"><span>Enabled</span></legend><label for="listagram_enabled_woocommerce_cart">
                            <input <?php if (esc_attr(get_option('listagram_enabled_woocommerce_cart')) == '1'): ?> checked="checked" <?php endif; ?> name="listagram_enabled_woocommerce_cart" type="checkbox" id="listagram_enabled_woocommerce_cart" value="1">
                            This will install Listagram in your WooCommerce cart page.</label>
                        </fieldset>
                    </td>
                </tr>

                <?php endif; ?>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>

<?php
}

add_action( 'admin_init', 'listagram_settings' );

/* Register the settings for our Listagram plugin */
function listagram_settings() {
	register_setting( 'listagram-settings-group', 'listagram_enabled' );
	register_setting( 'listagram-settings-group', 'listagram_enabled_woocommerce' );
	register_setting( 'listagram-settings-group', 'listagram_enabled_woocommerce_cart' );
	register_setting( 'listagram-settings-group', 'listagram_token' );
}

/* Prints the actual installation code based on a token */ 
function listagram_print_installation_code($token) {
    ?>
    <!-- LISTAGRAM.COM EMBED CODE --><script> (function() { var s = document.createElement('script'); s.async = true; s.src = 'https://listagram.s3-eu-west-1.amazonaws.com/static/api/listagram.js'; document.body.appendChild(s); window.LISTAGRAM_CFG = { 'token': '<?php echo $token; ?>', 'base_media': 'https://listagram.s3-eu-west-1.amazonaws.com/media/', 'base_static': 'https://listagram.s3-eu-west-1.amazonaws.com/static/', 'base_api': 'https://www.listagram.com/api/', }; }()); </script><!-- END OF LISTAGRAM.COM -->
    <?php
}

/* Return true or false based on what page user is on and config from admin */
function listagram_show_installation_code() {
    $enabled = get_option('listagram_enabled');
    $enabled_woocommerce = get_option('listagram_enabled_woocommerce');
    $enabled_woocommerce_cart = get_option('listagram_enabled_woocommerce_cart');
    
    if(listagram_is_woocommerce_installed()) {
        /* Only perform these checks when WooCommerce is installed */
        if(listagram_is_woocommerce_page()) {
            /* WooCommerce pages like category, product, shop */
            if($enabled_woocommerce == '1') {
                return true;
            }
        } elseif(is_cart()) {
            /* WooCommerce cart page only */
            if($enabled_woocommerce_cart) {
                return true;
            }
        } elseif(is_checkout()) {
            /* Never show installation in checkout pages */
            return false;
        } else {
            /* All other blog pages */
            if($enabled == '1') {
                return true;
            }
        }
    } else {
        if($enabled == '1') {
            return true;
        }
    }

    return false;
}

/* Prints the Listagram installation code if activated on current page */
function listagram_footer() {
    $token = get_option('listagram_token');

    if(listagram_show_installation_code()) {
        listagram_print_installation_code($token);
    }
}

/* Add our listagram_footer using wp_footer WP hook */
add_action('wp_footer', 'listagram_footer');
