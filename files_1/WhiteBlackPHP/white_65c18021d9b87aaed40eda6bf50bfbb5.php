<?php

namespace craft\migrations;

use craft\db\Migration;
use craft\db\Table;
use craft\helpers\MigrationHelper;
class m180824_193422_case_sensitivity_fixes extends Migration
{
    public function safeUp()
    {
        MigrationHelper::dropIndexIfExists(Table::ELEMENTS_SITES, ['uri', 'siteId'], true, $this);
        MigrationHelper::dropIndexIfExists(Table::USERS, ['email'], true, $this);
        MigrationHelper::dropIndexIfExists(Table::USERS, ['username'], true, $this);
        if ($this->db->getIsMysql()) {
            $this->createIndex(null, Table::ELEMENTS_SITES, ['uri', 'siteId']);
            $this->createIndex(null, Table::USERS, ['email']);
            $this->createIndex(null, Table::USERS, ['username']);
        } else {
            $this->createIndex(null, Table::ELEMENTS_SITES, ['lower([[uri]])', 'siteId']);
            $this->createIndex(null, Table::USERS, ['lower([[email]])']);
            $this->createIndex(null, Table::USERS, ['lower([[username]])']);
        }
    }
    public function safeDown()
    {
        echo "m180824_193422_case_sensitivity_fixes cannot be reverted.\n";
        return false;
    }
}
