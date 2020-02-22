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

namespace Naxero\Paybox\Block\Payment;

class Form extends \Magento\Framework\View\Element\Template
{
    /**
     * @var FormHandlerService
     */
    public $formHandler;

    /**
     * @var Config
     */
    public $config;

    /**
     * @var AssetRepository
     */
    public $assetRepository;

    /**
     * @var Array
     */
    public $months;

    /**
     * @var Array
     */
    public $years;

    /**
     * Form constructor.
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Naxero\Paybox\Model\Service\FormHandlerService $formHandler,
        \Naxero\Paybox\Gateway\Config\Config $config,
        \Magento\Framework\View\Asset\Repository $assetRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->formHandler = $formHandler;
        $this->config = $config;
        $this->assetRepository = $assetRepository;

        // Prepare the field values
        $this->months = $this->formHandler->getMonths();
        $this->years = $this->formHandler->getYears();
    }
}
