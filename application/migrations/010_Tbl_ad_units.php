<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Tbl_ad_units extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('tbl_ad_units')) {
            $this->dbforge->add_field(array(
                'au_auto_id' => array(
                    'type' => 'INT',
                    'auto_increment' => TRUE
                ),
                'au_app_auto_id' => array(
                    'type' => 'INT',
                    'null' => TRUE,
                    'default' => null,
                    'comment' => 'tbl_apps -> app_auto_id'
                ),
                'au_display_name' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '100',
                    'null' => TRUE,
                    'default' => null
                ),
                'au_id' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '100',
                    'null' => TRUE,
                    'default' => null,
                    'comment' => 'Unique AD UNIT ID'
                ),
                'au_format_auto_id' => array(
                    'type' => 'INT',
                    'null' => TRUE,
                    'default' => null,
                    'comment' => 'tbl_ad_unit_format -> au_format_auto_id'
                ),
                'au_created_at' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'default' => null
                ),
                'au_updated_at' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'default' => null
                ),
            ));
            $this->dbforge->add_key('au_auto_id', TRUE);
            $this->dbforge->create_table('tbl_ad_units');
        }
    }

    public function down()
    {
        $this->dbforge->drop_table('tbl_ad_units');
    }
}