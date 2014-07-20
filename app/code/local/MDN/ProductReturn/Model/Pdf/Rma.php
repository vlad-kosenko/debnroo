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
/**
 * Classe pour l'impression d'un bon de commande fournisseur
 *
 */
class MDN_ProductReturn_Model_Pdf_Rma extends MDN_ProductReturn_Model_Pdf_Pdfhelper
{
	
	public function getPdf($rmas = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        if ($this->pdf == null)
	        $this->pdf = new Zend_Pdf();
	    else 
	    	$this->firstPageIndex = count($this->pdf->pages);
	    
        $style = new Zend_Pdf_Style();
        $style->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);

        foreach ($rmas as $rma) 
        {

	        //new page
	        $titre = mage::helper('ProductReturn')->__('Product Return #%s', $rma->getrma_ref());
	        $settings = array();
	        $settings['title'] = $titre;
	        $settings['store_id'] = $rma->getSalesOrder()->getstore_id();
	        $page = $this->NewPage($settings);
			
            //addresses and main information
	        //$txt_date = mage::helper('ProductReturn')->__('Valid until : ').date('d/m/Y', strtotime($rma->getrma_expire_date()));
	        $txt_date = mage::helper('ProductReturn')->__('Date : %s', mage::helper('core')->formatDate($rma->getrma_created_at(), 'short'));
	        $valuStatus = $rma->getrma_status();
	        //$txtStatus = mage::helper('ProductReturn')->__('Status : ').mage::helper('ProductReturn')->__($valuStatus);
	        $txtStatus = mage::helper('ProductReturn')->__('Order : %s', $rma->getSalesOrder()->getincrement_id());
	        $myAddress = Mage::getStoreConfig('productreturn/pdf/address');
	        $customerAddress = $rma->getShippingAddress()->getFormated();
	        $this->AddAddressesBlock($page, $myAddress, $customerAddress, $txt_date, $txtStatus);
	        $this->y -= 20;
	                
	         //display valid date end status
		    $soustitre =  mage::helper('ProductReturn')->__('Valid until %s', mage::helper('core')->formatDate($rma->getrma_expire_date(), 'short'));
		    $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.3));
		    $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 14);
		    $this->drawTextInBlock($page, $soustitre, 0, $this->y, $this->_PAGE_WIDTH, 50, 'c');
		    $this->y -= 10;
		    $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR,  $this->y);

            //display product table header
            $this->drawTableHeader($page);

            $this->y -=10;

            //display products
            foreach($rma->getProducts() as $product)
            {
				if ($product->getrp_qty() > 0)
				{                
	                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.2));
		        	$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10);
		        	
		        	$productName = $product->getrp_product_name();
		        	$productName = $this->TruncateTextToWidth($page, $productName, 220);
		        	$page->drawText($this->TruncateTextToWidth($page, $productName, 400), 15, $this->y, 'UTF-8');
		        	
		        	$this->drawTextInBlock($page, (int)$product->getrp_qty(), 240, $this->y, 40, 20, 'c');   
		        	
		        	$page->drawText($this->TruncateTextToWidth($page, $product->getrp_reason(), 200), 300, $this->y, 'UTF-8');
		        	
		        	$decision = mage::helper('ProductReturn')->__($product->getrp_action());
		        	$page->drawText($this->TruncateTextToWidth($page, $decision, 200), 400, $this->y, 'UTF-8');
		        	
		        	$comments = mage::helper('ProductReturn')->__('Comments : ').$product->getrp_description();
		        	if ($product->getrp_serials() != '')
		        	{
		        		$comments .= "\n".mage::helper('ProductReturn')->__('Serials : %s', $product->getrp_serials());
		        	}
		        	$comments = $this->WrapTextToWidth($page, $comments, 450);
		        	$this->y -= $this->_ITEM_HEIGHT;
			        $offset = $this->DrawMultilineText($page, $comments, 100, $this->y, 10, 0.2, 11);
	   	        	$this->y -= $this->_ITEM_HEIGHT + $offset;
				}
				   	        			        
	        	//next page
	        	if ($this->y < ($this->_BLOC_FOOTER_HAUTEUR + 40))
	        	{
	        		$this->drawFooter($page);
	        		$page = $this->NewPage($settings);
	        		$this->drawTableHeader($page);
	        	}

            }

            //comments
	        $this->y -= 5;
	        $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR,  $this->y);
	        $this->y -= 15;	        
	        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.3));
	        $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 12);
	        	        
	        $comments = $rma->getrma_public_description();
	        if(!empty($comments)){
	        	$page->drawText(mage::helper('ProductReturn')->__('Comments :'), 15, $this->y, 'UTF-8');
	        	$comments = $this->WrapTextToWidth($page, $comments, $this->_PAGE_WIDTH);
	        	$offset = $this->DrawMultilineText($page, $comments, 15, $this->y - 20, 10, 0.2, 11);	        
	        	$this->y -= $offset;
	        	
	        	$this->y -= 35;
	        	$page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR,  $this->y);
	        	$this->y -= 15;	        
	        	$page->setFillColor(new Zend_Pdf_Color_GrayScale(0.3));
	        	$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 12);
	        }
	        
	        //add pdf comments
	        $comments = mage::getStoreConfig('productreturn/pdf/pdf_comment');
	        if ($comments != '')
	        {
	        	$comments = $this->WrapTextToWidth($page, $comments, $this->_PAGE_WIDTH);
	        	$offset = $this->DrawMultilineText($page, $comments, 15, $this->y - 20, 10, 0.2, 11);
	        }
	       
	        //si on a plus la place de rajouter le footer, on change de page
        	if ($this->y < (150))
        	{
        		$this->drawFooter($page);
        		$page = $this->NewPage($settings);
        		$this->drawTableHeader($page);
        	}
	        		       
            //dessine le pied de page
	        $this->drawFooter($page);
        }
        
        //rajoute la pagination
        $this->AddPagination($this->pdf);
        
        $this->_afterGetPdf();

        return $this->pdf;
    }
    
	 
	 /**
	  * Dessine l'entete du tableau avec la liste des produits
	  *
	  * @param unknown_type $page
	  */
	 public function drawTableHeader(&$page)
	 {
	 	
        //entetes de colonnes
        $this->y -= 15;
        $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10);
        
	 	$page->drawText(mage::helper('ProductReturn')->__('Product'), 15, $this->y, 'UTF-8');
        $page->drawText(mage::helper('ProductReturn')->__('Qty'), 250, $this->y, 'UTF-8');
        $page->drawText(mage::helper('ProductReturn')->__('Reason'), 300, $this->y, 'UTF-8');
        $page->drawText(mage::helper('ProductReturn')->__('Decision'), 400, $this->y, 'UTF-8');
                
        //barre grise fin entete colonnes
        $this->y -= 8;
        $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR,  $this->y);
        
        $this->y -= 15;
	 }
	
}