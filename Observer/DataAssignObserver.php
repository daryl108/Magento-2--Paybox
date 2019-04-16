<?php
/**
 * Cmsbox.fr Magento 2 Paybox Payment.
 *
 * PHP version 7
 *
 * @category  Cmsbox
 * @package   Paybox
 * @author    Cmsbox Development Team <contact@cmsbox.fr>
 * @copyright 2019 Cmsbox.fr all rights reserved
 * @license   https://opensource.org/licenses/mit-license.html MIT License
 * @link      https://www.cmsbox.fr
 */

namespace Cmsbox\Paybox\Observer;

use Magento\Framework\Event\Observer;

class DataAssignObserver extends \Magento\Payment\Observer\AbstractDataAssignObserver
{
    /**
     * @param  Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $method = $this->readMethodArgument($observer);
        $data = $this->readDataArgument($observer);

        $paymentInfo = $method->getInfoInstance();

        if ($data->getDataByKey('transaction_result') !== null) {
            $paymentInfo->setAdditionalInformation(
                'transaction_result',
                $data->getDataByKey('transaction_result')
            );
        }
    }
}