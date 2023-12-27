<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Tbl_settings extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('tbl_settings')) {
            $this->dbforge->add_field(array(
                'setting_id' => array(
                    'type' => 'INT',
                    'auto_increment' => TRUE
                ),
                'setting_key' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '200',
                    'null' => TRUE,
                    'default' => null
                ),
                'setting_value' => array(
                    'type' => 'TEXT',
                    'null' => TRUE,
                    'default' => null
                ),
                'setting_info' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '200',
                    'null' => TRUE,
                    'default' => null
                ),
                'created_at' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'default' => null
                ),
                'updated_at' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'default' => null
                ),
            ));
            $this->dbforge->add_key('setting_id', TRUE);
            $this->dbforge->create_table('tbl_settings');

            // Insert Default Records
            $data = array(
                array('setting_id' => '1', 'setting_key' => 'CRON_FETCH_APP_LIST', 'setting_value' => '0', 'setting_info' => 'primary admob_auto_id', 'created_at' => '2023-03-27 12:30:19', 'updated_at' => '2023-11-06 15:20:56'),
                array('setting_id' => '2', 'setting_key' => 'CRON_FETCH_APP_AD_UNITS', 'setting_value' => '0', 'setting_info' => 'primary admob_auto_id', 'created_at' => '2023-03-27 12:30:19', 'updated_at' => '2023-11-06 15:33:48'),
                array('setting_id' => '3', 'setting_key' => 'CRON_FETCH_APP_NETWORK_REPORT', 'setting_value' => '2', 'setting_info' => 'primary app_auto_id', 'created_at' => '2023-03-27 12:30:19', 'updated_at' => '2023-12-07 12:10:40'),
                array('setting_id' => '4', 'setting_key' => 'CRON_SEND_MAIL_REPORT_DAILY', 'setting_value' => '0', 'setting_info' => 'primary report_auto_id', 'created_at' => '2023-03-27 12:30:19', 'updated_at' => '2023-07-19 11:36:59'),
                array('setting_id' => '5', 'setting_key' => 'CRON_SEND_MAIL_REPORT_WEEKLY', 'setting_value' => '0', 'setting_info' => 'primary report_auto_id', 'created_at' => '2023-03-27 12:30:19', 'updated_at' => '2023-07-17 23:59:01'),
                array('setting_id' => '6', 'setting_key' => 'CRON_SEND_MAIL_REPORT_MONTHLY', 'setting_value' => '0', 'setting_info' => 'primary report_auto_id', 'created_at' => '2023-03-27 12:30:19', 'updated_at' => '2023-07-01 23:59:01'),
                array('setting_id' => '7', 'setting_key' => 'CRON_SEND_MAIL_REPORT_EVERY_3_MONTH', 'setting_value' => '0', 'setting_info' => 'primary report_auto_id', 'created_at' => '2023-03-27 12:30:19', 'updated_at' => '2023-07-01 23:59:01'),
                array('setting_id' => '8', 'setting_key' => 'CRON_SEND_MAIL_REPORT_EVERY_6_MONTH', 'setting_value' => '0', 'setting_info' => 'primary report_auto_id', 'created_at' => '2023-03-27 12:30:19', 'updated_at' => '2023-07-01 23:59:01'),
                array('setting_id' => '9', 'setting_key' => 'CRON_SEND_MAIL_REPORT_EVERY_YEAR', 'setting_value' => '0', 'setting_info' => 'primary report_auto_id', 'created_at' => '2023-03-27 12:30:19', 'updated_at' => '2023-06-14 09:05:01')
            );
            $this->db->insert_batch('tbl_settings', $data);
        }
    }

    public function down()
    {
        $this->dbforge->drop_table('tbl_settings');
    }
}