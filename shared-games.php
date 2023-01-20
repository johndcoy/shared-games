<?php
/**
Plugin Name: Shared Games
Description: Add and board games to a collection.
Version: 0.0.1
Requires PHP: 7.4
Author: John Coy
Author URI: https://heycoy.com
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

@package shared-games
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SHARED_GAMES_VERSION', '0.0.1' );

require_once __DIR__ . '/class-shared-games-plugin.php';

$shared_games_plugin = new Shared_Games_Plugin( __FILE__ );
$shared_games_plugin->run();


