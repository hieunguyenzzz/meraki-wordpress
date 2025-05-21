<?php
/**
 * UAEL Login Form Module.
 *
 * @package UAEL
 */

namespace UltimateElementor\Modules\LoginForm;

use UltimateElementor\Base\Module_Base;
use UltimateElementor\Classes\UAEL_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Module.
 */
class Module extends Module_Base {

	/**
	 * Module should load or not.
	 *
	 * @since 1.20.0
	 * @access public
	 *
	 * @return bool true|false.
	 */
	public static function is_enable() {
		return true;
	}

	/**
	 * Get Module Name.
	 *
	 * @since 1.20.0
	 * @access public
	 *
	 * @return string Module name.
	 */
	public function get_name() {
		return 'uael-login-form';
	}

	/**
	 * Get Widgets.
	 *
	 * @since 1.20.0
	 * @access public
	 *
	 * @return array Widgets.
	 */
	public function get_widgets() {
		return array(
			'LoginForm',
		);
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'wp_ajax_uael_login_form_submit', array( $this, 'get_form_data' ) );
		add_action( 'wp_ajax_nopriv_uael_login_form_submit', array( $this, 'get_form_data' ) );

		add_action( 'wp_ajax_uael_login_form_facebook', array( $this, 'get_facebook_data' ) );
		add_action( 'wp_ajax_nopriv_uael_login_form_facebook', array( $this, 'get_facebook_data' ) );

		add_action( 'wp_ajax_uael_login_form_google', array( $this, 'get_google_data' ) );
		add_action( 'wp_ajax_nopriv_uael_login_form_google', array( $this, 'get_google_data' ) );
	}

	/**
	 * Get Form Data via AJAX call.
	 *
	 * @since 1.20.0
	 * @access public
	 */
	public function get_form_data() {

		check_ajax_referer( 'uael-form-nonce', 'nonce' );

		$data     = array();
		$error    = array();
		$response = array();

		if ( isset( $_POST['data'] ) ) {

			$data = $_POST['data'];

			$username   = ! empty( $data['username'] ) ? sanitize_user( $data['username'] ) : '';
			$password   = ! empty( $data['password'] ) ? $data['password'] : '';
			$rememberme = ! empty( $data['rememberme'] ) ? sanitize_text_field( $data['rememberme'] ) : '';

			$user_data = get_user_by( 'login', $username );

			if ( ! $user_data ) {
				$user_data = get_user_by( 'email', $username );
			}

			if ( $user_data ) {
				$user_ID    = $user_data->ID;
				$user_email = $user_data->user_email;
				$user_pass  = $user_data->user_pass;

				if ( wp_check_password( $password, $user_pass, $user_ID ) ) {
					if ( 'forever' === $rememberme ) {
						wp_set_auth_cookie( $user_ID, true );
					} else {
						wp_set_auth_cookie( $user_ID );
					}
					wp_set_current_user( $user_ID, $username );
					do_action( 'wp_login', $user_data->user_login, $user_data );
					wp_send_json_success();

				} else {
					wp_send_json_error( 'Incorrect Password' );
				}
			} else {
				wp_send_json_error( 'Incorrect Username' );
			}
		}
	}

	/**
	 * Get Facebook Form Data via AJAX call.
	 *
	 * @since 1.20.0
	 * @access public
	 */
	public function get_facebook_data() {
		check_ajax_referer( 'uael-form-nonce', 'nonce' );

		$data      = array();
		$response  = array();
		$user_data = array();

		if ( isset( $_POST['data'] ) ) {

			$data = $_POST['data'];

			$fb_user_id   = filter_input( INPUT_POST, 'userID', FILTER_SANITIZE_STRING );
			$access_token = filter_input( INPUT_POST, 'access_token', FILTER_SANITIZE_STRING );

			$rest_data = $this->get_user_profile_info_facebook( $access_token );

			if ( empty( $fb_user_id ) || empty( $rest_data ) || ( $fb_user_id !== $rest_data->id ) ) {
				wp_send_json_error( 'Invalid Authorization' );
			}

			$name       = sanitize_user( $data['name'] );
			$first_name = sanitize_user( $data['first_name'] );
			$last_name  = sanitize_user( $data['last_name'] );

			if ( isset( $data['email'] ) || '' !== $data['email'] ) {
				$email = sanitize_email( $data['email'] );
			} else {
				$email = $data['id'] . '@facebook.com';
			}

			$user_data = get_user_by( 'email', $email );

			if ( ! empty( $user_data ) && false !== $user_data ) {
				$user_ID    = $user_data->ID;
				$user_email = $user_data->user_email;
				wp_set_auth_cookie( $user_ID );
				wp_set_current_user( $user_ID, $name );
				do_action( 'wp_login', $user_data->user_login, $user_data );

				$response['success'] = true;

			} else {
				$password = wp_generate_password( 12, true, false );

				$facebook_array = array(
					'user_login' => $name,
					'user_pass'  => $password,
					'user_email' => $email,
					'first_name' => isset( $first_name ) ? $first_name : $data['name'],
					'last_name'  => $last_name,
				);

				if ( username_exists( $name ) ) {
					// Generate something unique to append to the username in case of a conflict with another user.
					$suffix = '-' . zeroise( wp_rand( 0, 9999 ), 4 );
					$name  .= $suffix;

					$facebook_array['user_login'] = strtolower( preg_replace( '/\s+/', '', $name ) );

				}
				wp_insert_user( $facebook_array );

				$user_data = get_user_by( 'email', $email );

				if ( $user_data ) {
					$user_ID    = $user_data->ID;
					$user_email = $user_data->user_email;

					if ( wp_check_password( $password, $user_data->user_pass, $user_data->ID ) ) {
						wp_set_auth_cookie( $user_ID );
						wp_set_current_user( $user_ID, $name );
						do_action( 'wp_login', $user_data->user_login, $user_data );
						$response['success'] = true;
					}
				}
			}

			echo wp_send_json( $response );
		} else {
			die;
		}
	}

	/**
	 * Get Google Form Data via AJAX call.
	 *
	 * @since 1.20.0
	 * @access public
	 */
	public function get_google_data() {

		check_ajax_referer( 'uael-form-nonce', 'nonce' );

		$data      = array();
		$response  = array();
		$user_data = array();

		if ( isset( $_POST['data'] ) ) {

			$data = $_POST['data'];

			$name         = isset( $data['name'] ) ? sanitize_user( $data['name'] ) : '';
			$email        = isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
			$access_token = filter_input( INPUT_POST, 'access_token', FILTER_SANITIZE_STRING );

			$verified_data = $this->verify_user_data( $access_token );

			$integration_options = UAEL_Helper::get_integrations_options();

			// Check if email is verified with Google.
			if ( ( $verified_data['aud'] !== $integration_options['google_client_id'] ) || ( isset( $verified_data['email'] ) && $verified_data['email'] !== $email ) ) {
				wp_send_json_error(
					array(
						'error' => __( 'Unauthorized access', 'uael' ),
					)
				);
			}

			$user_data = get_user_by( 'email', $email );

			$response['username'] = $name;

			if ( ! empty( $user_data ) && false !== $user_data ) {

				$user_ID    = $user_data->ID;
				$user_email = $user_data->user_email;
				wp_set_auth_cookie( $user_ID );
				wp_set_current_user( $user_ID, $name );
				do_action( 'wp_login', $user_data->user_login, $user_data );
				$response['success'] = true;

			} else {

				$password = wp_generate_password( 12, true, false );

				if ( username_exists( $name ) ) {
					// Generate something unique to append to the username in case of a conflict with another user.
					$suffix = '-' . zeroise( wp_rand( 0, 9999 ), 4 );
					$name  .= $suffix;

					$user_array = array(
						'user_login' => strtolower( preg_replace( '/\s+/', '', $name ) ),
						'user_pass'  => $password,
						'user_email' => $email,
						'first_name' => $data['name'],
					);
					wp_insert_user( $user_array );
				} else {
					wp_create_user( $name, $password, $email );
				}

				$user_data = get_user_by( 'email', $email );

				if ( $user_data ) {

					$user_ID    = $user_data->ID;
					$user_email = $user_data->user_email;

					if ( wp_check_password( $password, $user_data->user_pass, $user_data->ID ) ) {

						wp_set_auth_cookie( $user_ID );
						wp_set_current_user( $user_ID, $name );
						do_action( 'wp_login', $user_data->user_login, $user_data );
						$response['success'] = true;
					}
				}
			}

			echo wp_send_json( $response );

		} else {
			die;
		}
	}

	/**
	 * Get access token info.
	 *
	 * @since 1.20.1
	 * @access public
	 * @param array $access_token Access token.
	 * @return array
	 */
	public function verify_user_data( $access_token ) {

		$google_oauth_token_url = 'https://www.googleapis.com/oauth2/v3/tokeninfo';
		$google_oauth_token_url = add_query_arg( array( 'access_token' => $access_token ), $google_oauth_token_url );
		$response               = wp_remote_get( $google_oauth_token_url );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error();
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}

	/**
	 * Function that authenticates Facebook user.
	 *
	 * @since 1.20.1
	 * @param string $access_token Access Token.
	 */
	public function get_user_profile_info_facebook( $access_token ) {
		$url      = 'https://graph.facebook.com/me';
		$url      = add_query_arg( array( 'access_token' => $access_token ), $url );
		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error();
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}

}
