<?php
/**
 * Fired during plugin activation.
 */
class CDG_Elements_Activator {
	 /**
	  * Initialize plugin default settings and database structure.
	  */
	 public static function activate() {
		 global $wpdb;
		 $charset_collate = $wpdb->get_charset_collate();
		 $table_name = $wpdb->prefix . 'cdg_elements';
 
		 // Drop the existing table
		 $wpdb->query("DROP TABLE IF EXISTS $table_name");
 
		 // Create the table with the new structure
		 $sql = "CREATE TABLE $table_name (
			 id mediumint(9) NOT NULL AUTO_INCREMENT,
			 post_id bigint(20) NOT NULL,
			 element_text text NOT NULL,
			 font_family varchar(100) NOT NULL,
			 font_url text DEFAULT NULL,
			 color varchar(20) NOT NULL,
			 size varchar(20) NOT NULL,
			 rotation smallint(6) NOT NULL,
			 position_x int(11) NOT NULL,
			 position_y int(11) NOT NULL,
			 is_active tinyint(1) DEFAULT 0,
			 created_at datetime DEFAULT CURRENT_TIMESTAMP,
			 updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			 PRIMARY KEY  (id),
			 KEY post_id (post_id),
			 KEY is_active (is_active)
		 ) $charset_collate;";
 
		 require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		 dbDelta($sql);
 
		 // Reset the version in options
		 delete_option('cdg_elements_db_version');
		 add_option('cdg_elements_db_version', CDG_ELEMENTS_VERSION);
 
		 // Clear any existing rewrite rules
		 flush_rewrite_rules();
	 }
 }