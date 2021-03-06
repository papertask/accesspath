<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Inventor_Customizations_General
 *
 * @class Inventor_Customizations_General
 * @package Inventor/Classes/Customizations
 * @author Pragmatic Mates
 */
class Inventor_Customizations_General {
	/**
	 * Initialize customization type
	 *
	 * @access public
	 * @return void
	 */
	public static function init() {
		add_action( 'customize_register', array( __CLASS__, 'customizations' ) );
	}

	/**
	 * Customizations
	 *
	 * @access public
	 * @param object $wp_customize
	 * @return void
	 */
	public static function customizations( $wp_customize ) {
		$wp_customize->add_section( 'inventor_general', array(
			'title' 	=> __( 'Inventor General', 'inventor' ),
			'priority' 	=> 1,
		) );	

		// Post Types
		$all_post_types = Inventor_Post_Types::get_all_listing_post_types();
		$post_types = array();

		if ( is_array( $post_types ) ) {
			foreach ( $all_post_types as $obj ) {
				if ( apply_filters( 'inventor_listing_type_supported', true, $obj->name ) ) {
					$post_types[ $obj->name ] = $obj->labels->name;
				}
			}
		}

		$wp_customize->add_setting( 'inventor_general_post_types', array(
			'default'           => array_keys( $post_types ),
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => array( 'Inventor_Customize_Control_Checkbox_Multiple', 'sanitize' ),
		) );

		$wp_customize->add_control( new Inventor_Customize_Control_Checkbox_Multiple(
			$wp_customize,
			'inventor_general_post_types',
			array(
				'section'       => 'inventor_general',
				'label'         => __( 'Post types', 'inventor' ),
				'choices'       => $post_types,
				'description'   => __( 'After changing post types make sure that your resave permalinks in "Settings - Permalinks"', 'inventor' ),
			)
		) );

		// Maximum allowed categories per listing
		$wp_customize->add_setting( 'inventor_max_listing_categories', array(
			'default'           => '',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( 'inventor_max_listing_categories', array(
			'type'          => 'text',
			'label'         => __( 'Maximum number of allowed categories per listing', 'inventor' ),
			'description'   => __( 'Keep empty for unlimited.', 'inventor' ),
			'section'       => 'inventor_general',
			'settings'      => 'inventor_max_listing_categories',
		) );

		// Maximum allowed photos in gallery per listing
		$wp_customize->add_setting( 'inventor_max_gallery_photos', array(
			'default'           => '',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( 'inventor_max_gallery_photos', array(
			'type'          => 'text',
			'label'         => __( 'Maximum number of allowed photos in listing gallery', 'inventor' ),
			'description'   => __( 'Keep empty for unlimited.', 'inventor' ),
			'section'       => 'inventor_general',
			'settings'      => 'inventor_max_gallery_photos',
		) );

		// Enable parent-child listing relationship
		$wp_customize->add_setting( 'inventor_general_enable_sublistings', array(
			'default'           => false,
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( 'inventor_general_enable_sublistings', array(
			'type'      	=> 'checkbox',
			'label'     	=> __( 'Enable sublistings', 'inventor' ),
			'description'	=> __( 'Allows parent-child listing relationship', 'inventor' ),
			'section'   	=> 'inventor_general',
			'settings'  	=> 'inventor_general_enable_sublistings',
		) );

		// Purchase code
		$wp_customize->add_setting( 'inventor_purchase_code', array(
			'default'           => null,
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( 'inventor_purchase_code', array(
			'type'          => 'text',
			'label'         => __( 'Purchase code', 'inventor' ),
			'section'       => 'inventor_general',
			'settings'      => 'inventor_purchase_code',
		) );

		// Google Browser API key
		$wp_customize->add_setting( 'inventor_general_google_browser_key', array(
			'default'           => null,
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( 'inventor_general_google_browser_key', array(
			'type'          => 'text',
			'label'         => __( 'Google Browser Key', 'inventor' ),
			'description'   => __( 'Browser API key. Read more <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">here</a>.', 'inventor' ),
			'section'       => 'inventor_general',
			'settings'      => 'inventor_general_google_browser_key',
		) );

		// Google Server API key
		$wp_customize->add_setting( 'inventor_general_google_server_key', array(
			'default'           => null,
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( 'inventor_general_google_server_key', array(
			'type'          => 'text',
			'label'         => __( 'Google Server Key', 'inventor' ),
			'description'   => __( 'Server API key. Read more <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">here</a>.', 'inventor' ),
			'section'       => 'inventor_general',
			'settings'      => 'inventor_general_google_server_key',
		) );
	}
}

Inventor_Customizations_General::init();
