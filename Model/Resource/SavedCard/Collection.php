<?php
namespace Primathonpay\MWarrior\Model\Resource\SavedCard;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Primathonpay\MWarrior\Model\SavedCard', 'Primathonpay\MWarrior\Model\Resource\SavedCard');
    }
 
    
}