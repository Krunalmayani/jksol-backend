<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Tbl_cron extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('tbl_cron')) {
            $this->dbforge->add_field(array(
                'cron_auto_id' => array(
                    'type' => 'INT',
                    'auto_increment' => TRUE
                ),
                'cron_command' => array(
                    'type' => 'TEXT',
                    'null' => TRUE,
                    'default' => null
                ),
                'cron_minute' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '50'
                ),
                'cron_hour' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '50'
                ),
                'cron_day' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '50'
                ),
                'cron_month' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '50'
                ),
                'cron_weekday' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '50'
                ),
                'cron_created_by' => array(
                    'type' => 'INT',
                    'null' => TRUE,
                    'default' => null
                ),
                'cron_created_at' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'default' => null
                ),
                'cron_updated_at' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'default' => null
                ),
            ));
            $this->dbforge->add_key('cron_auto_id', TRUE);
            $this->dbforge->create_table('tbl_cron');
        }
    }

    public function down()
    {
        $this->dbforge->drop_table('tbl_cron');
    }
}