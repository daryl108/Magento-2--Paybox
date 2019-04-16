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

namespace Cmsbox\Paybox\Model\Adminhtml\Source;

use Cmsbox\Paybox\Gateway\Processor\Connector;

class FormTemplate implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Possible form templates
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'template_1',
                'label' => __('Template 1'),
            ],
            [
                'value' => 'template_2',
                'label' => __('Template 2'),
            ],
            [
                'value' => 'template_3',
                'label' => __('Template 3'),
            ],
        ];
    }
}
