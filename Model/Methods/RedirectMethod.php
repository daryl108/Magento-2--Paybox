<?php
/**
 * Naxero.com Magento 2 Paybox Payment.
 *
 * PHP version 7
 *
 * @category  Naxero
 * @package   Paybox
 * @author    Naxero Development Team <contact@naxero.com>
 * @copyright 2019 Naxero.com all rights reserved
 * @license   https://opensource.org/licenses/mit-license.html MIT License
 * @link      https://www.naxero.com
 */

namespace Naxero\Paybox\Model\Methods;

use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Framework\Module\Dir;
use Naxero\Paybox\Gateway\Config\Core;
use Naxero\Paybox\Helper\Tools;
use Naxero\Paybox\Gateway\Processor\Connector;
use Naxero\Paybox\Gateway\Config\Config;
class RedirectMethod extends \Magento\Payment\Model\Method\AbstractMethod
{

    protected $_code;
    protected $_isInitializeNeeded = true;
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCancel = true;
    protected $_canCapturePartial = true;
    protected $_canVoid = true;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $backendAuthSession;
    protected $cart;
    protected $urlBuilder;
    protected $_objectManager;
    protected $invoiceSender;
    protected $transactionFactory;
    protected $customerSession;
    protected $checkoutSession;
    protected $checkoutData;
    protected $quoteRepository;
    protected $quoteManagement;
    protected $orderSender;
    protected $sessionQuote;
    protected $config;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Naxero\Paybox\Gateway\Config\Config $config,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->urlBuilder         = $urlBuilder;
        $this->backendAuthSession = $backendAuthSession;
        $this->cart               = $cart;
        $this->_objectManager     = $objectManager;
        $this->invoiceSender      = $invoiceSender;
        $this->transactionFactory = $transactionFactory;
        $this->customerSession    = $customerSession;
        $this->checkoutSession    = $checkoutSession;
        $this->checkoutData       = $checkoutData;
        $this->quoteRepository    = $quoteRepository;
        $this->quoteManagement    = $quoteManagement;
        $this->orderSender        = $orderSender;
        $this->sessionQuote       = $sessionQuote;
        $this->config             = $config;
        $this->_code              = Core::methodId(get_class());
    }

    /**
     * Check whether method is available
     *
     * @param  \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return parent::isAvailable($quote) && null !== $quote;
    }

    /**
     * Prepare the request data.
     */
    public static function getRequestData($config, $storeManager, $methodId, $cardData = null, $entity = null, $moduleDirReader = null)
    {
        // Get the order entity
        $entity = ($entity) ? $entity : $config->cart->getQuote();

        // Get the vendor instance
        /*
        $paymentRequest = new PostInterface(Connector::getSecretKey($config));

        // Prepare the request
        $paymentRequest->setMerchantId(Connector::getMerchantId($config));
        $paymentRequest->setKeyVersion($config->params[Core::moduleId()][Core::KEY_VERSION]);
        $paymentRequest->setTransactionReference($config->createTransactionReference());
        $paymentRequest->setAmount($config->formatAmount($entity->getGrandTotal()));
        $paymentRequest->setCurrency(Tools::getCurrencyCode($entity, $storeManager));
        $paymentRequest->setCustomerContactEmail($entity->getCustomerEmail());
        $paymentRequest->setOrderId(Tools::getIncrementId($entity));
        $paymentRequest->setCaptureMode($config->params[$methodId][Connector::KEY_CAPTURE_MODE]);
        $paymentRequest->setCaptureDay((string) $config->params[$methodId][Connector::KEY_CAPTURE_DAY]);
        $paymentRequest->setLanguage($config->getCustomerLanguage());
        $paymentRequest->setNormalReturnUrl(
            $config->storeManager->getStore()->getBaseUrl()
            . Core::moduleId() . '/' . $config->params[$methodId][Core::KEY_NORMAL_RETURN_URL]
        );
        $paymentRequest->setAutomaticResponseUrl(
            $config->storeManager->getStore()->getBaseUrl()
            . Core::moduleId() . '/' . $config->params[$methodId][Core::KEY_AUTOMATIC_RESPONSE_URL]
        );

        // Set the 3DS parameter
        if ($config->params[$methodId][Core::KEY_VERIFY_3DS] && $config->base[Connector::KEY_ENVIRONMENT] != 'simu') {
            $paymentRequest->setFraudDataBypass3DS($config->params[$methodId][Core::KEY_BYPASS_RECEIPT]);
        }

        // Set the billing address info
        $address = $entity->getBillingAddress();
        $paymentRequest->setBillingContactEmail($entity->getCustomerEmail());
        $paymentRequest->setBillingAddressStreet(implode(', ', $address->getStreet()));
        $paymentRequest->setBillingAddressZipCode($address->getPostcode());
        $paymentRequest->setBillingAddressCity($address->getCity());
        
        // Validate the request
        $paymentRequest->validate();
        */
        
        return [
            'params' => $paymentRequest->toParameterString(),
            'seal' => $paymentRequest->getShaSign()
        ];
    }

    /**
     * Process the gateway response.
     */
    public static function processResponse($config, $methodId, $asset, $moduleDirReader = null)
    {
        // Get the vendor instance
        $paymentResponse = new PostInterface(Connector::getSecretKey($config));

        // Set the response
        $paymentResponse->setResponse($asset);
    
        // Return the validity status
        return [
            'isValid' => $paymentResponse->isValid(),
            'isSuccess' => $paymentResponse->isSuccessful()
        ];
    }

    /**
     * Gets a transaction id.
     */
    public static function getTransactionId($config, $paymentObject)
    {
        return $paymentObject->getParam($config->base[Connector::KEY_TRANSACTION_ID_FIELD]);
    }
    
    /**
     * Determines if the method is active on frontend.
     */
    public static function isFrontend($config, $methodId)
    {
        // Get the quote entity
        $entity = $config->cart->getQuote();

        return $config->params[$methodId][Connector::KEY_ACTIVE] == 1;
    }
}
