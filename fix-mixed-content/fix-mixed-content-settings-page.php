<?php

class FixMC_SettingsPage {
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	const OPTION_NAME = "fix_mc_options";

	/**
	 * Start up
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page() {
		// This page will be under "Settings"
		add_options_page(
			"Fix Mixed Content Settings",
			"Fix Mixed Content Settings",
			'manage_options',
			'fix-mc-admin',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page() {
		// Set class property
		$this->options = get_option( self::OPTION_NAME );
		?>
        <div class="wrap">
            <h1>My Settings</h1>
            <form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				settings_fields( 'fix_mc_option_group' );
				do_settings_sections( 'fix-mc-admin' );
				submit_button();
				?>
            </form>
        </div>
		<?php
	}

	/**
	 * Register and add settings
	 */

	public function page_init() {
		register_setting(
			'fix_mc_option_group', // Option group
			self::OPTION_NAME, // Option name
			array( $this, 'sanitize' ) // Sanitize
		);

		add_settings_section(
			'fix_mc_settings', // ID
			'Mixed Content Detectors Active:', // Title
			array( $this, 'print_section_info' ), // Callback
			'fix-mc-admin' // Page
		);


		add_settings_field(
			'fix_mc_add_http',
			'Use HTTP',
			array( $this, 'http_callback' ),
			'fix-mc-admin',
			'fix_mc_settings'
		);

		add_settings_field(
			'fix_mc_add_meta',
			'Use Meta fields',
			array( $this, 'meta_callback' ),
			'fix-mc-admin',
			'fix_mc_settings'
		);

		add_settings_field(
			'fix_mc_add_javascript', // ID
			'Add JavaScript detector', // Title
			array( $this, 'js_callback' ), // Callback
			'fix-mc-admin', // Page
			'fix_mc_settings' // Section
		);

		add_settings_field(
			'fix_mc_add_compatibility', // ID
			'Compatibility Mode', // Title
			array( $this, 'compatibility_callback' ), // Callback
			'fix-mc-admin', // Page
			'fix_mc_settings' // Section
		);

		add_settings_field(
			'fix_mc_warn', // ID
			'Send warning email', // Title
			array( $this, 'warn_callback' ), // Callback
			'fix-mc-admin', // Page
			'fix_mc_settings' // Section
		);

		add_settings_field(
			'fix_mc_report_email', // ID
			'Report email', // Title
			array( $this, 'email_callback' ), // Callback
			'fix-mc-admin', // Page
			'fix_mc_settings' // Section
		);

	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize( $input ) {
		$new_input = array();

		foreach ( $input as $inputName => $value ) {
			if ( $inputName == "fix_mc_report_email" ) {
				if ( $value === "" ) {
					$new_input[ $inputName ] = get_option( "admin_email" );
				} else {
					$new_input[ $inputName ] = $value;
				}
			} else {
				$new_input[ $inputName ] = (bool) $value;
			}
		}

		return $new_input;
	}

	/**
	 * Print the Section text
	 */
	public function print_section_info() {
		print 'Use this to switch on and off different detectors:';
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function http_callback() {
		printf(
			'<input type="checkbox" id="fix_mc_add_http" name="' . self::OPTION_NAME . '[fix_mc_add_http]" %s />',
			isset( $this->options['fix_mc_add_http'] ) ? ( $this->options['fix_mc_add_http'] ? "checked" : "" ) : ''
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function meta_callback() {
		printf(
			'<input type="checkbox" id="fix_mc_add_meta" name="' . self::OPTION_NAME . '[fix_mc_add_meta]" %s />',
			isset( $this->options['fix_mc_add_meta'] ) ? ( $this->options['fix_mc_add_meta'] ? "checked" : "" ) : ''
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function js_callback() {
		printf(
			'<input type="checkbox" id="fix_mc_add_javascript" name="' . self::OPTION_NAME . '[fix_mc_add_javascript]" %s />',
			isset( $this->options['fix_mc_add_javascript'] ) ? ( $this->options['fix_mc_add_javascript'] ? "checked" : "" ) : ''
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function compatibility_callback() {
		printf(
			'<input type="checkbox" id="fix_mc_add_compatibility" name="' . self::OPTION_NAME . '[fix_mc_add_compatibility]" %s />',
			isset( $this->options['fix_mc_add_compatibility'] ) ? ( $this->options['fix_mc_add_compatibility'] ? "checked" : "" ) : ''
		);
	}


	/**
	 * Get the settings option array and print one of its values
	 */
	public function email_callback() {
		printf(
			'<input placeholder="If empty, defaults to admin email" type="text" id="fix_mc_report_email" name="' . self::OPTION_NAME . '[fix_mc_report_email]" value="%s" />',
			isset( $this->options['fix_mc_report_email'] ) ? $this->options['fix_mc_report_email'] : get_option( "admin_email" )
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function warn_callback() {
		printf(
			'<input type="checkbox" id="fix_mc_warn" name="' . self::OPTION_NAME . '[fix_mc_warn]" %s />',
			isset( $this->options['fix_mc_warn'] ) ? ( $this->options['fix_mc_warn'] ? "checked" : "" ) : ''
		);
	}
}

if ( is_admin() ) {
	$my_settings_page = new FixMC_SettingsPage();
}
