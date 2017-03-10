<?php
/*
Plugin Name: Save My Dates Plugin
Description: Mark arbitrary dates via calendar to Options table
Version:     20160911
Author:      lordcase (Krisztian Dobo)
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: lordcase
Domain Path: /languages
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

global $smd_plugin_dir;
global $smd_text;
global $smd_dates;
global $smd_arrayOfValues;

$smd_table_name = $wpdb->prefix . "savemydates"; 	
$smd_plugin_dir = plugins_url() . "/save-my-dates";
$smd_text = "";
$smd_dates = save_my_dates_getDates( $smd_text );
$smd_arrayOfValues["marked_dates"] = $smd_dates;



function save_my_date_install() {
	global $wpdb;
	global $smd_table_name;

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $smd_table_name (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  date date NOT NULL,
	  text text,
	  PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	add_option( "jal_db_version", "1.0" );
}

register_activation_hook( __FILE__, 'save_my_date_install' );




//Create the Admin menu item

function save_my_date_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap" id="smd-datepicker">';
	echo '</div>';
}

function save_my_date_menu() {
	add_menu_page( 'Save My Dates options', 'My Dates', 'manage_options', 'save-my-dates', 'save_my_date_options' );
}

add_action( 'admin_menu', 'save_my_date_menu' );




/**
 * Enqueue the date picker plus the multi extension
 */
function enqueue_date_picker(){

	global $smd_plugin_dir;
	global $smd_arrayOfValues;
	
  wp_enqueue_script(
		'multipicker-js', 
			$smd_plugin_dir . '/jquery-ui.multidatespicker.js', 
			array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'mypicker-js'),
			time(),
			true
		);
  wp_enqueue_script(
		'mypicker-js', 
			$smd_plugin_dir . '/mypicker.js', 
			array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'),
			time(),
			true
		);

	wp_localize_script( 'mypicker-js', 'loc_vars', $smd_arrayOfValues );
	wp_enqueue_style( 'jquery-ui-datepicker' );
	wp_enqueue_style( 'dpstyle', 'http://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
	wp_enqueue_style( 'smd-stylesheet', $smd_plugin_dir . '/save-my-dates.css');
}

add_action( 'admin_enqueue_scripts', 'enqueue_date_picker' );



// Create ajax control

function save_my_dates_ajax_control() {
	global $wpdb; // this is how you get access to the database

	if ($_POST['operation'] == 'del' && isset($_POST['date'])) {
		echo save_my_dates_delDate(($_POST['date'])) === 1 ? 'Törölve!' : 'HIBA!!!';
	} elseif ($_POST['operation'] == 'ins' && isset($_POST['date'])) {
		echo save_my_dates_addDate(($_POST['date']))=== 1 ? 'Hozzáadva!' : 'HIBA!!!';;
	}

	wp_die(); // this is required to terminate immediately and return a proper response
}

add_action( 'wp_ajax_save_my_dates', 'save_my_dates_ajax_control' );


// Retrieve dates from db

function save_my_dates_getDates ($text = '') {
	global $wpdb;
	global $smd_table_name;
	
	$where = $text == "" ? "" : "WHERE text = $text";
	$results = $wpdb->get_results( "SELECT * FROM $smd_table_name $where", ARRAY_A);
	return $results;

}


// Delete dates from db

function save_my_dates_delDate ($date, $text = '') {
	global $wpdb;
	global $smd_table_name;
	
	$where = array('date' => $date);
//	if ( $text !== "" ) $where['text'] = $text;
	$result = $wpdb->delete( $smd_table_name, $where);
	return $result;
	
}



// Add new dates to db

function save_my_dates_addDate ($date, $text = '') {
	global $wpdb;
	global $smd_table_name;
	$result = $wpdb->insert( $smd_table_name, array('date'=>$date, 'text'=>$text) );
	return $result;
	
}


