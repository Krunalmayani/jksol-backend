<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Tbl_user extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('tbl_user')) {
            $this->dbforge->add_field(array(
                'user_id' => array(
                    'type' => 'INT',
                    'auto_increment' => TRUE
                ),
                'user_email' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '100',
                    'null' => TRUE,
                    'default' => null
                ),
                'user_name' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '100',
                    'null' => TRUE,
                    'default' => null
                ),
                'user_password' => array(
                    'type' => 'TEXT',
                    'null' => TRUE,
                    'default' => null
                ),
                'user_role' => array(
                    'type' => 'TINYINT',
                    'constraint' => '4',
                    'null' => TRUE,
                    'default' => '2',
                    'comment' => '1 = Admin, 2 = Normal'
                ),
                'user_status' => array(
                    'type' => 'TINYINT',
                    'constraint' => '4',
                    'null' => TRUE,
                    'default' => '1',
                    'comment' => '0 = Inactive, 1 = Active'
                ),
                'user_added_by' => array(
                    'type' => 'INT',
                    'null' => TRUE,
                    'default' => null,
                    'comment' => 'tbl_user -> user_id'
                ),
                'user_created_at' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'default' => null
                ),
                'user_updated_at' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'default' => null
                ),
            ));
            $this->dbforge->add_key('user_id', TRUE);
            $this->dbforge->create_table('tbl_user');
        }
    }

    public function down()
    {
        $this->dbforge->drop_table('tbl_user');
    }
}