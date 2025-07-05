<?php
/**
 * Table Base Class
 *
 * Abstract base class for all database tables
 *
 * @package TicketStand
 */

namespace TicketStand\Database;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract Table Base Class
 */
abstract class Table_Base {

	/**
	 * WordPress database object
	 *
	 * @var \wpdb
	 */
	protected $wpdb;

	/**
	 * Table name without prefix
	 *
	 * @var string
	 */
	protected $table_name;

	/**
	 * Full table name with prefix
	 *
	 * @var string
	 */
	protected $table_full_name;

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
	protected $version;

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		
		// Set the full table name with WordPress prefix.
		$this->table_full_name = $this->wpdb->prefix . 'ticket_stand_' . $this->table_name;
	}

	/**
	 * Get the table name
	 *
	 * @return string
	 */
	public function get_table_name() {
		return $this->table_full_name;
	}

	/**
	 * Get the primary key
	 *
	 * @return string
	 */
	public function get_primary_key() {
		return $this->primary_key;
	}

	/**
	 * Create the database table
	 *
	 * @return void
	 */
	public function create() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		
		$sql = $this->get_create_table_sql();
		dbDelta( $sql );
	}

	/**
	 * Get the SQL statement to create the table
	 *
	 * @return string SQL statement
	 */
	abstract protected function get_create_table_sql();

	/**
	 * Insert a new record
	 *
	 * @param array $data Data to insert (column => value).
	 * @return int|false The number of rows inserted, or false on error.
	 */
	public function insert( $data ) {
		// Validate data before inserting.
		$data = $this->validate_data( $data );
		
		// Insert the data.
		$result = $this->wpdb->insert(
			$this->table_full_name,
			$data
		);
		
		return $result ? $this->wpdb->insert_id : false;
	}

	/**
	 * Update a record
	 *
	 * @param array $data  Data to update (column => value).
	 * @param array $where Where clause (column => value).
	 * @return int|false The number of rows updated, or false on error.
	 */
	public function update( $data, $where ) {
		// Validate data before updating.
		$data = $this->validate_data( $data );
		
		// Update the data.
		return $this->wpdb->update(
			$this->table_full_name,
			$data,
			$where
		);
	}

	/**
	 * Delete a record
	 *
	 * @param array $where Where clause (column => value).
	 * @return int|false The number of rows deleted, or false on error.
	 */
	public function delete( $where ) {
		return $this->wpdb->delete(
			$this->table_full_name,
			$where
		);
	}

	/**
	 * Get a single row by ID
	 *
	 * @param int $id ID of the record to get.
	 * @return object|null Database query result as object, or null on failure.
	 */
	public function get( $id ) {
		$query = $this->wpdb->prepare(
			"SELECT * FROM {$this->table_full_name} WHERE {$this->primary_key} = %d",
			$id
		);
		
		return $this->wpdb->get_row( $query );
	}

	/**
	 * Get multiple rows
	 *
	 * @param array $args Query arguments.
	 * @return array Array of objects.
	 */
	public function get_items( $args = array() ) {
		$defaults = array(
			'number'  => 20,
			'offset'  => 0,
			'orderby' => $this->primary_key,
			'order'   => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );
		
		$query = "SELECT * FROM {$this->table_full_name}";
		
		// Add where clause if specified.
		if ( isset( $args['where'] ) && is_array( $args['where'] ) ) {
			$query .= ' WHERE ';
			$conditions = array();
			
			foreach ( $args['where'] as $column => $value ) {
				$conditions[] = $this->wpdb->prepare( "{$column} = %s", $value );
			}
			
			$query .= implode( ' AND ', $conditions );
		}
		
		// Add order clause.
		$query .= " ORDER BY {$args['orderby']} {$args['order']}";
		
		// Add limit clause.
		$query .= $this->wpdb->prepare( " LIMIT %d, %d", $args['offset'], $args['number'] );
		
		return $this->wpdb->get_results( $query );
	}

	/**
	 * Count total items
	 *
	 * @param array $args Query arguments.
	 * @return int
	 */
	public function count( $args = array() ) {
		$query = "SELECT COUNT(*) FROM {$this->table_full_name}";
		
		// Add where clause if specified.
		if ( isset( $args['where'] ) && is_array( $args['where'] ) ) {
			$query .= ' WHERE ';
			$conditions = array();
			
			foreach ( $args['where'] as $column => $value ) {
				$conditions[] = $this->wpdb->prepare( "{$column} = %s", $value );
			}
			
			$query .= implode( ' AND ', $conditions );
		}
		
		return (int) $this->wpdb->get_var( $query );
	}

	/**
	 * Validate data before inserting or updating
	 *
	 * @param array $data Data to validate.
	 * @return array Validated data.
	 */
	protected function validate_data( $data ) {
		// Implement validation in child classes.
		return $data;
	}

	/**
	 * Check if the table exists
	 *
	 * @return bool
	 */
	public function exists() {
		$query = $this->wpdb->prepare(
			'SHOW TABLES LIKE %s',
			$this->wpdb->esc_like( $this->table_full_name )
		);
		
		return (bool) $this->wpdb->get_var( $query );
	}
}
