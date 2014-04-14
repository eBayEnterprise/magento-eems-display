<?php
/**
 * @category  TrueAction
 * @package   TrueAction_Fetchback
 * @copyright Copyright (c) 2012 True Action Network (http://www.trueaction.com)
 */

class TrueAction_Fetchback_Test_Model_Products extends EcomDev_PHPUnit_Test_Case
{
	/**
	 * makes sure that duplicate products aren't pulled from multiple stores
	 */
	public function collectionUniqueness()
	{
		// TODO: verify that we're not getting duplicate records because of
		// multiple store views.
		$products = Mage::getModel('fetchback/products');
	}
}
