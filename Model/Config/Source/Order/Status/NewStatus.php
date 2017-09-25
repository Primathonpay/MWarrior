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

namespace Primathonpay\MWarrior\Model\Config\Source\Order\Status;

/**
 * Order Statuses source model
 * Class NewStatus
 * @package Primathonpay\MWarrior\Model\Config\Source\Order\Status
 */
class NewStatus extends \Magento\Sales\Model\Config\Source\Order\Status
{
    /**
     * @var string
     */
    protected $_stateStatuses = \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT;
}
