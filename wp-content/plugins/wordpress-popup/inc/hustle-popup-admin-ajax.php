<?php
if( !class_exists("Hustle_Popup_Admin_Ajax") ):
/**
 * Class Hustle_Popup_Admin_Ajax
 * Takes care of all the ajax calls to admin pages
 *
 */
class Hustle_Popup_Admin_Ajax {

	private $_hustle;
	private $_admin;

	public function __construct( Opt_In $hustle, Hustle_Popup_Admin $admin ){

		$this->_hustle = $hustle;
		$this->_admin = $admin;

		add_action("wp_ajax_render_provider_account_options", array( $this, "render_provider_account_options" ));
		add_action("wp_ajax_refresh_provider_account_details", array( $this, "refresh_provider_account_details" ));
		add_action("wp_ajax_hustle_save_popup_module", array( $this, "save_popup" ));
		add_action("wp_ajax_hustle_popup_prepare_custom_css", array( $this, "prepare_custom_css" ));
		add_action("wp_ajax_hustle_popup_module_toggle_state", array( $this, "toggle_module_state" ));
		add_action("wp_ajax_hustle_popup_module_toggle_tracking_activity", array( $this, "toggle_tracking_activity" ));
		add_action("wp_ajax_hustle_popup_toggle_test_activity", array( $this, "toggle_test_activity" ));
		add_action("wp_ajax_hustle_delete_module", array( $this, "delete_module" ));
		add_action("wp_ajax_hustle_get_email_lists", array( $this, "get_subscriptions_list" ));
		add_action("wp_ajax_inc_optin_export_subscriptions", array( $this, "export_subscriptions" ));
		add_action("wp_ajax_persist_new_welcome_close", array( $this, "persist_new_welcome_close" ));
		add_action("wp_ajax_add_module_field", array( $this, "add_module_field" ) );
		add_action("wp_ajax_add_module_fields", array( $this, "add_module_fields" ) );
		add_action( "wp_ajax_get_error_list", array( $this, "get_error_list" ) );
		add_action( "wp_ajax_clear_logs", array( $this, "clear_logs" ) );
		add_action( "wp_ajax_inc_optin_export_error_logs", array( $this, "export_error_logs" ) );
		add_action( "wp_ajax_sshare_show_page_content", array( $this, "sshare_show_page_content" ) );
		add_action( "wp_ajax_update_hubspot_referrer", array( $this, "update_hubspot_referrer" ) );
		add_action( "wp_ajax_update_constantcontact_referrer", array( $this, "update_constantcontact_referrer" ) );
	}

	/**
	 * Renders provider account options based on the selected provider ( provider id )
	 *
	 * @since 1.0
	 */
	public function render_provider_account_options(){

		Opt_In_Utils::validate_ajax_call( "change_provider_name" );

		$provider_id =  filter_input( INPUT_GET, "provider_id" );

		$module_id =  filter_input( INPUT_GET, "module_id" );
		$module_type =  filter_input( INPUT_GET, "module_type" );

		if( empty( $provider_id ) )  wp_send_json_error( __("Invalid provider", Opt_In::TEXT_DOMAIN) );

		/**
		 * @var $provider Opt_In_Provider_Interface
		 */
		$provider = Opt_In::get_provider_by_id( $provider_id );

		$provider = Opt_In::provider_instance( $provider );

		// Make sure to use the correct module type's page.
		$provider->set_arg( 'current_page', 'hustle_' . $module_type );

		$options = $provider->is_authorized() ? $provider->get_account_options( $module_id ) : $provider->get_options();

		$html = "";
		foreach( $options as $key =>  $option ){
			$html .= $this->_hustle->render("general/option", array_merge( $option, array( "key" => $key ) ), true);
		}

		wp_send_json_success( $html );
	}

	/**
	 * Refreshes provider account details after the account creds are added and submitted
	 *
	 * @since 1.0
	 */
	public function refresh_provider_account_details(){

		Opt_In_Utils::validate_ajax_call( "refresh_provider_details" );

		$provider_id =  filter_input( INPUT_POST, "optin_provider_name" );

		$module_id =  filter_input( INPUT_POST, "module_id" );

		if( empty( $provider_id ) )  wp_send_json_error( __("Invalid provider", Opt_In::TEXT_DOMAIN) );

		$api_key =  filter_input( INPUT_POST, "optin_api_key" );
		/**
		 * @var $provider Opt_In_Provider_Interface
		 */
		$provider = Opt_In::get_provider_by_id( $provider_id );

		/**
		 * @var $provider Opt_In_Provider_Abstract
		 */
		$provider = Opt_In::provider_instance( $provider );

		$provider->set_arg( "api_key", $api_key );

		if ( filter_input( INPUT_POST, "optin_secret_key" ) )
			$provider->set_arg( "secret", filter_input( INPUT_POST, "optin_secret_key" ) );
		if ( filter_input( INPUT_POST, "optin_username" ) )
			$provider->set_arg( "username", filter_input( INPUT_POST, "optin_username" ) );
		if ( filter_input( INPUT_POST, "optin_password" ) )
			$provider->set_arg( "password", filter_input( INPUT_POST, "optin_password" ) );

		if ( filter_input( INPUT_POST, "optin_account_name" ) )
			$provider->set_arg( "account_name", filter_input( INPUT_POST, "optin_account_name" ) );

		if ( filter_input( INPUT_POST, "optin_url" ) )
			$provider->set_arg( "url", filter_input( INPUT_POST, "optin_url" ) );

		if ( filter_input( INPUT_POST, "optin_app_id" ) )
			$provider->set_arg( "app_id", filter_input( INPUT_POST, "optin_app_id" ) );


		$options = $provider->get_options();

		if( !empty( $options ) )
			$provider->update_option( Opt_In::get_const( $provider, 'LISTS' ), serialize( $options ) );


		if ( !is_wp_error( $options ) ) {
			$html = "";
			if ( !empty( $options ) ) {
				foreach( $options as $key =>  $option ){
					$html .= $this->_hustle->render("general/option", array_merge( $option, array( "key" => $key ) ), true);
				}
			}

			wp_send_json_success( $html );
		} else {
			/**
			 * @var WP_Error $options
			 */
			wp_send_json_error( implode( "<br/>", $options->get_error_messages() ) );
		}

	}

	/**
	 * Prepares the custom css string for the live previewer
	 *
	 * @since 1.0
	 */
	public function prepare_custom_css(){

		Opt_In_Utils::validate_ajax_call( "hustle_module_prepare_custom_css" );

		$_POST = stripslashes_deep( $_POST );
		if( !isset($_POST['css'] ) ) {
			wp_send_json_error();
		}

		$cssString = $_POST['css'];

		$styles = Opt_In::prepare_css($cssString, "");

		wp_send_json_success( $styles );
	}

	/**
	 * Checks if e-Newsletter should be synced with current local collection
	 *
	 * @since 3.0
	 *
	 * @return true|false
	 */
	public function check_enews_sync(){

		//do sync if e-Newsletter plugin is active, e-Newsletter is the active provider,
		//and if the plugin was deactivated or e-Newsletter wasn't the active provider before
		if( 'e_newsletter' === $_POST['content']['active_email_service'] && class_exists( 'Email_Newsletter' ) ) {

			if( !isset($_POST['content']['email_services']['e_newsletter']['synced']) || '0' === $_POST['content']['email_services']['e_newsletter']['synced'] ){
				$_POST['content']['email_services']['e_newsletter']['synced'] = 1;
				return true;
			}
			return false;

		} else {

			$_POST['content']['email_services']['e_newsletter']['synced'] = 0;
			return false;
		}
	}

	/**
	 * Does the actual sync with the current local collection and e-Newsletter
	 * It's only called when check_enews_sync method returns true
	 *
	 * @since 3.0
	 *
	 * @var int $id
	 */
	public function do_sync( $id ){
		$provider = Opt_In::get_provider_by_id( $_POST['content']['active_email_service'] );
		$provider = Opt_In::provider_instance( $provider );
		$module = Hustle_Module_Model::instance()->get( $id );
		$lists = isset($_POST['content']['email_services']['e_newsletter']['list_id']) ? $_POST['content']['email_services']['e_newsletter']['list_id'] : array();
		$provider->sync_with_current_local_collection( $module, $lists );
	}

	/**
	 * Saves new optin to db
	 *
	 * @since 1.0
	 */
	public function save_popup(){

		Opt_In_Utils::validate_ajax_call( "hustle_save_popup_module" );

		$_POST = stripslashes_deep( $_POST );

		//check if e-Newsletter sync should be done and set new "Synced" value
		if( isset($_POST['content']['email_services']['e_newsletter']) ){
			$do_sync = $this->check_enews_sync();
		}

		if( "-1" === $_POST['id']  )
			$res = $this->_admin->save_new( $_POST );
		else
			$res = $this->_admin->update_module( $_POST );

		//do sync with e-Newsletter after saving because we need the ID
		if( isset($do_sync) && $do_sync ) {
			$this->do_sync( $res );
		}

		wp_send_json( array(
			"success" => false === $res ? false: true,
			"data" => $res
		) );
	}


	/**
	 * Toggles optin active state
	 *
	 * @since 1.0
	 */
	public function toggle_module_state(){

		Opt_In_Utils::validate_ajax_call( "popup_module_toggle_state" );

		$id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
		$enabled = trim( filter_input( INPUT_POST, 'enabled', FILTER_SANITIZE_STRING ) );
		$enabled = in_array( $enabled, array( 'true', true ), true );

		if( !$id )
			wp_send_json_error(__("Invalid Request", Opt_In::TEXT_DOMAIN));

		$module = Hustle_Module_Model::instance()->get($id);
		$current_state = (bool) $module->active;

		if ( $enabled !== $current_state ) {
			$result = $module->toggle_state();
		} else {
			$result = true; // all is well
		}

		// clear test types
		$module->update_meta( $this->_hustle->get_const_var( "TEST_TYPES", $module ), array() );

		if( $result )
			wp_send_json_success( __("Successful", Opt_In::TEXT_DOMAIN) );
		else
			wp_send_json_error( __("Failed", Opt_In::TEXT_DOMAIN) );
	}

	public function toggle_tracking_activity(){

		Opt_In_Utils::validate_ajax_call( "popup_toggle_tracking_activity" );

		$id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
		$type = trim( filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING ) );

		if( !$id || !$type )
			wp_send_json_error(__("Invalid Request", Opt_In::TEXT_DOMAIN));

		$module =  Hustle_Module_Model::instance()->get($id);

		if( 'popup' !== $type )
			wp_send_json_error( __("Invalid environment: %s", Opt_In::TEXT_DOMAIN), $type);

		$result = $module->toggle_type_track_mode( $type );

		if( $result && !is_wp_error( $result ) )
			wp_send_json_success( __("Successful", Opt_In::TEXT_DOMAIN) );
		else
			wp_send_json_error( $result->get_error_message() );
	}

	/**
	 * Toggles optin type test mode
	 *
	 * @since 1.0
	 */
	public function toggle_test_activity(){

		Opt_In_Utils::validate_ajax_call( "popup_toggle_test_activity" );

		$id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
		$type = trim( filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING ) );

		if( !$id || !$type )
			wp_send_json_error(__("Invalid Request", Opt_In::TEXT_DOMAIN));

		$module =  Hustle_Module_Model::instance()->get($id);

		if( 'popup' !== $type )
			wp_send_json_error(__("Invalid environment: %s", Opt_In::TEXT_DOMAIN), $type);

		$result = $module->toggle_type_test_mode( $type );

		if( $result && !is_wp_error( $result ) )
			wp_send_json_success( __("Successful", Opt_In::TEXT_DOMAIN) );
		else
			wp_send_json_error( $result->get_error_message() );
	}

	/**
	 * Delete optin
	 */
	public function delete_module(){

		Opt_In_Utils::validate_ajax_call( "hustle_delete_module" );

		$id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
		$multiple = filter_input( INPUT_POST, 'multiple', FILTER_VALIDATE_INT );
		$ids = json_decode( filter_input( INPUT_POST, 'ids' ) );

		if ( $multiple ) {
			$ids = ( !is_array( $ids ) ) ? (array) $ids : $ids;
			foreach( $ids as $id ) {
				$result = Hustle_Module_Model::instance()->get($id)->delete();
				if ( !$result ) {
					wp_send_json_error( __("Failed", Opt_In::TEXT_DOMAIN) );
				}
			}
		} else {
			if( !$id )
				wp_send_json_error(__("Invalid Request", Opt_In::TEXT_DOMAIN));

			$result = Hustle_Module_Model::instance()->get($id)->delete();
		}

		if( $result )
			wp_send_json_success( __("Successful", Opt_In::TEXT_DOMAIN) );
		else
			wp_send_json_error( __("Failed", Opt_In::TEXT_DOMAIN) );
	}

	/**
	 * Retrieves the subscription list from db
	 *
	 *
	 * @since 1.1.0
	 */
	public function get_subscriptions_list(){
		Opt_In_Utils::validate_ajax_call("hustle_get_emails_list");

		$id = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );

		if( !$id )
			wp_send_json_error(__("Invalid Request", Opt_In::TEXT_DOMAIN));

		$subscriptions = Hustle_Module_Model::instance()->get($id)->get_local_subscriptions();
		$module_fields = Hustle_Module_Model::instance()->get($id)->get_content()->__get( 'form_elements' );
		// Parse JSON if string, otherwise no parsing.
		$module_fields = ( 'string' === gettype($module_fields) ) ? json_decode($module_fields, true) : $module_fields;

		if( $subscriptions )
			wp_send_json_success( array(
				"subscriptions" => $subscriptions,
				'module_fields'=> $module_fields,
			) );
		else
			wp_send_json_error( __("Failed to fetch subscriptions", Opt_In::TEXT_DOMAIN) );
	}

	/**
	 * Save persistent choice of closing new welcome notice on dashboard
	 *
	 * @since 2.0.2
	 */
	public function persist_new_welcome_close() {
		Opt_In_Utils::validate_ajax_call( "hustle_new_welcome_notice" );
		update_option("hustle_new_welcome_notice_dismissed", true);
		wp_send_json_success();
	}


	public function export_subscriptions(){
		Opt_In_Utils::validate_ajax_call( 'inc_optin_export_subscriptions' );

		$id = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );
		$type = trim( filter_input( INPUT_GET, 'type', FILTER_SANITIZE_STRING ) );

		if( !$id )
			die(esc_html__("Invalid Request", Opt_In::TEXT_DOMAIN));

		$optin = Hustle_Module_Model::instance()->get($id);
		$module_fields = $optin->get_content($type)->__get( 'form_elements' );
		// Parse JSON if string, otherwise no parsing.
		$module_fields = ( 'string' === gettype($module_fields) ) ? json_decode($module_fields, true) : $module_fields;
		$name = $optin->get_content($type)->__get( 'module_name' );
		$subscriptions = $optin->get_local_subscriptions();

		$fields = array();

		foreach ( $module_fields as $field ) {
			$fields[ $field['name'] ] = $field['label'];
		}
		$csv = implode( ', ', $fields ) . "\n";

		foreach( $subscriptions as $row ){
			$subscriber_data = array();

			foreach ( $fields as $key => $label ) {
				// Check for legacy
				if ( isset( $row->f_name ) && 'first_name' === $key )
					$key = 'f_name';
				if ( isset( $row->l_name ) && 'last_name' === $key )
					$key = 'l_name';

				$subscriber_data[ $key ] = isset( $row->$key ) ? $row->$key : '';
			}
			$csv .= implode( ', ', $subscriber_data ) . "\n";
		}

		$file_name = strtolower( sanitize_file_name( $name ) ) . ".csv";

		header("Content-type: application/x-msdownload", true, 200);
		header("Content-Disposition: attachment; filename=$file_name");
		header("Pragma: no-cache");
		header("Expires: 0");
		echo $csv; //phpcs:ignore
		die();

	}

	/**
	 * Validate new/updated custom module field.
	 **/
	public function add_module_field() {
		Opt_In_Utils::validate_ajax_call( 'optin_add_module_field' );
		$input = stripslashes_deep( $_REQUEST );

		if ( ! empty( $input ) ) {
			$provider = $input['provider'];
			$registered_providers = $this->_hustle->get_providers();
			$can_add = array(
				'success' => true,
				'field' => $input['field'],
			);

			if ( isset( $registered_providers[ $provider ] ) ) {
				$provider_class = $registered_providers[ $provider ]['class'];

				if ( class_exists( $provider_class )
					&& method_exists( $provider_class, 'add_custom_field' ) ) {
					$optin = Hustle_Module_Model::instance()->get( $input['optin_id'] );
					$can_add = call_user_func( array( $provider_class, 'add_custom_field' ), $input['field'], $optin );
				}
			}

			if ( isset( $can_add['success'] ) ) {
				wp_send_json_success( $can_add );
			} else {
				wp_send_json_error( $can_add );
			}
		}
	}

	/**
	 * Bulk Add optin module fields
	 */
	public function add_module_fields() {
		Opt_In_Utils::validate_ajax_call( 'optin_add_module_fields' );
		$can_add = array(
			'error' => true,
			'code' => 'custom',
			'message' => __( 'Unable to add custom fields', Opt_In::TEXT_DOMAIN ),
		);
		$provider = filter_input( INPUT_POST, 'provider' );
		$module_id = filter_input( INPUT_POST, 'module_id' );
		if ( $provider && $module_id ) {

			$registered_providers = $this->_hustle->get_providers();
			$default_form_elements = $this->_hustle->default_form_fields();
			if ( isset( $registered_providers[ $provider ] ) ) {
				$provider_class = $registered_providers[ $provider ]['class'];
				if ( class_exists( $provider_class )
					&& method_exists( $provider_class, 'add_custom_field' ) ) {

					$new_fields = filter_input( INPUT_POST, 'data' );
					$new_fields = json_decode( $new_fields, true );
					$default_field_keys = array_keys( $default_form_elements );
					if ( !empty ( $new_fields ) ){
						foreach ( $new_fields as $key => $new_field ) {
							if ( in_array( $new_field['name'], $default_field_keys, true ) ) {
								unset( $new_fields[$key] );
							}
						}
					}
					if ( !empty( $new_fields ) ) {
						$can_add = call_user_func( array( $provider_class, 'add_custom_field' ), $new_fields, $module_id );
					}
				}
			}
		}
		wp_send_json_error( $can_add );
	}

	public function get_error_list() {
		Opt_In_Utils::validate_ajax_call( 'hustle_get_error_logs' );
		$id = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );

		if ( (int) $id > 0 ) {
			$optin = Hustle_Module_Model::instance()->get( $id );
			$error_log = $optin->get_error_log();
			$module_fields = Hustle_Module_Model::instance()->get($id)->get_content()->__get( 'form_elements' );
			// Parse JSON if string, otherwise no parsing.
			$module_fields = ( 'string' === gettype($module_fields) ) ? json_decode($module_fields, true) : $module_fields;

			wp_send_json_success( array(
				'logs' => $error_log,
				'module_fields' => $module_fields,
			) );
		}
		wp_send_json_error(true);
	}

	public function clear_logs() {
		Opt_In_Utils::validate_ajax_call( 'optin_clear_logs' );
		$id = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );

		if ( (int) $id > 0 ) {
			Hustle_Module_Model::instance()->get( $id )->clear_error_log();
		}
		wp_send_json_success(true);
	}

	public function export_error_logs() {
		Opt_In_Utils::validate_ajax_call( 'optin_export_error_logs' );
		$id = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );

		if ( (int) $id > 0 ) {
			$optin = Hustle_Module_Model::instance()->get( $id );
			$error_log = $optin->get_error_log();
			$module_fields = json_decode($optin->get_content()->__get( 'form_elements' ));
			$name = Hustle_Module_Model::instance()->get($id)->get_content()->__get( 'module_name' );
			$csv = array(array());
			$keys = array();

			foreach ( $module_fields as $field ) {
				if ( 'submit' === $field->name || 'submit' === strtolower($field->label) ) {
					continue;
				}
				$csv[0][] = $field->label;
				$keys[] = $field->name;
			}
			$csv[0][] = __( 'Error', Opt_In::TEXT_DOMAIN );
			$csv[0][] = __( 'Date', Opt_In::TEXT_DOMAIN );
			array_push( $keys, 'error', 'date' );

			if ( ! empty( $error_log ) ) {
				foreach ( $error_log as $log ) {
					$logs = array();

					foreach ( $keys as $key ) {
						$logs[ $key ] = sanitize_text_field( $log->$key );
					}
					$csv[] = $logs;
				}
			}

			foreach ( $csv as $index => $_csv ) {
				$csv[ $index ] = implode( ',', $_csv );
			}

			$file_name = strtolower( sanitize_file_name( $name ) ) . "-errors.csv";
			header("Content-type: application/x-msdownload", true, 200);
			header("Content-Disposition: attachment; filename=$file_name");
			header("Pragma: no-cache");
			header("Expires: 0");
			echo implode( "\n", $csv );
			die();
		}
		wp_send_json_error(true);
	}

	public function sshare_show_page_content() {
		Opt_In_Utils::validate_ajax_call( "hustle_ss_stats_paged_data" );

		$page_id = filter_input( INPUT_POST, 'page_id', FILTER_VALIDATE_INT );
		$offset = ($page_id - 1) * 5;
		$ss_share_stats = Hustle_Module_Collection::instance()->get_share_stats( $offset, 5 );

		foreach($ss_share_stats as $key => $ss_stats) {
			$ss_share_stats[$key]->page_url = $ss_stats->ID ? esc_url(get_permalink($ss_stats->ID)) : esc_url(get_home_url());
			$ss_share_stats[$key]->page_title = $ss_stats->ID ? $ss_stats->post_title : get_bloginfo();
		}

		wp_send_json_success( array(
			'ss_share_stats' => $ss_share_stats
		) );
	}

	public function update_hubspot_referrer() {
		Opt_In_Utils::validate_ajax_call( "hustle_hubspot_referrer" );

		$optin_id = filter_input( INPUT_GET, 'optin_id', FILTER_VALIDATE_INT );

		if ( class_exists( 'Opt_In_HubSpot_Api') ) {
			$hubspot = new Opt_In_HubSpot_Api();
			$hubspot->get_authorization_uri( $optin_id );
		}
	}

	public function update_constantcontact_referrer() {
		Opt_In_Utils::validate_ajax_call( "hustle_constantcontact_referrer" );

		$optin_id = filter_input( INPUT_GET, 'optin_id', FILTER_VALIDATE_INT );
		if ( version_compare( PHP_VERSION, '5.3', '>=' ) && class_exists( 'Opt_In_ConstantContact_Api') ) {
			$constantcontact = new Opt_In_ConstantContact_Api();
			$constantcontact->get_authorization_uri( $optin_id );
		}
	}
}
endif;
