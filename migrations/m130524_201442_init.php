<?php

use yii\db\Schema;
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

        /**
         * CREATE TABLE IF NOT EXISTS `af` (
        `id` int(11) NOT NULL,
        `table` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
        `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
        `title` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
        `field_type` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'text',
        `description` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
        `validate_func` varchar(50) COLLATE utf8_unicode_ci DEFAULT '',
        `sql` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
        `sql_type` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'VARCHAR(255)',
        `sql_query` longtext COLLATE utf8_unicode_ci NOT NULL,
        `custom_func` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
        `size` int(11) NOT NULL DEFAULT '20',
        `default` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
        `options` longtext COLLATE utf8_unicode_ci NOT NULL,
        `cols` int(11) NOT NULL DEFAULT '20',
        `rows` int(11) NOT NULL DEFAULT '5',
        `width` int(11) NOT NULL DEFAULT '0',
        `height` int(11) NOT NULL DEFAULT '0',
        `width2` int(11) NOT NULL DEFAULT '0',
        `height2` int(11) NOT NULL DEFAULT '0',
        `display` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
        `show_time` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
        `admin_display` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'index, update, admin',
        `enable_condition` int(11) NOT NULL DEFAULT '0',
        `c_condition` longtext COLLATE utf8_unicode_ci NOT NULL,
        `c_action` longtext COLLATE utf8_unicode_ci NOT NULL,
        `c_if` longtext COLLATE utf8_unicode_ci NOT NULL,
        `c_value` longtext COLLATE utf8_unicode_ci NOT NULL,
        `c_table` longtext COLLATE utf8_unicode_ci NOT NULL,
        `c_field` longtext COLLATE utf8_unicode_ci NOT NULL,
        `c_option` longtext COLLATE utf8_unicode_ci NOT NULL,
        `c_template` longtext COLLATE utf8_unicode_ci NOT NULL,
        `c_user` longtext COLLATE utf8_unicode_ci NOT NULL,
        `ord` int(11) NOT NULL DEFAULT '1',
        `enable_workflow` int(11) NOT NULL,
        `wf_field_index` longtext COLLATE utf8_unicode_ci NOT NULL,
        `wf_field_update` longtext COLLATE utf8_unicode_ci NOT NULL,
        `wf_field_view` longtext COLLATE utf8_unicode_ci NOT NULL,
        `wf_definition_from` longtext COLLATE utf8_unicode_ci NOT NULL,
        `wf_definition_to` longtext COLLATE utf8_unicode_ci NOT NULL,
        `wf_initial` longtext COLLATE utf8_unicode_ci NOT NULL,
        `wf_definition_initial` longtext COLLATE utf8_unicode_ci NOT NULL,
        `wf_view_all` longtext COLLATE utf8_unicode_ci NOT NULL,
        `wf_view_by_owner` longtext COLLATE utf8_unicode_ci NOT NULL,
        `wf_view_audit_trail` longtext COLLATE utf8_unicode_ci NOT NULL,
        `wf_assign` longtext COLLATE utf8_unicode_ci NOT NULL,
        `wf_validate_from` longtext COLLATE utf8_unicode_ci NOT NULL,
        `wf_validate_to` longtext COLLATE utf8_unicode_ci NOT NULL,
        `wf_enable_md` longtext COLLATE utf8_unicode_ci NOT NULL,
        `wf_ignore_md` longtext COLLATE utf8_unicode_ci NOT NULL,
        `wf_fields_md` longtext COLLATE utf8_unicode_ci NOT NULL
        ) ENGINE=InnoDB AUTO_INCREMENT=549 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
         */

        // We need RBCA , audit_trail migeration too\
        //yii migrate --migrationPath=@yii/rbac/migrations
        //yii migrate --migrationPath=@sammaye/audittrail/migrations
        //change tbl_audit_trail to audit_trail


//        $this->createTable('{{%af}}', [
//            'id' => Schema::TYPE_PK,
//            'username' => Schema::TYPE_STRING . ' NOT NULL',
//            'auth_key' => Schema::TYPE_STRING . '(32) NOT NULL',
//            'password_hash' => Schema::TYPE_STRING . ' NOT NULL',
//            'password_reset_token' => Schema::TYPE_STRING,
//            'email' => Schema::TYPE_STRING . ' NOT NULL',
//
//            'status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 10',
//            'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
//            'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
//        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%af}}');
    }
}
