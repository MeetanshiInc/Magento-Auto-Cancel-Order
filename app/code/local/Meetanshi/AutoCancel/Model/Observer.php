<?php


class Meetanshi_Autocancel_Model_Observer
{
    public function autoCancel()
    {
        try {
            if ( Mage::helper('autocancel')->getAutoCancel() ) {

                $orders = Mage::getModel('sales/order')->getCollection()
                    ->addFieldToFilter('status', array('in' => array('pending', 'pending_payment')));

                $currentData = date_create(Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s'));
                $cancelOrder = '';
                foreach ($orders as $order):
                    $orderDate = date_create($order->getCreatedAt());
                    $dateDiffernt = date_diff($orderDate, $currentData);
                    $dayDifferent = $dateDiffernt->format("%a");
                    if ( $dayDifferent >= Mage::helper('autocancel')->getAutoCancel() ) {
                        $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true);
                        $history = $order->addStatusHistoryComment('Order was set to "Cancelled" due to the pending payment.', false);
                        $history->setIsCustomerNotified(false);
                        $order->save();
                        $cancelOrder .= $order->getIncrementId().' ';
                    }
                endforeach;
                if(!empty($cancelOrder)){
                    Mage::helper('autocancel')->sendEmail($cancelOrder);
                }
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }

    }
}