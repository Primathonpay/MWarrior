<?php
/*
 * Copyright (C) 2017 primathon
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @author      primathon
 * @copyright   2017 primathon
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPL-2.0)
 */

namespace Primathonpay\MWarrior\Model\Config\Source\Locale\Currency;

/**
 * Locale currency source
 * Class Currency
 * @package Primathonpay\MWarrior\Model\Config\Source\Locale\Currency
 */
class Currency extends \Magento\Config\Model\Config\Source\Locale\Currency
{
    /**
     * @var \Primathonpay\MWarrior\Helper\Data
     */
    protected $_moduleHelper;

    /**
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param \Primathonpay\MWarrior\Helper\Data $moduleHelper
     */
    public function __construct(
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Primathonpay\MWarrior\Helper\Data $moduleHelper
    ) {
        parent::__construct($localeLists);
        $this->_moduleHelper = $moduleHelper;
    }

    /**
     * Get an Instance of the Module Helper
     * @return \Primathonpay\MWarrior\Helper\Data
     */
    protected function getModuleHelper()
    {
        return $this->_moduleHelper;
    }

    /**
     * Builds the options array for the MultiSelect control in the Admin Zone
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();

        return $this->getModuleHelper()->getGlobalAllowedCurrenciesOptions($options);
    }
}
