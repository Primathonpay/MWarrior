<?php
namespace Primathonpay\MWarrior\Model\Resource;
 
class SavedCard extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mwarrior_token', 'id');
    }
}