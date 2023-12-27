<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Tbl_currency_codes extends CI_Migration
{
    public function up()
    {

        if (!$this->db->table_exists('tbl_currency_codes')) {

            $this->dbforge->add_field(array(
                'currency_code_auto_id' => array(
                    'type' => 'INT',
                    'auto_increment' => TRUE
                ),
                'currency_code' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '10',
                    'null' => TRUE,
                    'default' => null
                ),
            ));
            $this->dbforge->add_key('currency_code_auto_id', TRUE);
            $this->dbforge->create_table('tbl_currency_codes');

            // Insert Default Records
            $data = array(
                array('currency_code_auto_id' => '1', 'currency_code' => 'USD'),
                array('currency_code_auto_id' => '2', 'currency_code' => 'INR')
            );
            $this->db->insert_batch('tbl_currency_codes', $data);
        }
    }

    public function down()
    {
        $this->dbforge->drop_table('tbl_currency_codes');
    }
}