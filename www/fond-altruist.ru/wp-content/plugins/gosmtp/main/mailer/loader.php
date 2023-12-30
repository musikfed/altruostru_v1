<?php

namespace GOSMTP\Mailer;

class Loader{
	
	var $options;
	var $mailer = '';
	var $url = '';
	var $conn_id = 0;
	var $parent_log = 0;
	var $last_log = 0;
	var $headers = array();
	
	public function __construct(){
		
		// Load options
		$this->loadOptions();
		
	}
	
	public function loadOptions(){
		$options = get_option('gosmtp_options', array());
		
		$this->options = $options;
	}
	
	public function getMailerOption(){
		
		$mailer = $this->mailer;
		
		if(empty($mailer) || !isset($this->options['mailer'][$this->conn_id])){
			return array();
		}
		
		return $this->options['mailer'][$this->conn_id];
	}
	
	public function getActiveMailer(){
		
		if(!isset($this->options['mailer'][$this->conn_id]) || !isset($this->options['mailer'][$this->conn_id]['mail_type'])){
			return 'mail';
		}
		
		return $this->options['mailer'][$this->conn_id]['mail_type'];
	}
	
	public function getOption($key, $mailer = '', $default = ''){
		
		$options = $this->options;
		
		if(!empty($mailer) && $mailer == $this->getActiveMailer()){
			$options = $this->options['mailer'][$this->conn_id];
		}
		
		if(isset($options[$key])){
			return $options[$key];
		}
		
		return $default;	
	}
	
	public function save_options($options){
		
		if(!method_exists($this, 'load_field')){
			return $options;
		}
		
		$fields = $this->load_field();
		
		foreach($fields as $key => $field){
			
			$val = '';
			
			if(!empty($_REQUEST[$this->mailer]) && isset($_REQUEST[$this->mailer][$key])){
				$val = sanitize_text_field($_REQUEST[$this->mailer][$key]);
			}
			
			$options[$key] = $val;
		}
		
		return $options;	
	}
	
	public function delete_option($key, $mailer = ''){

		if(!empty($mailer) && isset($this->options['mailer'][$this->conn_id][$key])){
			unset($this->options['mailer'][$this->conn_id][$key]);
		}elseif(isset($this->options[$key])){
			unset($this->options[$key]);
		}

		update_option( 'gosmtp_options', $this->options );
	}
	
	public function update_option($key, $val, $mailer=''){
		
		if(!empty($mailer)){
			
			if(!is_array($this->options['mailer'][$this->conn_id])){
				$this->options['mailer'][$this->conn_id] = array();
			}
			
			$this->options['mailer'][$this->conn_id][$key] = $val;
			
		}else{
			$this->options[$key] = $val;
		}
		
		update_option( 'gosmtp_options', $this->options);
	}
	
	protected function filterRecipientsArray($args){
		$recipients = [];

		foreach($args as $key => $recip){
			
			$recip = array_filter($recip);

			if(empty($recip) || ! filter_var( $recip[0], FILTER_VALIDATE_EMAIL ) ){
				continue;
			}

			$recipients[$key] = array(
				'address' => $recip[0]
			);

			if(!empty($recip[1])){
				$recipients[$key]['name'] = $recip[1];
			}
		}

		return $recipients;
	}

	public function setHeaders($headers){

		foreach($headers as $header){
			$name = isset($header[0]) ? $header[0] : false;
			$value = isset($header[1]) ? $header[1] : false;

			if(empty($name) || empty($value)){
				continue;
			}

			$this->setHeader($name, $val);
		}
		
	}

	public function setHeader($name, $val){
		
		$name = sanitize_text_field($name);
		
		$this->headers[$name] = WP::sanitize_value($val);
		
	}

	protected function getDefaultParams(){
		$timeout = (int)ini_get('max_execution_time');

		return [
			'timeout'     => $timeout ?: 30,
			'httpversion' => '1.1',
			'blocking'    => true,
		];
	}

	public function set_from($from = ''){
		global $phpmailer, $gosmtp;
		
		$conn_id = $gosmtp->mailer->conn_id;

		// Check for force set
		if($conn_id === 0){
			$options = $this->options;
		}else{
			$options = $this->options['mailer'][$conn_id];
		}	
		
		if(!empty($options['force_from_email']) && !empty($options['from_email'])){
		    $phpmailer->From = $options['from_email'];
			$from = $phpmailer->From;
		}
		
		if(!empty($options['force_from_name']) && !empty($options['from_name'])){
		    $phpmailer->FromName = $options['from_name'];
		}
		
		return $from;
	}
	
	public function handle_response($response){
		
		$status = false;
		$message = array();

		if(is_wp_error($response)){

			$code = $response->get_error_code();

			if(!is_numeric($code)) {
				$code = 400;
			}

			$msg = $response->get_error_message();

			$message = array(
				'code'    => $code,
				'message' => $msg
			);
			
			$this->process_response($message, $status);
			
			throw new \PHPMailer\PHPMailer\Exception($msg, $code);
			
			return;
			
		}elseif($response['status'] == true){
			
			unset($response['status']);
			
			$message = $response;
			$status = true;
		
		}else{
			$message = array(
				'code'    => $code,
				'message' => __('Unable to send mail, Please check your SMTP details')
			);
		}
		
		return $this->process_response($message, $status);
		
	}
	
	public function get_mailer_source(){
		
		$result = [];
		$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );

		if(empty($backtrace)){
			return false;
		}

		foreach( $backtrace as $i => $item ){
			if( $item['function'] == 'wp_mail' ) {
				$result[] = $backtrace[$i];
			}
		}
		
		if(!isset($result[0]['file'])){
			return false;
		}
		
		return $this->get_plugin_name($result[0]['file']);
	}

	public function process_response($message, $status){
		global $phpmailer, $gosmtp;
		
		if(empty($gosmtp->options['logs']['enable_logs']) || !class_exists('\GOSMTP\Logger')){
			return $status;
		}
		
		$logger = new \GOSMTP\Logger();
		
		$source = $this->get_mailer_source();
		
		if(empty($source)){
			$source = __('NA');
		}
		
		$headers = array(
			'Reply-To' => $phpmailer->getReplyToAddresses(),
			'Cc' => $phpmailer->getCcAddresses(),
			'Bcc' => $phpmailer->getBccAddresses(),
			'Content-Type' => $phpmailer->ContentType,
		);
        
		$attachments = $phpmailer->getAttachments();

		if(!empty($gosmtp->options['logs']['log_attachments'])){
			
			$uploads_dir = wp_upload_dir();
			$path = $uploads_dir['basedir'].'/gosmtp-attachments';
			
			if( !file_exists($path) ){
				mkdir($path);
			}

			if(!file_exists($path.'/index.html')){
				file_put_contents($path.'/index.html', '');
			}

			if( count($attachments) > 0 ){

				foreach( $attachments as $key => $file ){
					$name = $file[2];
					$location = $path.'/'.$name;
		
					if(file_exists($file[0])){
						// TODO check the copy function use correct
						if(copy($file[0], $location)){
						    $file[0] = $location;
						}	
					}
					
					$attachments[$key] = $file;
				}
			}
		}
		
		$data = array(
			'site_id' => get_current_blog_id(),
			'to' => maybe_serialize($phpmailer->getToAddresses()),
			'message_id' => $this->RandomString(16),
			'from' => $phpmailer->From,
			'subject' => $phpmailer->Subject,
			'body' => $phpmailer->Body,
			'attachments' => maybe_serialize( $attachments ),
			'status' => $status ? 'sent' : 'failed',
			'response' => maybe_serialize($message),
			'headers' => maybe_serialize($headers),
			'provider' => $this->mailer,
			'source' => $source,
			'created_at' => current_time( 'mysql' )
		);
		
		if($gosmtp->mailer->conn_id !== 0 && !empty($gosmtp->mailer->parent_log)){
			$data['parent_id'] = $gosmtp->mailer->parent_log;
			$data['source'] = __('GoSMTP Pro');
		}

		if(isset($_POST['gostmp_id'])){
			$id = (int)gosmtp_optpost('gostmp_id');
			$result = $logger->get_logs('records', $id);
			$operation = isset($_POST['operation']) ? gosmtp_optpost('operation') : false;
			
			if(!empty($operation) && !empty($result)){
				
				if($operation == 'resend'){
					$data['resent_count'] = $result[0]->resent_count + 1;
				}else{
					$data['retries'] = $result[0]->retries + 1;
				}
				
				$logger->update_logs($data, $id);
			}
		}else{
			$gosmtp->mailer->last_log = $logger->add_logs($data);
		}

		return $status;
	}

	public function message_formatting($msg, $key = '', $desc = ''){

		$message = '';

		if(!empty($key)){
			$message .= $key.': ';
		}

		if(is_string($msg)){
			$message .= $msg;
		}else{
			$message .= wp_json_encode($msg);
		}

		if(!empty($desc)){
			$message .= PHP_EOL .$desc;
		}

		return $message;
	}
	
	public function get_response_error_message($response){

		if(is_wp_error($response)){
			return '';
		}

		$body = wp_remote_retrieve_body( $response );
		$message = wp_remote_retrieve_response_message( $response );
		$code = wp_remote_retrieve_response_code( $response );
		$desc = '';

		if(!empty($body)){
			$desc = is_string($body) ? $body : wp_json_encode($body);
		}

		return $this->message_formatting( $message, $code, $desc );
	}
		
	// Generate a random string
	public function RandomString($length = 10){
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for($i = 0; $i < $length; $i++){
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	public function get_plugin_name($file_path = ''){

		if( empty( $file_path ) ){
			return false;
		}
		
		if(!function_exists( 'get_plugins')){
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}
		
		$plugins = get_plugins();
		$content_dir = basename( WP_PLUGIN_DIR );
		$separator = defined( 'DIRECTORY_SEPARATOR' ) ? '\\' . DIRECTORY_SEPARATOR : '\/';
		
		preg_match( "/$separator$content_dir$separator(.[^$separator]+)($separator|\.php)/", $file_path , $match );
		
		if(empty($plugins) || empty($match[1])){
			return false;
		}
		
		$slug = $match[1];

		foreach( $plugins as $plugin => $data ){
			if( preg_match( "/^$slug(\/|\.php)/", $plugin ) === 1 && isset( $data['Name'] )) {
				return $data['Name'];
			}
		}
		
		return false;
	}

	public function get_backup_connection(){
		
		// Is Primary email?
		if($this->conn_id !== 0 || empty($this->options['mailer'][0]['backup_connection'])){
			return false;
		}
		
		return $this->options['mailer'][0]['backup_connection'];
	}
	
	
}
