<?php
/**
 * Event Extras Table Class
 *
 * Manages the event extras database table
 *
 * @package TicketStand
 */

namespace TicketStand\Database\Tables;

use TicketStand\Database\Table_Base;

defined( 'ABSPATH' ) || exit;

/**
 * Event Extras Table Class
 */
class Event_Extras_Table extends Table_Base {

	/**
	 * Table name without prefix
	 *
	 * @var string
	 */
	protected $table_name = 'event_extras';

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
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY event_id (event_id)
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

		return $data;
	}

	/**
	 * Get extras by event ID
	 *
	 * @param int   $event_id Event ID.
	 * @param array $args     Query arguments.
	 * @return array Array of objects.
	 */
	public function get_by_event( $event_id, $args = array() ) {
		$defaults = array(
			'number'  => 20,
			'offset'  => 0,
			'orderby' => 'name',
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
}
