<?php

class MDN_BarcodeLabel_Helper_Label extends Mage_Core_Helper_Abstract {

    // apply coefficient to increase the resolution
    private $_coef = 2;
    private $_customCount = 3;

    /**
     * Return label image for product
     * @param <type> $productId
     */
    public function getImage($productId) {
        $product = Mage::getModel('catalog/product')->load($productId);

        //create base image
        $labelSize = Mage::helper('BarcodeLabel')->getlabelSize();
        $height = $labelSize['height'] * $this->_coef;
        $width = $labelSize['width'] * $this->_coef;
        $im = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($im, 255, 255, 255);
        imagefilledrectangle($im, 0, 0, $width, $height, $white);

        //add barcode
        $this->addBarcode($im, $product);

        //add product name
        $this->addName($im, $product);

        //add product attributes
        $this->addProductAttributes($im, $product);

        //add manufacturer
        $this->addManufacturer($im, $product);

        //add logo
        $this->addLogo($im, $product);

        // add product image
        $this->addProductPicture($im, $product);

        //add product sku
        $this->addSku($im, $product);

        //add price
        $this->addPrice($im, $product);

        //add price
        $this->addCustoms($im, $product);

        //return image
        return $im;
    }

    /**
     * Add barcode to img
     * @param <type> $im
     */
    protected function addBarcode(&$image, $product) {

        if (Mage::getStoreConfig('barcodelabel/barcode/print') != 1)
            return false;


        //get barcode image
        $barcodeAttributeName = Mage::helper('BarcodeLabel')->getBarcodeAttribute();
        $barcode = $product->getData($barcodeAttributeName);
        $barcodeImage = Mage::helper('BarcodeLabel/BarcodePicture')->getImage($barcode);

        $barcodeImageWidth = imagesx($barcodeImage);
        $barcodeImageHeight = imagesy($barcodeImage);

        $position = $this->getPositions(Mage::getStoreConfig('barcodelabel/barcode/position'), true);
        if ($position['width'] == 0)
            $position['width'] = $barcodeImageWidth * $this->_coef;
        if ($position['height'] == 0)
            $position['height'] = $barcodeImageHeight * $this->_coef;

        //add barcode on the picture
        imagecopyresized($image, $barcodeImage, $position['x'], $position['y'], 0, 0, $position['width'], $position['height'], $barcodeImageWidth, $barcodeImageHeight);
        //imagecopyresized ($dst_image , $src_image , int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h )
    }

    /**
     *
     * @param <type> $im
     * @param <type> $product 
     */
    protected function addName(&$im, $product) {

        if (Mage::getStoreConfig('barcodelabel/name/print') != 1)
            return false;

        $name = $product->getName();
        $fontSize = Mage::getStoreConfig('barcodelabel/name/font_size') * $this->_coef;
        $position = $this->getPositions(Mage::getStoreConfig('barcodelabel/name/position'), true);
        $black = imagecolorallocate($im, 0, 0, 0);
        $font = $this->getFont();

        $name = $this->truncateToSize($name, $position['width'], $font, $fontSize);

        imagettftext($im, $fontSize, 0, $position['x'], $position['y'], $black, $font, $name);
    }

    /**
     *
     * @param <type> $im
     * @param <type> $product 
     */
    protected function addProductAttributes(&$im, $product) {
        
    }

    /**
     *
     * @param <type> $im
     * @param <type> $product 
     */
    protected function addManufacturer(&$im, $product) {

        if (Mage::getStoreConfig('barcodelabel/manufacturer/print') != 1)
            return false;


        $name = $product->getAttributeText('manufacturer');
        $fontSize = Mage::getStoreConfig('barcodelabel/manufacturer/font_size') * $this->_coef;
        $position = $this->getPositions(Mage::getStoreConfig('barcodelabel/manufacturer/position'), true);
        $black = imagecolorallocate($im, 0, 0, 0);
        $font = $this->getFont();

        $name = $this->truncateToSize($name, $position['width'], $font, $fontSize);

        imagettftext($im, $fontSize, 0, $position['x'], $position['y'], $black, $font, $name);
    }

    /**
     *
     * @param <type> $im
     * @param <type> $product 
     */
    protected function addLogo(&$im, $product) {

        if (Mage::getStoreConfig('barcodelabel/logo/print') != 1)
            return false;

        $logoPath = Mage::getBaseDir() . DS . 'media' . DS . 'upload' . DS . 'image' . DS . Mage::getStoreConfig('barcodelabel/logo/picture');
        $logoImg = imagecreatefromjpeg($logoPath);
        $extention = explode('.', $logoPath);

        // detect if the file are jpeg or png 
        if ($extention["1"] == 'jpeg' || $extention["1"] == 'jpg')
            $logoImg = imagecreatefromjpeg($logoPath);
        if ($extention["1"] == 'png')
            $logoImg = imagecreatefrompng($logoPath);

        $position = $this->getPositions(Mage::getStoreConfig('barcodelabel/logo/position'), true);

        $logoImageWidth = imagesx($logoImg);
        $logoImageHeight = imagesy($logoImg);

        imagecopyresized($im, $logoImg, $position['x'], $position['y'], 0, 0, $position['width'], $position['height'], $logoImageWidth, $logoImageHeight);
    }

    /**
     * create product image
     * @return boolean 
     */
    protected function addProductPicture(&$im, $product) {
        if (Mage::getStoreConfig('barcodelabel/product_image/print') != 1)
            return false;

        $gallery = $product->getmedia_gallery(); // echo'<pre>'; print_r($product->getmedia_gallery()); echo'</pre>'; 
        // check if image exist
        if (isset($gallery["images"]['0'])) {

            // get path of base image
            if ($product->getimage() != "no_selection")
                $logoPath = Mage::getBaseDir() . DS . 'media' . DS . 'catalog' . DS . 'product' . $product->getimage();
            // or small image
            if ($product->getsmall_image() != "no_selection")
                $logoPath = Mage::getBaseDir() . DS . 'media' . DS . 'catalog' . DS . 'product' . $product->getsmall_image();
            // or thumnail who is the the best for label
            if ($product->getthumbnail() != "no_selection")
                $logoPath = Mage::getBaseDir() . DS . 'media' . DS . 'catalog' . DS . 'product' . $product->getthumbnail();


            $extention = pathinfo($logoPath, PATHINFO_EXTENSION);

            // detect if the file are jpeg or png 
            switch ($extention) {
                case 'jpeg';
                case 'jpg';
                    $logoImg = imagecreatefromjpeg($logoPath);
                    break;
                case 'png':
                    $logoImg = imagecreatefrompng($logoPath);
                    break;
                default:
                    return false;
                    break;
            }

            $position = $this->getPositions(Mage::getStoreConfig('barcodelabel/product_image/position'), true);

            $logoImageWidth = imagesx($logoImg);
            $logoImageHeight = imagesy($logoImg);

            imagecopyresized($im, $logoImg, $position['x'], $position['y'], 0, 0, $position['width'], $position['height'], $logoImageWidth, $logoImageHeight);
        } // end if image exist
    }

    /**
     *
     * @param <type> $im
     * @param <type> $product 
     */
    protected function addSku(&$im, $product) {

        if (Mage::getStoreConfig('barcodelabel/sku/print') != 1)
            return false;


        $name = $product->getSku();
        $fontSize = Mage::getStoreConfig('barcodelabel/sku/font_size') * $this->_coef;
        $position = $this->getPositions(Mage::getStoreConfig('barcodelabel/sku/position'), true);
        $black = imagecolorallocate($im, 0, 0, 0);
        $font = $this->getFont();

        $name = $this->truncateToSize($name, $position['width'], $font, $fontSize);

        imagettftext($im, $fontSize, 0, $position['x'], $position['y'], $black, $font, $name);
    }

    /**
     *
     * @param <type> $im
     * @param <type> $product 
     */
    protected function addPrice(&$im, $product) {

        if (Mage::getStoreConfig('barcodelabel/price/print') != 1)
            return false;

        //get price
        $price = $product->getPrice();
        
        // get special price with range date
        if ($product->getspecial_price() != '') {
            if (Mage::app()->getLocale()->isStoreDateInInterval(Mage::app()->getStore(), $product->getspecial_from_date(), $product->getspecial_to_date()))
                $price = $product->getspecial_price();
        }

        //tax rate
        $taxRate = Mage::getStoreConfig('barcodelabel/price/tax_rate');
        $price = $price * (1 + $taxRate / 100);

        $currencyCode = Mage::getStoreConfig('barcodelabel/price/currency');
        $currency = Mage::getModel('directory/currency')->load($currencyCode);
        $price = $currency->format($price, array(), false, false);

        $fontSize = Mage::getStoreConfig('barcodelabel/price/font_size') * $this->_coef;
        $position = $this->getPositions(Mage::getStoreConfig('barcodelabel/price/position'), true);
        $black = imagecolorallocate($im, 0, 0, 0);
        $font = $this->getFont();

        imagettftext($im, $fontSize, 0, $position['x'], $position['y'], $black, $font, $price);
    }

    /**
     * Add custom zones
     * @param type $im
     * @param type $product 
     */
    protected function addCustoms($im, $product) {
        //for each custom zone
        for ($i = 1; $i <= $this->_customCount; $i++) {
            
            //if zone is not enabled
            if (!Mage::getStoreConfig('barcodelabel/custom_' . $i . '/print'))
                continue;
            
            //get value
            $content = Mage::getStoreConfig('barcodelabel/custom_' . $i . '/content');
            //echo $content;
            $regex = "/\{([^\}]*)\}/";
            preg_match_all($regex, $content, $matches);
            if (isset($matches[1]))
            {
                foreach($matches[1] as $match)
                { 
                    
                    $value = $product->getAttributeText($match);
                    
                    if ($value){
                        $content = str_replace('{'.$match.'}', $value, $content);
                    } 
                    if(!$value) {
                         $content = $product->getData($match);
                         if( $content == ""){
                            $content ='';
                        }
                    }
                }
            }

            
            //print
            $fontSize = Mage::getStoreConfig('barcodelabel/custom_' . $i . '/font_size') * $this->_coef;
            $position = $this->getPositions(Mage::getStoreConfig('barcodelabel/custom_' . $i . '/position'), true);
            $black = imagecolorallocate($im, 0, 0, 0);
            $font = $this->getFont();
            imagettftext($im, $fontSize, 0, $position['x'], $position['y'], $black, $font, $content);
            
        }
    }

    /**
     * Convert cm to point
     * @param <type> $cm
     * @return <type>
     */
    protected function convertCmToPoint($cm) {
        return $cm * 50;
    }

    /**
     *
     */
    public function getPositions($positionString, $convertToPoint) {
        $t = explode(',', $positionString);

        $positions = array();
        $positions['x'] = $t[0];
        $positions['y'] = $t[1];
        $positions['width'] = $t[2];
        $positions['height'] = $t[3];

        if ($convertToPoint) {
            foreach ($positions as $k => $value) {
                $positions[$k] = $this->convertCmToPoint($value) * $this->_coef;
            }
        }

        return $positions;
    }

    /**
     * Return font
     * @return string
     */
    protected function getFont() {
        $path = Mage::getBaseDir() . '/media/font/' . Mage::getStoreConfig('barcodelabel/label/font');
        return $path;
    }

    /**
     *
     * @param type $text
     * @param type $maxWidth : position X
     * @param type $font
     * @param type $size
     * @return type 
     */
    protected function truncateToSize($text, $maxWidth, $font, $size) {

            $dimensions = imagettfbbox($size, null, $font, $text); 
            $realWidth = $dimensions[2] - $dimensions[0]; // image width
            $ratio = 219; // ratio found when $maxWidth = strlen($text)
            $maxLenght = $maxWidth - $ratio;
            
            if ($realWidth >= $maxWidth) {          
                $param = substr($text, 0, $maxLenght); 
                $text = wordwrap($text, strlen($param), "\n");
            }

        return $text;
    }
    
    
}
