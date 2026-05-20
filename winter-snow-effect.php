<?php
/*
Plugin Name: Winter Snow Effect
Description: Automatically adds a falling snow effect to your website only during winter months (December, January, February).
Version: 1.2.3
Author: Sarangan Thillaiampalam
Author URI: https://sarangan.dk
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WinterSnowEffect {

	private $options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Add settings link on the plugins page
		$plugin_basename = plugin_basename( __FILE__ );
		add_filter( "plugin_action_links_{$plugin_basename}", array( $this, 'add_settings_link' ) );
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page() {
		add_options_page(
			'Settings Admin',
			'Winter Snow',
			'manage_options',
			'wse-setting-admin',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page() {
		$this->options = get_option( 'wse_settings' );
		?>
		<div class="wrap">
			<h1>Winter Snow Effect Settings</h1>
			<form method="post" action="options.php">
			<?php
				settings_fields( 'wse_option_group' );
				do_settings_sections( 'wse-setting-admin' );
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
			'wse_option_group',
			'wse_settings',
			array( $this, 'sanitize' )
		);

		add_settings_section(
			'setting_section_id',
			'General Settings',
			array( $this, 'print_section_info' ),
			'wse-setting-admin'
		);

		add_settings_field(
			'display_mode',
			'Display Mode',
			array( $this, 'display_mode_callback' ),
			'wse-setting-admin',
			'setting_section_id'
		);

		add_settings_field(
			'months',
			'Active Months (Standard Mode)',
			array( $this, 'months_callback' ),
			'wse-setting-admin',
			'setting_section_id'
		);

		add_settings_field(
			'custom_range',
			'Custom Date Range',
			array( $this, 'custom_range_callback' ),
			'wse-setting-admin',
			'setting_section_id'
		);

		add_settings_field(
			'flake_count',
			'Flake Count',
			array( $this, 'flake_count_callback' ),
			'wse-setting-admin',
			'setting_section_id'
		);

		add_settings_field(
			'flake_speed',
			'Min/Max Speed',
			array( $this, 'flake_speed_callback' ),
			'wse-setting-admin',
			'setting_section_id'
		);
	}

	/**
	 * Sanitize each setting field as needed
	 */
	public function sanitize( $input ) {
		$new_input = array();
		
		$new_input['display_mode'] = isset( $input['display_mode'] ) ? sanitize_text_field( $input['display_mode'] ) : 'standard';

		if ( isset( $input['months'] ) && is_array( $input['months'] ) ) {
			$new_input['months'] = array_map( 'intval', $input['months'] );
		} else {
			$new_input['months'] = array();
		}

		$new_input['start_date'] = isset( $input['start_date'] ) ? sanitize_text_field( $input['start_date'] ) : '';
		$new_input['end_date']   = isset( $input['end_date'] ) ? sanitize_text_field( $input['end_date'] ) : '';

		$new_input['flake_count'] = isset( $input['flake_count'] ) ? absint( $input['flake_count'] ) : 35;
		
		// Handle decimal commas (common in many locales) by converting to dots
		$min_raw = isset( $input['min_speed'] ) ? str_replace( ',', '.', $input['min_speed'] ) : '0.5';
		$max_raw = isset( $input['max_speed'] ) ? str_replace( ',', '.', $input['max_speed'] ) : '1.5';
		
		$new_input['min_speed'] = floatval( $min_raw );
		$new_input['max_speed'] = floatval( $max_raw );

		// Ensure max is not less than min
		if ( $new_input['max_speed'] < $new_input['min_speed'] ) {
			$new_input['max_speed'] = $new_input['min_speed'] + 1.0;
		}

		return $new_input;
	}

	public function print_section_info() {
		print 'Configure when and how the snow effect appears on your site.';
	}

	public function display_mode_callback() {
		$options = get_option( 'wse_settings' );
		$mode    = isset( $options['display_mode'] ) ? $options['display_mode'] : 'standard';
		?>
		<select id="display_mode" name="wse_settings[display_mode]">
			<option value="standard" <?php selected( $mode, 'standard' ); ?>>Standard (Selected Months)</option>
			<option value="custom" <?php selected( $mode, 'custom' ); ?>>Custom Date Range</option>
		</select>
		<?php
	}

	public function months_callback() {
		$options      = get_option( 'wse_settings' );
		$saved_months = isset( $options['months'] ) ? (array) $options['months'] : array( 12, 1, 2 );
		$months       = array(
			1  => __( 'January', 'winter-snow-effect' ),
			2  => __( 'February', 'winter-snow-effect' ),
			3  => __( 'March', 'winter-snow-effect' ),
			4  => __( 'April', 'winter-snow-effect' ),
			5  => __( 'May', 'winter-snow-effect' ),
			6  => __( 'June', 'winter-snow-effect' ),
			7  => __( 'July', 'winter-snow-effect' ),
			8  => __( 'August', 'winter-snow-effect' ),
			9  => __( 'September', 'winter-snow-effect' ),
			10 => __( 'October', 'winter-snow-effect' ),
			11 => __( 'November', 'winter-snow-effect' ),
			12 => __( 'December', 'winter-snow-effect' ),
		);

		foreach ( $months as $num => $name ) {
			printf(
				'<label style="margin-right: 15px;"><input type="checkbox" name="wse_settings[months][]" value="%d" %s /> %s</label>',
				(int) $num,
				checked( in_array( $num, $saved_months ), true, false ),
				esc_html( $name )
			);
			if ( $num % 3 === 0 ) echo '<br/>';
		}
	}

	public function custom_range_callback() {
		$options    = get_option( 'wse_settings' );
		$start_date = isset( $options['start_date'] ) ? $options['start_date'] : '';
		$end_date   = isset( $options['end_date'] ) ? $options['end_date'] : '';
		?>
		<label>Start: <input type="date" name="wse_settings[start_date]" value="<?php echo esc_attr( $start_date ); ?>" /></label>
		<label style="margin-left: 20px;">End: <input type="date" name="wse_settings[end_date]" value="<?php echo esc_attr( $end_date ); ?>" /></label>
		<p class="description">Only used if Display Mode is set to "Custom Date Range".</p>
		<?php
	}

	public function flake_count_callback() {
		$options = get_option( 'wse_settings' );
		$count   = isset( $options['flake_count'] ) ? $options['flake_count'] : 35;
		?>
		<input type="number" name="wse_settings[flake_count]" value="<?php echo esc_attr( $count ); ?>" min="1" max="200" />
		<p class="description">Recommended: 20-50. High values may affect performance.</p>
		<?php
	}

	public function flake_speed_callback() {
		$options   = get_option( 'wse_settings' );
		$min_speed = isset( $options['min_speed'] ) ? $options['min_speed'] : 0.5;
		$max_speed = isset( $options['max_speed'] ) ? $options['max_speed'] : 1.5;
		?>
		Min: <input type="number" step="0.1" name="wse_settings[min_speed]" value="<?php echo esc_attr( $min_speed ); ?>" />
		Max: <input type="number" step="0.1" name="wse_settings[max_speed]" value="<?php echo esc_attr( $max_speed ); ?>" />
		<?php
	}

	/**
	 * Check if snow should be shown based on settings
	 */
	public function should_show_snow() {
		$options = get_option( 'wse_settings' );
		
		// Ensure options is an array for safe index access
		if ( ! is_array( $options ) ) {
			$options = array();
		}

		$mode = isset( $options['display_mode'] ) ? $options['display_mode'] : 'standard';

		if ( $mode === 'custom' ) {
			$start = isset( $options['start_date'] ) ? $options['start_date'] : '';
			$end   = isset( $options['end_date'] ) ? $options['end_date'] : '';

			if ( empty( $start ) || empty( $end ) ) {
				return false;
			}

			// Use site local time for comparison if possible, otherwise gmdate
			$today = current_time( 'Y-m-d' );
			return ( $today >= $start && $today <= $end );
		} else {
			// Standard mode: check current month
			$current_month = (int) current_time( 'n' );
			$saved_months  = isset( $options['months'] ) ? (array) $options['months'] : array( 12, 1, 2 );
			
			// If saved_months is set but empty (user unchecked all), snow won't show.
			// However, if the key is missing (fresh install), it uses the fallback.
			return in_array( $current_month, $saved_months );
		}
	}

	/**
	 * Enqueue scripts and styles
	 */
	public function enqueue_scripts() {
		if ( $this->should_show_snow() ) {
			$options = get_option( 'wse_settings' );
			if ( ! is_array( $options ) ) {
				$options = array();
			}
			
			wp_enqueue_style( 'wse-snow-style', plugin_dir_url( __FILE__ ) . 'assets/css/snow.css', array(), '1.2.1' );
			wp_enqueue_script( 'wse-snow-script', plugin_dir_url( __FILE__ ) . 'assets/js/snow.js', array(), '1.2.1', true );

			// Pass settings to JS
			wp_localize_script( 'wse-snow-script', 'wse_settings', array(
				'flakeCount' => isset( $options['flake_count'] ) ? (int) $options['flake_count'] : 35,
				'minSpeed'   => isset( $options['min_speed'] ) ? (float) $options['min_speed'] : 0.5,
				'maxSpeed'   => isset( $options['max_speed'] ) ? (float) $options['max_speed'] : 1.5,
			) );
		}
	}
	/**
	 * Add settings link to the plugins page
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="options-general.php?page=wse-setting-admin">' . esc_html__( 'Settings', 'winter-snow-effect' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}
}

new WinterSnowEffect();
