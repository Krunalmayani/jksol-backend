<?php

class Mdl_datatable extends CI_Model
{

    function get_admob_account_list($option, $is_count = 0, $start = '', $limit = '')
    {
        $search = isset($_POST['sSearch']) ? $_POST['sSearch'] : "";

        if ($search != "") {

            $search = str_replace("'", '', $_POST['sSearch']);

            $this->db->where("(t1.admob_email LIKE '%" . $search . "%'"
                . " OR t1.admob_pub_id LIKE '%" . $search . "%' "
                . " OR t1.admob_created_at LIKE '%" . $search . "%' "
                . " OR t1.admob_updated_at LIKE '%" . $search . "%' "
                . ")");
        }

        $sortColumn = isset($_POST['iSortCol_0']) ? $_POST['iSortCol_0'] : "";
        $sSortDir_0 = isset($_POST['sSortDir_0']) ? $_POST['sSortDir_0'] : 'DESC';

        switch ($sortColumn) {
            case 0:
                $this->db->order_by("t1.admob_auto_id", $sSortDir_0);
                break;
            case 1:
                $this->db->order_by("t1.admob_email", $sSortDir_0);
                break;
            case 2:
                $this->db->order_by("t1.admob_pub_id", $sSortDir_0);
                break;
            case 3:
                $this->db->order_by("t1.admob_created_at", $sSortDir_0);
                break;
            case 4:
                $this->db->order_by("t1.admob_updated_at", $sSortDir_0);
                break;
            default:
                $this->db->order_by("t1.admob_auto_id", "DESC");
        }

        $this->db->select('t1.*');
        $this->db->from('tbl_admob_account as t1');

        if (isset($option['where']) && !empty($option['where'])) {
            $this->db->where($option['where']);
        }

        if ($is_count) {
            $res = $this->db->get()->num_rows();
        } else {

            $this->db->limit($limit, $start);
            $res = $this->db->get()->result();
        }

        return $res;
    }

    function get_apps_list($option, $is_count = 0, $start = '', $limit = '')
    {
        $search = isset($_POST['sSearch']) ? $_POST['sSearch'] : "";

        if ($search != "") {

            $search = str_replace("'", '', $_POST['sSearch']);

            $this->db->where("(t1.app_display_name LIKE '%" . $search . "%'"
                . " OR t1.app_store_id LIKE '%" . $search . "%' "
                . " OR t1.app_admob_app_id LIKE '%" . $search . "%' "
                . " OR t1.app_created_at LIKE '%" . $search . "%' "
                . " OR t1.app_updated_at LIKE '%" . $search . "%' "
                . " OR t2.admob_email LIKE '%" . $search . "%' "
                . ")");
        }

        $sortColumn = isset($_POST['iSortCol_0']) ? $_POST['iSortCol_0'] : "";
        $sSortDir_0 = isset($_POST['sSortDir_0']) ? $_POST['sSortDir_0'] : 'DESC';

        switch ($sortColumn) {
            case 0:
                $this->db->order_by("t1.app_auto_id", $sSortDir_0);
                break;
            case 1:
                $this->db->order_by("t1.app_display_name", $sSortDir_0);
                break;
            case 2:
                $this->db->order_by("t1.app_admob_app_id", $sSortDir_0);
                break;
            case 3:
                $this->db->order_by("t1.app_approval_state", $sSortDir_0);
                break;
            case 4:
                $this->db->order_by("t1.app_store_id", $sSortDir_0);
                break;
            default:
                $this->db->order_by("t1.app_auto_id", "DESC");
        }

        $this->db->select('t1.*,t2.admob_email');
        $this->db->from('tbl_apps as t1');
        $this->db->join('tbl_admob_account as t2', 't2.admob_auto_id = t1.app_admob_auto_id');
        $this->db->join('tbl_ad_units as t3', 't3.au_app_auto_id = t1.app_auto_id');

        if ($option['user_info']['user_role'] != 1) {
            $this->db->join('tbl_ad_unit_permissions as t4', 't4.permission_au_auto_id = t3.au_auto_id');
            $this->db->where("t4.permission_user_id", $option['user_info']['user_id']);
        }

        if (isset($option['where']) && !empty($option['where'])) {
            $this->db->where($option['where']);
        }

        $this->db->group_by('t1.app_auto_id');

        if ($is_count) {
            $res = $this->db->get()->num_rows();
        } else {

            $this->db->limit($limit, $start);
            $res = $this->db->get()->result();
        }

        return $res;
    }

    function get_report_list($option, $is_count = 0, $start = '', $limit = '')
    {
        $search = isset($_POST['sSearch']) ? $_POST['sSearch'] : "";

        if ($search != "") {

            $search = str_replace("'", '', $_POST['sSearch']);

            $this->db->where("(t1.report_name LIKE '%" . $search . "%'"
                . " OR t1.report_send_to_email LIKE '%" . $search . "%' "
                . " OR t1.report_cc_email LIKE '%" . $search . "%' "
                . " OR t1.report_created_at LIKE '%" . $search . "%' "
                . " OR t1.report_updated_at LIKE '%" . $search . "%' "
                . " OR t2.user_name LIKE '%" . $search . "%' "
                . " OR t3.app_display_name LIKE '%" . $search . "%' "
                . ")");
        }

        $sortColumn = isset($_POST['iSortCol_0']) ? $_POST['iSortCol_0'] : "";
        $sSortDir_0 = isset($_POST['sSortDir_0']) ? $_POST['sSortDir_0'] : 'DESC';

        switch ($sortColumn) {
            case 0:
                $this->db->order_by("t1.report_auto_id", $sSortDir_0);
                break;
            case 1:
                $this->db->order_by("t1.report_name", $sSortDir_0);
                break;
            case 2:
                $this->db->order_by("t3.app_display_name", $sSortDir_0);
                break;
            case 3:
                $this->db->order_by("t1.report_range_type", $sSortDir_0);
                break;
            case 4:
                $this->db->order_by("t1.report_schedule", $sSortDir_0);
                break;
            case 7:
                $this->db->order_by("t2.user_name", $sSortDir_0);
                break;
            case 8:
                $this->db->order_by("t1.report_created_at", $sSortDir_0);
                break;
            case 9:
                $this->db->order_by("t1.report_updated_at", $sSortDir_0);
                break;
            default:
                $this->db->order_by("t1.report_auto_id", "DESC");
        }

        $this->db->select('t1.*,t2.user_name,t3.app_display_name, t3.app_store_id');
        $this->db->from('tbl_report_send as t1');
        $this->db->join('tbl_user as t2', 't2.user_id = t1.report_created_by');
        $this->db->join('tbl_apps as t3', 't3.app_auto_id = t1.report_app_auto_id');
        $this->db->where('t1.report_status', '1');

        if (isset($option['where']) && !empty($option['where'])) {
            $this->db->where($option['where']);
        }

        if ($is_count) {
            $res = $this->db->get()->num_rows();
        } else {

            $this->db->limit($limit, $start);
            $res = $this->db->get()->result();
        }

        return $res;
    }

    function get_analytics_list($option, $is_count = 0, $start = '', $limit = '')
    {

        // $is_count = 0: Return result, 1: Return total record counter, 2: Return summation of total records

        $where = "";
        $search = isset($_POST['sSearch']) ? $_POST['sSearch'] : "";
        if ($search != "") {

            $search = str_replace(array("'", '"'), '', $_POST['sSearch']);

            if ($where) {
                $where .= " AND ";
            }

            $where .= " (t1.report_date LIKE '%" . $search . "%'"
                . " OR t1.report_estimated_earnings LIKE '%" . $search . "%' "
                . " OR t1.report_impressions LIKE '%" . $search . "%' "
                . " OR t1.report_ad_requests LIKE '%" . $search . "%' "
                . " OR t1.report_matched_requests LIKE '%" . $search . "%' "
                . " OR t1.report_clicks LIKE '%" . $search . "%' "
                . " OR t3.app_display_name LIKE '%" . $search . "%' "
                . " OR t2.au_display_name LIKE '%" . $search . "%' "
                . ") ";
        }

        // custom search parameters
        $app_display_name = isset($_POST['app_display_name']) ? $_POST['app_display_name'] : "";
        $au_display_name = isset($_POST['au_display_name']) ? $_POST['au_display_name'] : "";
        $report_date = isset($_POST['report_date']) ? $_POST['report_date'] : "";
        $estimated_earnings = isset($_POST['estimated_earnings']) ? $_POST['estimated_earnings'] : "";
        $observed_ecpm = isset($_POST['observed_ecpm']) ? $_POST['observed_ecpm'] : "";
        $ad_requests = isset($_POST['ad_requests']) ? $_POST['ad_requests'] : "";
        $match_rate = isset($_POST['match_rate']) ? $_POST['match_rate'] : "";
        $matched_requests = isset($_POST['matched_requests']) ? $_POST['matched_requests'] : "";
        $show_rate = isset($_POST['show_rate']) ? $_POST['show_rate'] : "";
        $impressions = isset($_POST['impressions']) ? $_POST['impressions'] : "";
        $impression_ctr = isset($_POST['impression_ctr']) ? $_POST['impression_ctr'] : "";
        $clicks = isset($_POST['clicks']) ? $_POST['clicks'] : "";
        $country_name = isset($_POST['country_name']) ? $_POST['country_name'] : "";
        $ad_unit_format = isset($_POST['ad_unit_format']) ? strtoupper(str_replace(" ", "_", $_POST['ad_unit_format'])) : "";
        $selected_ad_format = isset($_POST['selected_ad_format']) ? $_POST['selected_ad_format'] : "";
        $selected_country = isset($_POST['selected_country']) ? $_POST['selected_country'] : "";
        $selected_apps = isset($_POST['selected_apps']) ? $_POST['selected_apps'] : "";
        $selected_app_platform = isset($_POST['selected_app_platform']) ? $_POST['selected_app_platform'] : "";
        $selected_ad_units = isset($_POST['$selected_ad_units']) ? $_POST['$selected_ad_units'] : "";

        $having_total_count = '';

        // if ($ad_unit_format) {
        //     if ($where) {
        //         $where .= " AND ";
        //     }
        //     $where .= " t2.ad_unit_format LIKE '%$ad_unit_format%' ";
        // }

        if ($selected_ad_format) {
            if ($where) {
                $where .= " AND ";
            }
            $where .= " t2.au_format_auto_id IN ($selected_ad_format) ";
        }

        if ($selected_country) {
            if ($where) {
                $where .= " AND ";
            }
            $where .= " t1.report_country_auto_id IN ($selected_country) ";
        }

        if ($selected_apps) {
            if ($where) {
                $where .= " AND ";
            }
            $where .= " t3.app_auto_id IN ($selected_apps) ";
        }

        if ($selected_ad_units) {
            if ($where) {
                $where .= " AND ";
            }
            $where .= " t2.au_auto_id IN ($selected_ad_units) ";
        }

        if ($selected_app_platform) {
            if ($where) {
                $where .= " AND ";
            }
            $where .= " t3.app_platform IN ($selected_app_platform) ";
        }

        if ($country_name) {
            if ($where) {
                $where .= " AND ";
            }
            // $where .= " t5.country_name LIKE '%$country_name%' ";
            $where .= " t5.country_alpha2_code IN ($country_name) ";
        }

        if ($app_display_name) {
            if ($where) {
                $where .= " AND ";
            }
            $where .= " t3.app_display_name LIKE '%$app_display_name%' ";
        }

        if ($au_display_name) {
            if ($where) {
                $where .= " AND ";
            }
            $where .= " t2.au_display_name LIKE '%$au_display_name%' ";
        }

        if ($report_date) {
            // $date_parts = explode("/", $report_date);
            // $report_date = (isset($date_parts[2]) ? $date_parts[2] . '-' : "") . (isset($date_parts[1]) ? $date_parts[1] . '-' : "") . (isset($date_parts[0]) ? $date_parts[0] . '-' : "");
            if ($where) {
                $where .= " AND ";
            }
            $where .= " t1.report_date LIKE '%$report_date%' ";
        }

        if ($estimated_earnings) {

            if ($having_total_count) {
                $having_total_count .= " AND ";
            }
            $having_total_count .= " total_estimated_earnings LIKE '%$estimated_earnings%' ";
        }

        if ($observed_ecpm) {
            if ($having_total_count) {
                $having_total_count .= " AND ";
            }
            $having_total_count .= " total_observed_ecpm LIKE '%$observed_ecpm%' ";
        }

        if ($ad_requests) {
            if ($having_total_count) {
                $having_total_count .= " AND ";
            }
            $having_total_count .= " total_ad_requests LIKE '%$ad_requests%' ";
        }

        if ($match_rate) {
            if ($having_total_count) {
                $having_total_count .= " AND ";
            }
            $having_total_count .= " total_match_rate LIKE '%$match_rate%' ";
        }

        if ($matched_requests) {
            if ($having_total_count) {
                $having_total_count .= " AND ";
            }
            $having_total_count .= " total_matched_requests LIKE '%$matched_requests%' ";
        }

        if ($show_rate) {
            if ($having_total_count) {
                $having_total_count .= " AND ";
            }
            $having_total_count .= " total_show_rate LIKE '%$show_rate%' ";
        }

        if ($impressions) {
            if ($having_total_count) {
                $having_total_count .= " AND ";
            }
            $having_total_count .= " total_impressions LIKE '%$impressions%' ";
        }

        if ($impression_ctr) {
            if ($having_total_count) {
                $having_total_count .= " AND ";
            }
            $having_total_count .= " total_impression_ctr LIKE '%$impression_ctr%' ";
        }

        if ($clicks) {
            if ($having_total_count) {
                $having_total_count .= " AND ";
            }
            $having_total_count .= " total_clicks LIKE '%$clicks%' ";
        }

        if (isset($option['where']) && !empty($option['where'])) {
            if ($where) {
                $where .= " AND ";
            }
            $where .= $option['where'];
        }

        if ($is_count == 0) {
            $select = "FORMAT(SUM(t1.report_estimated_earnings),2) as total_estimated_earnings,
            FORMAT(((SUM(t1.report_estimated_earnings)/SUM(t1.report_impressions))*1000),2) as total_observed_ecpm,";
        } else {
            $select = "SUM(t1.report_estimated_earnings) as total_estimated_earnings,
            ((SUM(t1.report_estimated_earnings)/SUM(t1.report_impressions))*1000) as total_observed_ecpm,";
        }

        $query = "SELECT $select t1.report_date,
        FORMAT(((SUM(t1.report_matched_requests)/SUM(t1.report_ad_requests))*100),2) as total_match_rate,
        FORMAT(((SUM(t1.report_impressions)/SUM(t1.report_matched_requests))*100),2) as total_show_rate,
        SUM(t1.report_ad_requests) as total_ad_requests,
        SUM(t1.report_matched_requests) as total_matched_requests,
        SUM(t1.report_impressions) as total_impressions,
        FORMAT(((SUM(t1.report_clicks)/SUM(t1.report_impressions))*100),2) as total_impression_ctr,
        SUM(t1.report_clicks) as total_clicks,
        t2.au_display_name,t3.app_display_name,t3.app_icon,t3.app_platform,t3.app_store_id,t4.admob_currency_code,t2.au_format_auto_id,t5.country_name 
        FROM tbl_report as t1 
        JOIN tbl_ad_units as t2 ON t2.au_auto_id = t1.report_au_auto_id 
        JOIN tbl_apps as t3 ON t3.app_auto_id = t2.au_app_auto_id 
        JOIN tbl_admob_account as t4 ON t4.admob_auto_id = t3.app_admob_auto_id 
        JOIN tbl_country as t5 ON t5.country_auto_id = t1.report_country_auto_id";

        if ($option['user_info']['user_role'] != 1) {
            $query .= " JOIN tbl_ad_unit_permissions as t6 ON t6.permission_au_auto_id = t2.au_auto_id ";
        }

        $query .= " WHERE $where ";

        if (isset($option['group_by']) && !empty($option['group_by'])) {
            $query .= " GROUP BY " . $option['group_by'] . " ";
        }

        if ($having_total_count) {
            $query .= " HAVING $having_total_count ";
        }

        if ($is_count == 0) { // Return results

            // $sortColumn = isset($_POST['iSortCol_0']) ? $_POST['iSortCol_0'] : "";
            $order_by_key_name = isset($_POST['iSortCol_0']) ? $_POST['iSortCol_0'] : "";
            $sSortDir_0 = isset($_POST['sSortDir_0']) ? $_POST['sSortDir_0'] : 'DESC';

            // $order_by_key_name = isset($_POST["mDataProp_" . $sortColumn]) ? $_POST["mDataProp_" . $sortColumn] : '';

            $order_by = "";
            if ($order_by_key_name) {

                switch ($order_by_key_name) {
                    case "app_display_name":
                        $order_by = "t3.app_display_name";
                        break;
                    case "au_display_name":
                        $order_by = "t2.au_display_name";
                        break;
                    case "au_format_auto_id":
                        $order_by = "t2.au_format_auto_id";
                        break;
                    case "country_name":
                        $order_by = "t5.country_name";
                        break;
                    case "report_date":
                        $order_by = "t1.report_date";
                        break;
                    case "estimated_earnings":
                        $order_by = "total_estimated_earnings";
                        break;
                    case "observed_ecpm":
                        $order_by = "total_observed_ecpm";
                        break;
                    case "ad_requests":
                        $order_by = "total_ad_requests";
                        break;
                    case "report_match_rate":
                        $order_by = "t1.report_match_rate";
                        break;
                    case "matched_requests":
                        $order_by = "total_matched_requests";
                        break;
                    case "show_rate":
                        $order_by = "total_show_rate";
                        break;
                    case "impressions":
                        $order_by = "total_impressions";
                        break;
                    case "impression_ctr":
                        $order_by = "total_impression_ctr";
                        break;
                    case "clicks":
                        $order_by = "total_clicks";
                        break;
                }
            }

            if ($order_by) {
                $query .= " ORDER BY $order_by $sSortDir_0";
            } else {
                $query .= "ORDER BY t3.app_display_name ASC,t1.report_date ASC";
            }
            $query .= " LIMIT $start,$limit ";
            // echo $query;
            // exit;
            $res = $this->db->query($query)->result();
            return $res;
        } elseif ($is_count == 1) { // Return total record counter
            // echo $query;
            // exit;
            $res = $this->db->query($query)->num_rows();
            return $res;
        } elseif ($is_count == 2) { // Return summation of total records

            $res = $this->db->query($query)->result_array();

            if ($res) {

                $total_summary = array(
                    'total_estimated_earnings' => 0.00,
                    'total_observed_ecpm' => 0.00,
                    'total_ad_requests' => 0,
                    'total_match_rate' => 0.00,
                    'total_matched_requests' => 0,
                    'total_show_rate' => 0.00,
                    'total_impressions' => 0,
                    'total_impression_ctr' => 0.00,
                    'total_clicks' => 0,
                    'admob_currency_code' => $res[0]['admob_currency_code']
                );

                $total_observed_ecpm_counter = 0;
                $total_match_rate_counter = 0;
                $total_impression_ctr_counter = 0;

                foreach ($res as $single) {

                    foreach ($single as $key => $value) {
                        switch ($key) {
                            case "total_estimated_earnings":
                                $total_summary['total_estimated_earnings'] += $value;
                                break;
                            case "total_observed_ecpm":

                                if ($value > 0) {
                                    $total_summary['total_observed_ecpm'] += $value;
                                    $total_observed_ecpm_counter++;
                                }
                                break;
                            case "total_ad_requests":
                                $total_summary['total_ad_requests'] += $value;
                                break;
                            case "total_match_rate":

                                if ($value > 0) {
                                    $total_summary['total_match_rate'] += $value;
                                    $total_match_rate_counter++;
                                }
                                break;
                            case "total_matched_requests":
                                $total_summary['total_matched_requests'] += $value;
                                break;
                            case "total_show_rate":
                                $total_summary['total_show_rate'] += $value;
                                break;
                            case "total_impressions":
                                $total_summary['total_impressions'] += $value;
                                break;
                            case "total_impression_ctr":
                                if ($value > 0) {
                                    $total_summary['total_impression_ctr'] += $value;
                                    $total_impression_ctr_counter++;
                                }
                                break;
                            case "total_clicks":
                                $total_summary['total_clicks'] += $value;
                                break;
                        }
                    }
                }

                if ($total_summary['total_estimated_earnings'] > 0) {
                    $total_summary['total_observed_ecpm'] = number_format((float) (($total_summary['total_estimated_earnings'] / $total_summary['total_impressions']) * 1000), 2, '.', '');
                }

                if ($total_summary['total_matched_requests'] > 0) {
                    $total_summary['total_match_rate'] = number_format((float) (($total_summary['total_matched_requests'] / $total_summary['total_ad_requests']) * 100), 2, '.', '');
                }

                if ($total_summary['total_clicks'] > 0) {
                    $total_summary['total_impression_ctr'] = number_format((float) (($total_summary['total_clicks'] / $total_summary['total_impressions']) * 100), 2, '.', '');
                }

                if (!empty($total_summary['total_impressions']) && !empty($total_summary['total_matched_requests'])) {
                    $total_summary['total_show_rate'] = ($total_summary['total_impressions'] / $total_summary['total_matched_requests']) * 100;
                }

                $total_summary['total_estimated_earnings'] = number_format((float) $total_summary['total_estimated_earnings'], 2, '.', '');

                return $total_summary;
            } else {

                $total_summary = array(
                    'total_estimated_earnings' => '',
                    'total_observed_ecpm' => '',
                    'total_ad_requests' => '',
                    'total_match_rate' => '',
                    'total_matched_requests' => '',
                    'total_show_rate' => '',
                    'total_impressions' => '',
                    'total_impression_ctr' => '',
                    'total_clicks' => '',
                    'admob_currency_code' => 'USD'
                );
                return $total_summary;
            }
        }
    }

    function get_user_list($option, $is_count = 0, $start = '', $limit = '')
    {
        $search = isset($_POST['sSearch']) ? $_POST['sSearch'] : "";

        if ($search != "") {

            $search = str_replace("'", '', $_POST['sSearch']);

            $this->db->where("(t1.user_name LIKE '%" . $search . "%'"
                . " OR t1.user_email LIKE '%" . $search . "%' "
                . " OR t1.user_created_at LIKE '%" . $search . "%' "
                . " OR t1.user_updated_at LIKE '%" . $search . "%' "
                . ")");
        }

        $sortColumn = isset($_POST['iSortCol_0']) ? $_POST['iSortCol_0'] : "";
        $sSortDir_0 = isset($_POST['sSortDir_0']) ? $_POST['sSortDir_0'] : 'DESC';

        switch ($sortColumn) {
            case 0:
                $this->db->order_by("t1.user_id", $sSortDir_0);
                break;
            case 1:
                $this->db->order_by("t1.user_name", $sSortDir_0);
                break;
            case 2:
                $this->db->order_by("t1.user_email", $sSortDir_0);
                break;
            case 3:
                $this->db->order_by("t1.user_role", $sSortDir_0);
                break;
            case 4:
                $this->db->order_by("t1.user_status", $sSortDir_0);
                break;
            case 5:
                $this->db->order_by("t1.user_created_at", $sSortDir_0);
                break;
            case 6:
                $this->db->order_by("t1.user_updated_at", $sSortDir_0);
                break;
            default:
                $this->db->order_by("t1.user_id", "DESC");
        }

        $this->db->select('t1.*');
        $this->db->from('tbl_user as t1');

        if (isset($option['where']) && !empty($option['where'])) {
            $this->db->where($option['where']);
        }

        if ($is_count) {
            $res = $this->db->get()->num_rows();
        } else {

            $this->db->limit($limit, $start);
            $res = $this->db->get()->result();
        }

        return $res;
    }

    function get_cron_list($option, $is_count = 0, $start = '', $limit = '')
    {
        $search = isset($_POST['sSearch']) ? $_POST['sSearch'] : "";

        if ($search != "") {

            $search = str_replace("'", '', $_POST['sSearch']);

            $this->db->where("(t1.cron_command LIKE '%" . $search . "%'"
                . " OR t1.cron_minute LIKE '%" . $search . "%' "
                . " OR t1.cron_hour LIKE '%" . $search . "%' "
                . " OR t1.cron_day LIKE '%" . $search . "%' "
                . " OR t1.cron_month LIKE '%" . $search . "%' "
                . " OR t1.cron_weekday LIKE '%" . $search . "%' "
                . " OR t1.cron_created_by LIKE '%" . $search . "%' "
                . " OR t1.cron_created_at LIKE '%" . $search . "%' "
                . " OR t1.cron_updated_at LIKE '%" . $search . "%' "
                . ")");
        }

        $sortColumn = isset($_POST['iSortCol_0']) ? $_POST['iSortCol_0'] : "";
        $sSortDir_0 = isset($_POST['sSortDir_0']) ? $_POST['sSortDir_0'] : 'DESC';

        switch ($sortColumn) {
            case 0:
                $this->db->order_by("t1.cron_auto_id", $sSortDir_0);
                break;
            case 1:
                $this->db->order_by("t1.cron_command", $sSortDir_0);
                break;
            case 2:
                $this->db->order_by("t1.cron_created_at", $sSortDir_0);
                break;
            case 3:
                $this->db->order_by("t1.cron_updated_at", $sSortDir_0);
                break;
            default:
                $this->db->order_by("t1.cron_auto_id", "DESC");
        }

        $this->db->select('t1.*,t2.user_name');
        $this->db->from('tbl_cron as t1');
        $this->db->join('tbl_user as t2', 't2.user_id = t1.cron_created_by');

        if (isset($option['where']) && !empty($option['where'])) {
            $this->db->where($option['where']);
        }

        if ($is_count) {
            $res = $this->db->get()->num_rows();
        } else {

            $this->db->limit($limit, $start);
            $res = $this->db->get()->result();
        }

        return $res;
    }

    function get_permission_apps_list($option, $is_count = 0, $start = '', $limit = '')
    {
        $search = isset($_POST['sSearch']) ? $_POST['sSearch'] : "";

        if ($search != "") {

            $search = str_replace("'", '', $_POST['sSearch']);

            $this->db->where("(t3.app_display_name LIKE '%" . $search . "%'"
                . " OR t3.app_store_id LIKE '%" . $search . "%' "
                . " OR t3.app_admob_app_id LIKE '%" . $search . "%' "
                . " OR t3.app_created_at LIKE '%" . $search . "%' "
                . " OR t3.app_updated_at LIKE '%" . $search . "%' "
                . " OR t4.admob_email LIKE '%" . $search . "%' "
                . ")");
        }

        $sortColumn = isset($_POST['iSortCol_0']) ? $_POST['iSortCol_0'] : "";
        $sSortDir_0 = isset($_POST['sSortDir_0']) ? $_POST['sSortDir_0'] : 'DESC';

        switch ($sortColumn) {
            case 0:
                $this->db->order_by("t1.permission_auto_id", $sSortDir_0);
                break;
            case 1:
                $this->db->order_by("t3.app_display_name", $sSortDir_0);
                break;
            case 2:
                $this->db->order_by("t4.admob_pub_id", $sSortDir_0);
                break;
            default:
                $this->db->order_by("t1.permission_auto_id", "DESC");
        }

        $this->db->select('t3.*,t4.admob_email,t5.user_email,t5.user_name,t5.user_id as user_unique_id');
        $this->db->from('tbl_ad_unit_permissions as t1');
        $this->db->join('tbl_ad_units as t2', 't2.au_auto_id = t1.permission_au_auto_id');
        $this->db->join('tbl_apps as t3', 't3.app_auto_id = t2.au_app_auto_id');
        $this->db->join('tbl_admob_account as t4', 't4.admob_auto_id = t3.app_admob_auto_id');
        $this->db->join('tbl_user as t5', 't5.user_id = t1.permission_user_id');

        if (isset($option['where']) && !empty($option['where'])) {
            $this->db->where($option['where']);
        }

        $this->db->group_by('t2.au_app_auto_id');
        $this->db->group_by('t1.permission_user_id');
        if ($is_count) {
            $res = $this->db->get()->num_rows();
        } else {

            $this->db->limit($limit, $start);
            $res = $this->db->get()->result();
        }

        return $res;
    }
}
