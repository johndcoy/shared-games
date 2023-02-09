<?php

require_once __DIR__ . '/class-shared-games-fetch.php';
require_once __DIR__ . '/class-shared-games-db.php';

/**
 * Class for handleing the API data with the database data.
 *
 * @package shared-games
 */

class Shared_Games_Controller {

	/**
	 * Database class.
	 * 
	 * @var object
	 */
	public $db;

	/**
	 * Fetch games class.
	 * 
	 * @var object
	 */
	public $fetch_bga_games;

	/**
	 * Construct
	 */
	public function __construct() {
		$this->db              = new Shared_Games_DB();
		$this->fetch_bga_games = new Shared_Games_Fetch();
	}

	/**
	 * Process BGA API to match games with their category name.
	 * 
	 * @return array
	 */
	public function built_bga_games() {
		$bga_games      = $this->fetch_bga_games->fetch_bga_games();
		$bga_categories = $this->fetch_bga_games->fetch_bga_categories();

		if ( empty( $bga_categories ) || empty( $bga_games ) || is_null( $bga_categories ) ||  is_null( $bga_games ) ) {
			return new WP_Error( 'no_data', __( 'BGA API returned empty games or empty categories', 'shared-games' ) );
		}
		$sorted_categories = array_column( $bga_categories, 'name', 'id' );

		//if categories id is in the game categories array, add the category name to the game categories array
		array_walk( $bga_games, function( &$games ) use ( $sorted_categories ) {
			foreach ( $games['categories'] as $key => $value ) {
				$games['categories'][$key]['name'] = $sorted_categories[$value['id']];
			}
		});
		return $bga_games;
	}

	/**
	 * Compare the BGA API data with the local data and insert missing games as posts.
	 * 
	 * @return array
	 */
	public function insert_missing_bga_games() {
		$bga_games              = $this->built_bga_games();
		$bga_games_id           = array_column( $bga_games, 'id' );
		$local_board_games_meta = array_column ( $this->db->get_games_meta_bga_id(), 'meta_value' );
		
		//get the games that are missing from the local database by comparing bda_games_id with local_board_games_meta
		$missing_games = array_diff( $bga_games_id, $local_board_games_meta );
		$missing_games = array_intersect_key( $bga_games, $missing_games );
		
		$inserted_games = array();
		//add the missing games to the database. I had this as a separate function but it was easier to read to have it here.
		if ( ! empty( $missing_games ) ) {
			foreach ( $missing_games as $game ) {
				$inserted_games[] = $this->db->add_game( $game );
			}
		} else {
			return new WP_Error( 'no_data', __( 'No new games to add', 'shared-games' ) );
		}
		return $inserted_games;
	}
}
