<?php
/**
 * Ticket Type Extras Table Class
 *
 * Manages the junction table for ticket types and event extras (many-to-many relationship)
 *
 * @package TicketStand
 */

namespace TicketStand\Database\Tables;

use TicketStand\Database\Table_Base;

defined( 'ABSPATH' ) || exit;

/**
 * Ticket Type Extras Table Class
 */
class Ticket_Type_Extras_Table extends Table_Base {

	/**
	 * Table name without prefix
	 *
	 * @var string
	 */
	protected $table_name = 'ticket_type_extras';

	/**
	 * Primary key column name
	 *
	 * @var string
	 */
	protected $primary_key = 'id';

	/**
	 * Table version
	 *
	 * @var string
	 */
	protected $version = '1.0.0';

	/**
	 * Get the SQL statement to create the table
	 *
	 * @return string SQL statement
	 */
	protected function get_create_table_sql() {
		$collate = $this->wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$this->table_full_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			ticket_type_id bigint(20) unsigned NOT NULL,
			event_extra_id bigint(20) unsigned NOT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY ticket_extra (ticket_type_id, event_extra_id),
			KEY ticket_type_id (ticket_type_id),
			KEY event_extra_id (event_extra_id)
		) $collate;";

		return $sql;
	}

	/**
	 * Validate data before inserting or updating
	 *
	 * @param array $data Data to validate.
	 * @return array Validated data.
	 */
	protected function validate_data( $data ) {
		// Ticket type ID validation
		if ( isset( $data['ticket_type_id'] ) ) {
			$data['ticket_type_id'] = absint( $data['ticket_type_id'] );
		}

		// Event extra ID validation
		if ( isset( $data['event_extra_id'] ) ) {
			$data['event_extra_id'] = absint( $data['event_extra_id'] );
		}

		return $data;
	}

	/**
	 * Get extras by ticket type ID
	 *
	 * @param int $ticket_type_id Ticket type ID.
	 * @return array Array of event extra objects.
	 */
	public function get_extras_by_ticket_type( $ticket_type_id ) {
		$query = $this->wpdb->prepare(
			"SELECT e.* 
			FROM {$this->wpdb->prefix}ticket_stand_event_extras e
			JOIN {$this->table_full_name} te ON e.id = te.event_extra_id
			WHERE te.ticket_type_id = %d
			ORDER BY e.name ASC",
			$ticket_type_id
		);
		
		return $this->wpdb->get_results( $query );
	}

	/**
	 * Get ticket types by extra ID
	 *
	 * @param int $event_extra_id Event extra ID.
	 * @return array Array of ticket type objects.
	 */
	public function get_ticket_types_by_extra( $event_extra_id ) {
		$query = $this->wpdb->prepare(
			"SELECT t.* 
			FROM {$this->wpdb->prefix}ticket_stand_ticket_types t
			JOIN {$this->table_full_name} te ON t.id = te.ticket_type_id
			WHERE te.event_extra_id = %d
			ORDER BY t.name ASC",
			$event_extra_id
		);
		
		return $this->wpdb->get_results( $query );
	}

	/**
	 * Associate a ticket type with an event extra
	 *
	 * @param int $ticket_type_id Ticket type ID.
	 * @param int $event_extra_id Event extra ID.
	 * @return int|false The number of rows inserted, or false on error.
	 */
	public function associate( $ticket_type_id, $event_extra_id ) {
		// Check if the association already exists
		$exists = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->table_full_name} 
				WHERE ticket_type_id = %d AND event_extra_id = %d",
				$ticket_type_id, $event_extra_id
			)
		);
		
		if ( $exists ) {
			return true; // Already associated
		}
		
		// Create the association
		$data = array(
			'ticket_type_id' => $ticket_type_id,
			'event_extra_id' => $event_extra_id,
		);
		
		return $this->insert( $data );
	}

	/**
	 * Remove association between a ticket type and an event extra
	 *
	 * @param int $ticket_type_id Ticket type ID.
	 * @param int $event_extra_id Event extra ID.
	 * @return int|false The number of rows deleted, or false on error.
	 */
	public function dissociate( $ticket_type_id, $event_extra_id ) {
		return $this->delete(
			array(
				'ticket_type_id' => $ticket_type_id,
				'event_extra_id' => $event_extra_id,
			)
		);
	}
}
