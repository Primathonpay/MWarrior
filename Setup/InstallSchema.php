<?php 

namespace Primathonpay\MWarrior\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $table = $installer->getConnection()
            ->newTable($installer->getTable('mwarrior_token'))
            ->addColumn('id', Table::TYPE_INTEGER, null, array(
                    'auto_increment' => true,
                    'identity'  => true,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => true
                ), 'Id')
            ->addColumn('customer_id', Table::TYPE_INTEGER, null, array(
                    'unsigned'  => true,
                    'nullable'  => true
                ), 'Customer Id')
            ->addColumn('token', Table::TYPE_TEXT, 255, array(
                ), 'Token')
            ->addColumn('order_id', Table::TYPE_INTEGER, null, array(
                    'unsigned'  => true,
                    'nullable'  => true
                ), 'Order Id')
           ->addColumn(
                'cc_number_enc',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                [],
                'Cc Number Enc'
            )->addColumn(
                'cc_exp_month',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                12,
                [],
                'Cc Exp Month'
            )->addColumn(
                'cc_exp_year',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                2,
                ['nullable' => true, 'default' => null],
                'Cc Exp Year'
            );
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}