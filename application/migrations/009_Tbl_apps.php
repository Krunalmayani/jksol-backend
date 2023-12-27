<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Tbl_apps extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('tbl_apps')) {
            $this->dbforge->add_field(array(
                'app_auto_id' => array(
                    'type' => 'INT',
                    'auto_increment' => TRUE
                ),
                'app_admob_auto_id' => array(
                    'type' => 'INT',
                    'null' => TRUE,
                    'default' => null,
                    'comment' => 'tbl_admob_account -> admob_auto_id'
                ),
                'app_display_name' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '100',
                    'null' => TRUE,
                    'default' => null,
                    'comment' => 'Application Name'
                ),
                'app_store_id' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '100',
                    'null' => TRUE,
                    'default' => null,
                    'comment' => 'Package Name OR Package ID'
                ),
                'app_icon' => array(
                    'type' => 'TEXT',
                    'null' => TRUE,
                    'default' => null
                ),
                'app_admob_app_id' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '100',
                    'null' => TRUE,
                    'default' => null
                ),
                'app_platform' => array(
                    'type' => 'TINYINT',
                    'constraint' => '4',
                    'null' => TRUE,
                    'default' => '2',
                    'comment' => '1 = IOS, 2 = Android'
                ),
                'app_console_name' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 200,
                    'null' => TRUE,
                    'default' => null
                ),
                'app_approval_state' => array(
                    'type' => 'TINYINT',
                    'constraint' => '4',
                    'null' => TRUE,
                    'default' => '1',
                    'comment' => '1 = APPROVED, 2 = IN_REVIEW, 3 = ACTION_REQUIRED, 4 = APP_APPROVAL_STATE_UNSPECIFIED'
                ),
                'app_created_at' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'default' => null
                ),
                'app_updated_at' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'default' => null
                ),
            ));
            $this->dbforge->add_key('app_auto_id', TRUE);
            $this->dbforge->create_table('tbl_apps');
        }
    }

    public function down()
    {
        $this->dbforge->drop_table('tbl_apps');
    }
}