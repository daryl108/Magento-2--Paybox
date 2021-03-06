<?php
/**
 * Checkout.com Magento 2 Payment module (https://www.checkout.com)
 *
 * Copyright (c) 2017 Checkout.com (https://www.checkout.com)
 * Author: David Fiaty | integration@checkout.com
 *
 * MIT License
 */

namespace Naxero\Paybox\Observer\Backend;

use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order\Payment\Transaction;
use Naxero\Paybox\Gateway\Processor\Connector;
use Naxero\Paybox\Gateway\Config\Core;

class OrderSaveBefore implements \Magento\Framework\Event\ObserverInterface
{
 
    /**
     * @var Session
     */
    protected $backendAuthSession;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Tools
     */
    protected $tools;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var MethodHandlerService
     */
    protected $methodHandler;

    /**
     * @var Watchdog
     */
    protected $watchdog;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * OrderSaveBefore constructor.
     */
    public function __construct(
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Framework\App\Request\Http $request,
        \Naxero\Paybox\Helper\Tools $tools,
        \Naxero\Paybox\Gateway\Config\Config $config,
        \Naxero\Paybox\Model\Service\MethodHandlerService $methodHandler,
        \Naxero\Paybox\Helper\Watchdog $watchdog,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->backendAuthSession    = $backendAuthSession;
        $this->request               = $request;
        $this->tools                 = $tools;
        $this->config                = $config;
        $this->methodHandler         = $methodHandler;
        $this->watchdog              = $watchdog;
        $this->storeManager          = $storeManager;

        // Get the request parameters
        $this->params = $this->request->getParams();
    }
 
    /**
     * Observer execute function.
     */
    public function execute(Observer $observer)
    {
        if ($this->backendAuthSession->isLoggedIn()) {
            try {
                // Get the request parameters
                $params = $this->request->getParams();

                // Prepare the method id
                $methodId = $params['payment']['method'] ?? null;

                // Prepare the card data
                $cardData = $params['card_data'] ?? null;

                // Get the order
                $order = $observer->getEvent()->getOrder();

                // Get the payment info instance
                $paymentInfo = $order->getPayment()->getMethodInstance()->getInfoInstance();

                // Load the method instance if parameters are valid
                if ($methodId && is_array($cardData) && !empty($cardData)) {
                    // Load the method instance
                    $methodInstance = $this->methodHandler::getStaticInstance($methodId);

                    // Perform the charge request
                    if ($methodInstance) {
                        // Get the request object
                        $paymentObject = $methodInstance::getRequestData(
                            $this->config,
                            $this->storeManager,
                            $methodId,
                            $cardData,
                            $order
                        );

                        // Log the request
                        $methodInstance::logRequestData(
                            Connector::KEY_REQUEST,
                            $this->watchdog,
                            $paymentObject
                        );

                        // Log the response
                        $methodInstance::logResponseData(
                            Connector::KEY_RESPONSE,
                            $this->watchdog,
                            $paymentObject
                        );

                        // Process the response
                        $response = $methodInstance::processResponse(
                            $this->config,
                            $methodId,
                            $paymentObject
                        );

                        if (isset($response['isValid']) && $response['isValid'] === true 
                        && isset($response['isSuccess']) && $response['isSuccess'] === true) {
                            // Add the transaction info for order save after
                            $paymentInfo->setAdditionalInformation(
                                Connector::KEY_TRANSACTION_INFO,
                                [
                                    $this->config->base[
                                        Connector::KEY_TRANSACTION_ID_FIELD
                                    ] => $methodInstance::getTransactionId(
                                        $this->config,
                                        $paymentObject
                                    )
                                ]
                            );

                            // Handle the order status
                            $isCaptureImmediate = $this->config->params[$methodId]
                            [Connector::KEY_CAPTURE_MODE] == Connector::KEY_CAPTURE_IMMEDIATE;
                            if ($isCaptureImmediate) {
                                $order->setStatus(
                                    $this->config->params[Core::moduleId()][Connector::KEY_ORDER_STATUS_CAPTURED]
                                );
                            } else {
                                $order->setStatus(
                                    $this->config->params[Core::moduleId()][Connector::KEY_ORDER_STATUS_AUTHORIZED]
                                );
                            }
                        } else {
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __('The transaction could not be processed')
                            );
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->watchdog->logError($e);
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }
        }

        return $this;
    }
}
