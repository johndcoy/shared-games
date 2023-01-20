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
