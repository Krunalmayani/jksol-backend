<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Tbl_ad_unit_permissions extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('tbl_ad_unit_permissions')) {
            $this->dbforge->add_field(array(
                'permission_auto_id' => array(
                    'type' => 'BIGINT',
                    'constraint' => '20',
                    'auto_increment' => TRUE
                ),
                'permission_user_id' => array(
                    'type' => 'INT',
                    'null' => TRUE,
                    'default' => null,
                    'comment' => 'tbl_user -> user_id'
                ),
                'permission_given_by' => array(
                    'type' => 'INT',
                    'null' => TRUE,
                    'default' => null,
                    'comment' => 'tbl_user -> user_id'
                ),
                'permission_au_auto_id' => array(
                    'type' => 'INT',
                    'null' => TRUE,
                    'default' => null,
                    'comment' => 'tbl_ad_units -> au_auto_id'
                ),
                'permission_created_at' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'default' => null
                ),
            ));
            $this->dbforge->add_key('permission_auto_id', TRUE);
            $this->dbforge->create_table('tbl_ad_unit_permissions');
        }
    }

    public function down()
    {
        $this->dbforge->drop_table('tbl_ad_unit_permissions');
    }
}