<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Inventor_Customizations_Listing_Filter
 *
 * @class Inventor_Customizations_Listing_Filter
 * @package Inventor/Classes/Customizations
 * @author Pragmatic Mates
 */
class Inventor_Customizations_Listing_Filter {
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
		$wp_customize->add_section( 'inventor_listing_filter', array(
			'title' 	=> __( 'Inventor Listing Filter', 'inventor' ),
			'priority' 	=> 1,
		) );	

		// Filter fields
		$filter_fields = apply_filters( 'inventor_filter_fields', array() );

		// Post Types
		$wp_customize->add_setting( 'inventor_filter_fields', array(
			'default'           => array(),
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => array( 'Inventor_Customize_Control_Checkbox_Multiple', 'sanitize' ),
		) );

		$wp_customize->add_control( new Inventor_Customize_Control_Checkbox_Multiple(
			$wp_customize,
			'inventor_filter_fields',
			array(
				'section'       => 'inventor_listing_filter',
				'label'         => __( 'Filter fields', 'inventor' ),
				'choices'       => $filter_fields,
				'description'   => __( 'Which fields should be available in the filter.', 'inventor' ),
			)
		) );

		// Default multiple values filter field type
		$wp_customize->add_setting( 'inventor_filter_multivalue_field_type', array(
			'default'           => 'SELECT',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( 'inventor_filter_multivalue_field_type', array(
			'type'          => 'select',
			'label'         => __( 'Default multiple values filter field widget', 'inventor' ),
			'description'	=> __( 'How to display filter fields of types with multiple value available', 'inventor' ),
			'section'       => 'inventor_listing_filter',
			'settings'      => 'inventor_filter_multivalue_field_type',
			'choices'		=> array(
				'SELECT'		=> __( 'Multi select (dropdown)', 'inventor' ),
				'CHECKBOXES'	=> __( 'Checkboxes', 'inventor' ),
			)
		) );
	}
}

Inventor_Customizations_Listing_Filter::init();
