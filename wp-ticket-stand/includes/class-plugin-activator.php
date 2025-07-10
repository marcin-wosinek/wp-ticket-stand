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
use TicketStand\Database\Tables\Events_Table;
use TicketStand\Database\Tables\Ticket_Types_Table;
use TicketStand\Database\Tables\Event_Extras_Table;
use TicketStand\Database\Tables\Ticket_Type_Extras_Table;

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
		$events_table = new Events_Table();
		$ticket_types_table = new Ticket_Types_Table();
		$event_extras_table = new Event_Extras_Table();
		$ticket_type_extras_table = new Ticket_Type_Extras_Table();
		
		$db_manager->register_table( $events_table );
		$db_manager->register_table( $ticket_types_table );
		$db_manager->register_table( $event_extras_table );
		$db_manager->register_table( $ticket_type_extras_table );
		
		// Install tables.
		$db_manager->install();
	}
}
