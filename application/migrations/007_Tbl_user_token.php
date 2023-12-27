<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Tbl_user_token extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('tbl_user_token')) {
            $this->dbforge->add_field(array(
                'token_auto_id' => array(
                    'type' => 'INT',
                    'auto_increment' => TRUE
                ),
                'token_user_id' => array(
                    'type' => 'INT',
                    'null' => TRUE,
                    'default' => null,
                    'comment' => 'tbl_user -> user_id'
                ),
                'user_token' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '20',
                    'null' => TRUE,
                    'default' => null
                ),
                'token_created_at' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'default' => null
                ),
            ));
            $this->dbforge->add_key('token_auto_id', TRUE);
            $this->dbforge->create_table('tbl_user_token');
        }
    }

    public function down()
    {
        $this->dbforge->drop_table('tbl_user_token');
    }
}