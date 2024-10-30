<?php

/*
Plugin Name: LeagueManager GameDay Scoreboard widget
Plugin URI: http://www.brcode.co.il
Description: LeagueManager scoreboard widget for GameDay theme
Version: 1.0.1
Author: Bar Shai
Author URI: http://www.brcode.co.il
License: GPL3
*/

class Lm_Gd_Scoreboard
{
	/**
	 * Lm_Gd_Scoreboard constructor.
	 * construct plugin's class
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_text_domain' ) );

		add_action( 'widgets_init', array( $this, 'register_widget' ) );

		/* Unregister widget if wordpress does not meets requirements */
		add_action( 'admin_init', array( $this, 'activate'));
	}

	/**
	 * This function run at plugin's activation.
	 * checks if gameday theme is active, and LeagueManager plugin is active
	 */
	static function activate() {
		if ( !self::are_requirements_meets() )
		{
			add_action( 'admin_notices', array( 'Lm_Gd_Scoreboard', 'force_deactivate_message' ) );
			deactivate_plugins( plugin_basename( __FILE__ ) );
		}
	}

	/**
	 * Echo dashboard notice with details about missed widget requirements.
	 */
	static function force_deactivate_message() {
		?>
		<div class="error">
			<p><?php echo self::requirements_error_message(); ?></p>
		</div>
		<?php
	}

	/**
	 * Unregister widget if wordpress does not meets requirements
	 */
	public function unregister_widget_if_needed()
	{
		if (!self::are_requirements_meets())
		{
			/* Loads our widget class file */
			require_once( 'class-lm-gd-scoreboard-widget.php' );
			unregister_widget( 'Lm_Gd_Scoreboard_Widget' );

			/* adds error message to admin dashboard */
			add_action( 'admin_notices', array( 'Lm_Gd_Scoreboard', 'force_deactivate_message' ) );

		}
	}

	/**
	 * Register new scoreboard widget
	 */
	public function register_widget() {
			/* Loads our widget class file */
			require_once( 'class-lm-gd-scoreboard-widget.php' );

			register_widget( 'Lm_Gd_Scoreboard_Widget' );
	}

	/**
	 * Loads plugin's text domain for localization
	 */
	public function load_text_domain() {
		load_plugin_textdomain( 'lm_gd_scoreboard_widget', false, plugin_basename( dirname( __FILE__ ) ) . '/languages');
	}

	/**
	 * Check if LeagueManager plugin is active
	 * @return bool - true if plugin active
	 */
	public static function is_leaguemanager_active()
	{
		return is_plugin_active('leaguemanager/leaguemanager.php');
	}

	/**
	 * Check if gameday or child theme of gameday is the active theme
	 * @return bool - true if active theme is gameday or child of
	 */
	public static function is_gameday_or_child_active()
	{
		$current_theme = wp_get_theme();

		return $current_theme->get('Name') == 'Gameday' || $current_theme->get_template() == 'gameday';
	}

	/**
	 * Check if wordpress installation meets plugin's requirements
	 * @return bool - true if wordpress installation is good
	 */
	public static function are_requirements_meets()
	{
		return self::is_leaguemanager_active() && self::is_gameday_or_child_active();
	}

	/**
	 * Generate actual requirements error message
	 * @return string - requirements error message
	 */
	public static function requirements_error_message()
	{
		$error_message = '';

		if ( !self::is_leaguemanager_active() )
		{
			$error_message .= __( "LeagueManager plugin isn't active or installed, ", "lm_gd_scoreboard_widget" );
		}

		if ( !self::is_gameday_or_child_active() )
		{
			$error_message .= __( "Gameday or child theme of Gameday isn't your active theme, ", "lm_gd_scoreboard_widget" );
		}

		$error_message .= __( "therefore LeagueManager scoreboard widget for Gameday can't be activate.", "lm_gd_scoreboard_widget" );

		return $error_message;
	}
}

/* Checks if current wordpress installation meets plugins requirements */
register_activation_hook( __FILE__, array( 'Lm_Gd_Scoreboard' , 'activate' ) );

$lm_gd_scoreboard_plugin = new Lm_Gd_Scoreboard();