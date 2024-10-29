<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://b2.ai
 * @since      1.0.0
 *
 * @package    B2_Analytics
 * @subpackage B2_Analytics/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    B2_Analytics
 * @subpackage B2_Analytics/public
 * @author     B2 <info@b2.ai>
 */
class B2_Analytics_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in B2_Analytics_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The B2_Analytics_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/b2-analytics-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in B2_Analytics_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The B2_Analytics_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/b2-analytics-public.js', array( 'jquery' ), $this->version, false );

	}


	/**
	* The name of the plugin option given a key 
	*/
	public function get_option_name($key) {
		return "_b2-analytics_" . $key;
	}

	/**
	 * Output B2 <script> tag 
	 */
	public function b2_script() {

		if ( is_admin() ) {
			return;
		}

		echo '<script async defer src="https://cdn2.b2.ai/cdn/b2.min.js" b2CustomerKey="' . esc_html(get_option($this->get_option_name("CustomerID"))) . '" b2Api="https://analytics.b2.ai/api/B2Analytics" type="text/javascript"></script>';	
	
	}

}
