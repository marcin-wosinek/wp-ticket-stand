<?php
/**
 * Migration Base Class
 *
 * Abstract base class for all migrations
 *
 * @package TicketStand
 */

namespace TicketStand\Database\Migrations;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract Migration Base Class
 */
abstract class Migration_Base {

	/**
	 * Tables registered with the database manager
	 *
	 * @var array
	 */
	protected $tables;

	/**
	 * WordPress database object
	 *
	 * @var \wpdb
	 */
	protected $wpdb;

	/**
	 * Constructor
	 *
	 * @param array $tables Tables registered with the database manager.
	 */
	public function __construct( $tables ) {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->tables = $tables;
	}

	/**
	 * Run the migration
	 *
	 * @return void
	 */
	abstract public function run();

	/**
	 * Add a column to a table if it doesn't exist
	 *
	 * @param string $table_name  Full table name.
	 * @param string $column_name Column name.
	 * @param string $column_def  Column definition (e.g., "varchar(255) NOT NULL DEFAULT ''").
	 * @return bool True if column was added, false otherwise.
	 */
	protected function add_column_if_not_exists( $table_name, $column_name, $column_def ) {
		$column_exists = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SHOW COLUMNS FROM {$table_name} LIKE %s",
				$column_name
			)
		);

		if ( empty( $column_exists ) ) {
			$this->wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN {$column_name} {$column_def}" );
			return true;
		}

		return false;
	}

	/**
	 * Modify a column in a table
	 *
	 * @param string $table_name  Full table name.
	 * @param string $column_name Column name.
	 * @param string $column_def  New column definition.
	 * @return bool True if column was modified, false otherwise.
	 */
	protected function modify_column( $table_name, $column_name, $column_def ) {
		$column_exists = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SHOW COLUMNS FROM {$table_name} LIKE %s",
				$column_name
			)
		);

		if ( ! empty( $column_exists ) ) {
			$this->wpdb->query( "ALTER TABLE {$table_name} MODIFY COLUMN {$column_name} {$column_def}" );
			return true;
		}

		return false;
	}

	/**
	 * Drop a column from a table if it exists
	 *
	 * @param string $table_name  Full table name.
	 * @param string $column_name Column name.
	 * @return bool True if column was dropped, false otherwise.
	 */
	protected function drop_column_if_exists( $table_name, $column_name ) {
		$column_exists = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SHOW COLUMNS FROM {$table_name} LIKE %s",
				$column_name
			)
		);

		if ( ! empty( $column_exists ) ) {
			$this->wpdb->query( "ALTER TABLE {$table_name} DROP COLUMN {$column_name}" );
			return true;
		}

		return false;
	}

	/**
	 * Add an index to a table if it doesn't exist
	 *
	 * @param string $table_name Full table name.
	 * @param string $index_name Index name.
	 * @param string $columns    Columns to include in the index.
	 * @param bool   $unique     Whether the index should be unique.
	 * @return bool True if index was added, false otherwise.
	 */
	protected function add_index_if_not_exists( $table_name, $index_name, $columns, $unique = false ) {
		$index_exists = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SHOW INDEX FROM {$table_name} WHERE Key_name = %s",
				$index_name
			)
		);

		if ( empty( $index_exists ) ) {
			$index_type = $unique ? 'UNIQUE INDEX' : 'INDEX';
			$this->wpdb->query( "ALTER TABLE {$table_name} ADD {$index_type} {$index_name} ({$columns})" );
			return true;
		}

		return false;
	}

	/**
	 * Drop an index from a table if it exists
	 *
	 * @param string $table_name Full table name.
	 * @param string $index_name Index name.
	 * @return bool True if index was dropped, false otherwise.
	 */
	protected function drop_index_if_exists( $table_name, $index_name ) {
		$index_exists = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SHOW INDEX FROM {$table_name} WHERE Key_name = %s",
				$index_name
			)
		);

		if ( ! empty( $index_exists ) ) {
			$this->wpdb->query( "ALTER TABLE {$table_name} DROP INDEX {$index_name}" );
			return true;
		}

		return false;
	}
}
