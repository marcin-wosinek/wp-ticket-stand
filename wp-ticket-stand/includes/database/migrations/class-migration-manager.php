<?php
/**
 * Migration Manager Class
 *
 * Handles running database migrations
 *
 * @package TicketStand
 */

namespace TicketStand\Database\Migrations;

defined( 'ABSPATH' ) || exit;

/**
 * Migration Manager Class
 */
class Migration_Manager {

	/**
	 * Tables registered with the database manager
	 *
	 * @var array
	 */
	private $tables;

	/**
	 * Constructor
	 *
	 * @param array $tables Tables registered with the database manager.
	 */
	public function __construct( $tables ) {
		$this->tables = $tables;
	}

	/**
	 * Run migrations from one version to another
	 *
	 * @param string $from_version Current version.
	 * @param string $to_version   Target version.
	 * @return void
	 */
	public function run_migrations( $from_version, $to_version ) {
		// Get all available migrations.
		$migrations = $this->get_available_migrations();
		
		// Sort migrations by version.
		usort( $migrations, function( $a, $b ) {
			return version_compare( $a['version'], $b['version'] );
		} );
		
		// Run migrations that are between from_version and to_version.
		foreach ( $migrations as $migration ) {
			if ( version_compare( $migration['version'], $from_version, '>' ) && 
				 version_compare( $migration['version'], $to_version, '<=' ) ) {
				$this->run_migration( $migration['class'] );
			}
		}
	}

	/**
	 * Get all available migrations
	 *
	 * @return array Array of available migrations.
	 */
	private function get_available_migrations() {
		$migrations = array();
		$migration_dir = plugin_dir_path( __FILE__ ) . 'versions';
		
		if ( ! is_dir( $migration_dir ) ) {
			return $migrations;
		}
		
		$files = scandir( $migration_dir );
		
		foreach ( $files as $file ) {
			if ( '.' === $file || '..' === $file ) {
				continue;
			}
			
			if ( preg_match( '/^class-migration-([0-9]+\.[0-9]+\.[0-9]+)\.php$/', $file, $matches ) ) {
				$version = $matches[1];
				$class_name = 'TicketStand\\Database\\Migrations\\Versions\\Migration_' . str_replace( '.', '_', $version );
				
				$migrations[] = array(
					'version' => $version,
					'class'   => $class_name,
					'file'    => $migration_dir . '/' . $file,
				);
			}
		}
		
		return $migrations;
	}

	/**
	 * Run a specific migration
	 *
	 * @param string $class_name Migration class name.
	 * @return void
	 */
	private function run_migration( $class_name ) {
		if ( class_exists( $class_name ) ) {
			$migration = new $class_name( $this->tables );
			$migration->run();
			
			// Log the migration.
			$this->log_migration( $class_name );
		}
	}

	/**
	 * Log a migration
	 *
	 * @param string $class_name Migration class name.
	 * @return void
	 */
	private function log_migration( $class_name ) {
		// Extract version from class name.
		preg_match( '/Migration_([0-9]+)_([0-9]+)_([0-9]+)$/', $class_name, $matches );
		$version = $matches[1] . '.' . $matches[2] . '.' . $matches[3];
		
		// Log the migration.
		$log = array(
			'version'   => $version,
			'timestamp' => current_time( 'mysql' ),
			'status'    => 'completed',
		);
		
		$migration_logs = get_option( 'ticket_stand_migration_logs', array() );
		$migration_logs[] = $log;
		
		update_option( 'ticket_stand_migration_logs', $migration_logs );
	}
}
