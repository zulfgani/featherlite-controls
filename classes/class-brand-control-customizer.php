<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Featherlite_Brand_Control_Customizer {
	/**
	 * Constructor function.
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct () {
		add_filter( 'customize_register', array( $this, 'customizer_setup' ) );
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	} // End __construct()

	/**
	 * Add section, setting and load custom customizer control.
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function customizer_setup ( $wp_customize ) {
		
		$wp_customize->add_section( 'brand_control', array(
			'title'          => __( 'Brand Control', 'featherlite' ),
			'priority'       => 21,
			'panel'		=> 'featherlite_header_panel',
		) );
		
		$wp_customize->add_setting( 'brand_control', array(
			'default' 		=> $this->_brand_format_defaults(), // get default order
			'type' 			=> 'theme_mod',
			'capability' 	=> 'edit_theme_options',
		) );

		require_once( 'class-brand-control-customizer-control.php' );

		$wp_customize->add_control( new Featherlite_Brand_Control_Customizer_Control( $wp_customize, 'brand_control', array(
			'description'       => __( 'Re-order the Brand components.', 'featherlite' ),
			'section'           => 'brand_control',
			'settings'          => 'brand_control',
			'choices'           => $this->_get_brand_hooked_functions(),
			'priority'          => 10,
			'type'				=> 'hidden',			
			'sanitize_callback'	=> array( $this, '_canvas_sanitize_components' ),
		) ) );
	
	}
	
	/**
	 * Enqueue scripts.
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( Featherlite_Controls()->brand_token . '-sortables', esc_url( Featherlite_Controls()->plugin_url . 'assets/js/brand.js' ), array( 'jquery', 'jquery-ui-sortable' ), Featherlite_Controls()->version );
	}

	/**
	 * Enqueue styles.
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function enqueue_styles() {
		wp_enqueue_style( Featherlite_Controls()->brand_token . '-customizer',  esc_url( Featherlite_Controls()->plugin_url . 'assets/css/brand.css' ), '', Featherlite_Controls()->version );
	}

	/**
	 * Ensures only array keys matching the original settings specified in add_control() are valid.
	 * @access  public
	 * @since   1.0.0
	 * @return  string The valid component.
	*/
	public function _canvas_sanitize_components( $input ) {
		$valid = $this->_get_brand_hooked_functions();

		if ( array_key_exists( $input, $valid ) || array_key_exists( str_replace( '[disabled]', '', $input ), $valid ) ) {
			return $input;
		} else {
			return '';
		}
	}

	/**
	 * Retrive the functions hooked on to the "actions_header" hook.
	 * @access  private
	 * @since   1.0.0
	 * @return  array An array of the functions, grouped by function name, with a formatted title.
	 */
	private function _get_brand_hooked_functions () {
		global $wp_filter;

		$response = array();

		if ( isset( $wp_filter[Featherlite_Controls()->brand_hook] ) && 0 < iterator_count( $wp_filter[Featherlite_Controls()->brand_hook] ) ) {
			foreach ( $wp_filter[Featherlite_Controls()->brand_hook] as $k => $v ) {
				if ( is_array( $v ) ) {
					foreach ( $v as $i => $j ) {
						if ( is_array( $j['function'] ) ) {
							$i = get_class( $j['function'][0] ) . '@' . $j['function'][1];
							$response[$i] = $this->_brand_format_title( $j['function'][1] );
						} else {
							$response[$i] = $this->_brand_format_title( $i );
						}
					}
				}
			}
		}

		return $response;
	} // End _get_hooked_functions()

	/**
	 * Format a given key into a title.
	 * @access  private
	 * @since   1.0.0
	 * @return  string A formatted title. If no formatting is possible, return the key.
	 */
	private function _brand_format_title ( $key ) {
		$prefix = (string)apply_filters( 'brand_control_prefix', 'featherlite_display_' );
		$title = $key;

		$title = str_replace( $prefix, '', $title );
		$title = str_replace( '_', ' ', $title );
		$title = ucwords( $title );

		return $title;
	} // End _maybe_format_title()

	/**
	 * Format an array of components as a comma separated list.
	 * @access  private
	 * @since   1.0.0
	 * @return  string A list of components separated by a comma.
	 */
	private function _brand_format_defaults () {
		$components = $this->_get_brand_hooked_functions();
		$defaults = array();

		foreach ( $components as $k => $v ) {
			if ( apply_filters( 'brand_control_hide_' . $k, false ) ) {
				$defaults[] = '[disabled]' . $k;
			} else {
				$defaults[] = $k;
			}
		}

		return join( ',', $defaults );
	}
}

new Featherlite_Brand_Control_Customizer();