<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_SalesOrderPlanning_ProductAvailabilityRangeController extends Mage_Adminhtml_Controller_Action
{
	
    /**
     * Affiche la liste
     *
     */
	public function ListAction()
    {
    	$this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Product availability ranges'));

        $this->renderLayout();
    }
    
    /**
     * Add a range
     *
     */
    public function AddRangeAction()
    {
    	mage::helper('SalesOrderPlanning/ProductAvailabilityRange')->newRange();
    		   	
    	//Confirm & redirect
		Mage::getSingleton('adminhtml/session')->addSuccess($this->__('New range added'));	
		$this->_redirect('SalesOrderPlanning/ProductAvailabilityRange/List');
    }
    
    /**
     * Save datas
     *
     */
    public function SaveAction()
    {   	
    	//retrieve and remove items
    	$config = $this->getRequest()->getPost('config');
		
		try
		{
		
			$targetConfig = array();
			for ($i=0;$i<count($config);$i++)
			{
				//delete if required
				if (!isset($config[$i]['delete']))
				{
					//save picture
					try
					{
						$uploader = new Varien_File_Uploader('image_'.$i);
						$uploader->setAllowedExtensions(array('gif'));
						$path = mage::helper('SalesOrderPlanning/ProductAvailabilityRange')->getImageDirectory();
						$uploader->save($path);		
						if ($uploadFile = $uploader->getUploadedFileName()) 
						{
							//rename
							$picturePath = $path.$uploadFile;			
							$targetPath = $path.$i.'.gif';
							if (file_exists($targetPath))
								unlink($targetPath);
							rename($picturePath, $targetPath);
						}
					}
					catch(Exception $ex)
					{
						//nothing
					}
					$targetConfig[] = $config[$i];
				}

					
			}

			//save
			mage::helper('SalesOrderPlanning/ProductAvailabilityRange')->saveConfig($targetConfig);
			
			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Data saved'));	
		}
		catch(Exception $ex)
		{
			Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured : %s', $ex->getMessage()));			
		}
    	
    	//redirect
		$this->_redirect('SalesOrderPlanning/ProductAvailabilityRange/List');
    }
    
}