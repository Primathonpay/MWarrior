<?php
namespace Primathonpay\MWarrior\Model;
class SavedCard extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Init model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Primathonpay\MWarrior\Model\Resource\SavedCard');
    }
}