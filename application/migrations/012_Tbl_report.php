<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Tbl_report extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('tbl_report')) {
            $this->dbforge->add_field(array(
                'report_id' => array(
                    'type' => 'INT',
                    'auto_increment' => TRUE
                ),
                'report_au_auto_id' => array(
                    'type' => 'INT',
                    'null' => TRUE,
                    'default' => null,
                    'comment' => 'tbl_ad_units -> au_auto_id'
                ),
                'report_country_auto_id' => array(
                    'type' => 'INT',
                    'null' => TRUE,
                    'default' => null,
                    'comment' => 'tbl_country -> country_auto_id'
                ),
                'report_currency_code_auto_id' => array(
                    'type' => 'INT',
                    'null' => TRUE,
                    'default' => null,
                    'comment' => 'tbl_currency_code -> report_currency_code_auto_id'
                ),
                'report_estimated_earnings' => array(
                    'type' => 'DOUBLE',
                    'null' => TRUE,
                    'default' => null
                ),
                'report_observed_ecpm' => array(
                    'type' => 'DOUBLE',
                    'null' => TRUE,
                    'default' => null,
                    'comment' => 'impression RPM'
                ),
                'report_ad_requests' => array(
                    'type' => 'BIGINT',
                    'constraint' => '20',
                    'null' => TRUE,
                    'default' => '0'
                ),
                'report_match_rate' => array(
                    'type' => 'DOUBLE',
                    'constraint' => '6,2',
                    'null' => TRUE,
                    'default' => null
                ),
                'report_matched_requests' => array(
                    'type' => 'BIGINT',
                    'constraint' => '20',
                    'null' => TRUE,
                    'default' => '0'
                ),
                'report_show_rate' => array(
                    'type' => 'DOUBLE',
                    'constraint' => '6,2',
                    'null' => TRUE,
                    'default' => null
                ),
                'report_impressions' => array(
                    'type' => 'BIGINT',
                    'constraint' => '20',
                    'null' => TRUE,
                    'default' => '0'
                ),
                'report_impression_ctr' => array(
                    'type' => 'DOUBLE',
                    'constraint' => '6,2',
                    'null' => TRUE,
                    'default' => null
                ),
                'report_clicks' => array(
                    'type' => 'BIGINT',
                    'constraint' => '20',
                    'null' => TRUE,
                    'default' => '0'
                ),
                'report_date' => array(
                    'type' => 'DATE',
                    'null' => TRUE,
                    'default' => null
                ),
                'report_created_at' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'default' => null
                ),
                'report_updated_at' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'default' => null
                ),
            ));
            $this->dbforge->add_key('report_id', TRUE);
            $this->dbforge->create_table('tbl_report');
        }
    }

    public function down()
    {
        $this->dbforge->drop_table('tbl_report');
    }
}