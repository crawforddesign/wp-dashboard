<?php
/**
 * Handle database migrations
 */
class CDG_Elements_Migrations {
	private $current_version;
	private $installed_version;

	public function __construct() {
		$this->current_version = CDG_ELEMENTS_VERSION;
		$this->installed_version = get_option('cdg_elements_version', '0.0.0');
	}

	/**
	 * Run migrations if necessary
	 */
	public function run_migrations() {
		if (version_compare($this->installed_version, $this->current_version, '<')) {
			$this->migrate();
		}
	}

	/**
	 * Perform migration steps
	 */
	private function migrate() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		// Migration steps based on version
		if (version_compare($this->installed_version, '1.0.0', '<')) {
			$this->migrate_to_1_0_0($wpdb, $charset_collate);
		}

		// Update version in database
		update_option('cdg_elements_version', $this->current_version);
	}

	/**
	 * Migration to version 1.0.0
	 */
	private function migrate_to_1_0_0($wpdb, $charset_collate) {
		$table_name = $wpdb->prefix . 'cdg_elements';
		
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			post_id bigint(20) NOT NULL,
			element_text text NOT NULL,
			font_family varchar(100) NOT NULL,
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

		dbDelta($sql);
	}
}