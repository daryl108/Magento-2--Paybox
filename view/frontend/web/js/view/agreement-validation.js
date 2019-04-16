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

define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Cmsbox_Paybox/js/model/agreement-validator',
    ],
    function (Component, additionalValidators, agreementValidator) {
        'use strict';
        additionalValidators.registerValidator(agreementValidator);
        return Component.extend({});
    }
);