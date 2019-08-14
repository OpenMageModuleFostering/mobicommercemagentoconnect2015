<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales report grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mobicommerce_Mobiadmin_Block_Adminhtml_Report_Sales_Sales_Grid extends Mage_Adminhtml_Block_Report_Sales_Sales_Grid
{
    protected $_columnGroupBy = 'period';

    public function __construct()
    {
        parent::__construct();
        $this->setCountTotals(true);
    }

    public function getResourceCollectionName()
    {
        return ($this->getFilterData()->getData('report_type') == 'updated_at_order')
            ? 'sales/report_order_updatedat_collection'
            : 'sales/report_order_collection';
    }
	protected function _prepareCollection() {
		parent::_prepareCollection();
		$collection = $this->getCollection();
		
		
		$fromDate = $this->getFilterData('from');
		$report_type = $this->getFilterData('report_type');
		if($report_type == 'created_at_order'){
			$filterby = 'created_at';
		}else if($report_type == 'updated_at_order'){
			$filterby = 'updated_at';
		}
		$toDate = $this->getFilterData('to');
		$orderfrom = $this->getFilterData('orderfrom');
		$orderstatus = $this->getFilterData('show_order_statuses');
		if($orderfrom =='mobicommerce' || $orderfrom =='website'){
		    $fromDate = date('Y-m-d H:i:s', strtotime($fromDate));
            $toDate = date('Y-m-d H:i:s', strtotime($toDate));
			if($orderfrom =='mobicommerce'){
				if($orderstatus == '1')
				{
					$order_statuses = $this->getFilterData('order_statuses');
					$orderCollection = Mage::getModel('sales/order')->getCollection()
					->addAttributeToFilter($filterby, array('from'=>$fromDate, 'to'=>$toDate))
					->addAttributeToFilter('status', array('in' => $order_statuses))
					->addAttributeToFilter('orderfromplatform', array('eq' => 'mobicommerce'));					
				}else{
				$orderCollection = Mage::getModel('sales/order')->getCollection()
					->addAttributeToFilter($filterby, array('from'=>$fromDate, 'to'=>$toDate))
					->addAttributeToFilter('orderfromplatform', array('eq' => 'mobicommerce'));
				}
			}elseif($orderfrom =='website'){
				if($orderstatus == '1')
				{
					$order_statuses = $this->getFilterData('order_statuses');
					$orderCollection = Mage::getModel('sales/order')->getCollection()
					->addAttributeToFilter($filterby, array('from'=>$fromDate, 'to'=>$toDate))
					->addAttributeToFilter('status', array('in' => $order_statuses))
					->addAttributeToFilter('orderfromplatform', array('neq' => 'mobicommerce'));
					
				}else{
				$orderCollection = Mage::getModel('sales/order')->getCollection()
					->addAttributeToFilter($filterby, array('from'=>$fromDate, 'to'=>$toDate))
					->addAttributeToFilter(
					'orderfromplatform', 
					array(array('neq' => 'mobicommerce'),array('null' => 'null')));
				}
			}
			//echo $orderCollection->getSelect(); 
			$filtercollection = array();
			foreach($orderCollection->getData() as $order)
			{
				$created_date = date('Y-m-d', strtotime($order[$filterby]));
				if(array_key_exists($created_date,$filtercollection))
				{
					$filtercollection[$created_date][] = $order;
				}else{
					$filtercollection[$created_date] = array($order);
				}
							
			}
			$item1 = array();
            foreach($collection as $item){
			    $item1[] = $item->getData();
			}

			$collectionforfilter = array();
			$i = 0;
			foreach($filtercollection as $indexDate => $orderData){
                $collectionforfilter[$i]['period'] = $indexDate;
                $collectionforfilter[$i]['orders_count'] = count($orderData);
				$qty = array();
				$base_subtotal_canceled = array();
				$total_invoiced_amount = array();
				$total_canceled_amount = array();
				$total_paid_amount = array();
				$total_refunded_amount = array();
				$total_tax_amount = array();
				$total_shipping_amount = array();
				$total_discount_amount = array();
				foreach($orderData as $order){
					$qty[] = $order['total_qty_ordered'];
					$base_subtotal_canceled[] = $order['base_subtotal_canceled'];
					$total_invoiced_amount[] = $order['subtotal_invoiced'];
					$total_canceled_amount[] = $order['base_subtotal_canceled'];
					$total_paid_amount[] = $order['base_total_paid'];
					$total_refunded_amount[] = $order['subtotal_refunded'];
					$total_tax_amount[] = $order['tax_amount'];
					$total_shipping_amount[] = $order['shipping_amount'];
					$total_discount_amount[] = $order['discount_amount'];
				}
                $collectionforfilter[$i]['total_qty_ordered'] = array_sum($qty);
                $collectionforfilter[$i]['total_qty_invoiced'] = 0;
                $collectionforfilter[$i]['total_income_amount'] = array_sum($total_paid_amount);
                $collectionforfilter[$i]['total_revenue_amount'] = 0;
                $collectionforfilter[$i]['total_profit_amount'] = 0;                
                $collectionforfilter[$i]['total_invoiced_amount'] = array_sum($total_invoiced_amount);
                $collectionforfilter[$i]['total_canceled_amount'] = array_sum($total_canceled_amount);
                $collectionforfilter[$i]['total_paid_amount'] = array_sum($total_paid_amount);
                $collectionforfilter[$i]['total_refunded_amount'] = array_sum($total_refunded_amount);
                $collectionforfilter[$i]['total_tax_amount'] = array_sum($total_tax_amount);
                $collectionforfilter[$i]['total_tax_amount_actual'] = array_sum($total_tax_amount);
                $collectionforfilter[$i]['total_shipping_amount'] = array_sum($total_shipping_amount);
                $collectionforfilter[$i]['total_shipping_amount_actual'] = array_sum($total_shipping_amount);
                $collectionforfilter[$i]['total_discount_amount'] = array_sum($total_discount_amount);
                $collectionforfilter[$i]['total_discount_amount_actual'] = array_sum($total_discount_amount);

				$zero_check = array(
					"total_income_amount", 
					"total_tax_amount",
					"total_discount_amount",
					"total_refunded_amount",
					"total_invoiced_amount",
					"shipping_amount",
					"total_shipping_amount",
					"total_canceled_amount",
				);
				foreach($zero_check as $key){
					if(empty($collectionforfilter[$i][$key])){
						$collectionforfilter[$i][$key] = "0.00";
					}
				}
				$i++;
			}
			$collection = new Varien_Data_Collection();	
			foreach ($collectionforfilter as $item) {
			    $varienObject = new Varien_Object();
			    $varienObject->setData($item);
			    $collection->addItem($varienObject);
			}
			$this->setCollection($collection);
        }
		
	}

	public function getCountTotals()
    {
		$orderfrom = $this->getFilterData('orderfrom');
		if($orderfrom !='mobicommerce' && $orderfrom !='website'){
		    return parent::getCountTotals();
		}
    }

    protected function _prepareColumns()
    {
        $this->addColumn('period', array(
            'header'          => Mage::helper('sales')->__('Period'),
            'index'           => 'period',
            'width'           => 100,
            'sortable'        => false,
            'period_type'     => $this->getPeriodType(),
            'renderer'        => 'adminhtml/report_sales_grid_column_renderer_date',
            'totals_label'    => Mage::helper('sales')->__('Total'),
            'html_decorators' => array('nobr'),
            ));

        $this->addColumn('orders_count', array(
            'header'   => Mage::helper('sales')->__('Orders'),
            'index'    => 'orders_count',
            'type'     => 'number',
            'total'    => 'sum',
            'sortable' => false
            ));

        $this->addColumn('total_qty_ordered', array(
            'header'   => Mage::helper('sales')->__('Sales Items'),
            'index'    => 'total_qty_ordered',
            'type'     => 'number',
            'total'    => 'sum',
            'sortable' => false
            ));

        $this->addColumn('total_qty_invoiced', array(
            'header'            => Mage::helper('sales')->__('Items'),
            'index'             => 'total_qty_invoiced',
            'type'              => 'number',
            'total'             => 'sum',
            'sortable'          => false,
            'visibility_filter' => array('show_actual_columns')
            ));

        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }
        $currencyCode = $this->getCurrentCurrencyCode();
        $rate = $this->getRate($currencyCode);

        $this->addColumn('total_income_amount', array(
            'header'        => Mage::helper('sales')->__('Sales Total'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_income_amount',
            'total'         => 'sum',
            'sortable'      => false,
            'rate'          => $rate,
            ));

        $this->addColumn('total_revenue_amount', array(
            'header'            => Mage::helper('sales')->__('Revenue'),
            'type'              => 'currency',
            'currency_code'     => $currencyCode,
            'index'             => 'total_revenue_amount',
            'total'             => 'sum',
            'sortable'          => false,
            'visibility_filter' => array('show_actual_columns'),
            'rate'              => $rate,
            ));

        $this->addColumn('total_profit_amount', array(
            'header'            => Mage::helper('sales')->__('Profit'),
            'type'              => 'currency',
            'currency_code'     => $currencyCode,
            'index'             => 'total_profit_amount',
            'total'             => 'sum',
            'sortable'          => false,
            'visibility_filter' => array('show_actual_columns'),
            'rate'              => $rate,
            ));

        $this->addColumn('total_invoiced_amount', array(
            'header'        => Mage::helper('sales')->__('Invoiced'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_invoiced_amount',
            'total'         => 'sum',
            'sortable'      => false,
            'rate'          => $rate,
            ));

        $this->addColumn('total_paid_amount', array(
            'header'            => Mage::helper('sales')->__('Paid'),
            'type'              => 'currency',
            'currency_code'     => $currencyCode,
            'index'             => 'total_paid_amount',
            'total'             => 'sum',
            'sortable'          => false,
            'visibility_filter' => array('show_actual_columns'),
            'rate'              => $rate,
            ));

        $this->addColumn('total_refunded_amount', array(
            'header'        => Mage::helper('sales')->__('Refunded'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_refunded_amount',
            'total'         => 'sum',
            'sortable'      => false,
            'rate'          => $rate,
            ));

        $this->addColumn('total_tax_amount', array(
            'header'        => Mage::helper('sales')->__('Sales Tax'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_tax_amount',
            'total'         => 'sum',
            'sortable'      => false,
            'rate'          => $rate,
            ));

        $this->addColumn('total_tax_amount_actual', array(
            'header'            => Mage::helper('sales')->__('Tax'),
            'type'              => 'currency',
            'currency_code'     => $currencyCode,
            'index'             => 'total_tax_amount_actual',
            'total'             => 'sum',
            'sortable'          => false,
            'visibility_filter' => array('show_actual_columns'),
            'rate'              => $rate,
            ));

        $this->addColumn('total_shipping_amount', array(
            'header'        => Mage::helper('sales')->__('Sales Shipping'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_shipping_amount',
            'total'         => 'sum',
            'sortable'      => false,
            'rate'          => $rate,
            ));

        $this->addColumn('total_shipping_amount_actual', array(
            'header'            => Mage::helper('sales')->__('Shipping'),
            'type'              => 'currency',
            'currency_code'     => $currencyCode,
            'index'             => 'total_shipping_amount_actual',
            'total'             => 'sum',
            'sortable'          => false,
            'visibility_filter' => array('show_actual_columns'),
            'rate'              => $rate,
            ));

        $this->addColumn('total_discount_amount', array(
            'header'        => Mage::helper('sales')->__('Sales Discount'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_discount_amount',
            'total'         => 'sum',
            'sortable'      => false,
            'rate'          => $rate,
            ));

        $this->addColumn('total_discount_amount_actual', array(
            'header'            => Mage::helper('sales')->__('Discount'),
            'type'              => 'currency',
            'currency_code'     => $currencyCode,
            'index'             => 'total_discount_amount_actual',
            'total'             => 'sum',
            'sortable'          => false,
            'visibility_filter' => array('show_actual_columns'),
            'rate'              => $rate,
            ));

        $this->addColumn('total_canceled_amount', array(
            'header'        => Mage::helper('sales')->__('Canceled'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_canceled_amount',
            'total'         => 'sum',
            'sortable'      => false,
            'rate'          => $rate,
            ));

        $this->addExportType('*/*/exportSalesCsv', Mage::helper('adminhtml')->__('CSV'));
        $this->addExportType('*/*/exportSalesExcel', Mage::helper('adminhtml')->__('Excel XML'));

        return parent::_prepareColumns();
    }
}