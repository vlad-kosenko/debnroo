var SimpleBarcodeInventory = Class.create();
SimpleBarcodeInventory.prototype = {

    //**********************************************************************************************************************************
    //initialize object
    initialize: function(eProductInformationUrl, eCommitProductStockUrl, eImmediateSave, eDefaultMode){
        this.ProductInformationUrl = eProductInformationUrl;
        this.CommitProductStockUrl = eCommitProductStockUrl;
        this.ImmediateSave = eImmediateSave;
        
        this.KC_catchKeys = false;
        this.KC_value = '';
        this.KC_onEnter = null;
        this.KC_onType = null;
        
        this.mode = eDefaultMode;
        
        this.currentProduct = null;
        this.knownProducts = new Array();
        
        this.newStockLevels = new Array();
    },
    
    //**********************************************************************************************************************************
    //
    waitForScan: function()
    {
        document.getElementById('div_product').style.display = 'none';
        
        mainObj.showInstruction(kTextScanProduct, false);

        //handle keyboard events
        document.onkeypress = mainObj.handleKey;
        mainObj.KC_catchKeys = true;
        mainObj.KC_displayInput = 'mainObj.scanProduct()';
        mainObj.KC_onEnter = 'mainObj.scanProduct()';
        mainObj.KC_onType = 'mainObj.barcodeDigitScanned()';

    },
    
    //**********************************************************************************************************************************
    //
    handleKey: function(evt) {

        if (!mainObj.KC_catchKeys)
            return true;

        //Dont process event if focuses control is text
        if (document.activeElement)
        {
            if ((document.activeElement.type == 'text') || (document.activeElement.tagName.toLowerCase() == 'textarea'))
                return true;
        }

        var evt = (window.event ? window.event : evt);
        var keyCode;

        //if not ie
        if (navigator.appName != 'Microsoft Internet Explorer')
            keyCode = evt.which;
        else
            keyCode = evt.keyCode;

        if (keyCode != 13)
        {
            mainObj.KC_value += String.fromCharCode(keyCode);
            if (mainObj.KC_displayInput != null)
                mainObj.KC_displayInput.value = mainObj.KC_value;
            if (document.getElementById('scanner_value'))
                document.getElementById('scanner_value').innerHTML = KC_value;
            if (mainObj.KC_onType)
                eval(mainObj.KC_onType);
        }
        else
        {
            eval(mainObj.KC_onEnter);
            mainObj.KC_value = '';
        }

        return false;
    },

    //**********************************************************************************************************************************
    //
    scanProduct: function()
    {
        //init vars
        var barcode = this.KC_value;
        this.KC_value = '';
        
        document.getElementById('div_product').style.display = 'none';

        //check if product already known
        if (mainObj.knownProducts[barcode])
        {
            mainObj.currentProduct = mainObj.knownProducts[barcode];
            mainObj.processProduct();
            return true;
        }


        //launch ajax request to get product information
        var url = this.ProductInformationUrl;
        url = url.replace('XXX', barcode);
        
        //ajax request
        var request = new Ajax.Request(
            url,
            {
                method: 'GET',
                evalScripts: true,
                onSuccess: function onSuccess(transport)
                {
                    elementValues = eval('(' + transport.responseText + ')');
                    if (elementValues['error'] == true)
                    {
                        mainObj.showMessage(elementValues['message'], true);
                    }
                    else
                    {
                        //display products information
                        mainObj.currentProduct = elementValues['product_information'];
                        mainObj.currentProduct['product_stock'] = elementValues['product_stock'];
        
                        //append product in cache
                        mainObj.knownProducts[mainObj.currentProduct['barcode']] = mainObj.currentProduct;

                        mainObj.processProduct();
                    }
                },
                onFailure: function onFailure(transport)
                {
                    mainObj.showMessage('An error occured', true);
                }
            }
            );
        
    },
    
    //**********************************************************************************************************************************
    //
    processProduct: function()
    {
        mainObj.showMessage(mainObj.currentProduct['name']);
                    
        document.getElementById('product_name').innerHTML = mainObj.currentProduct['name'];
        document.getElementById('product_sku').innerHTML = mainObj.currentProduct['sku'];
        document.getElementById('product_image').src = mainObj.currentProduct['image_url'];
        document.getElementById('product_stock').value = parseInt(mainObj.currentProduct['product_stock']['qty']);

        //process depending of mode
        switch(mainObj.mode)
        {
            case 'manual':
                document.getElementById('product_stock').value = parseInt(document.getElementById('product_stock').value);
                document.getElementById('div_product').style.display = '';
                break;
            case 'increase':
                document.getElementById('product_stock').value = parseInt(document.getElementById('product_stock').value) + 1;
                mainObj.commitCurrentProductStock();
                break;
            case 'decrease':
                if (document.getElementById('product_stock').value > 0)
                    document.getElementById('product_stock').value = parseInt(document.getElementById('product_stock').value) - 1;
                mainObj.commitCurrentProductStock();
                break;
        }
        
    },

    //**********************************************************************************************************************************
    //
    barcodeDigitScanned:function()
    {
        mainObj.showMessage(mainObj.KC_value);
    },
    //******************************************************************************
    //
    showMessage: function(text, error)
    {
        if (text == '')
            text = '&nbsp;';

        if (error)
            text = '<font color="red">' + text + '</font>';
        else
            text = '<font color="green">' + text + '</font>';

        document.getElementById('div_message').innerHTML = text;
        document.getElementById('div_message').style.display = '';
    },

    //******************************************************************************
    //
    hideMessage: function()
    {
        document.getElementById('div_message').style.display = 'none';
    },


    //******************************************************************************
    //display instruction for current
    showInstruction: function(text)
    {
        document.getElementById('div_instruction').innerHTML = text;
        document.getElementById('div_instruction').style.display = '';
    },

    //******************************************************************************
    //
    hideInstruction: function()
    {
        document.getElementById('div_instruction').style.display = 'none';
    },
    
    //******************************************************************************
    //
    setMode: function(mode)
    {
        mainObj.mode = mode;
        
        document.getElementById('btn-mode-manual').className = 'sbi-button';
        document.getElementById('btn-mode-increase').className = 'sbi-button';
        document.getElementById('btn-mode-decrease').className = 'sbi-button';
        
        document.getElementById('btn-mode-' + mode).className = 'sbi-button-selected';
    },
    
    //******************************************************************************
    //
    increaseCurrentProductStock: function()
    {
        document.getElementById('product_stock').value = parseInt(document.getElementById('product_stock').value) + 1;
    },
    
    //******************************************************************************
    //
    decreaseCurrentProductStock: function()
    {
        if (document.getElementById('product_stock').value > 0)
            document.getElementById('product_stock').value = parseInt(document.getElementById('product_stock').value) - 1;
    },
    
    //******************************************************************************
    //
    commitCurrentProductStock: function()
    {
        //launch ajax request to get product information
        var url = this.CommitProductStockUrl;
        url = url.replace('XXX', mainObj.currentProduct['entity_id']);
        url = url.replace('YYY', document.getElementById('product_stock').value);

        //ajax request
        if (mainObj.ImmediateSave)
        {
            var request = new Ajax.Request(
                url,
                {
                    method: 'GET',
                    evalScripts: true,
                    onSuccess: function onSuccess(transport)
                    {
                        elementValues = eval('(' + transport.responseText + ')');
                        if (elementValues['error'] == true)
                        {
                            mainObj.showMessage(elementValues['message'], true);
                        }
                        else
                        {
                            //confirm
                            mainObj.showMessage(elementValues['message']);
                            mainObj.addHistory(mainObj.currentProduct, document.getElementById('product_stock').value);
                            
                            //update cache
                            mainObj.knownProducts[mainObj.currentProduct['barcode']]['product_stock']['qty'] =  document.getElementById('product_stock').value;

                        }

                        document.getElementById('div_product').style.display = 'none';
                    },
                    onFailure: function onFailure(transport)
                    {
                        mainObj.showMessage('An error occured', true);
                    }
                }
                );
        }
        else
        {
            //append changes to array (for future mass save)
            mainObj.newStockLevels[mainObj.currentProduct['entity_id']] = document.getElementById('product_stock').value;
            mainObj.addHistory(mainObj.currentProduct, document.getElementById('product_stock').value);
            document.getElementById('div_product').style.display = 'none';
            
            //update cache
            mainObj.knownProducts[mainObj.currentProduct['barcode']]['product_stock']['qty'] =  document.getElementById('product_stock').value;

        }
        
    },
        
    //******************************************************************************
    //    
    addHistory: function(product, newStockLevel)
    {
        //calculate difference
        var diff = newStockLevel - product['product_stock']['qty'];
        if (diff > 0)
            diff = '+' + diff;
        
        //append in history
        var table = document.getElementById('table_history');

        var row = table.insertRow(1);
        
        var cellImage = row.insertCell(0);
        cellImage.innerHTML = '<img src="' + product['image_url'] + '" height="30">';

        var cellBarcode = row.insertCell(1);
        cellBarcode.className = 'sbi-table-cell';
        cellBarcode.innerHTML = product['barcode'];
        
        var cellSku = row.insertCell(2);
        cellSku.className = 'sbi-table-cell';
        cellSku.innerHTML = product['sku'];
        
        var cellName = row.insertCell(3);
        cellName.className = 'sbi-table-cell';
        cellName.innerHTML = product['name'];
        
        var cellDiff = row.insertCell(4);
        cellDiff.className = 'sbi-table-cell';
        cellDiff.innerHTML = diff;
        
        var cellNewStock = row.insertCell(5);
        cellNewStock.className = 'sbi-table-cell';
        cellNewStock.innerHTML = newStockLevel;
    },
    
    //******************************************************************************
    //Save all changes (when immediate save is disabled)
    save: function()
    {
        //create a string with all new values
        var i;
        var string = '';
        for(i=0;i<mainObj.newStockLevels.length;i++)
        {
            if (mainObj.newStockLevels[i] != undefined)
            {
                string += i + '=' + mainObj.newStockLevels[i] + ';';
            }
        }
        if (string == '')
        {
            alert(kTextNoProductToSave);
            return false;
        }
        
        //store stirng in form and submit it
        document.getElementById('changes').value = string;
        document.getElementById('form_save').submit();
    }
    
}
