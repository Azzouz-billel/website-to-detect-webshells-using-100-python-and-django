<?php

namespace craft\migrations;

use craft\db\Migration;
use craft\db\Table;
class m200211_175048_truncate_element_query_cache extends Migration
{
    public function safeUp()
    {
        $this->truncateTable(Table::TEMPLATECACHEQUERIES);
    }
}
