<?php
/**
 * The core plugin class
 */
class CDG_Elements_Base {

	protected $loader;
	protected $plugin_name;
	protected $version;

	public function __construct() {
		$this->plugin_name = 'cdg-elements';
		$this->version = CDG_ELEMENTS_VERSION;
		
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies() {
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-cdg-loader.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-cdg-i18n.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-cdg-admin.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-cdg-admin-ajax.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-cdg-meta-box.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-cdg-frontend.php';
		
		$this->loader = new CDG_Elements_Loader();
	}

	private function set_locale() {
		$plugin_i18n = new CDG_Elements_i18n();
		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	private function define_admin_hooks() {
		$plugin_admin = new CDG_Elements_Admin($this->get_plugin_name(), $this->get_version());
		$plugin_ajax = new CDG_Elements_Admin_Ajax();
		$plugin_meta_box = new CDG_Elements_Meta_Box();

		// Admin hooks
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		$this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
	}

	private function define_public_hooks() {
		$plugin_public = new CDG_Elements_Frontend($this->get_plugin_name(), $this->get_version());
		$plugin_public->initialize();
	}

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_version() {
		return $this->version;
	}
}