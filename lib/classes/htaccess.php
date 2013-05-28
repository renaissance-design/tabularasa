<?php

/**
 * Sets up the .htaccess file, adding custom rules including the Perishable Press 5G Firewall.
 * 
 */
class TabulaRasa_htaccess {

	public $slug;

	public function __construct($slug) {

		$this->slug = $slug;

		add_action('admin_init', array(&$this, 'htaccess_write_check'));

		add_action('generate_rewrite_rules', array(&$this, 'htaccess_add_rules'));
	}

	/**
	 * Checks if the .htaccess file is writable. If not, queues an error if the current user is admin.
	 * 
	 * @return void
	 */
	function htaccess_write_check() {
		if (!is_writable(get_home_path() . '.htaccess')) {
			if (current_user_can('manage_options')) {
				add_action('admin_notices', array(&$this, 'htaccess_writable_error'));
			}
		};
	}

	/**
	 * Displays an error notification if the .htaccess file isn't writable
	 * 
	 * @return void
	 */
	function htaccess_writable_error() {
		echo '<div class="error"><p>' . sprintf(__('Please make sure your <a href="%s">.htaccess</a> file is writable ', $this->slug), admin_url('options-permalink.php')) . '</p></div>';
	}

	/**
	 * Adds extra goodness whenever WP writes to .htaccess
	 * 
	 * @global object $wp_rewrite
	 * @param string $content
	 * @return boolean
	 */
	function htaccess_add_rules($content) {
		global $wp_rewrite;
		$home_path = function_exists('get_home_path') ? get_home_path() : ABSPATH;
		$htaccess_file = $home_path . '.htaccess';
		$mod_rewrite_enabled = function_exists('got_mod_rewrite') ? got_mod_rewrite() : false;
		$firewall_rules = extract_from_markers($htaccess_file, 'Tabula Rasa');
		if ($firewall_rules === array()) {
			$filename = dirname(__FILE__) . '/TR-htaccess';
			return $this->prepend_with_markers($htaccess_file, 'Tabula Rasa', extract_from_markers($filename, 'Tabula Rasa'));
		}
		return $content;
	}

	/**
	 * Inserts an array of strings at the beginning of a file (.htaccess ), placing it between
	 * BEGIN and END markers. Replaces existing marked info. Retains surrounding
	 * data. Creates file if none exists.
	 * 
	 * @param string $filename
	 * @param string $marker
	 * @param array $insertion
	 * @return boolean
	 */
	function prepend_with_markers($filename, $marker, $insertion) {
		if (!file_exists($filename) || is_writeable($filename)) {
			if (!file_exists($filename)) {
				$markerdata = '';
			} else {
				$markerdata = explode("\n", implode('', file($filename)));
			}

			if (!$f = @fopen($filename, 'c+'))
				return false;
			$foundit = false;
			if (!$foundit) {
				fwrite($f, "\n# BEGIN {$marker}\n");
				foreach ($insertion as $insertline)
					fwrite($f, "{$insertline}\n");
				fwrite($f, "# END {$marker}\n");
			}
			if ($markerdata) {
				$state = true;
				foreach ($markerdata as $n => $markerline) {
					if (strpos($markerline, '# BEGIN ' . $marker) !== false)
						$state = false;
					if ($state) {
						if ($n + 1 < count($markerdata))
							fwrite($f, "{$markerline}\n");
						else
							fwrite($f, "{$markerline}");
					}
					if (strpos($markerline, '# END ' . $marker) !== false) {
						fwrite($f, "# BEGIN {$marker}\n");
						if (is_array($insertion))
							foreach ($insertion as $insertline)
								fwrite($f, "{$insertline}\n");
						fwrite($f, "# END {$marker}\n");
						$state = true;
						$foundit = true;
					}
				}
			}

			fclose($f);
			return true;
		} else {
			return false;
		}
	}

}