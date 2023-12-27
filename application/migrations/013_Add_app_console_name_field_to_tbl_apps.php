<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Add_app_console_name_field_to_tbl_apps extends CI_Migration
{
    public function up()
    {

        if (!$this->db->field_exists('app_console_name', 'tbl_apps')) {

            $fields = array(
                'app_console_name' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 200,
                    'null' => TRUE,
                    'default' => null,
                    'after' => 'app_platform'
                )
            );
            $this->dbforge->add_column('tbl_apps', $fields);
        }
    }

    public function down()
    {

    }
}