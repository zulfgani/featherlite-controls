<?php
/**
 * Plugin Name: FeatherLite Controls
 * Plugin URI: https://github.com/zulfgani/featherlite-controls/
 * Description: Hi! I'm here to assist you with re-ordering or disabling components of your theme's various designs i.e Header, Homepage, Footer e.t.c.
 * Version: 1.0.0
 * Author: GetFeatherLite
 * Author URI: https://getfeatherlite.com/
 * Requires at least: 1.0.0
 * Tested up to: 1.1.0
 *
 * Text Domain: featherlite-controls
 *
 * @package FeatherLite_Controls
 * @category Addon
 * @author GetFeatherLite
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Returns the main instance of Featherlite_Controls to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Featherlite_Controls
 */
function Featherlite_Controls() {
	return Featherlite_Controls::instance();
} // End Featherlite_Controls()

Featherlite_Controls();

/**
 * Main Featherlite_Controls Class
 *
 * @class Featherlite_Controls
 * @version	1.0.0
 * @since 1.0.0
 * @package	FeatherLite_Controls
 * @author GetFeatherLite
 */
final class Featherlite_Controls {
	/**
	 * Featherlite_Controls The single instance of Featherlite_Controls.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $token;
	public $header_token;
	public $masthead_token;
	public $brand_token;
	public $post_token;
	public $footer_token;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $version;

	/**
	 * An instance of the Featherlite_Controls_Admin class.
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $admin;

	/**
	 * The name of the hook on which we will be working our magic.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $hook;
	public $header_hook;
	public $masthead_hook;
	public $brand_hook;
	public $post_hook;
	public $footer_hook;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct () {
		$this->plugin_url 		= plugin_dir_url( __FILE__ );
		$this->plugin_path 		= plugin_dir_path( __FILE__ );
		$this->version 			= '1.0.0';
		$this->token 			= 'featherlite-controls';
		$this->header_token 	= 'header-control';
		$this->masthead_token 	= 'masthead-control';		
		$this->brand_token 		= 'brand-control';
		$this->post_token 		= 'post-control';
		$this->footer_token 	= 'footer-control';
		$this->hook 			= (string)apply_filters( 'homepage_control_hook', 'featherlite_homepage' );
		$this->header_hook 		= (string)apply_filters( 'header_control_hook', 'featherlite_header' );
		$this->masthead_hook 	= (string)apply_filters( 'masthead_control_hook', 'featherlite_main_header' );
		$this->brand_hook 		= (string)apply_filters( 'brand_control_hook', 'featherlite_brand_area' );
		$this->post_hook 		= (string)apply_filters( 'post_control_hook', 'single_post_render' );
		$this->footer_hook 		= (string)apply_filters( 'footer_control_hook', 'featherlite_footer' );

		add_action( 'plugins_loaded', array( $this, 'maybe_migrate_data' ) );
		add_action( 'plugins_loaded', array( $this, 'header_migrate_data' ) );
		add_action( 'plugins_loaded', array( $this, 'masthead_migrate_data' ) );
		add_action( 'plugins_loaded', array( $this, 'brand_migrate_data' ) );
		add_action( 'plugins_loaded', array( $this, 'post_migrate_data' ) );
		add_action( 'plugins_loaded', array( $this, 'footer_migrate_data' ) );

		register_activation_hook( __FILE__, array( $this, 'install' ) );

		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		/* Setup Customizer. */
		require_once( 'classes/class-featherlite-controls-customizer.php' );
		require_once( 'classes/class-header-control-customizer.php' );
		require_once( 'classes/class-masthead-control-customizer.php' );
		require_once( 'classes/class-brand-control-customizer.php' );
		require_once( 'classes/class-post-control-customizer.php' );
		require_once( 'classes/class-footer-control-customizer.php' );

		/* Reorder Components. */
		if ( ! is_admin() ) {
			add_action( 'get_header', array( $this, 'maybe_apply_restructuring_filter' ) );
			add_action( 'get_header', array( $this, 'header_apply_restructuring_filter' ) );
			add_action( 'get_header', array( $this, 'masthead_apply_restructuring_filter' ) );	
			add_action( 'get_header', array( $this, 'brand_apply_restructuring_filter' ) );	
			add_action( 'get_header', array( $this, 'post_apply_restructuring_filter' ) );	
			add_action( 'get_header', array( $this, 'footer_apply_restructuring_filter' ) );
		}
	} // End __construct()

	/**
	 * Main Featherlite_Controls Instance
	 *
	 * Ensures only one instance of Featherlite_Controls is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Featherlite_Controls()
	 * @return Main Featherlite_Controls instance
	 */
	public static function instance () {
		if ( is_null( self::$_instance ) )
			self::$_instance = new self();
		return self::$_instance;
	} // End instance()

	/**
	 * Load the localisation file.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'featherlite-controls' );
	} // End load_plugin_textdomain()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
	} // End __wakeup()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number() {
		// Log the version number.
		update_option( $this->token . '_version', $this->version );
	} // End _log_version_number()

	/**
	 * Migrate data from versions prior to 1.0.0.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function maybe_migrate_data() {
		$options = get_theme_mod( 'homepage_control' );

		if ( ! isset( $options ) ) {
			return; // Option is empty, probably first time installing the plugin.
		}

		if ( is_array( $options ) ) {
			$order = '';
			$disabled = '';
			$components = array();

			if ( isset( $options['component_order'] ) ) {
				$order = explode( ',', $options['component_order'] );

				if ( isset( $options['disabled_components'] ) ) {
					$disabled = explode( ',', $options['disabled_components'] );
				}

				if ( 0 < count( $order ) ) {
					foreach ( $order as $k => $v ) {
						if ( in_array( $v, $disabled ) ) {
							$components[] = '[disabled]' . $v; // Add disabled tag
						} else {
							$components[] = $v;
						}
					}
				}
			}

			$components = join( ',', $components );

			// Replace old data
			set_theme_mod( 'homepage_control', $components );
		}
	} // End maybe_migrate_data()
	
	// Header Control
	/**
	 * Migrate data from versions prior to 2.0.0.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function header_migrate_data() {
		
		$options = get_theme_mod( 'header_control' );

		if ( ! isset( $options ) ) {
			return; // Option is empty, probably first time installing the plugin.
		}		

		if ( is_array( $options ) ) {
			$order = '';
			$disabled = '';
			$components = array();

			if ( isset( $options['component_order'] ) ) {
				$order = explode( ',', $options['component_order'] );

				if ( isset( $options['disabled_components'] ) ) {
					$disabled = explode( ',', $options['disabled_components'] );
				}

				if ( 0 < count( $order ) ) {
					foreach ( $order as $k => $v ) {
						if ( in_array( $v, $disabled ) ) {
							$components[] = '[disabled]' . $v; // Add disabled tag
						} else {
							$components[] = $v;
						}
					}
				}
			}

			$components = join( ',', $components );

			// Replace old data
			set_theme_mod( 'header_control', $components );
		}
	} // End header_migrate_data()
	
	// Masthead Control
	/**
	 * Migrate data from versions prior to 1.0.0.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function masthead_migrate_data() {
		
		$options = get_theme_mod( 'masthead_control' );

		if ( ! isset( $options ) ) {
			return; // Option is empty, probably first time installing the plugin.
		}		

		if ( is_array( $options ) ) {
			$order = '';
			$disabled = '';
			$components = array();

			if ( isset( $options['component_order'] ) ) {
				$order = explode( ',', $options['component_order'] );

				if ( isset( $options['disabled_components'] ) ) {
					$disabled = explode( ',', $options['disabled_components'] );
				}

				if ( 0 < count( $order ) ) {
					foreach ( $order as $k => $v ) {
						if ( in_array( $v, $disabled ) ) {
							$components[] = '[disabled]' . $v; // Add disabled tag
						} else {
							$components[] = $v;
						}
					}
				}
			}

			$components = join( ',', $components );

			// Replace old data
			set_theme_mod( 'masthead_control', $components );
		}
	} // End masthead_migrate_data()
	
	// Brand Control
	/**
	 * Migrate data from versions prior to 1.0.0.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function brand_migrate_data() {
		
		$options = get_theme_mod( 'brand_control' );

		if ( ! isset( $options ) ) {
			return; // Option is empty, probably first time installing the plugin.
		}		

		if ( is_array( $options ) ) {
			$order = '';
			$disabled = '';
			$components = array();

			if ( isset( $options['component_order'] ) ) {
				$order = explode( ',', $options['component_order'] );

				if ( isset( $options['disabled_components'] ) ) {
					$disabled = explode( ',', $options['disabled_components'] );
				}

				if ( 0 < count( $order ) ) {
					foreach ( $order as $k => $v ) {
						if ( in_array( $v, $disabled ) ) {
							$components[] = '[disabled]' . $v; // Add disabled tag
						} else {
							$components[] = $v;
						}
					}
				}
			}

			$components = join( ',', $components );

			// Replace old data
			set_theme_mod( 'brand_control', $components );
		}
	} // End brand_migrate_data()
	
	// Post Control
	/**
	 * Migrate data from versions prior to 1.0.0.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function post_migrate_data() {
		
		$options = get_theme_mod( 'post_control' );

		if ( ! isset( $options ) ) {
			return; // Option is empty, probably first time installing the plugin.
		}		

		if ( is_array( $options ) ) {
			$order = '';
			$disabled = '';
			$components = array();

			if ( isset( $options['component_order'] ) ) {
				$order = explode( ',', $options['component_order'] );

				if ( isset( $options['disabled_components'] ) ) {
					$disabled = explode( ',', $options['disabled_components'] );
				}

				if ( 0 < count( $order ) ) {
					foreach ( $order as $k => $v ) {
						if ( in_array( $v, $disabled ) ) {
							$components[] = '[disabled]' . $v; // Add disabled tag
						} else {
							$components[] = $v;
						}
					}
				}
			}

			$components = join( ',', $components );

			// Replace old data
			set_theme_mod( 'post_control', $components );
		}
	} // End post_migrate_data()
	
	// Footer Control
	/**
	 * Migrate data from versions prior to 1.0.0.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function footer_migrate_data() {
		
		$options = get_theme_mod( 'footer_control' );

		if ( ! isset( $options ) ) {
			return; // Option is empty, probably first time installing the plugin.
		}		

		if ( is_array( $options ) ) {
			$order = '';
			$disabled = '';
			$components = array();

			if ( isset( $options['component_order'] ) ) {
				$order = explode( ',', $options['component_order'] );

				if ( isset( $options['disabled_components'] ) ) {
					$disabled = explode( ',', $options['disabled_components'] );
				}

				if ( 0 < count( $order ) ) {
					foreach ( $order as $k => $v ) {
						if ( in_array( $v, $disabled ) ) {
							$components[] = '[disabled]' . $v; // Add disabled tag
						} else {
							$components[] = $v;
						}
					}
				}
			}

			$components = join( ',', $components );

			// Replace old data
			set_theme_mod( 'footer_control', $components );
		}
	} // End footer_migrate_data()

	/**
	 * Work through the stored data and display the components in the desired order, without the disabled components.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function maybe_apply_restructuring_filter() {
		$options = get_theme_mod( 'homepage_control' );
		$components = array();

		if ( isset( $options ) && '' != $options ) {
			$components = explode( ',', $options );

			// Remove all existing actions on woo_homepage.
			remove_all_actions( $this->hook );

			// Remove disabled components
			$components = $this->_maybe_remove_disabled_items( $components );

			// Perform the reordering!
			if ( 0 < count( $components ) ) {
				$count = 5;
				foreach ( $components as $k => $v ) {
					if (strpos( $v, '@' ) !== FALSE) {
						$obj_v = explode( '@' , $v );
						if ( class_exists( $obj_v[0] ) && method_exists( $obj_v[0], $obj_v[1] ) ) {
							add_action( $this->hook, array( $obj_v[0], $obj_v[1] ), $count );
						} // End If Statement
					} else {
						if ( function_exists( $v ) ) {
							add_action( $this->hook, esc_attr( $v ), $count );
						}
					} // End If Statement

					$count + 5;
				}
			}
		}
	} // End maybe_apply_restructuring_filter()
	
	/**
	 * Work through the stored data and display the components in the desired order, without the disabled components.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function header_apply_restructuring_filter() {
		$options = get_theme_mod( 'header_control' );
		$components = array();

		if ( isset( $options ) && '' != $options ) {
			$components = explode( ',', $options );

			// Remove all existing actions on woo_homepage.
			remove_all_actions( $this->header_hook );

			// Remove disabled components
			$components = $this->_maybe_remove_disabled_items( $components );

			// Perform the reordering!
			if ( 0 < count( $components ) ) {
				$count = 5;
				foreach ( $components as $k => $v ) {
					if (strpos( $v, '@' ) !== FALSE) {
						$obj_v = explode( '@' , $v );
						if ( class_exists( $obj_v[0] ) && method_exists( $obj_v[0], $obj_v[1] ) ) {
							add_action( $this->header_hook, array( $obj_v[0], $obj_v[1] ), $count );
						} // End If Statement
					} else {
						if ( function_exists( $v ) ) {
							add_action( $this->header_hook, esc_attr( $v ), $count );
						}
					} // End If Statement

					$count + 5;
				}
			}
		}
	} // End header_apply_restructuring_filter()
	
	/**
	 * Work through the stored data and display the components in the desired order, without the disabled components.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function masthead_apply_restructuring_filter() {
		$options = get_theme_mod( 'masthead_control' );
		$components = array();

		if ( isset( $options ) && '' != $options ) {
			$components = explode( ',', $options );

			// Remove all existing actions on theme masthead.
			remove_all_actions( $this->masthead_hook );

			// Remove disabled components
			$components = $this->_maybe_remove_disabled_items( $components );

			// Perform the reordering!
			if ( 0 < count( $components ) ) {
				$count = 5;
				foreach ( $components as $k => $v ) {
					if (strpos( $v, '@' ) !== FALSE) {
						$obj_v = explode( '@' , $v );
						if ( class_exists( $obj_v[0] ) && method_exists( $obj_v[0], $obj_v[1] ) ) {
							add_action( $this->masthead_hook, array( $obj_v[0], $obj_v[1] ), $count );
						} // End If Statement
					} else {
						if ( function_exists( $v ) ) {
							add_action( $this->masthead_hook, esc_attr( $v ), $count );
						}
					} // End If Statement

					$count + 5;
				}
			}
		}
	} // End masthead_apply_restructuring_filter()
	
	/**
	 * Work through the stored data and display the components in the desired order, without the disabled components.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function brand_apply_restructuring_filter() {
		$options = get_theme_mod( 'brand_control' );
		$components = array();

		if ( isset( $options ) && '' != $options ) {
			$components = explode( ',', $options );

			// Remove all existing actions on theme masthead.
			remove_all_actions( $this->brand_hook );

			// Remove disabled components
			$components = $this->_maybe_remove_disabled_items( $components );

			// Perform the reordering!
			if ( 0 < count( $components ) ) {
				$count = 5;
				foreach ( $components as $k => $v ) {
					if (strpos( $v, '@' ) !== FALSE) {
						$obj_v = explode( '@' , $v );
						if ( class_exists( $obj_v[0] ) && method_exists( $obj_v[0], $obj_v[1] ) ) {
							add_action( $this->brand_hook, array( $obj_v[0], $obj_v[1] ), $count );
						} // End If Statement
					} else {
						if ( function_exists( $v ) ) {
							add_action( $this->brand_hook, esc_attr( $v ), $count );
						}
					} // End If Statement

					$count + 5;
				}
			}
		}
	} // End brand_apply_restructuring_filter()
	
	/**
	 * Work through the stored data and display the components in the desired order, without the disabled components.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function post_apply_restructuring_filter() {
		$options = get_theme_mod( 'post_control' );
		$components = array();

		if ( isset( $options ) && '' != $options ) {
			$components = explode( ',', $options );

			// Remove all existing actions on theme post.
			remove_all_actions( $this->post_hook );

			// Remove disabled components
			$components = $this->_maybe_remove_disabled_items( $components );

			// Perform the reordering!
			if ( 0 < count( $components ) ) {
				$count = 5;
				foreach ( $components as $k => $v ) {
					if (strpos( $v, '@' ) !== FALSE) {
						$obj_v = explode( '@' , $v );
						if ( class_exists( $obj_v[0] ) && method_exists( $obj_v[0], $obj_v[1] ) ) {
							add_action( $this->post_hook, array( $obj_v[0], $obj_v[1] ), $count );
						} // End If Statement
					} else {
						if ( function_exists( $v ) ) {
							add_action( $this->post_hook, esc_attr( $v ), $count );
						}
					} // End If Statement

					$count + 5;
				}
			}
		}
	} // End post_apply_restructuring_filter()
	
	/**
	 * Work through the stored data and display the components in the desired order, without the disabled components.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function footer_apply_restructuring_filter() {
		$options = get_theme_mod( 'footer_control' );
		$components = array();

		if ( isset( $options ) && '' != $options ) {
			$components = explode( ',', $options );

			// Remove all existing actions on theme footer.
			remove_all_actions( $this->footer_hook );

			// Remove disabled components
			$components = $this->_maybe_remove_disabled_items( $components );

			// Perform the reordering!
			if ( 0 < count( $components ) ) {
				$count = 5;
				foreach ( $components as $k => $v ) {
					if (strpos( $v, '@' ) !== FALSE) {
						$obj_v = explode( '@' , $v );
						if ( class_exists( $obj_v[0] ) && method_exists( $obj_v[0], $obj_v[1] ) ) {
							add_action( $this->footer_hook, array( $obj_v[0], $obj_v[1] ), $count );
						} // End If Statement
					} else {
						if ( function_exists( $v ) ) {
							add_action( $this->footer_hook, esc_attr( $v ), $count );
						}
					} // End If Statement

					$count + 5;
				}
			}
		}
	} // End footer_apply_restructuring_filter()

	/**
	 * Maybe remove disabled items from the main ordered array.
	 * @access  private
	 * @since   1.0.0
	 * @param   array $components 	Array with components order.
	 * @return  array           	Re-ordered components with disabled components removed.
	 */
	private function _maybe_remove_disabled_items( $components ) {
		if ( 0 < count( $components ) ) {
			foreach ( $components as $k => $v ) {
				if ( false !== strpos( $v, '[disabled]' ) ) {
					unset( $components[ $k ] );
				}
			}
		}
		return $components;
	} // End _maybe_remove_disabled_items()
} // End Class
