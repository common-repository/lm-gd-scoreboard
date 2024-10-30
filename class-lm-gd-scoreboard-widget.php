<?php
/**
 * LeagueManager scoreboard widget for Gameday class
 *
 * @author 	Bar Shai
 * @package	LeagueManager GameDay Scoreboard widget
 * @copyright Copyright 2016
 */

class Lm_Gd_Scoreboard_Widget extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function __construct() {
		/* Widget settings. */
		$widget_ops = array(
			'classname' => 'Lm_Gd_Scoreboard_Widget',
			'description' => __('LeagueManager scoreboard widget for gameday theme.', 'lm_gd_scoreboard_widget'),
		);

		/* Widget control settings. */
		$control_ops = array(
			'width' => 250,
			'height' => 350,
			'id_base' => 'lm_gd_scoreboard_widget'
		);

		/* Create the widget. */
		$this->WP_Widget( 'lm_gd_scoreboard_widget', __('Gameday: LeagueManager Scoreboard Widget', 'lm_gd_scoreboard_widget'), $widget_ops, $control_ops );
	}

	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );
		global $wpdb;

		/* Gets current date and time and get our game days */
		$nowDateTime = new DateTime('NOW');
		$nowDateTime->setTime(0,0);

		/* get the date range that defined in widget settings and get gamedays in this range */
		$dateRange = $instance['date_range'];
		$dateInterval = new DateInterval('P' . $dateRange . 'D');

		$fromDate = clone $nowDateTime;
		$fromDate->sub($dateInterval);
		$fromDate = $fromDate->format('Y-m-d H:i:s');

		$toDate = clone $nowDateTime;
		$toDate->add($dateInterval);
		$toDate->setTime(23,59,59);
		$toDate = $toDate->format('Y-m-d H:i:s');

		/* Gets Days with matches in range */
		$gameDays = $wpdb->get_results(
			"SELECT DISTINCT DATE_FORMAT(date, '%Y-%m-%d') AS  datef FROM " . $wpdb->prefix
			."leaguemanager_matches WHERE date >= '" . $fromDate ."' AND date <= '" . $toDate . "' ORDER BY date asc"
		);
		$numOfDays = $wpdb->num_rows;

		?>

		<div class="score-wrapper tabber-container">
			<ul class="score-nav tabs">
				<?php
				$gameDatesCounter = 1;
				$nearestTab = 1;
				$minTimeDiff = null;

				/* Tabs game days */
				foreach($gameDays as $gameDay)
				{
					$gameDateTime = DateTime::createFromFormat('Y-m-d', $gameDay->datef);
					$tabTimeInterval = $gameDateTime->diff($nowDateTime);
					$gameDateTime = $gameDateTime->format('d/m');

					if ($gameDateTime == $nowDateTime->format('d/m'))
					{
						$gameDateTime .= __( ' - Today', 'lm_gd_scoreboard_widget');
					}
					else {
						switch(DateTime::createFromFormat('Y-m-d', $gameDay->datef)->format('w'))
						{
							case '0':
								$weekDay = __( 'Sun', 'lm_gd_scoreboard_widget' );
								break;
							case '1':
								$weekDay = __( 'Mon', 'lm_gd_scoreboard_widget' );
								break;
							case '2':
								$weekDay = __( 'Tue', 'lm_gd_scoreboard_widget' );
								break;
							case '3':
								$weekDay = __( 'Wed', 'lm_gd_scoreboard_widget' );
								break;
							case '4':
								$weekDay = __( 'Thu', 'lm_gd_scoreboard_widget' );
								break;
							case '5':
								$weekDay = __( 'Fri', 'lm_gd_scoreboard_widget' );
								break;
							default:
								$weekDay = __( 'Sat', 'lm_gd_scoreboard_widget' );
								break;
						}
						$gameDateTime .= " " . $weekDay;
					}

					$dateAddionalClass = '';

					if ($minTimeDiff == null || $minTimeDiff->format("%a") >= $tabTimeInterval->format("%a"))
					{
						$minTimeDiff = $tabTimeInterval;
						$nearestTab = $gameDatesCounter;
						$dateAddionalClass =' class="nearest-score-tab"';
					}

					echo '<li tab-id="' . $gameDatesCounter . '"><a href="#tab' . $gameDatesCounter . '">' . $gameDateTime . '</a></li>';

					$gameDatesCounter++;
				}

				?>
			</ul>
			<?php
			$gameDatesCounter = 1;
			foreach($gameDays as $gameDay)
			{
				$fromDate = DateTime::createFromFormat('Y-m-d', $gameDay->datef);
				$fromDate->setTime(0,0);

				$toDate = clone $fromDate;
				$toDate->setTime(23,59,59);

				$fromDate = $fromDate->format('Y-m-d H:i:s');
				$toDate = $toDate->format('Y-m-d H:i:s');

				$gameDayMatches = $wpdb->get_results(
					"SELECT home_team, away_team, match_day, location, date, home_points, away_points"
					." FROM " . $wpdb->prefix . "leaguemanager_matches "
					."WHERE date >= '" . $fromDate ."' AND date <= '" . $toDate . "'"
				);

				?>
				<div id="tab<?php echo $gameDatesCounter ?>" class="carousel es-carousel es-carousel-wrapper tabber-content">
					<ul class="score-list">
						<?php
						foreach($gameDayMatches as $gameDayMatch)
						{
							$gameTime = DateTime::createFromFormat('Y-m-d H:i:s', $gameDayMatch->date);
							$gameTime = $gameTime->format('H:i');
							$homeTeam = $wpdb->get_var( "SELECT title FROM " . $wpdb->prefix . "leaguemanager_teams WHERE id=". $gameDayMatch->home_team);
							$awayTeam = $wpdb->get_var( "SELECT title FROM " . $wpdb->prefix . "leaguemanager_teams WHERE id=". $gameDayMatch->away_team);
							?>
							<li>
								<span class="score-status">
									<?php echo sprintf( __('R\' %1$s - <strong>%2$s</strong>', 'lm_gd_scoreboard_widget'), $gameDayMatch->match_day, $gameTime ); ?>
								</span>
								<div class="score-teams">
									<?php echo $homeTeam; ?><br />
									<?php echo $awayTeam; ?>
								</div><!--score-teams-->
								<div class="score-right">
									<?php echo $gameDayMatch->home_points; ?><br />
									<?php echo $gameDayMatch->away_points; ?>
								</div><!--score-right-->
							</li>
							<?php
						}
						?>
					</ul><!--score-list-->
				</div><!--tab1-->

				<?php
				$gameDatesCounter++;
			}

			?>
		</div><!--score-wrapper-->
		<script>
			jQuery( document ).ready(function() {
				jQuery('.tabber-container').each(function() {
					jQuery(this).find(".tabber-content").hide(); //Hide all content
				});
				jQuery("ul.score-nav").find("li[tab-id=<?php echo  $nearestTab; ?>]").addClass("active"); //Add "active" class to selected tab
				jQuery("#tab<?php echo  $nearestTab; ?>").show(); //Fade in the active ID content
			});
		</script>

		<?php

	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['date_range'] = strip_tags( $new_instance['date_range'] );

		return $instance;
	}


	function form( $instance ) {

		$defaults = array(
			'date_range'    =>  3,
		);

		/* Set up some default widget settings. */
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Category Name 1 -->
		<p>
			<label for="<?php echo $this->get_field_id( 'date_range' ); ?>">Date Range:</label>
			<input id="<?php echo $this->get_field_id( 'date_range' ); ?>" name="<?php echo $this->get_field_name( 'date_range' ); ?>" value="<?php echo $instance['date_range']; ?>" type="number" step="1" min="0" max="5" style="width:90%;" />
		</p>

		<?php
	}
}