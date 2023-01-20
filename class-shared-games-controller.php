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
	 * Get games from fetch_bga_games
	 * 
	 */
	public function get_bga_games() {
		$bga_api_games = get_transient( 'shared_games_bga_games');
		if ( false === $bga_api_games || empty( $bga_api_games ) ) {
			$bga_games = $this->fetch_bga_games->fetch_bga_games();
			set_transient( 'shared_games_bga_games', $bga_games);
			$bga_games = get_transient( 'shared_games_bga_games');
		}
		return $bga_api_games;
	}

	/**
	 * Get game categories from fetch_bga_categories.
	 *
	 */
	public function get_bga_categories() {
		$bga_api_categories = get_transient( 'shared_games_bga_categories');
		if ( false === $bga_api_categories || empty( $bga_api_categories ) ) {
			$bga_categories = $this->fetch_bga_games->fetch_bga_categories();
			set_transient( 'shared_games_bga_categories', $bga_categories);
			$bga_categories = get_transient( 'shared_games_bga_categories');
		}
		return $bga_api_categories;
	}

	/**
	 * Process transients from BGA API to match games with their category name.
	 * 
	 * @return array
	 */
	public function built_bga_games() {
		$bga_categories_transient = $this->get_bga_categories();
		$bga_games_transient      = $this->get_bga_games();

		if ( empty( $bga_categories_transient ) || empty( $bga_games_transient ) ) {
			return new WP_Error( 'no_data', __( 'Could not find BGA API tranients to process games', 'shared-games' ) );
		}

		$bga_games = array();
		array_walk( $bga_games_transient, function( $game ) use ( $bga_categories_transient, &$bga_games ) {
			$game_categories = array();
			foreach ( $game['categories'] as $category ) {
				foreach ( $bga_categories_transient as $bga_category ) {
					if ( $category['id'] === $bga_category['id'] ) {
						$game_categories['categories'][] = $bga_category['name'];
					}
				}
			}
			$game['categories'] = $game_categories;
			$bga_games[]        = $game;
		} );
		return $bga_games;
	}

	/**
	 * Compare the BGA API data with the local data and insert missing games as posts.
	 * 
	 * @return array
	 */
	public function insert_missing_bga_games() {
		$bga_games = $this->built_bga_games();
		$bga_games_id = array_column( $bga_games, 'id' );
		$local_board_games_meta = array_column ( $this->db->get_games_meta_bga_id(), 'meta_value' );
		
		if ( empty( $bga_games ) ) {
			return new WP_Error( 'no_data', __( 'Could not find BGA API data to compare with local data', 'shared-games' ) );
		}
		
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

	/**
	 * Delete local transients/not sure if this is needed but it came to mind one night.
	 * 
	 */
	public function validate_transients() {
		//In the future this should compare a new count from api with count in tranients before deleting.
		delete_transient( 'shared_games_bga_games' );
		delete_transient( 'shared_games_bga_categories' );
	}
}
