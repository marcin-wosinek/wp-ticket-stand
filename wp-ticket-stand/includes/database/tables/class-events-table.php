<?php
/**
 * Events Table Class
 *
 * Manages the events database table
 *
 * @package TicketStand
 */

namespace TicketStand\Database\Tables;

use TicketStand\Database\Table_Base;

defined( 'ABSPATH' ) || exit;

/**
 * Events Table Class
 */
class Events_Table extends Table_Base {

	/**
	 * Table name without prefix
	 *
	 * @var string
	 */
	protected $table_name = 'events';

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
			slug varchar(200) NOT NULL,
			name varchar(255) NOT NULL,
			summary text DEFAULT NULL,
			post_id bigint(20) unsigned DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY slug (slug),
			KEY post_id (post_id)
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
		// Slug validation
		if ( isset( $data['slug'] ) ) {
			$data['slug'] = sanitize_title( $data['slug'] );
		}

		// Name validation
		if ( isset( $data['name'] ) ) {
			$data['name'] = sanitize_text_field( $data['name'] );
		}

		// Summary validation
		if ( isset( $data['summary'] ) ) {
			$data['summary'] = wp_kses_post( $data['summary'] );
		}

		// Post ID validation
		if ( isset( $data['post_id'] ) ) {
			$data['post_id'] = absint( $data['post_id'] );
		}

		return $data;
	}

	/**
	 * Get event by slug
	 *
	 * @param string $slug Event slug.
	 * @return object|null Database query result as object, or null on failure.
	 */
	public function get_by_slug( $slug ) {
		$query = $this->wpdb->prepare(
			"SELECT * FROM {$this->table_full_name} WHERE slug = %s",
			$slug
		);
		
		return $this->wpdb->get_row( $query );
	}

	/**
	 * Get event by post ID
	 *
	 * @param int $post_id WordPress post ID.
	 * @return object|null Database query result as object, or null on failure.
	 */
	public function get_by_post_id( $post_id ) {
		$query = $this->wpdb->prepare(
			"SELECT * FROM {$this->table_full_name} WHERE post_id = %d",
			$post_id
		);
		
		return $this->wpdb->get_row( $query );
	}

	/**
	 * Search events
	 *
	 * @param string $search Search term.
	 * @param array  $args   Query arguments.
	 * @return array Array of objects.
	 */
	public function search( $search, $args = array() ) {
		$defaults = array(
			'number'  => 20,
			'offset'  => 0,
			'orderby' => 'name',
			'order'   => 'ASC',
		);

		$args = wp_parse_args( $args, $defaults );
		
		$search = '%' . $this->wpdb->esc_like( $search ) . '%';
		
		$query = $this->wpdb->prepare(
			"SELECT * FROM {$this->table_full_name} 
			WHERE name LIKE %s 
			OR slug LIKE %s 
			OR summary LIKE %s
			ORDER BY {$args['orderby']} {$args['order']}
			LIMIT %d, %d",
			$search, $search, $search, $args['offset'], $args['number']
		);
		
		return $this->wpdb->get_results( $query );
	}
}
