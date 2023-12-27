<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Tbl_ad_unit_format extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('tbl_ad_unit_format')) {
            $this->dbforge->add_field(array(
                'au_format_auto_id' => array(
                    'type' => 'INT',
                    'auto_increment' => TRUE
                ),
                'au_format_unique_name' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '100',
                    'null' => TRUE,
                    'default' => null
                ),
                'au_format_display_name' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '100',
                    'null' => TRUE,
                    'default' => null
                )
            ));
            $this->dbforge->add_key('au_format_auto_id', TRUE);
            $this->dbforge->create_table('tbl_ad_unit_format');

            // Insert Default Records
            $data = array(
                array('au_format_auto_id' => '1', 'au_format_unique_name' => 'APP_OPEN', 'au_format_display_name' => 'App Open'),
                array('au_format_auto_id' => '2', 'au_format_unique_name' => 'BANNER', 'au_format_display_name' => 'Banner'),
                array('au_format_auto_id' => '3', 'au_format_unique_name' => 'INTERSTITIAL', 'au_format_display_name' => 'Interstitial'),
                array('au_format_auto_id' => '4', 'au_format_unique_name' => 'NATIVE', 'au_format_display_name' => 'Native'),
                array('au_format_auto_id' => '5', 'au_format_unique_name' => 'REWARDED', 'au_format_display_name' => 'Rewarded'),
                array('au_format_auto_id' => '6', 'au_format_unique_name' => 'REWARDED_INTERSTITIAL', 'au_format_display_name' => 'Rewarded Interstitial')
            );
            $this->db->insert_batch('tbl_ad_unit_format', $data);
        }
    }

    public function down()
    {
        $this->dbforge->drop_table('tbl_ad_unit_format');
    }
}