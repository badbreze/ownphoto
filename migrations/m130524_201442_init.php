<?php

use yii\db\Migration;

class m130524_201442_init extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull()->unique(),
            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'password_reset_token' => $this->string()->unique(),
            'email' => $this->string()->notNull()->unique(),
            'access_token' => $this->string(),

            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createTable('{{%access_tokens}}', [
            'access_token' => $this->string(32)->comment('Access Token'),
            'user_id' => $this->integer(11)->comment('User id'),
            'device_info' => $this->text()->null()->comment('Device info'),
            'ip' => $this->string(32)->null()->comment('IP info'),
            'location' => $this->string(255)->null()->comment('Location'),
            'fcm_token' => $this->string(255)->null()->comment('FCM Token'),
            'device_os' => $this->string(64)->null()->comment('Device OS'),
            'logout_at' => $this->dateTime()->null(),
            'logout_by' => $this->integer(11)->null(),
            'created_at' => $this->dateTime()->null(),
            'created_by' => $this->integer(11)->null(),
            'updated_at' => $this->dateTime()->null(),
            'updated_by' => $this->integer(11)->null(),
            'deleted_at' => $this->dateTime()->null(),
            'deleted_by' => $this->integer(11)->null(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%user}}');
    }
}
