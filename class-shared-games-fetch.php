<?php


require_once __DIR__ . '/class-shared-games-controller.php';

/**
 * Class for fetching games from the API.
 *
 * @package shared-games
 */

class Shared_Games_Fetch {

	/**
	 * Client ID for the API.
	 * 
	 * @var string
	 */
	public $client_id;

	/**
	 * Games array.
	 * 
	 * @var array
	 */
	public $games;

	/**
	 * Construct
	 */
	public function __construct() {
		$this->client_id = get_option( 'shared_games_settings' )['client_id'];
	}

	/**
	 * Connect to the Board Game Atlas API & fetch games.
	 * 
	 * @return array
	 */
	public function fetch_bga_games() {
		if ( '' === $this->client_id || empty( $this->client_id ) ) {
			return new WP_Error( 'api_error', 'Missing client_id.' );
		}

		$limit = 100;
		$skip  = 0;
		$year_published = 1950;
		
		$games = array();
		
		//create a loop to get all the games until the count is reached
		do {
			$api_url   = 'https://api.boardgameatlas.com/api/search?client_id=' . $this->client_id . '&limit=' . $limit . '&skip=' . $skip . '&year_published=' . $year_published . '&fields=name,description,image_url,categories,id,year_published,min_players,max_players,min_playtime,max_playtime,min_age';
			$request   = $this->check_for_errors( $api_url );
			$ratelimit = $request["headers"]["x-ratelimit-remaining"];
			$count = json_decode( wp_remote_retrieve_body( $request ), true )['count'];
			if ( is_null( $count ) ) {
				return new WP_Error( 'api_error', 'Missing count to loop through BGA API.' );
			}
			$response    = json_decode( wp_remote_retrieve_body( $request ), true );
			$games       = array_merge( $games, $response['games'] );
			$games_count = count( $games );
			$skip += $limit;
			if ( $games_count >= $count ) {
				$games_count = 0;
				$skip        = 0;
				$year_published++;
			}
			if ( $ratelimit <= 1 ) {
				sleep(60);
			}
		} while ( $games_count < $count && $year_published >= 1951 );
		if ( empty( $games ) ) {
			return new WP_Error( 'api_error', 'No games found.' );
		}
		return $games;
	}

	/**
	 * Connect to the Board Game Atlas API & fetch categories.
	 * 
	 * @return array
	 */
	public function fetch_bga_categories() {
		$api_url = 'https://api.boardgameatlas.com/api/game/categories?client_id=' . $this->client_id;
		$request = $this->check_for_errors( $api_url);
		$response = json_decode( wp_remote_retrieve_body( $request ), true );
		$categories = $response['categories'];
		return $categories;
	}

	/**
	 * Check for errors.
	 * 
	 */
	public function check_for_errors( $api_url ) {
		$request       = wp_remote_get( $api_url );
		$response_code = wp_remote_retrieve_response_code( $request );

		if ( is_wp_error( $request ) || 200 !== $response_code ) {
			return new WP_Error( 'api_error', 'Error fetching names from the API' );
		} elseif ( 403 === $response_code ) {
			return new WP_Error( 'api_error', 'Error: forbidden or invalid client_id' );
		} else {
			return $request;
		}
	}
}
