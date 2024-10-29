<?php

/**
 * Fired during plugin activation
 *
 * @link       https://b2.ai
 * @since      1.0.0
 *
 * @package    B2_Analytics
 * @subpackage B2_Analytics/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    B2_Analytics
 * @subpackage B2_Analytics/includes
 * @author     B2 <info@b2.ai>
 */
class B2_Analytics_Activator {

	
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		// Create Options 
		$b2Options = array(           
            "CustomerID",
            "AuthKey"
        );

		foreach ($b2Options as $key) 
		{
			// Check if they exist
			if(!get_option( $key, $default = false ))
			{
				add_option(B2_Analytics::get_option_name($key), B2_Analytics::createGUID());
			}

		}

	

	}

	
	


	

	 

}
