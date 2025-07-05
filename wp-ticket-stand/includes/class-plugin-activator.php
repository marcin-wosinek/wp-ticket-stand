<?php
/**
 * Plugin Activator Class
 *
 * Handles plugin activation tasks
 *
 * @package TicketStand
 */

namespace TicketStand;

use TicketStand\Database\Database_Manager;
use TicketStand\Database\Tables\Customers_Table;
use TicketStand\Database\Tables\Tickets_Table;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin Activator Class
 */
class Plugin_Activator {

	/**
	 * Run activation tasks
	 *
	 * @return void
	 */
	public static function activate() {
		// Initialize the database.
		self::init_database();
		
		// Set the activation flag.
		update_option( 'ticket_stand_activated', true );
		update_option( 'ticket_stand_version', TICKET_STAND_VERSION );
		
		// Clear any caches.
		wp_cache_flush();
	}

	/**
	 * Initialize the database
	 *
	 * @return void
	 */
	private static function init_database() {
		// Create the database manager.
		$db_manager = new Database_Manager();
		
		// Register tables.
		$customers_table = new Customers_Table();
		$tickets_table = new Tickets_Table();
		
		$db_manager->register_table( $customers_table );
		$db_manager->register_table( $tickets_table );
		
		// Install tables.
		$db_manager->install();
	}
}
