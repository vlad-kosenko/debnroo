<?php

class MDN_HealthyERP_Block_Adminhtml_System_Config_Probe_Views extends MDN_HealthyERP_Block_Adminhtml_System_Config_Probe_Abstract
{

    const COUNT_VIEW = 4;

    const DEFAULT_ACTION = 'fix';

    private $_missing_views;


    /**
     * Check the presence in data bse of Each View required for ERP
     *
     * @return type
     */
    protected function checkProbe()
    {
      $this->_missing_views = array();

      //View list
      $viewList = Mage::helper('HealthyERP/View')->getViewList();
      
      $status = parent::STATUS_UNKNOWN;

      $countOK = 0;
      foreach ($viewList as $view) {
        $result = false;

        //Check if a View (or a Table) with this name exists
        try {
          $sql = 'DESC '. $view;
          $result = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);
        }catch (Exception $ex){
          $result = false;
        }

        //Check if it's really a view
        try {
            if($result){
              $sql = "select count(*) from INFORMATION_SCHEMA.VIEWS where table_name = '$view' ";//TODO and table_schema = 'MySchema'
              $viewCount = (int)mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);
              if($viewCount==1){
                $result = true;
              }
            }
        }catch (Exception $ex){
        }
        
        if(!$result){
          $this->_missing_views[] = $view;
        }else{
          $countOK++;
        }
      }
      $countErrors = count($this->_missing_views);
      if($countErrors>0){
        $status = parent::STATUS_PARTIAL;
      }
      if($countErrors==self::COUNT_VIEW){
        $status = parent::STATUS_NOK;
      }
      if($countOK==self::COUNT_VIEW){
        $status = parent::STATUS_OK;
      }
      return $status;
    }
    

    

    

    protected function getActions()
    {
      $actions = array();

      $action = self::DEFAULT_ACTION;
      $openMode = null;

      switch($this->_indicator_status){
        case parent::STATUS_OK :
          break;
        case parent::STATUS_PARTIAL :
        case parent::STATUS_NOK :
           foreach($this->_missing_views as $viewTofix){
             $label = $this->__('CREATE '.$viewTofix);
             $actions[] = array($label, $viewTofix, $action, $openMode);
           }
           break;
      }
      return $actions;
    }    

    protected function getCurrentSituation()
    { 
      $situation = '';
      if (Mage::getStoreConfig('healthyerp/options/display_basic_message')){
        $situation = '';
        switch($this->_indicator_status){
          case parent::STATUS_OK :
             $situation = $this->__('All views are present');
             break;
          case parent::STATUS_NOK :
             $situation = $this->__('None of the view are present');
             break;
          case parent::STATUS_PARTIAL :
             $missingList = implode(", ", $this->_missing_views);
             $situation = $this->__('Some views are missing : %s',$missingList);
             break;
          default:
             $situation = $this->__(parent::DEFAULT_STATUS_MESSAGE);
             break;
        }      
      }
      return $situation;
    }


    /**
     * Re create the missing view
     */
    public static function fixIssue($view){

      $redirect = true;      

      //Get the SQL to recreate the view
      $sql = Mage::helper('HealthyERP/View')->getCreateViewQuery($view);

      //Execute the SQL
      if(!empty($sql)){
        mage::getResourceModel('sales/order_item_collection')->getConnection()->query($sql);      
      }
      
      return $redirect;
    }

}