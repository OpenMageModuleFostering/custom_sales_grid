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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales orders grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'sales/order_grid_collection';
    }

   protected function _prepareCollection()
{
$collection = Mage::getResourceModel($this->_getCollectionClass())
->join(
'sales/order_item',
'`sales/order_item`.order_id=`main_table`.entity_id',
array(
'skus' => new Zend_Db_Expr('group_concat(`sales/order_item`.sku SEPARATOR ",")'),
)
);

$collection->getSelect()->group('main_table.entity_id');

$collection->getSelect()->joinLeft(array('sfog' => 'sales_flat_order_grid'),
'main_table.entity_id = sfog.entity_id',array('sfog.shipping_name','sfog.billing_name'));

$collection->getSelect()->joinLeft(array('sfo'=>'sales_flat_order'),
'sfo.entity_id=main_table.entity_id',array('sfo.customer_email','sfo.weight',
'sfo.discount_description','sfo.increment_id','sfo.store_id','sfo.created_at','sfo.status','sfo.base_subtotal','sfo.store_id',
'sfo.base_grand_total','sfo.grand_total'));

$collection->getSelect()->joinLeft(array('sfoa'=>'sales_flat_order_address'),
'main_table.entity_id = sfoa.parent_id AND sfoa.address_type="shipping"',array('sfoa.street',
'sfoa.city','sfoa.region','sfoa.postcode','sfoa.telephone'));

$this->setCollection($collection);

return parent::_prepareCollection();
}



       protected function _prepareColumns()
{

$this->addColumn('real_order_id', array(
'header'=> Mage::helper('sales')->__('Order #'),
'width' => '80px',
'type' => 'text',
'index' => 'increment_id',
'filter_index' => 'sfo.increment_id',
));

if (!Mage::app()->isSingleStoreMode()) {
$this->addColumn('store_id', array(
'header' => Mage::helper('sales')->__('Purchased From (Store)'),
'index' => 'store_id',
'filter_index' => 'sfo.store_id',
'type' => 'store',
'store_view'=> true,
'display_deleted' => true,
'width' => '50px',
));
}

$this->addColumn('created_at', array(
'header' => Mage::helper('sales')->__('Purchased On'),
'index' => 'created_at',
'filter_index' => 'sfo.created_at',
'type' => 'datetime',
'width' => '50px',
));

$this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Customer Name'),
            'index' => 'billing_name',
			'type' => 'text',
			'filter_index' => 'sfog.billing_name',
			'width' => '50px',
        ));

$this->addColumn('shipping_city', array(
'header' => Mage::helper('sales')->__('Shipping City'),
'index' => 'city',
'type' => 'text',
'filter_index' => 'sfoa.city',
'width' => '50px',
));
/*$this->addColumn('shipping_region', array(
'header' => Mage::helper('sales')->__('Shipping Region'),
'index' => 'region',
'type' => 'text',
'filter_index' => 'sfoa.region',
'width' => '50px',
));*/
$this->addColumn('customer_email', array(
'header' => Mage::helper('sales')->__('Customer Email'),
'index' => 'customer_email',
'type' => 'text',
'filter_index' => 'sfo.customer_email',
'width' => '60px',
));
$this->addColumn('telephone', array(
'header' => Mage::helper('sales')->__('Customer phone'),
'index' => 'telephone',
'type' => 'text',
'filter_index' => 'sfoa.telephone',
'width' => '30px',
));
$this->addColumn('base_subtotal', array(
'header' => Mage::helper('sales')->__('Sub Total'),
'index' => 'base_subtotal',
'filter_index' => 'sfo.base_subtotal',
'type' => 'currency',
'currency' => 'order_currency_code',
'width' => '30px',
));
$this->addColumn('grand_total', array(
'header' => Mage::helper('sales')->__('Total'),
'index' => 'grand_total',
'filter_index' => 'sfo.grand_total',
'type' => 'currency',
'currency' => 'order_currency_code',
'width' => '30px',
));

$this->addColumn('status', array(
'header' => Mage::helper('sales')->__('Status'),
'index' => 'status',
'filter_index' => 'sfo.status',
'type' => 'options',
'width' => '70px',
'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
));


        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('action',
                array(
                    'header'    => Mage::helper('sales')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('sales')->__('View'),
                            'url'     => array('base'=>'*/sales_order/view'),
                            'field'   => 'order_id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
            ));
        }
        $this->addRssList('rss/order/new', Mage::helper('sales')->__('New Order RSS'));

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/cancel')) {
            $this->getMassactionBlock()->addItem('cancel_order', array(
                 'label'=> Mage::helper('sales')->__('Cancel'),
                 'url'  => $this->getUrl('*/sales_order/massCancel'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/hold')) {
            $this->getMassactionBlock()->addItem('hold_order', array(
                 'label'=> Mage::helper('sales')->__('Hold'),
                 'url'  => $this->getUrl('*/sales_order/massHold'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/unhold')) {
            $this->getMassactionBlock()->addItem('unhold_order', array(
                 'label'=> Mage::helper('sales')->__('Unhold'),
                 'url'  => $this->getUrl('*/sales_order/massUnhold'),
            ));
        }

        $this->getMassactionBlock()->addItem('pdfinvoices_order', array(
             'label'=> Mage::helper('sales')->__('Print Invoices'),
             'url'  => $this->getUrl('*/sales_order/pdfinvoices'),
        ));

        $this->getMassactionBlock()->addItem('pdfshipments_order', array(
             'label'=> Mage::helper('sales')->__('Print Packingslips'),
             'url'  => $this->getUrl('*/sales_order/pdfshipments'),
        ));

        $this->getMassactionBlock()->addItem('pdfcreditmemos_order', array(
             'label'=> Mage::helper('sales')->__('Print Credit Memos'),
             'url'  => $this->getUrl('*/sales_order/pdfcreditmemos'),
        ));

        $this->getMassactionBlock()->addItem('pdfdocs_order', array(
             'label'=> Mage::helper('sales')->__('Print All'),
             'url'  => $this->getUrl('*/sales_order/pdfdocs'),
        ));

        $this->getMassactionBlock()->addItem('print_shipping_label', array(
             'label'=> Mage::helper('sales')->__('Print Shipping Labels'),
             'url'  => $this->getUrl('*/sales_order_shipment/massPrintShippingLabel'),
        ));

        return $this;
    }

    public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('*/sales_order/view', array('order_id' => $row->getId()));
        }
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

}
