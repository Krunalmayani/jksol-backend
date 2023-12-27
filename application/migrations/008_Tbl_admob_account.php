<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Tbl_admob_account extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('tbl_admob_account')) {
            $this->dbforge->add_field(array(
                'admob_auto_id' => array(
                    'type' => 'INT',
                    'auto_increment' => TRUE
                ),
                'admob_added_by' => array(
                    'type' => 'INT',
                    'null' => TRUE,
                    'default' => null,
                    'comment' => 'tbl_user -> user_id'
                ),
                'admob_email' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '100',
                    'null' => TRUE,
                    'default' => null
                ),
                'admob_pub_id' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '100',
                    'null' => TRUE,
                    'default' => null
                ),
                'admob_access_token' => array(
                    'type' => 'TEXT',
                    'null' => TRUE,
                    'default' => null
                ),
                'admob_currency_code' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '20',
                    'null' => TRUE,
                    'default' => 'USD'
                ),
                'admob_created_at' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'default' => null
                ),
                'admob_updated_at' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'default' => null
                ),
            ));
            $this->dbforge->add_key('admob_auto_id', TRUE);
            $this->dbforge->create_table('tbl_admob_account');
        }
    }

    public function down()
    {
        $this->dbforge->drop_table('tbl_admob_account');
    }
}