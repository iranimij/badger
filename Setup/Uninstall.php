<?php
declare(strict_types=1);

namespace Iranimij\Badger\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    private const TABLES = [
        'iranimij_badger_index',
        'iranimij_badger_tooltip',
        'iranimij_badger_visual',
        'iranimij_badger_customer_group',
        'iranimij_badger_store',
        'iranimij_badger_entity',
    ];

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();
        $connection = $setup->getConnection();

        $connection->query('SET FOREIGN_KEY_CHECKS = 0');
        foreach (self::TABLES as $table) {
            $connection->dropTable($setup->getTable($table));
        }
        $connection->query('SET FOREIGN_KEY_CHECKS = 1');

        $setup->endSetup();
    }
}
