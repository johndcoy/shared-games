<?php

require_once __DIR__ . '/class-shared-games-plugin.php';
require_once __DIR__ . '/class-shared-games-fetch.php';

/**
 * A home for the API database functions
 */
class Shared_Games_DB {

	/**
	 * Client ID for the API.
	 * 
	 * @var string
	 */
	public $client_id;

	/**
	 * Construct
	 */
	public function __construct() {
		$this->client_id = get_option( 'shared_games_settings' )['client_id'];
	}

	//find the differences from the transients with the name of board games in the database
	public function check_for_missing_game() {
		//output the transient game ids from transient_game_ids()
		$transient_games = get_transient( 'shared_games_bga_games' );
		$this->add_game( $transient_games );
		$this->get_games_bga_id();
	}

	/**
	 * Get board_game_bga_id postmeta from board games
	 * @return array
	 */
	public function get_games_meta_bga_id() {
		global $wpdb;
		$local_bga_ids = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE meta_key = 'board_game_bga_id'" );
		return $local_bga_ids;
	}

	/**
	 * Get game ids from transient.
	 * @return array
	 */
	public function transient_game_ids() {
		$transient_games = get_transient( 'shared_games_bga_games' );
		
		if ( false === $transient_games || empty( $transient_games ) ) {
			$games = new Shared_Games_Fetch();
			$games = $games->fetch_bga_games( $this->client_id );
		} else {
			$games = $transient_games;
		}
		foreach ( $games as $game ) {
			$transient_games_ids[] = $game['id'];
		}
		return $transient_games_ids;
	}

	/**
	 * Get the board_game_bga_id meta from the wp_posts
	 */
	public function get_games_bga_id() {

		global $wpdb;
		//get the board_game_bga_id from the _postsmeta table
		$board_game_bga_id = $wpdb->get_col( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = 'board_game_bga_id'" );
		
		//get the post id from _posts for the custom post type board games that have a board_game_bga_id post_meta value
		$board_game_bga_id_post_id = $wpdb->get_col( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'board_game_bga_id'" );

		//get the difference between the transient game ids and the board_game_bga_id
		$missing_game = array_diff( $this->transient_game_ids(), $board_game_bga_id );

		var_dump( $missing_game);
	}


	/**
	 * Write games to the board game post type.
	 * @param array $game;
	 */
	public function add_game( $game ) {
	
		wp_insert_post( array(
		'post_title'   => $game['name'],
		'post_content' => $game['description'],
		'post_status'  => 'publish',
		'post_type'    => 'board_games',
		'meta_input'   => array(
			'board_game_image'          => $game['image_url'],
			'board_game_year_published' => $game['year_published'],
			'board_game_min_players'    => $game['min_players'],
			'board_game_max_players'    => $game['max_players'],
			'board_game_min_playtime'   => $game['min_playtime'],
			'board_game_max_playtime'   => $game['max_playtime'],
			'board_game_min_age'        => $game['min_age'],
			'board_game_bga_id'         => $game['id'],
		),
		) );
	}
}
