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

namespace Naxero\Paybox\Plugin\Frontend;

/**
 * Class CsrfValidatorSkip.
 */
class CsrfValidatorSkip
{
    /**
     * @param \Magento\Framework\App\Request\CsrfValidator $subject
     * @param \Closure                                     $proceed
     * @param \Magento\Framework\App\RequestInterface      $request
     * @param \Magento\Framework\App\ActionInterface       $action
     */
    public function aroundValidate(
        $subject,
        \Closure $proceed,
        $request,
        $action
    ) {
        // Skip CSRF check
        if ($request->getModuleName() == 'naxero_paybox') {
            return;
        }

        // Proceed Magento 2 core functionalities
        $proceed($request, $action);
    }
}
