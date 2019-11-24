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

namespace Naxero\Paybox\Model\Ui;

class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{

    /**
     * @var Config
     */
    protected $config;

    /**
     * ConfigProvider constructor.
     */
    public function __construct(
        \Naxero\Paybox\Gateway\Config\Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Send the configuration to the frontend
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config->getFrontendConfig();
    }
}
