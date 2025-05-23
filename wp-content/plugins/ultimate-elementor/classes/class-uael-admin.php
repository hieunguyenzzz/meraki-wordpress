<?php
/**
 * UAEL Admin.
 *
 * @package UAEL
 */

namespace UltimateElementor\Classes;

use UltimateElementor\Classes\UAEL_Helper;

if ( ! class_exists( 'UAEL_Admin' ) ) {

	/**
	 * Class UAEL_Admin.
	 */
	final class UAEL_Admin {

		/**
		 * Calls on initialization
		 *
		 * @since 0.0.1
		 */
		public static function init() {

			self::initialize_ajax();
			self::initialise_plugin();
			add_action( 'after_setup_theme', __CLASS__ . '::init_hooks' );
			add_action( 'elementor/init', __CLASS__ . '::load_admin', 0 );

			add_filter( 'bsf_product_name_uael', __CLASS__ . '::uael_whitelabel_name' );
			add_filter( 'bsf_product_description_uael', __CLASS__ . '::uael_whitelabel_description' );
			add_filter( 'bsf_product_author_uael', __CLASS__ . '::uael_whitelabel_author_name' );
			add_filter( 'bsf_product_homepage_uael', __CLASS__ . '::uael_whitelabel_author_url' );
			add_filter( 'gettext', __CLASS__ . '::get_plugin_branding_name' );

			$branding = UAEL_Helper::get_white_labels();

			if ( 'disable' === $branding['enable_knowledgebase'] ) {
				add_filter( 'bsf_product_changelog_uael', '__return_empty_string' );
			}

		}

		/**
		 *  Function that renders UAEL's branding Plugin Name on Updates page
		 *
		 *  @since 1.10.1
		 *  @param string $text gets an string for is plugin name.
		 *  @return string
		 */
		public static function get_plugin_branding_name( $text ) {

			if ( is_admin() && 'Ultimate Addons for Elementor' === $text ) {

				$branding      = UAEL_Helper::get_white_labels();
				$branding_name = $branding['plugin']['name'];

				if ( ! empty( $branding_name ) ) {
					$text = $branding_name;
				}
			}

			return $text;
		}

		/**
		 * Function that renders UAEL's branding Plugin name
		 *
		 * @since 1.10.1
		 */
		public static function uael_whitelabel_name() {
			$branding      = UAEL_Helper::get_white_labels();
			$branding_name = $branding['plugin']['name'];

			if ( empty( $branding_name ) ) {
				$branding_name = __( 'Ultimate Addons for Elementor', 'uael' );
			}
			return $branding_name;

		}

		/**
		 * Function that renders UAEL's branding Plugin Description
		 *
		 * @since 1.10.1
		 */
		public static function uael_whitelabel_description() {
			$branding      = UAEL_Helper::get_white_labels();
			$branding_desc = $branding['plugin']['description'];

			if ( empty( $branding_desc ) ) {
				$branding_desc = __( 'Ultimate Addons is a premium extension for Elementor that adds 30+ widgets and works on top of any Elementor Package (Free, Pro). You can use it with any WordPress theme.', 'uael' );
			}

			return $branding_desc;
		}

		/**
		 * Function that renders UAEL's branding Plugin Author name
		 *
		 * @since 1.10.1
		 */
		public static function uael_whitelabel_author_name() {
			$branding             = UAEL_Helper::get_white_labels();
			$branding_author_name = $branding['agency']['author'];

			if ( empty( $branding_author_name ) ) {
				$branding_author_name = __( 'Brainstorm Force', 'uael' );
			}

			return $branding_author_name;
		}

		/**
		 * Function that renders UAEL's branding Plugin Author URL
		 *
		 * @since 1.10.1
		 */
		public static function uael_whitelabel_author_url() {
			$branding     = UAEL_Helper::get_white_labels();
			$branding_url = $branding['agency']['author_url'];

			if ( empty( $branding_url ) ) {
				$branding_url = __( 'https://uaelementor.com', 'uael' );
			}
			return $branding_url;

		}

		/**
		 * Defines all constants
		 *
		 * @since 0.0.1
		 */
		public static function load_admin() {
			add_action( 'elementor/editor/after_enqueue_styles', __CLASS__ . '::uael_admin_enqueue_scripts' );
		}

		/**
		 * Enqueue admin scripts
		 *
		 * @since 0.0.1
		 * @param string $hook Current page hook.
		 * @access public
		 */
		public static function uael_admin_enqueue_scripts( $hook ) {

			// Register styles.
			wp_register_style(
				'uael-style',
				UAEL_URL . 'editor-assets/css/style.css',
				array(),
				UAEL_VER
			);

			wp_enqueue_style( 'uael-style' );

			$branding = UAEL_Helper::get_white_labels();

			if ( isset( $branding['plugin']['short_name'] ) && '' !== $branding['plugin']['short_name'] ) {
				$short_name  = $branding['plugin']['short_name'];
				$custom_css  = '.elementor-element [class*="uael-icon-"]:after {';
				$custom_css .= 'content: "' . $short_name . '"; }';
				wp_add_inline_style( 'uael-style', $custom_css );
			}

			$uael_localize = apply_filters(
				'uael_admin_js_localize',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
				)
			);
			wp_localize_script( 'jquery', 'uael_admin', $uael_localize );
		}

		/**
		 * Adds the admin menu and enqueues CSS/JS if we are on
		 * the builder admin settings page.
		 *
		 * @since 0.0.1
		 * @return void
		 */
		static public function init_hooks() {
			if ( ! is_admin() ) {
				return;
			}

			// Add UAEL menu option to admin.
			add_action( 'network_admin_menu', __CLASS__ . '::menu' );
			add_action( 'admin_menu', __CLASS__ . '::menu' );

			// Filter to White labled options.
			add_filter( 'all_plugins', __CLASS__ . '::plugins_page' );

			add_action( 'uael_render_admin_content', __CLASS__ . '::render_content' );

			// Enqueue admin scripts.
			if ( isset( $_REQUEST['page'] ) && UAEL_SLUG === $_REQUEST['page'] ) {

				add_action( 'admin_enqueue_scripts', __CLASS__ . '::styles_scripts' );

				self::save_settings();
			}
		}

		/**
		 * Initialises the Plugin Name.
		 *
		 * @since 0.0.1
		 * @return void
		 */
		static public function initialise_plugin() {

			$branding_settings = UAEL_Helper::get_white_labels();

			if (
				isset( $branding_settings['plugin']['name'] ) &&
				'' !== $branding_settings['plugin']['name']
			) {
				$name = $branding_settings['plugin']['name'];
			} else {
				$name = 'Ultimate Addons for Elementor';
			}

			if (
				isset( $branding_settings['plugin']['short_name'] ) &&
				'' !== $branding_settings['plugin']['short_name']
			) {
				$short_name = $branding_settings['plugin']['short_name'];
			} else {
				$short_name = 'UAE';
			}

			define( 'UAEL_PLUGIN_NAME', $name );
			define( 'UAEL_PLUGIN_SHORT_NAME', $short_name );
		}

		/**
		 * Renders the admin settings menu.
		 *
		 * @since 0.0.1
		 * @return void
		 */
		static public function menu() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			add_submenu_page(
				'options-general.php',
				UAEL_PLUGIN_SHORT_NAME,
				UAEL_PLUGIN_SHORT_NAME,
				'manage_options',
				UAEL_SLUG,
				__CLASS__ . '::render'
			);
		}

		/**
		 * Renders the admin settings.
		 *
		 * @since 0.0.1
		 * @return void
		 */
		static public function render() {
			$action = ( isset( $_GET['action'] ) ) ? $_GET['action'] : '';
			$action = ( ! empty( $action ) && '' !== $action ) ? $action : 'general';
			$action = str_replace( '_', '-', $action );

			// Enable header icon filter below.
			$uael_icon                 = apply_filters( 'uael_header_top_icon', true );
			$uael_visit_site_url       = apply_filters( 'uael_site_url', 'https://uaelementor.com' );
			$uael_header_wrapper_class = apply_filters( 'uael_header_wrapper_class', array( $action ) );

			include_once UAEL_DIR . 'includes/admin/uael-admin.php';
		}

		/**
		 * Renders the admin settings content.
		 *
		 * @since 0.0.1
		 * @return void
		 */
		static public function render_content() {

			$action = ( isset( $_GET['action'] ) ) ? $_GET['action'] : '';
			$action = ( ! empty( $action ) && '' !== $action ) ? $action : 'general';
			$action = str_replace( '_', '-', $action );

			$uael_header_wrapper_class = apply_filters( 'uael_header_wrapper_class', array( $action ) );

			include_once UAEL_DIR . 'includes/admin/uael-' . $action . '.php';
		}

		/**
		 * Save General Setting options.
		 *
		 * @since 0.0.1
		 */
		static public function save_integration_option() {

			if ( isset( $_POST['uael-integration-nonce'] ) && wp_verify_nonce( $_POST['uael-integration-nonce'], 'uael-integration' ) ) {

				$url            = $_SERVER['REQUEST_URI'];
				$input_settings = array();
				$new_settings   = array();

				if ( isset( $_POST['uael_integration'] ) ) {

					$input_settings = $_POST['uael_integration'];

					// Loop through the input and sanitize each of the values.
					foreach ( $input_settings as $key => $val ) {

						if ( is_array( $val ) ) {
							foreach ( $val as $k => $v ) {
								$new_settings[ $key ][ $k ] = ( isset( $val[ $k ] ) ) ? sanitize_text_field( $v ) : '';
							}
						} else {
							$new_settings[ $key ] = ( isset( $input_settings[ $key ] ) ) ? sanitize_text_field( $val ) : '';
						}
					}
				}

				UAEL_Helper::update_admin_settings_option( '_uael_integration', $new_settings, true );

				$query = array(
					'message' => 'saved',
				);

				$redirect_to = add_query_arg( $query, $url );

				wp_redirect( $redirect_to );
				exit;
			} // End if statement.
		}

		/**
		 * Save White Label options.
		 *
		 * @since 0.0.1
		 */
		static public function save_branding_option() {

			if ( isset( $_POST['uael-white-label-nonce'] ) && wp_verify_nonce( $_POST['uael-white-label-nonce'], 'white-label' ) ) {

				$url             = $_SERVER['REQUEST_URI'];
				$stored_settings = UAEL_Helper::get_white_labels();
				$input_settings  = array();
				$new_settings    = array();

				if ( isset( $_POST['uael_white_label'] ) ) {

					$input_settings = $_POST['uael_white_label'];

					// Loop through the input and sanitize each of the values.
					foreach ( $input_settings as $key => $val ) {

						if ( is_array( $val ) ) {
							foreach ( $val as $k => $v ) {
								$new_settings[ $key ][ $k ] = ( isset( $val[ $k ] ) ) ? sanitize_text_field( $v ) : '';
							}
						} else {
							$new_settings[ $key ] = ( isset( $input_settings[ $key ] ) ) ? sanitize_text_field( $val ) : '';
						}
					}
				}

				if ( ! isset( $new_settings['agency']['hide_branding'] ) ) {
					$new_settings['agency']['hide_branding'] = false;
				} else {
					$url = str_replace( 'branding', 'general', $url );
				}

				$checkbox_var = array(
					'replace_logo',
					'enable_knowledgebase',
					'enable_support',
					'enable_beta_box',
					'internal_help_links',
				);

				foreach ( $checkbox_var as $key => $value ) {
					if ( ! isset( $new_settings[ $value ] ) ) {
						$new_settings[ $value ] = 'disable';
					}
				}

				$new_settings = wp_parse_args( $new_settings, $stored_settings );

				UAEL_Helper::update_admin_settings_option( '_uael_white_label', $new_settings, true );

				$query = array(
					'message' => 'saved',
				);

				$redirect_to = add_query_arg( $query, $url );

				wp_redirect( $redirect_to );
				exit;
			}
		}

		/**
		 * Branding addon on the plugins page.
		 *
		 * @since 0.0.1
		 * @param array $plugins An array data for each plugin.
		 * @return array
		 */
		static public function plugins_page( $plugins ) {

			$branding = UAEL_Helper::get_white_labels();
			$basename = plugin_basename( UAEL_DIR . 'ultimate-elementor.php' );

			if ( isset( $plugins[ $basename ] ) && is_array( $branding ) ) {

				$plugin_name = ( isset( $branding['plugin']['name'] ) && '' !== $branding['plugin']['name'] ) ? $branding['plugin']['name'] : '';
				$plugin_desc = ( isset( $branding['plugin']['description'] ) && '' !== $branding['plugin']['description'] ) ? $branding['plugin']['description'] : '';
				$author_name = ( isset( $branding['agency']['author'] ) && '' !== $branding['agency']['author'] ) ? $branding['agency']['author'] : '';
				$author_url  = ( isset( $branding['agency']['author_url'] ) && '' !== $branding['agency']['author_url'] ) ? $branding['agency']['author_url'] : '';

				if ( '' !== $plugin_name ) {
					$plugins[ $basename ]['Name']  = $plugin_name;
					$plugins[ $basename ]['Title'] = $plugin_name;
				}

				if ( '' !== $plugin_desc ) {
					$plugins[ $basename ]['Description'] = $plugin_desc;
				}

				if ( '' !== $author_name ) {
					$plugins[ $basename ]['Author']     = $author_name;
					$plugins[ $basename ]['AuthorName'] = $author_name;
				}

				if ( '' !== $author_url ) {
					$plugins[ $basename ]['AuthorURI'] = $author_url;
					$plugins[ $basename ]['PluginURI'] = $author_url;
				}
			}
			return $plugins;
		}

		/**
		 * Enqueues the needed CSS/JS for the builder's admin settings page.
		 *
		 * @since 1.0
		 */
		static public function styles_scripts() {

			// Styles.
			wp_enqueue_style( 'uael-admin-settings', UAEL_URL . 'admin/assets/admin-menu-settings.css', array(), UAEL_VER );
			// Script.
			wp_enqueue_script( 'uael-admin-settings', UAEL_URL . 'admin/assets/admin-menu-settings.js', array( 'jquery', 'wp-util', 'updates' ), UAEL_VER );

			$localize = array(
				'ajax_nonce'   => wp_create_nonce( 'uael-widget-nonce' ),
				'activate'     => __( 'Activate', 'uael' ),
				'deactivate'   => __( 'Deactivate', 'uael' ),
				'enable_beta'  => __( 'Enable Beta Updates', 'uael' ),
				'disable_beta' => __( 'Disable Beta Updates', 'uael' ),
			);

			wp_localize_script( 'uael-admin-settings', 'uael', apply_filters( 'uael_js_localize', $localize ) );
		}

		/**
		 * Save All admin settings here
		 */
		static public function save_settings() {

			// Only admins can save settings.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			self::save_integration_option();
			self::save_branding_option();

			// Let extensions hook into saving.
			do_action( 'uael_admin_settings_save' );
		}

		/**
		 * Initialize Ajax
		 */
		static public function initialize_ajax() {
			// Ajax requests.
			add_action( 'wp_ajax_uael_activate_widget', __CLASS__ . '::activate_widget' );
			add_action( 'wp_ajax_uael_deactivate_widget', __CLASS__ . '::deactivate_widget' );

			add_action( 'wp_ajax_uael_bulk_activate_widgets', __CLASS__ . '::bulk_activate_widgets' );
			add_action( 'wp_ajax_uael_bulk_deactivate_widgets', __CLASS__ . '::bulk_deactivate_widgets' );

			add_action( 'wp_ajax_uael_allow_beta_updates', __CLASS__ . '::allow_beta_updates' );
		}

		/**
		 * Activate module
		 */
		static public function activate_widget() {

			check_ajax_referer( 'uael-widget-nonce', 'nonce' );

			$module_id             = sanitize_text_field( $_POST['module_id'] );
			$widgets               = UAEL_Helper::get_admin_settings_option( '_uael_widgets', array() );
			$widgets[ $module_id ] = $module_id;
			$widgets               = array_map( 'esc_attr', $widgets );

			// Update widgets.
			UAEL_Helper::update_admin_settings_option( '_uael_widgets', $widgets );

			echo $module_id;

			die();
		}

		/**
		 * Deactivate module
		 */
		static public function deactivate_widget() {

			check_ajax_referer( 'uael-widget-nonce', 'nonce' );

			$module_id             = sanitize_text_field( $_POST['module_id'] );
			$widgets               = UAEL_Helper::get_admin_settings_option( '_uael_widgets', array() );
			$widgets[ $module_id ] = 'disabled';
			$widgets               = array_map( 'esc_attr', $widgets );

			// Update widgets.
			UAEL_Helper::update_admin_settings_option( '_uael_widgets', $widgets );

			echo $module_id;

			die();
		}

		/**
		 * Activate all module
		 */
		static public function bulk_activate_widgets() {

			check_ajax_referer( 'uael-widget-nonce', 'nonce' );

			// Get all widgets.
			$all_widgets = UAEL_Helper::get_widget_list();
			$new_widgets = array();

			// Set all extension to enabled.
			foreach ( $all_widgets  as $slug => $value ) {
				$new_widgets[ $slug ] = $slug;
			}

			// Escape attrs.
			$new_widgets = array_map( 'esc_attr', $new_widgets );

			// Update new_extensions.
			UAEL_Helper::update_admin_settings_option( '_uael_widgets', $new_widgets );

			echo 'success';

			die();
		}

		/**
		 * Deactivate all module
		 */
		static public function bulk_deactivate_widgets() {

			check_ajax_referer( 'uael-widget-nonce', 'nonce' );

			// Get all extensions.
			$old_widgets = UAEL_Helper::get_widget_list();
			$new_widgets = array();

			// Set all extension to enabled.
			foreach ( $old_widgets as $slug => $value ) {
				$new_widgets[ $slug ] = 'disabled';
			}

			// Escape attrs.
			$new_widgets = array_map( 'esc_attr', $new_widgets );

			// Update new_extensions.
			UAEL_Helper::update_admin_settings_option( '_uael_widgets', $new_widgets );

			echo 'success';

			die();
		}

		/**
		 * Allow beta updates
		 */
		static public function allow_beta_updates() {

			check_ajax_referer( 'uael-widget-nonce', 'nonce' );

			$beta_update = sanitize_text_field( $_POST['allow_beta'] );

			// Update new_extensions.
			UAEL_Helper::update_admin_settings_option( '_uael_beta', $beta_update );

			echo 'success';

			die();
		}

	}

	UAEL_Admin::init();

}

