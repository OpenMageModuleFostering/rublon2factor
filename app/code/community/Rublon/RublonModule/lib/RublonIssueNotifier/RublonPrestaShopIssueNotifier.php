<?php
/**
 * 2013 Rublon
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Rublon to newer
 * versions in the future. If you wish to customize Rublon for your
 * needs please contact us at support@rublon.com for more information.
 *
 *  @author    Rublon <support@rublon.com>
 *  @copyright 2013 Adips
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  Property of Adips Sp. z o. o., Poland
 */

require_once 'RublonIssueNotifier.php';

class RublonPrestaShopIssueNotifier extends RublonIssueNotifier {


	/**
	 * Add Magento specific variables into issue information
	 * 
	 * @see RublonIssueNotifier::formatData()
	 */
	protected function formatData($issue, $options) {
		
		$data = parent::formatData($issue, $options);
		
		if (function_exists('ini_get_all')) {
			@ $data['context']['php']['ini'] = ini_get_all(null, false);
		}
		if (function_exists('get_loaded_extensions')) {
			@ $data['context']['php']['loaded_extensions'] = get_loaded_extensions();
		}
		
		if (empty($data['profile_id'])) {
			$data['profile_id'] = RublonHelper::getRublonProfileId();
		}
		
		$data['context']['_SERVER'] = $_SERVER;
		$data['context']['_POST'] = $_POST;
		$data['context']['_GET'] = $_GET;
		$data['context']['_COOKIE'] = $_COOKIE;
		
		return $data;
		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see RublonIssueNotifier::getDomain()
	 */
	protected function getDomain() {

		return RublonHelper::getAPIDomain();

	}
	
	/**
	 * (non-PHPdoc)
	 * @see RublonIssueNotifier::getTechnology()
	 */
	protected function getTechnology() {

		return RublonHelper::getTechnology();

	}
	
	/**
	 * (non-PHPdoc)
	 * @see RublonIssueNotifier::sendByBrowser()
	 */
	protected function sendByBrowser(array $options) {

		$content = $this->getBrowserIssueForm($options);
		$rublon = Module::getInstanceByName('rublon');
		$rublon->addJSNotification($content);
		return true;

	}
	
	/**
	 * (non-PHPdoc)
	 * @see RublonIssueNotifier::getCurrentUrl()
	 */
	protected function getCurrentUrl() {

		return RublonHelper::getCurrentURL();

	}
	
}
