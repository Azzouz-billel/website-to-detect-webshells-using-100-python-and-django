<?php

namespace craft\migrations;

use craft\db\Migration;
class m161007_130653_update_email_settings extends Migration
{
    public function safeUp()
    {
        $this->replace('{{%systemsettings}}', 'settings', 'daptor', 'dapter', ['category' => 'email']);
    }
    public function safeDown()
    {
        echo "m161007_130653_update_email_settings cannot be reverted.\n";
        return false;
    }
}
