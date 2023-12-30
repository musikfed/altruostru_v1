<?php
/*
* GoSMTP
* https://gosmtp.net
* (c) Softaculous Team
*/

if(!defined('GOSMTP_VERSION')){
	die('Hacking Attempt!');
}

// Load mailer list
function gosmtp_get_mailer_list(){
	
	$list = array(
		'mail' => [ 'title' => __('Mail'), 'class' => 'GOSMTP\Mailer\Mail'],
		'smtp' => [ 'title' => __('Other SMTP'), 'class' => 'GOSMTP\Mailer\SMTP'],
		'amazonses' => [ 'title' => __('AmazonSES'), 'class' => 'GOSMTP\Mailer\AmazonSES\AmazonSES'],
		'gmail' => [ 'title' => __('Gmail'), 'class' => 'GOSMTP\Mailer\Gmail\Gmail'],
		'outlook' => [ 'title' => __('Outlook'), 'class' => 'GOSMTP\Mailer\Outlook\Outlook'],
		'zoho' => [ 'title' => __('Zoho'), 'class' => 'GOSMTP\Mailer\Zoho'],
		'sendlayer' => [ 'title' => __('Sendlayer'), 'class' => 'GOSMTP\Mailer\Sendlayer'],
		'smtpcom' => [ 'title' => __('SMTPcom'), 'class' => 'GOSMTP\Mailer\SMTPcom'],
		'sendinblue' => [ 'title' => __('Sendinblue'), 'class' => 'GOSMTP\Mailer\Sendinblue'],
		'mailgun' => [ 'title' => __('Mailgun'), 'class' => 'GOSMTP\Mailer\Mailgun'],
		'postmark' => [ 'title' => __('Postmark'), 'class' => 'GOSMTP\Mailer\Postmark'],
		'sendgrid' => [ 'title' => __('Sendgrid'), 'class' => 'GOSMTP\Mailer\Sendgrid'],
		'sparkpost' => [ 'title' => __('Sparkpost'), 'class' => 'GOSMTP\Mailer\Sparkpost']
	);
	
	return apply_filters( 'gosmtp_get_mailer_list', $list );
}

// Load mailer list
function gosmtp_load_mailer_list(){
	
	$list = gosmtp_get_mailer_list();
	
	$gosmtpmailer = array();
	
	foreach($list as $key => $mailer){
		
		$class = $mailer['class'];
		
		if(!class_exists($class)){
			continue;
		}
		
		$gosmtpmailer[$key] = new $class();
	}
		
	return apply_filters( 'gosmtp_load_mailer_list', $gosmtpmailer );
}

function gosmtp_clean($var){
	
	if(is_array($var) || is_object($var)){
		return map_deep($var, 'sanitize_text_field');
	}
	
	if(is_scalar($var)){
		return sanitize_text_field($var);
	}

	return '';

}

// Check if a field is posted via GET else return default value
function gosmtp_optget($name, $default = ''){
	
	if(!empty($_GET[$name])){
		return gosmtp_clean($_GET[$name]);
	}
	
	return $default;	
}

// Check if a field is posted via POST else return default value
function gosmtp_optpost($name, $default = ''){
	
	if(!empty($_POST[$name])){
		return gosmtp_clean($_POST[$name]);
	}
	
	return $default;	
}

// Check if a field is posted via REQUEST else return default value
function gosmtp_optreq($name, $default = ''){
	
	if(!empty($_REQUEST[$name])){
		return gosmtp_clean($_REQUEST[$name]);
	}
	
	return $default;	
}

// Simply echo and dir
function gosmtp_json_output(&$done){
	echo json_encode($done);
	wp_die();
}

// Generate a random string
function gosmtp_RandomId($length = 10){
	$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
	$charactersLength = strlen($characters);
	$randomString = '';
	for($i = 0; $i < $length; $i++){
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}

// Show notice
function gosmtp_show_notices(){
	
	$options = get_option('gosmtp_options', array());
	
	if(defined('GOSMTP_PREMIUM') && empty($options['logs']['enable_logs'])){
		echo '<div class="error is-dismissible notice">
			<p>'.__('Email log is disabled. To store and view email logs, please enable email logs from GoSMTP ').' <a href="'.admin_url('admin.php?page=gosmtp#logs-settings').'">'.__('settings').'</a>.</p>
		</div>';
	}
	
	if(empty($options['mailer']) || empty($options['mailer'][0]) || $options['mailer'][0]['mail_type'] == 'mail'){
		echo '<div class="error is-dismissible notice">
			<p>'.__('It seems that you haven\'t configured GoSMTP mailer yet or it is set to default PHP. You need to setup mailer to send emails via SMTP. To setup the mailer settings click').' <a href="'.admin_url('admin.php?page=gosmtp#smtpsetting').'">'.__('here').'</a>.</p>
		</div>';
	}
	
	do_action('gosmtp_show_notices');
}
