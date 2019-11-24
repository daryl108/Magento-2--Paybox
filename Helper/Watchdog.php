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

namespace Naxero\Paybox\Helper;

use Naxero\Paybox\Gateway\Config\Core;
use Naxero\Paybox\Gateway\Processor\Connector;

class Watchdog
{
    /**
     * @var ManagerInterface
     */
    protected $messageManager;
 
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Tools
     */
    protected $tools;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Watchdog constructor.
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Naxero\Paybox\Gateway\Config\Config $config,
        \Naxero\Paybox\Helper\Tools $tools,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->messageManager = $messageManager;
        $this->config         = $config;
        $this->tools          = $tools;
        $this->logger         = $logger;
    }

    /**
     * Display messages and write to custom log file.
     */
    public function bark($action, $data, $canDisplay = true, $canLog = true)
    {
        // Prepare the output
        $output = ($data) ? print_r($data, 1) : '';
        $output = strtoupper($action) . "\n" . $output;

        // Process file logging
        if ((int) $this->config->params[Core::moduleId()][Connector::KEY_LOGGING] == 1 && $canLog) {
            // Build the log file name
            $logFile = BP . '/var/log/' . Core::moduleId() . '_' . $action . '.log';

            // Write to the log file
            $writer = new \Zend\Log\Writer\Stream($logFile);
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($output);
        }

        // Process interface display
        if ((int) $this->config->params[Core::moduleId()]['debug'] == 1 && $canDisplay) {
            $this->messageManager->addNoticeMessage($output);
        }
    }

    /**
     * Write to system file.
     */
    public function logError($message, $canDisplay = true)
    {
        // Log to system log file
        if ((int) $this->config->params[Core::moduleId()][Connector::KEY_LOGGING] == 1) {
            $output = Core::moduleId() . ' | ' . $message;
            $this->logger->log('ERROR', $output);
        }

        // Display if needed
        if ($canDisplay) {
            get_class($message) == 'Exception'
            ? $this->messageManager->addExceptionMessage($message, $message->getMessage())
            : $this->messageManager->addErrorMessage($message);
        }
    }
}
