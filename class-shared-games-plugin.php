<?php

require_once __DIR__ . '/class-shared-games-controller.php';

/**
 * WordPress plugin pipeline
 */
class Shared_Games_Plugin {

	/**
	 * Main Plugin file
	 *
	 * @var string
	 */
	public $plugin_file;

	/**
	 * Client ID for the API.
	 * 
	 * @var string
	 */
	public $client_id;

	/**
	 * Constructor
	 *
	 * @param string $plugin_file base plugin filename.
	 * @param string $version Plugin version.
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
		$this->client_id   = get_option( 'shared_games_settings' )['client_id'];
	}
	
	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 * 
	 * since 1.0.0
	 */
	public function run() {
		$this->register_hooks();
	}

	public function register_hooks() {
		if ( is_admin() ) {
			add_action( 'init',       array ( $this, 'shared_games_init' ), 5 );
			add_action( 'init',       array ( $this, 'shared_games_board_games_category' ), 10 );
			add_action( 'admin_init', array ( $this, 'shared_games_settings_init' ), 10 );
			add_action( 'admin_menu', array ( $this, 'shared_games_settings_menu' ), 10 );
			add_action( 'admin_menu', array ( $this, 'shared_games_add_meta_box' ), 10 );

			// Add settings link to plugins page.
			add_filter( 'plugin_action_links_' . plugin_basename( $this->plugin_file ), array( $this, 'shared_games_settings_link' ) );
		}
	}

	/**
	 * Add a link to plugin settings page
	 * 
	 * since 1.0.0
	 */
	public function shared_games_settings_link( $links ) {
		$settings_link = '<a href="edit.php?post_type=board_games&page=shared-games-settings">' . __( 'Settings', 'shared-games' ) . '</a>';
		array_push( $links, $settings_link );
		return $links;
	}

	/**
	 * Register the Boad games custom post type
	 * 
	 * @since 1.0.0
	 */
	public function shared_games_init() {
		$labels = array(
			'name'                  => _x( 'Board games', 'Post type general name', 'board game' ),
			'singular_name'         => _x( 'Board game', 'Post type singular name', 'board game' ),
			'menu_name'             => _x( 'Board games', 'Admin Menu text', 'board game' ),
			'name_admin_bar'        => _x( 'Board game', 'Add New on Toolbar', 'board game' ),
			'add_new'               => __( 'Add New', 'board game' ),
			'add_new_item'          => __( 'Add New board game', 'board game' ),
			'new_item'              => __( 'New board game', 'board game' ),
			'edit_item'             => __( 'Edit board game', 'board game' ),
			'view_item'             => __( 'View board game', 'board game' ),
			'all_items'             => __( 'All board games', 'board game' ),
			'search_items'          => __( 'Search board board games', 'board game' ),
			'parent_item_colon'     => __( 'Parent board board games:', 'board game' ),
			'not_found'             => __( 'No board board games found.', 'board game' ),
			'not_found_in_trash'    => __( 'No board board games found in Trash.', 'board game' ),
			'featured_image'        => _x( 'Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'board game' ),
			'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'board game' ),
			'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'board game' ),
			'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'board game' ),
			'archives'              => _x( 'Board game archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'board game' ),
			'insert_into_item'      => _x( 'Insert into board game', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'board game' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this board game', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'board game' ),
			'filter_items_list'     => _x( 'Filter board board games list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'board game' ),
			'items_list_navigation' => _x( 'Board games list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'board game' ),
			'items_list'            => _x( 'Board games list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'board game' ),
		);     
		$args = array(
			'labels'             => $labels,
			'description'        => 'Board game custom post type.',
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'board game' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_icon'          => 'dashicons-layout',
			'menu_position'      => 20,
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'revisions', 'custom-fields' ),
			'show_in_rest'       => true
		);
			
		register_post_type( 'board_games', $args );

		$args = array (
			'type'              => 'string',
			'description'       => 'Shared games settings',
			'sanitize_callback' => 'shared_games_validate_options',
			'default'           => null,
		);
	}

	/**
	 * Render Custom Taxonomy for board games category.
	 * 
	 * @since 1.0.0
	 */
	public function shared_games_board_games_category() {
		
		$args = array(
			'hierarchical'     => true,
			'labels'           => array(
				'name'                  => __( 'Board game categories', 'board game' ),
				'singular_name'         => __( 'Category', 'board game' ),
				'menu_name'             => _x( 'Categories', 'Admin menu name', 'board game' ),
				'search_items'          => __( 'Search categories', 'board game' ),
				'all_items'             => __( 'All categories', 'board game' ),
				'parent_item'           => __( 'Parent category', 'board game' ),
				'parent_item_colon'     => __( 'Parent category:', 'board game' ),
				'edit_item'             => __( 'Edit category', 'board game' ),
				'update_item'           => __( 'Update category', 'board game' ),
				'add_new_item'          => __( 'Add new category', 'board game' ),
				'new_item_name'         => __( 'New category name', 'board game' ),
				'not_found'             => __( 'No categories found', 'board game' ),
				'item_link'             => __( 'Board Game Category Link', 'board game' ),
				'item_link_description' => __( 'A link to a board game category.', 'board game' )
			),
			'public'                     => true,
			'has_archive'                => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
		);
		register_taxonomy( 'board_games_category', array( 'board_games' ), $args ); 
	}

	/**
	 * Render the menu to the settings page.
	 * 
	 * @since 1.0.0
	 */
	public function shared_games_settings_menu() {
		add_submenu_page(
			'edit.php?post_type=board_games',
			'Shared Games Settings',
			'Settings',
			'manage_options',
			'shared_games_settings',
			array ( $this, 'shared_games_settings_page' ),
			100
		);
	}

	/**
	 * Add meta box to the board game post type.
	 * 
	 * @since 1.0.0
	 */
	public function shared_games_add_meta_box() {
		add_meta_box(
			'shared_games_meta_box',
			'Board Game Details',
			array ( $this, 'shared_games_meta_box_callback' ),
			'board_games',
			'side',
			'high'
		);
	}

	/**
	 * Render the meta box.
	 * 
	 * @since 1.0.0
	 */
	public function shared_games_meta_box_callback() {
		echo "This is the meta box.";
	}

	/**
	 * Render the settings page.
	 */
	public function shared_games_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		} else {
			require_once __DIR__ . '/admin-settings-page.php';
		}
	}

	/**
	 * Render the sections and fields for the settings page.
	 * 
	 * @since 1.0.0
	 */
	public function shared_games_settings_init() {
		$this->options = array (
			'client_id' => '',
			);

		if ( false === get_option( 'shared_games_settings' ) ) {
			add_option( 'shared_games_settings', $this->options );
		} elseif ( '' === get_option( 'shared_games_settings' ) ) {
			update_option( 'shared_games_settings', $this->options );
		}

		$args = array(
			'type'        => 'array',
			'description' => 'Shared Games Settings',
		);

		register_setting( 'shared_games_settings', 'shared_games_settings', $args );

		add_settings_section(
			'shared_games_settings_main_section',
			__( 'Shared Games Settings', 'shared_games' ),
			array( $this, 'shared_games_settings_section_callback' ),
			'shared-games-settings'
		);
		add_settings_field(
			'shared_games_settings_client_id',
			__( 'Client ID', 'shared_games' ),
			array( $this, 'shared_games_settings_client_id_callback' ),
			'shared-games-settings',
			'shared_games_settings_main_section',
		);
	}

	public function shared_games_settings_section_callback() {
		echo __( 'Enter your client ID from Board Game Atlas.', 'shared_games' );
	}
	public function shared_games_settings_client_id_callback() {

		if ( isset( $this->options['client_id'] ) ) {
			$this->options['client_id'] = esc_html( $this->options['client_id'] );
		}
		echo '<input name="shared_games_settings[client_id]" type="text" value="' . esc_attr( $this->client_id ) . '">';

	}
}

