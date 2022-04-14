<?php
/**
 * Plugin Name: keima | Event manager
 * Description:  Control restricted page for events.
 * Version: 1.0.1
 * Plugin URI:
 * Author: keima.co
 * Author URI: https://www.keima.co/
 * Text Domain: keima-event-manager
 * Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

define( 'KEIMA_EVENT_MANAGER_FILE', __FILE__ );
define( 'KEIMA_EVENT_MANAGER_DIR', plugin_dir_path( __FILE__ ) );
define( 'KEIMA_EVENT_MANAGER_VER', '1.0.1' );

if ( ! class_exists( 'KEIMA_EVNET_MANAGER' ) ) :

  class KEIMA_EVNET_MANAGER {

    function __construct() {
      // Do nothing.
    }

    function initialize() {

      add_action( 'plugins_loaded', function () {
        load_plugin_textdomain( 'keima-event-manager', false, 'keima-event-manager/languages/' );
      });

      if ( ! function_exists( 'get_plugins' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
      }
      if (
        array_key_exists( 'advanced-custom-fields/acf.php', get_plugins() )
        && is_plugin_active( 'advanced-custom-fields/acf.php' )
      ) {
        $this->activation();
      } else {
        $this->deactivation();
      }
    }

    function activation () {
      add_action( 'init', array( $this, 'register_post_types' ), 5 );

      include_once KEIMA_EVENT_MANAGER_DIR . 'includes/kem-generate-fields-of-cpt.php';
      include_once KEIMA_EVENT_MANAGER_DIR . 'includes/kem-generate-fields-of-page.php';
      include_once KEIMA_EVENT_MANAGER_DIR . 'includes/kem-generate-fields-of-user.php';
      include_once KEIMA_EVENT_MANAGER_DIR . 'includes/kem-controller.php';
      include_once KEIMA_EVENT_MANAGER_DIR . 'includes/kem-shortcode.php';
      include_once KEIMA_EVENT_MANAGER_DIR . 'includes/kem-user-data-import.php';
      include_once KEIMA_EVENT_MANAGER_DIR . 'includes/kem-user-data-export.php';
    }

    function deactivation () {
      if ( ! array_key_exists( 'advanced-custom-fields/acf.php', get_plugins() ) ) {
        add_action( 'admin_notices', function () {
          echo "<div class=\"error notice\"><p>";
          echo __( 'This plugin needs "Advanced Custom Fields" to run. Please <a href="https://www.advancedcustomfields.com" target="_blank">download</a> and activate it before.', 'keima-event-manager' );
          echo "</p></div>";
        } );
      } else {
        if ( ! is_plugin_active( 'advanced-custom-fields/acf.php' ) ) {
          add_action( 'admin_notices', function () {
            echo "<div class=\"error notice\"><p>";
            echo __( 'This plugin needs "Advanced Custom Fields" to run. Please activate it before.', 'keima-event-manager' );
            echo "</p></div>";
          } );
        }
      }

      if ( isset( $_GET['activate'] ) )
        unset( $_GET['activate'] );

      deactivate_plugins( plugin_basename( __FILE__ ) );
    }

    function register_post_types () {
      register_post_type( 'keima_event_manager',
        array(
          'labels' => array(
            'name'          => __( 'Event Manager', 'keima-event-manager' ),
            'singular_name' => __( 'Event Manager', 'keima-event-manager' )
          ),
          'has_archive'     => false,
          'public'          => true,
          'rewrite'         => array('slug' => 'keima_event_manager', 'with_front' => false ),
          'show_in_rest'    => true,
        )
      );
    }
  }

  function keima_event_manager() {
    global $keima_event_manager;

    // Instantiate only once.
    if ( ! isset( $keima_event_manager ) ) {
      $keima_event_manager = new KEIMA_EVNET_MANAGER();
      $keima_event_manager->initialize();
    }
    return $keima_event_manager;
  }

  // Instantiate.
  keima_event_manager();

endif; // class_exists check
