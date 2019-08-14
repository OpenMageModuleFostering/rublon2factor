<?php
/**
 * Rublon customer resource model
 *
 * @package   rublon/rublonmodule
 * @author     Adips Sp. z o.o.
 * @copyright  Adips Sp. z o.o.
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

/**
 * Rublon administrator resource model class
 */
class Rublon_RublonModule_Model_Resource_User extends Mage_Core_Model_Resource_Db_Abstract
{
	/**
	 * Default constructor
	 *
	 */
	protected function _construct()
	{
		//Set table and primary key
		$this->_init('rublon2factor/rublon_user', 'user_id');
		
		//Set primary key as not auto increment - is is also foreign key to admin/user table
		$this->_isPkAutoIncrement = false;
	}
}
 

?>