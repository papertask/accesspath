<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Inventor_Customizations_Listing_Archive
 *
 * @class Inventor_Customizations_Listing_Archive
 * @package Inventor/Classes/Customizations
 * @author Pragmatic Mates
 */
class Inventor_Customizations_Listing_Archive {
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
		$wp_customize->add_section( 'inventor_listing_archive', array(
			'title' 	=> __( 'Inventor Listing Archive', 'inventor' ),
			'priority' 	=> 1,
		) );	

		// Default listing sorting
		$wp_customize->add_setting( 'inventor_general_default_listing_sort', array(
			'default'           => 'published',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$sort_by_choices = apply_filters( 'inventor_filter_sort_by_choices', array() );
		$sort_by_choices['rand'] = __( 'Random', 'inventor' );

		$wp_customize->add_control( 'inventor_general_default_listing_sort', array(
			'type'          => 'select',
			'label'         => __( 'Default sorting for listing archive', 'inventor' ),
			'section'       => 'inventor_listing_archive',
			'settings'      => 'inventor_general_default_listing_sort',
			'choices'		=> $sort_by_choices
		) );

		// Default listing ordering by
		$wp_customize->add_setting( 'inventor_general_default_listing_order', array(
			'default'           => 'desc',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( 'inventor_general_default_listing_order', array(
			'type'          => 'select',
			'label'         => __( 'Default ordering for listing archive', 'inventor' ),
			'section'       => 'inventor_listing_archive',
			'settings'      => 'inventor_general_default_listing_order',
			'choices'		=> array(
				'asc'			=> 'Ascending',
				'desc'			=> 'Descending',
			),
		) );

		// Default distance filter value
		$wp_customize->add_setting( 'inventor_general_default_distance', array(
			'default'           => 15,
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( 'inventor_general_default_distance', array(
			'type'          => 'text',
			'label'         => __( 'Default distance for listing filter', 'inventor' ),
			'description'   => __( 'In units set in Inventor Measurement section.', 'inventor' ),
			'section'       => 'inventor_listing_archive',
			'settings'      => 'inventor_general_default_distance',
		) );

		// Show listing archive as grid
		$wp_customize->add_setting( 'inventor_general_show_listing_archive_as_grid', array(
			'default'           => false,
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( 'inventor_general_show_listing_archive_as_grid', array(
			'type'      	=> 'checkbox',
			'label'     	=> __( 'Show listing archive as grid', 'inventor' ),
			'description'	=> __( 'Listings will be shown as box instead of row', 'inventor' ),
			'section'   	=> 'inventor_listing_archive',
			'settings'  	=> 'inventor_general_show_listing_archive_as_grid',
		) );

		// Show featured listings on top
		$wp_customize->add_setting( 'inventor_general_featured_on_top', array(
			'default'           => false,
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( 'inventor_general_featured_on_top', array(
			'type'      	=> 'checkbox',
			'label'     	=> __( 'Show featured listings on top', 'inventor' ),
			'description'	=> __( 'In archive pages if filter is not set', 'inventor' ),
			'section'   	=> 'inventor_listing_archive',
			'settings'  	=> 'inventor_general_featured_on_top',
		) );

		// Show featured listings always on top
		$wp_customize->add_setting( 'inventor_general_featured_always_on_top', array(
			'default'           => false,
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( 'inventor_general_featured_always_on_top', array(
			'type'      	=> 'checkbox',
			'label'     	=> __( 'Show featured listings always on top.', 'inventor' ),
			'description'	=> __( 'In archive pages even if filter is set. (Previous setting has to be set too.)', 'inventor' ),
			'section'   	=> 'inventor_listing_archive',
			'settings'  	=> 'inventor_general_featured_always_on_top',
		) );

		// Exclude sublistings from archive pages
		$wp_customize->add_setting( 'inventor_general_exclude_sublistings', array(
			'default'           => false,
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( 'inventor_general_exclude_sublistings', array(
			'type'      	=> 'checkbox',
			'label'     	=> __( 'Exclude sublistings', 'inventor' ),
			'description'	=> __( 'from archive pages', 'inventor' ),
			'section'   	=> 'inventor_listing_archive',
			'settings'  	=> 'inventor_general_exclude_sublistings',
		) );
	}
}

Inventor_Customizations_Listing_Archive::init();
