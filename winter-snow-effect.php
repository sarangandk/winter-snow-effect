<?php
/*
Plugin Name: Winter Snow Effect
Description: Automatically adds a falling snow effect to your website only during winter months (December, January, February).
Version: 2.0
Author: Sarangan Thillaiampalam
Author URI: https://sarangan.dk
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define plugin constants
define( 'WSE_VERSION', '2.0' );
define( 'WSE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WSE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Get plugin settings with defaults.
 *
 * @return array Plugin settings.
 */
function wse_get_settings() {
	$defaults = array(
		'enabled'              => 'auto', // 'auto', 'on', 'off'
		'start_month'          => 12, // December
		'start_day'            => 1,
		'end_month'            => 2, // February
		'end_day'              => 28,
		'flake_count_mobile'   => 6,
		'flake_count_desktop'  => 35,
		'flake_size_min'       => 10,
		'flake_size_max'       => 30,
		'flake_speed_min'      => 0.5,
		'flake_speed_max'      => 1.5,
		'flake_opacity_min'    => 0.6,
		'flake_opacity_max'    => 0.9,
		'respect_reduced_motion' => true,
		'pause_on_scroll'      => false,
		'pause_on_inactive'     => true,
	);

	return wp_parse_args( get_option( 'wse_settings', array() ), $defaults );
}

/**
 * Check if snow effect should be active based on settings.
 *
 * @return bool True if snow should be active, false otherwise.
 */
function wse_should_activate() {
	$settings = wse_get_settings();

	// Manual override: off
	if ( 'off' === $settings['enabled'] ) {
		return false;
	}

	// Manual override: on
	if ( 'on' === $settings['enabled'] ) {
		return true;
	}

	// Auto mode: check date range
	$current_date = current_time( 'Y-m-d' );
	$current_year = (int) current_time( 'Y' );
	$current_month = (int) current_time( 'n' );
	
	// Handle year wrap-around (e.g., Dec 1 to Feb 28)
	if ( $settings['start_month'] > $settings['end_month'] ) {
		// Cross-year range (e.g., Dec to Feb)
		// Determine which year's range we're checking
		if ( $current_month >= $settings['start_month'] ) {
			// We're in Dec or later, so start is this year, end is next year
			$start_date = sprintf( '%d-%02d-%02d', $current_year, $settings['start_month'], $settings['start_day'] );
			$end_date = sprintf( '%d-%02d-%02d', $current_year + 1, $settings['end_month'], $settings['end_day'] );
		} else {
			// We're before Dec (Jan/Feb), so start was last year, end is this year
			$start_date = sprintf( '%d-%02d-%02d', $current_year - 1, $settings['start_month'], $settings['start_day'] );
			$end_date = sprintf( '%d-%02d-%02d', $current_year, $settings['end_month'], $settings['end_day'] );
		}
		
		// Check if current date is in range
		if ( $current_date >= $start_date && $current_date <= $end_date ) {
			return true;
		}
	} else {
		// Same year range
		$start_date = sprintf( '%d-%02d-%02d', $current_year, $settings['start_month'], $settings['start_day'] );
		$end_date = sprintf( '%d-%02d-%02d', $current_year, $settings['end_month'], $settings['end_day'] );
		
		if ( $current_date >= $start_date && $current_date <= $end_date ) {
			return true;
		}
	}

	return false;
}

/**
 * Register plugin settings.
 */
function wse_register_settings() {
	register_setting( 'wse_settings_group', 'wse_settings', array(
		'sanitize_callback' => 'wse_sanitize_settings',
	) );
	
	// Redirect to show success message
	if ( isset( $_POST['submit'] ) && isset( $_POST['option_page'] ) && 'wse_settings_group' === $_POST['option_page'] ) {
		add_settings_error( 'wse_settings', 'wse_settings_saved', __( 'Settings saved successfully!', 'winter-snow-effect' ), 'updated' );
	}
}
add_action( 'admin_init', 'wse_register_settings' );

/**
 * Sanitize settings before saving.
 *
 * @param array $input Raw settings input.
 * @return array Sanitized settings.
 */
function wse_sanitize_settings( $input ) {
	$sanitized = array();

	if ( isset( $input['enabled'] ) ) {
		$sanitized['enabled'] = in_array( $input['enabled'], array( 'auto', 'on', 'off' ), true ) ? $input['enabled'] : 'auto';
	}

	if ( isset( $input['start_month'] ) ) {
		$sanitized['start_month'] = absint( $input['start_month'] );
		$sanitized['start_month'] = max( 1, min( 12, $sanitized['start_month'] ) );
	}

	if ( isset( $input['start_day'] ) ) {
		$sanitized['start_day'] = absint( $input['start_day'] );
		$sanitized['start_day'] = max( 1, min( 31, $sanitized['start_day'] ) );
	}

	if ( isset( $input['end_month'] ) ) {
		$sanitized['end_month'] = absint( $input['end_month'] );
		$sanitized['end_month'] = max( 1, min( 12, $sanitized['end_month'] ) );
	}

	if ( isset( $input['end_day'] ) ) {
		$sanitized['end_day'] = absint( $input['end_day'] );
		$sanitized['end_day'] = max( 1, min( 31, $sanitized['end_day'] ) );
	}

	if ( isset( $input['flake_count_mobile'] ) ) {
		$sanitized['flake_count_mobile'] = absint( $input['flake_count_mobile'] );
		$sanitized['flake_count_mobile'] = max( 1, min( 100, $sanitized['flake_count_mobile'] ) );
	}

	if ( isset( $input['flake_count_desktop'] ) ) {
		$sanitized['flake_count_desktop'] = absint( $input['flake_count_desktop'] );
		$sanitized['flake_count_desktop'] = max( 1, min( 200, $sanitized['flake_count_desktop'] ) );
	}

	if ( isset( $input['flake_size_min'] ) ) {
		$sanitized['flake_size_min'] = absint( $input['flake_size_min'] );
		$sanitized['flake_size_min'] = max( 5, min( 50, $sanitized['flake_size_min'] ) );
	}

	if ( isset( $input['flake_size_max'] ) ) {
		$sanitized['flake_size_max'] = absint( $input['flake_size_max'] );
		$sanitized['flake_size_max'] = max( $sanitized['flake_size_min'], min( 100, $sanitized['flake_size_max'] ) );
	}

	if ( isset( $input['flake_speed_min'] ) ) {
		$sanitized['flake_speed_min'] = floatval( $input['flake_speed_min'] );
		$sanitized['flake_speed_min'] = max( 0.1, min( 5.0, $sanitized['flake_speed_min'] ) );
	}

	if ( isset( $input['flake_speed_max'] ) ) {
		$sanitized['flake_speed_max'] = floatval( $input['flake_speed_max'] );
		$sanitized['flake_speed_max'] = max( $sanitized['flake_speed_min'], min( 10.0, $sanitized['flake_speed_max'] ) );
	}

	if ( isset( $input['flake_opacity_min'] ) ) {
		$sanitized['flake_opacity_min'] = floatval( $input['flake_opacity_min'] );
		$sanitized['flake_opacity_min'] = max( 0.1, min( 1.0, $sanitized['flake_opacity_min'] ) );
	}

	if ( isset( $input['flake_opacity_max'] ) ) {
		$sanitized['flake_opacity_max'] = floatval( $input['flake_opacity_max'] );
		$sanitized['flake_opacity_max'] = max( $sanitized['flake_opacity_min'], min( 1.0, $sanitized['flake_opacity_max'] ) );
	}

	$sanitized['respect_reduced_motion'] = isset( $input['respect_reduced_motion'] ) && $input['respect_reduced_motion'];
	$sanitized['pause_on_scroll'] = isset( $input['pause_on_scroll'] ) && $input['pause_on_scroll'];
	$sanitized['pause_on_inactive'] = isset( $input['pause_on_inactive'] ) && $input['pause_on_inactive'];

	return $sanitized;
}

/**
 * Add admin menu page.
 */
function wse_add_admin_menu() {
	add_options_page(
		__( 'Winter Snow Effect Settings', 'winter-snow-effect' ),
		__( 'Winter Snow Effect', 'winter-snow-effect' ),
		'manage_options',
		'winter-snow-effect',
		'wse_render_settings_page'
	);
}
add_action( 'admin_menu', 'wse_add_admin_menu' );

/**
 * Render settings page.
 */
function wse_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings = wse_get_settings();
	$is_active = wse_should_activate();
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		
		<?php if ( isset( $_GET['settings-updated'] ) ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Settings saved successfully!', 'winter-snow-effect' ); ?></p>
			</div>
		<?php endif; ?>

		<div class="wse-status-box" style="background: #fff; border-left: 4px solid #2271b1; padding: 12px; margin: 20px 0;">
			<p style="margin: 0;">
				<strong><?php esc_html_e( 'Current Status:', 'winter-snow-effect' ); ?></strong>
				<?php if ( $is_active ) : ?>
					<span style="color: #00a32a;"><?php esc_html_e( 'Active', 'winter-snow-effect' ); ?></span>
				<?php else : ?>
					<span style="color: #d63638;"><?php esc_html_e( 'Inactive', 'winter-snow-effect' ); ?></span>
				<?php endif; ?>
			</p>
		</div>

		<form action="options.php" method="post">
			<?php
			settings_fields( 'wse_settings_group' );
			do_settings_sections( 'wse_settings_group' );
			?>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="wse_enabled"><?php esc_html_e( 'Enable Snow Effect', 'winter-snow-effect' ); ?></label>
					</th>
					<td>
						<select name="wse_settings[enabled]" id="wse_enabled">
							<option value="auto" <?php selected( $settings['enabled'], 'auto' ); ?>><?php esc_html_e( 'Automatic (Based on Date Range)', 'winter-snow-effect' ); ?></option>
							<option value="on" <?php selected( $settings['enabled'], 'on' ); ?>><?php esc_html_e( 'Always On', 'winter-snow-effect' ); ?></option>
							<option value="off" <?php selected( $settings['enabled'], 'off' ); ?>><?php esc_html_e( 'Always Off', 'winter-snow-effect' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Choose when the snow effect should be active.', 'winter-snow-effect' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label><?php esc_html_e( 'Date Range', 'winter-snow-effect' ); ?></label>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php esc_html_e( 'Date Range', 'winter-snow-effect' ); ?></span></legend>
							<label>
								<?php esc_html_e( 'Start:', 'winter-snow-effect' ); ?>
								<select name="wse_settings[start_month]" id="wse_start_month">
									<?php
									$months = array(
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
											'<option value="%d" %s>%s</option>',
											$num,
											selected( $settings['start_month'], $num, false ),
											esc_html( $name )
										);
									}
									?>
								</select>
								<input type="number" name="wse_settings[start_day]" id="wse_start_day" value="<?php echo esc_attr( $settings['start_day'] ); ?>" min="1" max="31" style="width: 60px;">
							</label>
							<br>
							<label style="margin-top: 8px; display: inline-block;">
								<?php esc_html_e( 'End:', 'winter-snow-effect' ); ?>
								<select name="wse_settings[end_month]" id="wse_end_month">
									<?php
									foreach ( $months as $num => $name ) {
										printf(
											'<option value="%d" %s>%s</option>',
											$num,
											selected( $settings['end_month'], $num, false ),
											esc_html( $name )
										);
									}
									?>
								</select>
								<input type="number" name="wse_settings[end_day]" id="wse_end_day" value="<?php echo esc_attr( $settings['end_day'] ); ?>" min="1" max="31" style="width: 60px;">
							</label>
						</fieldset>
						<p class="description"><?php esc_html_e( 'Set the date range when snow should appear (in automatic mode).', 'winter-snow-effect' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label><?php esc_html_e( 'Snowflake Count', 'winter-snow-effect' ); ?></label>
					</th>
					<td>
						<label>
							<?php esc_html_e( 'Mobile:', 'winter-snow-effect' ); ?>
							<input type="number" name="wse_settings[flake_count_mobile]" value="<?php echo esc_attr( $settings['flake_count_mobile'] ); ?>" min="1" max="100" style="width: 80px;">
						</label>
						<br>
						<label style="margin-top: 8px; display: inline-block;">
							<?php esc_html_e( 'Desktop:', 'winter-snow-effect' ); ?>
							<input type="number" name="wse_settings[flake_count_desktop]" value="<?php echo esc_attr( $settings['flake_count_desktop'] ); ?>" min="1" max="200" style="width: 80px;">
						</label>
						<p class="description"><?php esc_html_e( 'Number of snowflakes to display on different screen sizes.', 'winter-snow-effect' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label><?php esc_html_e( 'Snowflake Size', 'winter-snow-effect' ); ?></label>
					</th>
					<td>
						<label>
							<?php esc_html_e( 'Min:', 'winter-snow-effect' ); ?>
							<input type="number" name="wse_settings[flake_size_min]" value="<?php echo esc_attr( $settings['flake_size_min'] ); ?>" min="5" max="50" style="width: 80px;"> px
						</label>
						<br>
						<label style="margin-top: 8px; display: inline-block;">
							<?php esc_html_e( 'Max:', 'winter-snow-effect' ); ?>
							<input type="number" name="wse_settings[flake_size_max]" value="<?php echo esc_attr( $settings['flake_size_max'] ); ?>" min="5" max="100" style="width: 80px;"> px
						</label>
						<p class="description"><?php esc_html_e( 'Size range for snowflakes in pixels.', 'winter-snow-effect' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label><?php esc_html_e( 'Snowflake Speed', 'winter-snow-effect' ); ?></label>
					</th>
					<td>
						<label>
							<?php esc_html_e( 'Min:', 'winter-snow-effect' ); ?>
							<input type="number" name="wse_settings[flake_speed_min]" value="<?php echo esc_attr( $settings['flake_speed_min'] ); ?>" min="0.1" max="5" step="0.1" style="width: 80px;">
						</label>
						<br>
						<label style="margin-top: 8px; display: inline-block;">
							<?php esc_html_e( 'Max:', 'winter-snow-effect' ); ?>
							<input type="number" name="wse_settings[flake_speed_max]" value="<?php echo esc_attr( $settings['flake_speed_max'] ); ?>" min="0.1" max="10" step="0.1" style="width: 80px;">
						</label>
						<p class="description"><?php esc_html_e( 'Falling speed range for snowflakes.', 'winter-snow-effect' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label><?php esc_html_e( 'Snowflake Opacity', 'winter-snow-effect' ); ?></label>
					</th>
					<td>
						<label>
							<?php esc_html_e( 'Min:', 'winter-snow-effect' ); ?>
							<input type="number" name="wse_settings[flake_opacity_min]" value="<?php echo esc_attr( $settings['flake_opacity_min'] ); ?>" min="0.1" max="1" step="0.1" style="width: 80px;">
						</label>
						<br>
						<label style="margin-top: 8px; display: inline-block;">
							<?php esc_html_e( 'Max:', 'winter-snow-effect' ); ?>
							<input type="number" name="wse_settings[flake_opacity_max]" value="<?php echo esc_attr( $settings['flake_opacity_max'] ); ?>" min="0.1" max="1" step="0.1" style="width: 80px;">
						</label>
						<p class="description"><?php esc_html_e( 'Opacity range for snowflakes (0.1 = transparent, 1.0 = opaque).', 'winter-snow-effect' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label><?php esc_html_e( 'Performance Options', 'winter-snow-effect' ); ?></label>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php esc_html_e( 'Performance Options', 'winter-snow-effect' ); ?></span></legend>
							<label>
								<input type="checkbox" name="wse_settings[respect_reduced_motion]" value="1" <?php checked( $settings['respect_reduced_motion'] ); ?>>
								<?php esc_html_e( 'Respect reduced motion preference', 'winter-snow-effect' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'Disable animation for users who prefer reduced motion (accessibility).', 'winter-snow-effect' ); ?></p>
							<br>
							<label>
								<input type="checkbox" name="wse_settings[pause_on_scroll]" value="1" <?php checked( $settings['pause_on_scroll'] ); ?>>
								<?php esc_html_e( 'Pause animation when scrolling', 'winter-snow-effect' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'Improve performance by pausing snow when user is scrolling.', 'winter-snow-effect' ); ?></p>
							<br>
							<label>
								<input type="checkbox" name="wse_settings[pause_on_inactive]" value="1" <?php checked( $settings['pause_on_inactive'] ); ?>>
								<?php esc_html_e( 'Pause animation when tab is inactive', 'winter-snow-effect' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'Save resources by pausing animation when browser tab is in background.', 'winter-snow-effect' ); ?></p>
						</fieldset>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Enqueue scripts and styles.
 */
function wse_enqueue_scripts() {
	if ( ! wse_should_activate() ) {
		return;
	}

	$settings = wse_get_settings();

	wp_enqueue_style( 'wse-snow-style', WSE_PLUGIN_URL . 'assets/css/snow.css', array(), WSE_VERSION );
	wp_enqueue_script( 'wse-snow-script', WSE_PLUGIN_URL . 'assets/js/snow.js', array(), WSE_VERSION, true );

	// Pass settings to JavaScript
	wp_localize_script( 'wse-snow-script', 'wseSettings', array(
		'flakeCountMobile'      => (int) $settings['flake_count_mobile'],
		'flakeCountDesktop'     => (int) $settings['flake_count_desktop'],
		'flakeSizeMin'          => (int) $settings['flake_size_min'],
		'flakeSizeMax'          => (int) $settings['flake_size_max'],
		'flakeSpeedMin'         => (float) $settings['flake_speed_min'],
		'flakeSpeedMax'         => (float) $settings['flake_speed_max'],
		'flakeOpacityMin'       => (float) $settings['flake_opacity_min'],
		'flakeOpacityMax'       => (float) $settings['flake_opacity_max'],
		'respectReducedMotion'  => (bool) $settings['respect_reduced_motion'],
		'pauseOnScroll'         => (bool) $settings['pause_on_scroll'],
		'pauseOnInactive'       => (bool) $settings['pause_on_inactive'],
	) );
}
add_action( 'wp_enqueue_scripts', 'wse_enqueue_scripts' );
