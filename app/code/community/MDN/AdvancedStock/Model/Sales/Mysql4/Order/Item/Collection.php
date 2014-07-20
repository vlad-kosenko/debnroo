<?php

class MDN_AdvancedStock_Model_Sales_Mysql4_Order_Item_Collection extends Mage_Sales_Model_Mysql4_Order_Item_Collection
{
    /**
     * Join with erp_sales_flat_order_item table
     */
    public function joinErpTable() {
        //Left join with erp_sales_flat_order_item table
        $this->getSelect()->joinLeft(
                array('esfoi' => $this->getTable('AdvancedStock/SalesFlatOrderItem')),
                "item_id = esfoi_item_id",
                array('preparation_warehouse', 'reserved_qty', 'comments', 'esfoi_item_id', 'serials'));

        return $this;
    }

}