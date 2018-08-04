<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Inventor_Customizations_Listing_Detail
 *
 * @class Inventor_Customizations_Listing_Detail
 * @package Inventor/Classes/Customizations
 * @author Pragmatic Mates
 */
class Inventor_Customizations_Listing_Detail {
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
		$wp_customize->add_section( 'inventor_listing_detail', array(
			'title' 	=> __( 'Inventor Listing Detail', 'inventor' ),
			'priority' 	=> 1,
		) );	

		// Banner Types
		$banner_types = apply_filters( 'inventor_metabox_banner_types', array() );

		$wp_customize->add_setting( 'inventor_general_banner_types', array(
			'default'           => array_keys( $banner_types ),
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => array( 'Inventor_Customize_Control_Checkbox_Multiple', 'sanitize' ),
		) );

		$wp_customize->add_control( new Inventor_Customize_Control_Checkbox_Multiple(
			$wp_customize,
			'inventor_general_banner_types',
			array(
				'section'       => 'inventor_listing_detail',
				'label'         => __( 'Listing banner types', 'inventor' ),
				'choices'       => $banner_types,
				'description'   => __( 'List of available banner types for listings', 'inventor' ),
			)
		) );

		// Default listing banner type
		$wp_customize->add_setting( 'inventor_general_default_banner_type', array(
			'default'           => 'banner_featured_image',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( 'inventor_general_default_banner_type', array(
			'type'          => 'select',
			'label'         => __( 'Default listing banner type', 'inventor' ),
			'description'	=> __( 'Has to be one of the enabled above', 'inventor' ),
			'section'       => 'inventor_listing_detail',
			'settings'      => 'inventor_general_default_banner_type',
			'choices'		=> $banner_types
		) );

		// Show featured image in gallery
		$wp_customize->add_setting( 'inventor_general_show_featured_image_in_gallery', array(
			'default'           => false,
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( 'inventor_general_show_featured_image_in_gallery', array(
			'type'      	=> 'checkbox',
			'label'     	=> __( 'Show featured image in listing gallery', 'inventor' ),
			'description'	=> __( 'Featured image of listing will be shown in its gallery', 'inventor' ),
			'section'   	=> 'inventor_listing_detail',
			'settings'  	=> 'inventor_general_show_featured_image_in_gallery',
		) );

		// Multiple galleries
		$wp_customize->add_setting( 'inventor_multiple_listing_galleries', array(
			'default'           => false,
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( 'inventor_multiple_listing_galleries', array(
			'type'      	=> 'checkbox',
			'label'     	=> __( 'Multiple galleries', 'inventor' ),
			'description'	=> __( 'Allow multiple galleries per listing', 'inventor' ),
			'section'   	=> 'inventor_listing_detail',
			'settings'  	=> 'inventor_multiple_listing_galleries',
		) );

		// Listing map type
		$wp_customize->add_setting( 'inventor_general_listing_map_type', array(
			'default'           => 'ROADMAP',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( 'inventor_general_listing_map_type', array(
			'type'          => 'select',
			'label'         => __( 'Listing map type', 'inventor' ),
			'section'       => 'inventor_listing_detail',
			'settings'      => 'inventor_general_listing_map_type',
			'choices'		=> array(
				'ROADMAP' 		=> __( 'Roadmap', 'inventor' ),
				'SATELLITE' 	=> __( 'Satellite', 'inventor' ),
				'HYBRID' 		=> __( 'Hybrid', 'inventor' ),
			)
		) );

		// Default location tab
		$wp_customize->add_setting( 'inventor_default_location_tab', array(
			'default'           => 'MAP',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( 'inventor_default_location_tab', array(
			'type'          => 'select',
			'label'         => __( 'Default location tab', 'inventor' ),
			'section'       => 'inventor_listing_detail',
			'settings'      => 'inventor_default_location_tab',
			'choices'		=> array(
				'MAP' 			=> __( 'Map', 'inventor' ),
				'STREET_VIEW' 	=> __( 'Street View', 'inventor' ),
				'INSIDE_VIEW' 	=> __( 'Inside View', 'inventor' ),
			)
		) );

		// Show directions button in location
		$wp_customize->add_setting( 'inventor_show_directions_button', array(
			'default'           => true,
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( 'inventor_show_directions_button', array(
			'type'      	=> 'checkbox',
			'label'     	=> __( 'Show directions button', 'inventor' ),
			'description'	=> __( 'In the location section', 'inventor' ),
			'section'   	=> 'inventor_listing_detail',
			'settings'  	=> 'inventor_show_directions_button',
		) );
	}
}

Inventor_Customizations_Listing_Detail::init();
