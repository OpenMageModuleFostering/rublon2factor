<?php

/**
 * Rublon issue notification helper
 * 
 * Class has method to notify Rublon Team about issues and errors.
 * It utilize curl or file_get_contents requests on the server-side
 * or client-side requests from browser's JavaScript
 * if the server-side methods are not available.
 * 
 * Create a subclass to override detailed parameters.
 *
 */
class RublonIssueNotifier {
	
	/**
	 * Rublon API domain
	 *
	 * @var string
	 */
	const API_DOMAIN = 'https://code.rublon.com';
	
	
	/**
	 * Rublon API path
	 * 
	 * @var string
	 */
	const API_PATH = '/issue_notifier/module_notify';
	
	/**
	 * Default technology tag
	 * 
	 * @var string
	 */
	const DEFAULT_TECHNOLOGY = 'php-sdk';
	
	/**
	 * User agent header for request
	 * 
	 * @var string
	 */
	const USER_AGENT = 'RublonIssueNotifier';
	
	
	/**
	 * Notify Rublon Team about some issue
	 * 
	 * @param mixed $issue
	 * @param array $options
	 * @return boolean
	 */
	public function notify($issue, $options = array()) {
		return $this->send($this->formatData($issue, $options));
	}
	
	
	/**
	 * Get client-side notification HTML code
	 *
	 * @param array $options
	 * @return string
	 */
	public function getBrowserIssueForm($options) {
		return '<script type="text/javascript">
			(function() {
				var frame = document.createElement("iframe");
				frame.id = "RublonIssueFrame";
				frame.name = "RublonIssueFrame";
				frame.style.display = "none";
				document.body.appendChild(frame);
				var form = document.createElement("form");
				form.method = "POST";
				form.action = '. json_encode($this->getRequestURL()) .';
				form.id = "RublonIssueForm";
				form.target = "RublonIssueFrame";
				form.style.display = "none";
				document.body.appendChild(form);
				var content = document.createElement("textarea");
				content.name = "issue";
				content.value = '. json_encode(json_encode($options)) .';
				form.appendChild(content);
				form.submit();
			})();
			</script>';
	}
	


	/**
	 * Create issue information array
	 *
	 * @param mixed $issue
	 * @param array $options
	 * @return array
	 */
	protected function formatData($issue, $options) {
	
		if (!is_array($options)) {
			$options = array();
		}
		$data = array();
	
		if (is_object($issue) AND $issue instanceof Exception) { // Exception
			$data['description'] = $issue->getMessage();
			$options['exception']['code'] = $issue->getCode();
			$options['exception']['file'] = $issue->getFile();
			$options['exception']['line'] = $issue->getLine();
			if (method_exists($issue, 'getTraceAsString')) {
				$options['exception']['trace'] = $issue->getTraceAsString();
			} else {
				$options['exception']['string'] = (string)$issue;
			}
		}
		else if (!is_scalar($issue)) { // Object/array
			$data['description'] = print_r($issue, true);
		} else { // scalar (eg. text)
			$data['description'] = $issue;
		}
	
		// Rewrite some fields
		$rewrite = array('url' => 'url', 'method' => 'where', 'profile_id' => 'profile_id');
		foreach ($rewrite as $name => $new) {
			if (!empty($options[$name])) {
				if (!isset($data[$new])) {
					$data[$new] = $options[$name];
				}
			}
		}
	
		// Add technology to the "where" field
		if (!isset($data['where'])) {
			$data['where'] = $this->getTechnology();
		} else {
			$data['where'] = $this->getTechnology() . ' - '. $data['where'];
		}
		
		if (empty($data['url'])) {
			$data['url'] = $this->getCurrentUrl();
		}
		
		$data['context'] = $options;
		$data['ip_addr'] = $_SERVER['REMOTE_ADDR'];
	
		return $data;
	}
	
	
	/**
	 * Send notification
	 * 
	 * @param array $options
	 * @return boolean
	 */
	protected function send(array $options) {
		return ($this->sendByCurl($options)
			OR $this->sendByFileGetContents($options)
			OR $this->sendByBrowser($options)
		);
	}
	


	/**
	 * Returns module's technology tag
	 *
	 * @return string
	 */
	protected function getTechnology() {
		return self::DEFAULT_TECHNOLOGY;
	}
	
	
	/**
	 * Get API domain
	 *
	 * @return string
	 */
	protected function getDomain() {
		return self::API_DOMAIN;
	}
	
	
	/**
	 * Get URL address of the notification request
	 * 
	 * @return string
	 */
	protected function getRequestURL() {
		return $this->getDomain() . self::API_PATH;
	}
	
	/**
	 * Send notification by cURL
	 *
	 * @param array $options
	 * @return boolean
	 */
	protected function sendByCurl(array $options) {
		
		if (!function_exists('curl_init')) {
			return false;
		}
		
		$ch = curl_init($this->getRequestURL());
		$headers = array(
			"Content-Type: application/json; charset=utf-8",
			"Accept: application/json, text/javascript, */*; q=0.01",
			"X-Rublon-Technology: ". $this->getTechnology(),
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($options));
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		
		$response = curl_exec($ch);
		
		return $this->validateResponse($response);
		
	}
	
	
	/**
	 * Send notification by PHP-native file_get_contents function
	 *
	 * @param array $options
	 * @return boolean
	 */
	protected function sendByFileGetContents(array $options) {
		
		if (!function_exists('file_get_contents')
				OR !function_exists('stream_context_create')) {
			return false;
		}
		
		$opts = array(
			'http' => array(
				'method' => "PUT",
				'header' => "content-type: application/json; charset=UTF-8\n
				user-agent: ". self::USER_AGENT ."\n
				X-Rublon-Technology: ". $this->getTechnology(),
				'content' => json_encode($options),
				'timeout' => 10,
			),
		);
		
		$context = stream_context_create($opts);
		$response = @file_get_contents($this->getRequestURL(), false, $context);
		
		return $this->validateResponse($response);
		
	}
	
	
	/**
	 * Validate HTTP response from API server
	 * 
	 * @param mixed $response
	 * @return boolean
	 */
	protected function validateResponse($response) {
		if (is_scalar($response)) {
			$response = json_decode($response, true);
		} else {
			$response = (array)$response;
		}
		return (!empty($response)
			AND is_array($response)
			AND !empty($response['status'])
			AND $response['status'] == 'OK');
	}
	
	/**
	 * Send notification by browser output handler
	 * 
	 * Method not implemented - to override in subclass.
	 *
	 * @param array $options
	 * @return boolean
	 */
	protected function sendByBrowser(array $options) {
		return false;
	}
	
	
	/**
	 * Get current URL address
	 * 
	 * @return string|NULL
	 */
	protected function getCurrentUrl() {
		if (isset($_SERVER['SERVER_NAME']) AND isset($_SERVER['REQUEST_URI'])) {
			return $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		} else {
			return null;
		}
	}
	
	
}