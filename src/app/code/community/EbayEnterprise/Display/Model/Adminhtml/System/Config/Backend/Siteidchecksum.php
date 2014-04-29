<?php
/**
 * A class that checks that the entered md5 Site Id Checksum mathes the one we generate.
 */
class EbayEnterprise_Display_Model_Adminhtml_System_Config_Backend_Siteidchecksum
	extends Mage_Core_Model_Config_Data
{
	const SESSION_KEY  = 'adminhtml/session';
	const CONTACT_INFO = 'Contact dl-ebayent-displaysupport@ebay.com, or call (888) 343-6411 ext. 4 to obtain your Site Id and Site Id Checksum';
	const FIELD_SEP    = ':';
	/**
	 * Send only the Checksum part of the Site Id Checksum Field.
	 * @return self
	 */
	public function _afterLoad()
	{
		parent::_afterLoad();
		list($justTheHash,) = Mage::helper('eems_display')->splitSiteIdChecksumField($this->getOldValue());
		$this->setValue($justTheHash);
		return $this;
	}
	/**
	 * Checks to see if we have a new and valid Site Id Checksum Entered
	 * @return self
	 */
	public function _beforeSave()
	{
		$helper   = Mage::helper('eems_display'); // We need this helper several times herein

		$newChecksum = $this->getValue();
		list($oldHash,$oldSite) = $helper->splitSiteIdChecksumField($this->getOldValue());
		if (empty($newChecksum) && empty($oldHash)) {
			// If both old and new checksums are still empty, prompt with some help info.
			$this->_dataSaveAllowed = false;
			Mage::getSingleton($this::SESSION_KEY)
				->addWarning('Please note that tracking is not enabled. Site Id Checksum is empty. ' . self::CONTACT_INFO);
			return $this;
		}
		$storeId  = $helper->getStoreIdForCurrentAdminScope();
		$siteId   = Mage::helper('eems_display/config')->getSiteId($storeId);

		// Not allowed to change the Checksum unless we previously had a hash and we've changed Site Ids
		if (!empty($oldHash) && $oldSite === $siteId) {
			$this->_dataSaveAllowed = false;
			return $this;
		}
		// Check that the value provided in newCheckSum matches what we calculate for ourHash.
		$url = parse_url($helper->getProductFeedUrl($storeId), PHP_URL_HOST);
		$ourHash = md5($siteId . $url);
		if ($ourHash === $newChecksum) {
			// Upon success, we save the hash and the siteId. In the frontend at runtime,
			// we just have make sure that the siteId matches the runtime siteId
			$this->setValue($newChecksum . self::FIELD_SEP . $siteId);
			parent::_beforeSave();
		} else {
			$this->setValue(self::FIELD_SEP);
			Mage::getSingleton($this::SESSION_KEY)
				->addError('Failed to validate the Site Id. ' . self::CONTACT_INFO);
		}
		return $this;
	}
}
