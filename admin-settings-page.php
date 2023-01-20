<div class="wrap">
	<form method = "post" action = "options.php">
		<?php settings_fields( 'shared_games_settings' ); ?>
		<?php do_settings_sections( 'shared-games-settings' ); ?>
		<?php submit_button(); ?>
	</form>
	<?php
	if ( isset( $_POST ['insert_missing_bga_games'] ) ) {
		$fetch_bga_games = new Shared_Games_Controller();
		$fetch_bga_games->insert_missing_bga_games();
	}
	?>
	<form method="post" action="">
		<input type="submit" name="insert_missing_bga_games" id="insert_missing_bga_games" class="button button-primary" value="Insert Missing BGA Games">
	</form>
	</div>