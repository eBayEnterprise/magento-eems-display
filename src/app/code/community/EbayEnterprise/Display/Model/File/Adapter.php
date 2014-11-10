<?php
/**
 * Copyright (c) 2014 eBay Enterprise, Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the eBay Enterprise
 * Magento Extensions End User License Agreement
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://www.ebayenterprise.com/files/pdf/Magento_Connect_Extensions_EULA_050714.pdf
 *
 * @copyright   Copyright (c) 2014 eBay Enterprise, Inc. (http://www.ebayenterprise.com/)
 * @license     http://www.ebayenterprise.com/files/pdf/Magento_Connect_Extensions_EULA_050714.pdf  eBay Enterprise Magento Extensions End User License Agreement
 *
 */

/**
 * File Adapter model
 *
 * @method string getCsvFieldDelimiter()
 * @method EbayEnterprise_Display_Model_File_Adapter setCsvFieldDelimiter(string $value)
 * @method string getCsvFieldEnclosure()
 * @method EbayEnterprise_Display_Model_File_Adapter setCsvFieldEnclosure(string $value)
 */

/**
 * Ignoring code coverage for this class because it is mainly responsible for creating new CSV files, adding new rows to CSV files
 * and closing CSV files.
 * @codeCoverageIgnore
 */
class EbayEnterprise_Display_Model_File_Adapter extends Varien_Object
{
	/** @var resource $_stream */
	protected $_stream;
	/**
	 * Create CSV files
	 * @param  string $file the csv file to create
	 * @return self
	 * @throws EbayEnterprise_Display_Model_File_Exception
	 */
	public function openCsvFile($file)
	{
		$this->_stream = fopen($file, 'w');
		if ($this->_stream === false) {
			throw Mage::exception('EbayEnterprise_Display_Model_File', 'Cannot open file for writing in ' . dirname($file) . '.');
		}
		return $this;
	}
	/**
	 * Writes an array as a string into the resource stream
	 * @param  array $dataRow array of values to be written
	 * @return self
	 */
	public function addNewCsvRow(array $dataRow)
	{
		if ($this->_stream) {
			fputcsv($this->_stream, $dataRow, $this->getCsvFieldDelimiter(), $this->getCsvFieldEnclosure());
		}
		return $this;
	}
	/**
	 * Close CSV files
	 * @return self
	 */
	public function closeCsvFile()
	{
		if ($this->_stream) {
			fclose($this->_stream);
		}
		return $this;
	}
}
