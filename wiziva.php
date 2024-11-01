<?php

/*
	Plugin Name: Wiziva
	Plugin URI: http://www.wiziva.com
	Description: Wiziva is a packing and installation platform for WordPress. Within the Wiziva Dashboard you can manage a portfolio of your favorite plugins and themes and easily install them in bulk. Optionally you can put all that in the cloud with a Wiziva.com account and access your portfolio from every WordPress installation you may have. It is also the Wiziva.com core plugin, that makes the connection between the wordpress installation and Wiziva wizard engine.
	Version: 1.0.0
	Author: Stanil Dobrev
	Author URI: http://www.wiziva.com
    Copyright 2014 wiziva.com (email : support@wiziva.com)
*/


// get wordpress version number and fill it up to 9 digits
$int_wp_version = preg_replace('#[^0-9]#', '', get_bloginfo('version'));
while(strlen($int_wp_version) < 9) $int_wp_version .= '0'; 

// get php version number and fill it up to 9 digits
$int_php_version = preg_replace('#[^0-9]#', '', phpversion());
while(strlen($int_php_version) < 9) $int_php_version .= '0'; 

if ($int_wp_version >= 300000000 && 		// Wordpress version > 3.0
	$int_php_version >= 520000000 && 		// PHP version > 5.2
	defined('ABSPATH') && 					// Plugin is not loaded directly
	defined('WPINC')) {						// Plugin is not loaded directly
	define('Wiziva_DIR', dirname(__FILE__));
	define('Wiziva_URL', plugins_url('/', __FILE__));
	define('Wiziva_PLUGIN_NAME' , 'Wiziva');
	define('Wiziva_PLUGIN_SLUG' , 'wiziva');
	define('Wiziva_PLUGIN_VERSION' , '1.0.0');
	require_once(dirname(__FILE__).'/class.main.php');
	$wizivaplugin = new WizivaPlugin();
}
else add_action('admin_notices', 'wiziva_incomp');

function wiziva_incomp(){
	echo '<div id="message" class="error">
	<p><b>The "Wiziva" Plugin does not work on this WordPress installation!</b></p>
	<p>Please check your WordPress installation for following minimum requirements:</p>
	<p>
	- WordPress version 3.0 or higer<br />
	- PHP version 5.2 or higher<br />
	</p>
	<p>Do you need help? Contact <a href="mailto:support@wiziva.com">Support</a></p>
	</div>';
}

register_activation_hook(__FILE__, 'wizivaInstall');

function wizivaInstall() {
	global $wpdb;
    $wpdb->query("
		CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wiziva_plugins` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `ptype` enum('plugin','theme','pack') NOT NULL DEFAULT 'plugin',
		  `reposurl` varchar(255) NOT NULL,
		  `title` varchar(100) NOT NULL,
		  `kw` varchar(50) NOT NULL,
		  `description` text NOT NULL,
		  `version` varchar(15) NOT NULL,
		  `author` varchar(50) NOT NULL,
		  `aurl` varchar(255) NOT NULL,
		  `tags` text NOT NULL,
		  `url` varchar(255) NOT NULL,
		  `license` varchar(255) NOT NULL,
		  `lurl` varchar(255) NOT NULL,
		  `downloadurl` varchar(255) NOT NULL,
		  `lastupdate` int(10) unsigned NOT NULL,
		  `tadded` int(10) unsigned NOT NULL,
		  `groups` text NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `ptype` (`ptype`)
		);
	");
    $wpdb->query("
		CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wiziva_groups` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `title` varchar(30) NOT NULL,
		  `tadded` int(10) unsigned NOT NULL,
		  PRIMARY KEY (`id`)
		);
	");
}

?>