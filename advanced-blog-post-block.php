<?php
/*
 * Plugin Name:       Advanced Blog Post Block
 * Description:       The Advanced Blog Post Block is a powerful Gutenberg Block that enhances your website's post display with advanced customization options, allowing you to create stunning and dynamic layouts effortlessly.
 * Requires at least: 6.2
 * Tested up to:      6.4.2
 * Requires PHP:      7.1
 * Version:           1.0.3
 * Author:            Best Wp Developer
 * Author URI:        https://bestwpdeveloper.com/
 * Plugin URI:        https://wordpress.org/plugins/advanced-blog-post-block/
 * Text Domain:       advanced-blog-post-block
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package           @wordpress/create-block 
 */


// Stop Direct Access 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package BWDABPB Blocks 
 * @version 1.0.0
 * Final Class BWDABPB Blocks
 */

final class BWDABPB_ADVANCED_BLOG_POST_BLOCKS_CLASS {
	
	private static $instance;

	/**
	 * Singleton Instance
	 */
	public static function instance(){
		if( is_null( self::$instance ) ){
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Class Constructor
	 */
	public function __construct(){
		$this->define_constants();

		if ( !version_compare( BWDABPB_ADVANCED_BLOG_POST_WP_VERSION, '5.8', '>=' ) ){
            add_action( 'admin_notices', [ $this, 'check_wp_version' ] );
        } elseif ( !version_compare( BWDABPB_ADVANCED_BLOG_POST_PHP_VERSION, '5.6', '>=' ) ){
            add_action( 'admin_notices', [ $this, 'check_php_version' ] );
        } elseif ( !function_exists( 'register_block_type' ) ){
            add_action( 'admin_notices', [ $this, 'gutenberg_unavailable_notice' ] );
        } else {
            $this->includes();
            // Load the plugin text domain for localization
            add_action('plugins_loaded', [$this, 'load_textdomain']);
        }
		
        // activation hook
        register_activation_hook( BWDABPB_ADVANCED_BLOG_POST_FILE, [ $this, 'blocks_activation_hook' ] );
        // deactivation hook
        register_deactivation_hook( BWDABPB_ADVANCED_BLOG_POST_FILE, [ $this, 'blocks_deactivation_hook' ] );

	}

      /**
     * Load the plugin text domain for localization
    */
    public function load_textdomain() {
        load_plugin_textdomain('advanced-blog-post-block', false, BWDABPB_ADVANCED_BLOG_POST_DIR_PATH . '/languages/');
    }

	/**
     * Define Constants
     */
    public function define_constants(){
        define('BWDABPB_ADVANCED_BLOG_POST_VERSION', '1.0.0');
        define('BWDABPB_ADVANCED_BLOG_POST_FILE', __FILE__);
		define('BWDABPB_ADVANCED_BLOG_POST_DIR', __DIR__);
        define('BWDABPB_ADVANCED_BLOG_POST_DIR_PATH', plugin_dir_path(__FILE__));
        define('BWDABPB_ADVANCED_BLOG_POST_ADMIN_URL', plugin_dir_url(__FILE__));
        define('BWDABPB_ADVANCED_BLOG_POST_WP_VERSION', (float) get_bloginfo('version'));
        define('BWDABPB_ADVANCED_BLOG_POST_PHP_VERSION', (float) phpversion());
    }

	/**
     * PHP Version Related Admin Notice
     */
    public function check_php_version(){
        /* translators: 1: Minimum required PHP version, 2: Current PHP version */
        $message = sprintf(
            esc_html__(
                'Advanced Blog Post Block requires minimum PHP version %1$s where your current PHP version is %2$2s. Please update your PHP version to enable BWDABPB Blocks features.',
                'advanced-blog-post-block'
            ),

            '5.6',
            BWDABPB_ADVANCED_BLOG_POST_PHP_VERSION
        );
        $html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
        echo wp_kses_post( $html_message );
    }

    /**
     * WordPress Version Related Admin Notice
     */
    public function check_wp_version(){
        /* translators: 1: Minimum required WordPress version, 2: Current WordPress version */
        $message = sprintf(
            esc_html__(
                'Advanced Blog Post Block requires minimum WordPress version %1$s where your current WordPress version is %2$2s. Please update your WordPress version to enable BWD Blocks features.',
                'advanced-blog-post-block'
                ),
            '5.8',
            BWDABPB_ADVANCED_BLOG_POST_WP_VERSION
        );
        $html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
        echo wp_kses_post( $html_message );
    }

    /**
     * Gutenberg Plugin Activation Related Admin Notice
     */
    public function gutenberg_unavailable_notice(){

        if ( ! current_user_can( 'install_plugins' ) ) {
            return;
        }

        $class = 'notice notice-error';
        /* translators: %s: html tags */
        $message = sprintf(
            esc_html__( 'The <%1$s>%2$s</%1$s> plugin requires <%1$s>Gutenberg</%1$s> plugin installed & activated.', 'advanced-blog-post-block' ),
            $tag = 'strong',
            BWDABPB_ADVANCED_BLOG_POST_FILE
        );

        $action_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=gutenberg' ), 'install-plugin_gutenberg' );
        $button_label = __( 'Install Gutenberg', 'advanced-blog-post-block' );

        $button = '<p><a href="' . $action_url . '" class="button-primary">' . $button_label . '</a></p><p></p>';

        printf( '<div class="%1$s"><p>%2$s</p>%3$s</div>', esc_attr( $class ), wp_kses_post( $message ), wp_kses_post( $button ) );
    }

	/**
     * Activation Hook
     */
    public function blocks_activation_hook() {
        // flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Deactivation Hook
     */
    public function blocks_deactivation_hook() {
        // flush rewrite rules
        flush_rewrite_rules();
    }

	/**
     * Include required files
     */
    public function includes(){
        require_once BWDABPB_ADVANCED_BLOG_POST_DIR_PATH . 'includes/blocks-loader.php';
        $this->bwdabpb_appsero_init_tracker();
    }

    public function bwdabpb_appsero_init_tracker() {
        require BWDABPB_ADVANCED_BLOG_POST_DIR . '/vendor/autoload.php';
        function loaded_appsero_files(){
            if ( ! class_exists( 'Appsero\Client' ) ) {
            require_once BWDABPB_ADVANCED_BLOG_POST_DIR . '/appsero/src/Client.php';
            }
            $client = new Appsero\Client( 'b2943537-cc83-4cf7-9f07-8c3279c8f271', 'Advanced blog post block', BWDABPB_ADVANCED_BLOG_POST_FILE );
            $client->insights()->init();
        }
        loaded_appsero_files();
    }
}

/**
 * Kickoff
*/
function bwdabpb_advanced_blog_post_blocks(){
	return BWDABPB_ADVANCED_BLOG_POST_BLOCKS_CLASS::instance();
}

// start plugin
bwdabpb_advanced_blog_post_blocks();
