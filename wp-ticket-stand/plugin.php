<?php
/**
 * Ticket Stand
 *
 * A WordPress plugin for managing ticket sales and event attendance.
 *
 * PHP version 8.2
 *
 * @category WordPress_Plugin
 * @package  TicketStand
 * @author   Marcin Wosinek <marcin.wosinek@gmail.com>
 * @license  GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 * @link     https://github.com/marcin-wosinek/wp-ticket-stand
 * @since    1.0.0
 *
 * @wordpress-plugin
 * Plugin Name: Ticket Stand
 * Plugin URI:  https://github.com/marcin-wosinek/wp-ticket-stand
 * Description: A WordPress plugin for managing ticket sales and event attendance.
 * Author:      Marcin Wosinek <marcin.wosinek@gmail.com>
 * Version:     1.0.0
 */

namespace TicketStand;

defined('WPINC') || die;
require_once __DIR__ . '/vendor/autoload.php';

// Define plugin constants
define('TICKET_STAND_VERSION', '1.0.0');
define('TICKET_STAND_PLUGIN_FILE', __FILE__);
define('TICKET_STAND_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TICKET_STAND_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load required files
require_once TICKET_STAND_PLUGIN_DIR . 'includes/class-plugin-activator.php';
require_once TICKET_STAND_PLUGIN_DIR . 'includes/database/class-database-manager.php';
require_once TICKET_STAND_PLUGIN_DIR . 'includes/database/class-table-base.php';

// Load table classes
require_once TICKET_STAND_PLUGIN_DIR . 'includes/database/tables/class-events-table.php';
require_once TICKET_STAND_PLUGIN_DIR . 'includes/database/tables/class-ticket-types-table.php';
require_once TICKET_STAND_PLUGIN_DIR . 'includes/database/tables/class-event-extras-table.php';
require_once TICKET_STAND_PLUGIN_DIR . 'includes/database/tables/class-ticket-type-extras-table.php';

// Load migration classes
require_once TICKET_STAND_PLUGIN_DIR . 'includes/database/migrations/class-migration-base.php';
require_once TICKET_STAND_PLUGIN_DIR . 'includes/database/migrations/class-migration-manager.php';

// Register activation hook
register_activation_hook(__FILE__, array('TicketStand\Plugin_Activator', 'activate'));

/**
 * Initialize the plugin
 */
function init() {
    // Initialize database manager
    $db_manager = new Database\Database_Manager();
    
    // Register tables
    $events_table = new Database\Tables\Events_Table();
    $ticket_types_table = new Database\Tables\Ticket_Types_Table();
    $event_extras_table = new Database\Tables\Event_Extras_Table();
    $ticket_type_extras_table = new Database\Tables\Ticket_Type_Extras_Table();
    
    $db_manager->register_table($events_table);
    $db_manager->register_table($ticket_types_table);
    $db_manager->register_table($event_extras_table);
    $db_manager->register_table($ticket_type_extras_table);
}

// Initialize the plugin
add_action('plugins_loaded', 'TicketStand\init');
