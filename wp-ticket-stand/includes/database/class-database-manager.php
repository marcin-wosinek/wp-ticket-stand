<?php
/**
 * Database Manager Class
 *
 * Handles database initialization, version control, and migrations
 *
 * @package TicketStand
 */

namespace TicketStand\Database;

defined( 'ABSPATH' ) || exit;

/**
 * Database Manager Class
 */
class Database_Manager {

	/**
	 * The current database version
	 *
	 * @var string
	 */
	private $db_version;

	/**
	 * Option name to store the database version
	 *
	 * @var string
	 */
	private $version_option_name = 'ticket_stand_db_version';

	/**
	 * Tables registered with this manager
	 *
	 * @var array
	 */
	private $tables = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->db_version = get_option( $this->version_option_name, TICKET_STAND_VERSION );
		$this->register_hooks();
	}

	/**
	 * Register WordPress hooks
	 *
	 * @return void
	 */
	private function register_hooks() {
		// Run migrations when plugin is updated.
		add_action( 'plugins_loaded', array( $this, 'check_version' ) );
		
		// Register activation hook to create tables.
		register_activation_hook( TICKET_STAND_PLUGIN_FILE, array( $this, 'install' ) );
	}

	/**
	 * Register a table with the manager
	 *
	 * @param Table_Base $table Table object to register.
	 * @return void
	 */
	public function register_table( $table ) {
		$this->tables[] = $table;
	}

	/**
	 * Check if the database version has changed and run migrations if needed
	 *
	 * @return void
	 */
	public function check_version() {
		$current_version = TICKET_STAND_VERSION;
		
		if ( version_compare( $this->db_version, $current_version, '<' ) ) {
			$this->run_migrations( $this->db_version, $current_version );
			$this->update_db_version( $current_version );
		}
	}

	/**
	 * Update the stored database version
	 *
	 * @param string $version New version to store.
	 * @return void
	 */
	private function update_db_version( $version ) {
		update_option( $this->version_option_name, $version );
		$this->db_version = $version;
	}

	/**
	 * Install database tables
	 *
	 * @return void
	 */
	public function install() {
		$this->create_tables();
		$this->update_db_version( TICKET_STAND_VERSION );
	}

	/**
	 * Create all registered tables
	 *
	 * @return void
	 */
	private function create_tables() {
		global $wpdb;
		
		// Make sure we have the dbDelta function.
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		
		// Create each registered table.
		foreach ( $this->tables as $table ) {
			$table->create();
		}
	}

	/**
	 * Run database migrations
	 *
	 * @param string $from_version Current version.
	 * @param string $to_version   Target version.
	 * @return void
	 */
	private function run_migrations( $from_version, $to_version ) {
		$migration_manager = new Migrations\Migration_Manager( $this->tables );
		$migration_manager->run_migrations( $from_version, $to_version );
	}
}
