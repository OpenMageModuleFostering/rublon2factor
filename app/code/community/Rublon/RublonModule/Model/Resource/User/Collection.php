<?php
/**
 * Rublon admin user resource collection
 *
 * @package   rublon/rublon2factor
 * @author     Adips Sp. z o.o.
 * @copyright  Adips Sp. z o.o.
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

/**
 * Rublon administrator resource collection class
 */
class Rublon_Rublon2Factor_Model_Resource_User_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
	/**
	 * Default constructor
	 */
	protected function _construct()
	{
		$this->_init('rublon2factor/user');
	}
}
 

?>