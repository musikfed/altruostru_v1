<?php
/*
Plugin Name: GoSMTP
Plugin URI: https://gosmtp.net
Description: Send emails from your WordPress site using your preferred SMTP provider like Gmail, Outlook, AWS, Zoho, SMTP.com, Sendinblue, Mailgun, Postmark, Sendgrid, Sparkpost, Sendlayer or any custom SMTP provider.
Version: 1.0.3
Author: Softaculous Team
Author URI: https://softaculous.com
Text Domain: gosmtp
*/

// We need the ABSPATH
if (!defined('ABSPATH')) exit;

if(!function_exists('add_action')){
	echo 'You are not allowed to access this page directly.';
	exit;
}

$_tmp_plugins = get_option('active_plugins');

// Is the premium plugin loaded ?
if(in_array('gosmtp-pro/gosmtp-pro.php', $_tmp_plugins)){
	return;
}

// If GOSMTP_VERSION exists then the plugin is loaded already !
if(defined('GOSMTP_VERSION')) {
	return;
}

define('GOSMTP_FILE', __FILE__);

include_once(dirname(__FILE__).'/init.php');
