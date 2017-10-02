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

namespace Primathonpay\MWarrior\Controller\Checkout;

/**
 * Front Controller for Checkout Method
 * it does a redirect to checkout
 * Class Index
 * @package Primathonpay\MWarrior\Controller\Checkout
 */
class Index extends \Primathonpay\MWarrior\Controller\AbstractCheckoutAction
{
    /**
     * Redirect to checkout
     *
     * @return void
     */
    public function execute()
    {
        $order = $this->getOrder();
        if (isset($order)) {
                $this->redirectToCheckoutOnePageSuccess();
        }
    }
}
