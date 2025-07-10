<?php
/**
 * Ticket Types Table Class
 *
 * Manages the ticket types database table
 *
 * @package TicketStand
 */

namespace TicketStand\Database\Tables;

use TicketStand\Database\Table_Base;

defined( 'ABSPATH' ) || exit;

/**
 * Ticket Types Table Class
 */
class Ticket_Types_Table extends Table_Base {

	/**
	 * Table name without prefix
	 *
	 * @var string
	 */
	protected $table_name = 'ticket_types';

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
			event_id bigint(20) unsigned NOT NULL,
			name varchar(255) NOT NULL,
			price decimal(10,2) NOT NULL DEFAULT 0,
			availability_start datetime DEFAULT NULL,
			availability_end datetime DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY event_id (event_id),
			KEY availability (availability_start, availability_end)
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
		// Name validation
		if ( isset( $data['name'] ) ) {
			$data['name'] = sanitize_text_field( $data['name'] );
		}

		// Price validation
		if ( isset( $data['price'] ) ) {
			$data['price'] = (float) $data['price'];
		}

		// Event ID validation
		if ( isset( $data['event_id'] ) ) {
			$data['event_id'] = absint( $data['event_id'] );
		}

		// Date validations
		if ( isset( $data['availability_start'] ) && ! empty( $data['availability_start'] ) ) {
			$data['availability_start'] = sanitize_text_field( $data['availability_start'] );
		}

		if ( isset( $data['availability_end'] ) && ! empty( $data['availability_end'] ) ) {
			$data['availability_end'] = sanitize_text_field( $data['availability_end'] );
		}

		return $data;
	}

	/**
	 * Get ticket types by event ID
	 *
	 * @param int   $event_id Event ID.
	 * @param array $args     Query arguments.
	 * @return array Array of objects.
	 */
	public function get_by_event( $event_id, $args = array() ) {
		$defaults = array(
			'number'  => 20,
			'offset'  => 0,
			'orderby' => 'price',
			'order'   => 'ASC',
		);

		$args = wp_parse_args( $args, $defaults );
		
		$query = $this->wpdb->prepare(
			"SELECT * FROM {$this->table_full_name} 
			WHERE event_id = %d
			ORDER BY {$args['orderby']} {$args['order']}
			LIMIT %d, %d",
			$event_id, $args['offset'], $args['number']
		);
		
		return $this->wpdb->get_results( $query );
	}

	/**
	 * Get currently available ticket types
	 *
	 * @param int   $event_id Event ID.
	 * @param array $args     Query arguments.
	 * @return array Array of objects.
	 */
	public function get_available( $event_id, $args = array() ) {
		$defaults = array(
			'number'  => 20,
			'offset'  => 0,
			'orderby' => 'price',
			'order'   => 'ASC',
		);

		$args = wp_parse_args( $args, $defaults );
		
		$now = current_time( 'mysql' );
		
		$query = $this->wpdb->prepare(
			"SELECT * FROM {$this->table_full_name} 
			WHERE event_id = %d
			AND (availability_start IS NULL OR availability_start <= %s)
			AND (availability_end IS NULL OR availability_end >= %s)
			ORDER BY {$args['orderby']} {$args['order']}
			LIMIT %d, %d",
			$event_id, $now, $now, $args['offset'], $args['number']
		);
		
		return $this->wpdb->get_results( $query );
	}
}
