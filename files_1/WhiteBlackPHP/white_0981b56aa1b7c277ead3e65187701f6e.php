<?php

namespace craft\migrations;

use Craft;
use craft\db\Connection;
use craft\db\Migration;
use craft\db\Table;
class m161220_000000_volumes_hasurl_notnull extends Migration
{
    public function safeUp()
    {
        if (Craft::$app->getDb()->getDriverName() === Connection::DRIVER_PGSQL) {
            $this->alterColumn(Table::VOLUMES, 'hasUrls', 'SET NOT NULL');
            $this->alterColumn(Table::VOLUMES, 'hasUrls', 'SET DEFAULT FALSE');
        } else {
            $this->alterColumn(Table::VOLUMES, 'hasUrls', $this->boolean()->defaultValue(false)->notNull());
        }
    }
    public function safeDown()
    {
        echo "m161220_000000_volumes_hasurl_notnull cannot be reverted.\n";
        return false;
    }
}
