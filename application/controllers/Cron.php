<?php

use Google\Service\CloudSourceRepositories\Repo;

defined('BASEPATH') or exit('No direct script access allowed');
include_once(dirname(__FILE__) . "/Common.php");

class Cron extends Common
{

    // [Common Methods for Cron]
    function google_client_init($access_token)
    {
        $client = new Google_Client();

        $client->addScope('https://www.googleapis.com/auth/admob.readonly');
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        $client->setIncludeGrantedScopes(true);
        $client->setAuthConfig('application/third_party/client_secrets.json');
        $service = new Google_Service_AdMob($client);

        // echo '<pre>';
        // print_r(current_url());
        // exit;

        $client->setRedirectUri(current_url());
        $client->setAccessToken(trim($access_token));

        $tokenValid = 0;
        $getAccessToken = array();
        if ($client->getAccessToken()) {

            $getAccessToken = $client->getAccessToken();
            // $this->plog($getAccessToken);

            if ($client->isAccessTokenExpired()) {

                $getRefreshToken = $client->getRefreshToken();
                // $this->plog($getRefreshToken);

                if ($getRefreshToken) {
                    $authData = $client->fetchAccessTokenWithRefreshToken($getRefreshToken);

                    // echo 'expired<pre>';
                    // print_r($authData);
                    // exit;

                    if (array_key_exists('error', $authData)) {
                        // throw new Exception(join(', ', $authData));
                        print_r($authData);
                        $tokenValid = 0;
                    } else {

                        $authData['refresh_token'] = $getRefreshToken;

                        // $client->setAccessToken(trim(json_encode($authData)));

                        // Update new access token in DB
                        $option_update_access_token = array(
                            'from' => 'tbl_admob_account',
                            'update_data' => array(
                                'admob_access_token' => json_encode($authData)
                            ),
                            'where' => array(
                                'admob_access_token' => $access_token
                            )
                        );

                        // $this->plog($option_update_access_token);

                        $update_access_token = $this->mdl_common->update($option_update_access_token);

                        if ($update_access_token) {
                            $tokenValid = 1;

                            // new client object
                            $client2 = new Google_Client();
                            $client2->addScope('https://www.googleapis.com/auth/admob.readonly');
                            $client2->setAccessType('offline');
                            $client2->setApprovalPrompt('force');
                            $client2->setIncludeGrantedScopes(true);
                            $client2->setAuthConfig('application/third_party/client_secrets.json');
                            $service2 = new Google_Service_AdMob($client2);

                            $client2->setRedirectUri(current_url());

                            $client2->setAccessToken(trim(json_encode($authData)));

                            if ($client2->getAccessToken()) {
                                $getAccessToken = $client2->getAccessToken();

                                // $this->plog($access_token, 1);
                                // $this->plog($authData, 1);
                                // $this->plog($getAccessToken);
                            } else {
                                $tokenValid = 0;
                            }
                        } else {
                            $tokenValid = 0;
                        }
                    }
                } else {
                    $tokenValid = 0;
                }
            } else {
                $tokenValid = 1;
            }
        } else {
            $tokenValid = 0;
        }

        if ($tokenValid == 1 && !empty($getAccessToken)) {
            return array(
                'getAccessToken' => $getAccessToken,
                'service' => isset($service2) ? $service2 : $service
            );
        } else {
            echo "Invalid Access Token";
            exit;
        }
    }
    // [/Common Methods for Cron]

    function fetch_app_list()
    {
        $get_admob_auto_id = $this->get_setting_value("CRON_FETCH_APP_LIST");

        // $this->plog($get_admob_auto_id);

        if ($get_admob_auto_id != '') {

            $option_for_fetch_admob = array(
                'select' => 'admob_auto_id,admob_access_token,admob_pub_id',
                'from' => 'tbl_admob_account',
                'where' => "admob_access_token IS NOT NULL AND admob_access_token!='' AND admob_auto_id > $get_admob_auto_id ",
                'pagination' => array(
                    'limit' => 1
                )
            );
            // $this->plog($option_for_fetch_admob);

            $fetch_admob = $this->mdl_common->select($option_for_fetch_admob);

            // $this->plog($fetch_admob);

            if (isset($fetch_admob[0]['admob_access_token'])) {
                $admob_auto_id = $fetch_admob[0]['admob_auto_id'];

                $cron_fetch_app_list = $admob_auto_id;

                // $this->plog($cron_fetch_app_list, 1);


                // ADMOB API call
                $google_client_init = $this->google_client_init($fetch_admob[0]['admob_access_token']);

                // $this->plog($google_client_init);

                $getAccessToken = $google_client_init['getAccessToken'];
                $service = $google_client_init['service'];

                $accountName = 'accounts/' . $fetch_admob[0]['admob_pub_id'];

                // Get list of apps.

                try {
                    $response = $service->accounts_apps->listAccountsApps($accountName, []);
                    $apps = $response->getApps();
                } catch (Exception $e) {
                    print_r("Exception");
                    print_r($e);
                    exit;
                    $apps = array();
                }

                // $this->plog($apps);

                if (!empty($apps)) {

                    foreach ($apps as $app) {

                        $appData['app_admob_auto_id'] = $admob_auto_id;

                        if (!empty($app['linkedAppInfo'])) {
                            $app_store_id = $app['linkedAppInfo']['appStoreId'];
                            $app_display_name = $app['linkedAppInfo']['displayName'];
                        } else {
                            $app_store_id = '';
                            $app_display_name = $app['manualAppInfo']['displayName'];
                        }

                        $appData['app_display_name'] = $app_display_name;
                        $appData['app_store_id'] = $app_store_id;

                        if (isset($app['appApprovalState'])) {
                            $appData['app_approval_state'] = $this->get_short_id_by_admob_approval_state($app['appApprovalState']);
                        }

                        $app_admob_app_id = $app['appId'];
                        $appData['app_admob_app_id'] = $app_admob_app_id;

                        switch (strtoupper($app['platform'])) {
                            case "ANDROID":

                                $admob_app_platform = 2;

                                if ($app_store_id) {
                                    $app_info_response = $this->live_android_app_info($app_store_id);

                                    if ($app_info_response['error'] == '') {
                                        $app_info_response_data = json_decode($app_info_response['response']);
                                        if ($app_info_response_data->status_code == 1) {
                                            $app_info = $app_info_response_data->data;
                                            $appData['app_icon'] = $app_info->icon;
                                            $appData['app_console_name'] = $app_info->developer;
                                        }
                                    }
                                }
                                break;
                            case "IOS":
                                $admob_app_platform = 1;

                                if ($app_store_id) {
                                    $app_info_response = $this->live_ios_app_info($app_store_id);

                                    if ($app_info_response['is_active'] == 1) {
                                        $appData['app_icon'] = $app_info_response['app_icon'];
                                        $appData['app_console_name'] = $app_info_response['dev_console_name'];
                                    }
                                }

                                break;
                            default:
                                $admob_app_platform = 2;
                        }

                        $appData['app_platform'] = $admob_app_platform;
                        $appData['app_updated_at'] = date('Y-m-d H:i:s');

                        $optionApp = array(
                            'select' => 'app_auto_id',
                            'from' => 'tbl_apps',
                            'where' => array(
                                'app_admob_app_id' => $app_admob_app_id
                            ),
                            'pagination' => array(
                                'limit' => 1
                            )
                        );
                        $fetch_app = $this->mdl_common->select($optionApp);

                        if (isset($fetch_app[0]['app_auto_id'])) { // Update app info

                            $option_update = array(
                                'from' => 'tbl_apps',
                                'update_data' => $appData,
                                'where' => array(
                                    'app_auto_id' => $fetch_app[0]['app_auto_id']
                                )
                            );
                            $update_app_info = $this->mdl_common->update($option_update);

                            echo "<br>Updated app info => ";
                            var_dump($update_app_info);
                        } else { // Insert app info

                            $appData['app_created_at'] = date('Y-m-d H:i:s');

                            $option_insert = array(
                                'from' => 'tbl_apps',
                                'insert_data' => $appData,
                            );
                            $insert_app_info = $this->mdl_common->insert($option_insert);

                            echo "<br>Inserted app info => ";
                            var_dump($insert_app_info);
                        }
                    }
                } else {
                    echo "Apps not found";
                }
            } else {
                echo "Record not found";
                $cron_fetch_app_list = 0;
            }

            $this->update_setting_value("CRON_FETCH_APP_LIST", $cron_fetch_app_list);
        } else {
            echo "Settings not found";
        }
    }

    function fetch_app_ad_units()
    {
        $get_admob_auto_id = $this->get_setting_value("CRON_FETCH_APP_AD_UNITS");

        if ($get_admob_auto_id != '') {

            $option_for_fetch_admob = array(
                'select' => 'admob_auto_id,admob_access_token,admob_pub_id',
                'from' => 'tbl_admob_account',
                'where' => "admob_access_token IS NOT NULL AND admob_access_token!='' AND admob_auto_id > $get_admob_auto_id ",
                'pagination' => array(
                    'limit' => 1
                )
            );
            $fetch_admob = $this->mdl_common->select($option_for_fetch_admob);

            if (isset($fetch_admob[0]['admob_access_token'])) {

                $cron_fetch_app_ad_units = $fetch_admob[0]['admob_auto_id'];

                // ADMOB API call
                $google_client_init = $this->google_client_init($fetch_admob[0]['admob_access_token']);

                $getAccessToken = $google_client_init['getAccessToken'];
                $service = $google_client_init['service'];

                $accountName = 'accounts/' . $fetch_admob[0]['admob_pub_id'];

                // Get list of apps.
                try {
                    $response = $service->accounts_adUnits->listAccountsAdUnits($accountName, []);
                    $ad_units = $response->adUnits;
                } catch (Exception $e) {
                    $ad_units = array();
                }

                // $this->plog($ad_units);

                if (!empty($ad_units)) {

                    // get all ad formats
                    $option_ad_formats = array(
                        'from' => 'tbl_ad_unit_format'
                    );
                    $ad_formats = $this->mdl_common->select($option_ad_formats);

                    $db_apps_info = array();

                    foreach ($ad_units as $ad_unit) {

                        // $this->plog($ad_unit);

                        $app_admob_app_id = $ad_unit['appId'];

                        $is_app_found = 1;

                        // fetch app_auto_id
                        $db_app_info_key = array_search($app_admob_app_id, array_column($db_apps_info, 'app_admob_app_id'));

                        if ($db_app_info_key !== false) { // find from db_apps_info array
                            $app_auto_id = $db_apps_info[$db_app_info_key]['app_auto_id'];
                        } else { // fetch from DB

                            $option_app_info = array(
                                'select' => 'app_auto_id',
                                'from' => 'tbl_apps',
                                'where' => array(
                                    'app_admob_app_id' => $app_admob_app_id
                                ),
                                'pagination' => array(
                                    'limit' => 1
                                )
                            );
                            $app_info_response = $this->mdl_common->select($option_app_info);
                            if (isset($app_info_response[0]['app_auto_id'])) {
                                $app_auto_id = $app_info_response[0]['app_auto_id'];

                                // fetch ad units of this app
                                $option_ad_units = array(
                                    'select' => 'au_id',
                                    'from' => 'tbl_ad_units',
                                    'where' => array(
                                        'au_app_auto_id' => $app_auto_id
                                    )
                                );
                                $app_ad_units_result = $this->mdl_common->select($option_ad_units);

                                $db_apps_info[] = array(
                                    'app_auto_id' => $app_auto_id,
                                    'app_admob_app_id' => $app_admob_app_id,
                                    'ad_units' => $app_ad_units_result
                                );

                                $db_app_info_key = count($db_apps_info) - 1;
                            } else {
                                $is_app_found = 0;
                            }
                        }

                        if ($is_app_found) {

                            $ad_unit_data['au_id'] = $ad_unit['adUnitId'];
                            $ad_unit_data['au_app_auto_id'] = $app_auto_id;
                            $ad_unit_data['au_display_name'] = $ad_unit['displayName'];

                            $ad_formats_key = array_search(strtoupper($ad_unit['adFormat']), array_column($ad_formats, 'au_format_unique_name'));

                            if ($ad_formats_key !== false) {
                                $ad_unit_format = $ad_formats[$ad_formats_key]['au_format_auto_id'];
                            } else {
                                $ad_unit_format = 0;
                            }

                            $ad_unit_data['au_format_auto_id'] = $ad_unit_format;
                            $ad_unit_data['au_updated_at'] = date('Y-m-d H:i:s');

                            // check ad unit exist
                            $is_ad_unit_exist = 1;
                            if (count($db_apps_info[$db_app_info_key]['ad_units']) > 0) {

                                $find_ad_unit_key = array_search($ad_unit['adUnitId'], array_column($db_apps_info[$db_app_info_key]['ad_units'], 'au_id'));

                                if ($find_ad_unit_key !== false) { // update ad unit

                                    unset($ad_unit_data['au_id']);

                                    $option_update = array(
                                        'from' => 'tbl_ad_units',
                                        'update_data' => $ad_unit_data,
                                        'where' => array(
                                            'au_id' => $ad_unit['adUnitId']
                                        )
                                    );
                                    $update_ad_unit = $this->mdl_common->update($option_update);

                                    echo "UPDATE => ";
                                    var_dump($update_ad_unit);
                                    echo "<br>";
                                } else {
                                    $is_ad_unit_exist = 0;
                                }
                            } else {
                                $is_ad_unit_exist = 0;
                            }

                            if ($is_ad_unit_exist == 0) { // Insert New Ad Unit

                                $ad_unit_data['au_created_at'] = date('Y-m-d H:i:s');
                                $option_insert = array(
                                    'from' => 'tbl_ad_units',
                                    'insert_data' => $ad_unit_data
                                );
                                $insert_ad_unit = $this->mdl_common->insert($option_insert);

                                echo "INSERT => ";
                                var_dump($insert_ad_unit);
                                echo "<br>";
                            }
                        } else {
                            echo 'App not found in our record. app_admob_app_id => ' . $app_admob_app_id;
                        }
                    }
                } else {
                    echo "Ad Units not found";
                }
            } else {
                echo "Record not found";
                $cron_fetch_app_ad_units = 0;
            }

            $this->update_setting_value("CRON_FETCH_APP_AD_UNITS", $cron_fetch_app_ad_units);
        } else {
            echo "Settings not found";
        }
    }

    function fetch_app_network_report()
    {
        $get_app_auto_id = $this->get_setting_value("CRON_FETCH_APP_NETWORK_REPORT");

        if ($get_app_auto_id != '') {

            $option_for_fetch_admob_and_app = "SELECT t1.app_admob_app_id,t1.app_auto_id,t2.admob_access_token,t2.admob_pub_id   
            FROM tbl_apps as t1
            JOIN tbl_admob_account as t2 ON t2.admob_auto_id = t1.app_admob_auto_id
            WHERE t2.admob_access_token IS NOT NULL AND t2.admob_access_token !='' AND t1.app_auto_id > $get_app_auto_id 
            ORDER BY t1.app_auto_id LIMIT 2";
            $fetch_db_apps = $this->mdl_common->custom_query($option_for_fetch_admob_and_app)->result_array();

            // echo "<pre>";
            // print_r($fetch_db_apps);
            // exit;

            if ($fetch_db_apps) {

                // get all currency code
                $db_currency_codes = $this->mdl_common->custom_query("SELECT currency_code_auto_id,currency_code FROM tbl_currency_codes")->result_array();
                // $this->plog($db_currency_codes);

                // get all country list
                $db_country_list = $this->mdl_common->custom_query("SELECT * FROM tbl_country")->result_array();
                // $this->plog($db_country_list);

                foreach ($fetch_db_apps as $single_app) {

                    // $this->plog($single_app);

                    // ADMOB API call
                    $google_client_init = $this->google_client_init($single_app['admob_access_token']);

                    $getAccessToken = $google_client_init['getAccessToken'];
                    $service = $google_client_init['service'];

                    $app_auto_id = $single_app['app_auto_id'];
                    $accountName = 'accounts/' . $single_app['admob_pub_id'];
                    $app_admob_app_id = $single_app['app_admob_app_id'];

                    // $startDate = $this->tenDaysBeforeToday();
                    $startDate = $this->twoDaysBeforeToday();
                    $endDate = $this->today();

                    // $startDate = $this->firstDateOfLastMonth();
                    // $endDate = $this->lastDateOfLastMonth();

                    // Specify date range.
                    $dateRange = new \Google_Service_AdMob_DateRange();
                    $dateRange->setStartDate($startDate);
                    $dateRange->setEndDate($endDate);

                    // Specify dimension filters.
                    $apps = new \Google_Service_AdMob_StringList();
                    $apps->setValues([$app_admob_app_id]);
                    $dimensionFilterMatches = new \Google_Service_AdMob_MediationReportSpecDimensionFilter();
                    $dimensionFilterMatches->setDimension('APP');
                    $dimensionFilterMatches->setMatchesAny($apps);

                    // Create network report specification.
                    $dimensions = ['APP', 'COUNTRY', 'AD_UNIT', 'DATE'];
                    $metrics = [
                        'ESTIMATED_EARNINGS',
                        'IMPRESSION_RPM',
                        'MATCHED_REQUESTS',
                        'SHOW_RATE',
                        'IMPRESSIONS',
                        'IMPRESSION_CTR',
                        'CLICKS'
                    ];  // IMPRESSION_RPM - for OBSERVED_ECPM as (OBSERVED_ECPM) metric not working in network report
                    if (!in_array('AD_TYPE', $dimensions)) {
                        array_push($metrics, 'AD_REQUESTS', 'MATCH_RATE');
                    }
                    $reportSpec = new \Google_Service_AdMob_NetworkReportSpec();
                    $reportSpec->setMetrics($metrics);
                    $reportSpec->setDimensions($dimensions);
                    $reportSpec->setDateRange($dateRange);
                    $reportSpec->setDimensionFilters($dimensionFilterMatches);

                    // Create network report request.
                    $networkReportRequest = new \Google_Service_AdMob_GenerateNetworkReportRequest();
                    $networkReportRequest->setReportSpec($reportSpec);
                    $networkReportRequest = $networkReportRequest;

                    try {
                        $networkReportResponse = $service->accounts_networkReport->generate(
                            $accountName,
                            $networkReportRequest
                        );

                        // Convert network report response to a simple object.
                        $networkReportResponse = $networkReportResponse->tosimpleObject();
                    } catch (Exception $e) {
                        $networkReportResponse = array();
                    }

                    // print_r($networkReportResponse);
                    // exit;

                    if (!empty($networkReportResponse)) {

                        // get all ad_units for current app
                        $db_app_ad_units = $this->mdl_common->custom_query("SELECT au_auto_id,au_id FROM tbl_ad_units WHERE au_app_auto_id = $app_auto_id")->result_array();

                        // fetch header from response
                        if (isset($networkReportResponse->{0}['header'])) {

                            $networkReportHeader = $networkReportResponse->{0}['header'];

                            if (isset($networkReportHeader['localizationSettings']['currencyCode'])) {
                                $currencyCode = $networkReportHeader['localizationSettings']['currencyCode'];

                                $db_currency_codes_key = array_search($currencyCode, array_column($db_currency_codes, 'currency_code'));

                                if ($db_currency_codes_key !== false) {
                                    $currency_code_auto_id = $db_currency_codes[$db_currency_codes_key]['currency_code_auto_id'];
                                }
                            }

                            unset($networkReportResponse->{0});
                        }

                        foreach ($networkReportResponse as $networkReport) {
                            $report_data = array();

                            if (isset($networkReport['row'])) {

                                $dimensionValues = $networkReport['row']['dimensionValues'];

                                $au_id = $dimensionValues['AD_UNIT']['value'];

                                $db_app_ad_units_key = array_search($au_id, array_column($db_app_ad_units, 'au_id'));

                                if ($db_app_ad_units_key !== false) {

                                    $report_au_auto_id = $db_app_ad_units[$db_app_ad_units_key]['au_auto_id'];
                                    $report_data['report_au_auto_id'] = $report_au_auto_id;

                                    $report_date = $dimensionValues['DATE']['value'];

                                    $short_country_code = isset($dimensionValues['COUNTRY']['value']) ? $dimensionValues['COUNTRY']['value'] : 'UR';
                                    $db_country_code_key = array_search($short_country_code, array_column($db_country_list, 'country_alpha2_code'));

                                    if ($db_country_code_key !== false) {
                                        $report_data['report_country_auto_id'] = $db_country_list[$db_country_code_key]['country_auto_id'];
                                    } else {
                                        $report_data['report_country_auto_id'] = "250";
                                    }

                                    if (isset($currency_code_auto_id)) {
                                        $report_data['report_currency_code_auto_id'] = $currency_code_auto_id;
                                    }

                                    $metricValues = $networkReport['row']['metricValues'];

                                    if (isset($metricValues['ESTIMATED_EARNINGS']['microsValue'])) {
                                        $est_earn = $this->microValueConvert($metricValues['ESTIMATED_EARNINGS']['microsValue']);
                                        // $report_data['estimated_earnings'] = number_format($est_earn, 2);
                                        $report_data['report_estimated_earnings'] = $est_earn;
                                    }

                                    if (isset($metricValues['IMPRESSION_RPM']['doubleValue'])) {
                                        $report_data['report_observed_ecpm'] = number_format($metricValues['IMPRESSION_RPM']['doubleValue'], 2);
                                        // $report_data['observed_ecpm'] = $metricValues['IMPRESSION_RPM']['doubleValue'];
                                    }

                                    if (isset($metricValues['AD_REQUESTS']['integerValue'])) {
                                        $report_data['report_ad_requests'] = $metricValues['AD_REQUESTS']['integerValue'];
                                    }

                                    if (isset($metricValues['MATCH_RATE']['doubleValue'])) {
                                        $report_data['report_match_rate'] = number_format(($metricValues['MATCH_RATE']['doubleValue'] * 100), 2);
                                    }

                                    if (isset($metricValues['MATCHED_REQUESTS']['integerValue'])) {
                                        $report_data['report_matched_requests'] = $metricValues['MATCHED_REQUESTS']['integerValue'];
                                    }

                                    if (isset($metricValues['SHOW_RATE']['doubleValue'])) {
                                        $report_data['report_show_rate'] = number_format(($metricValues['SHOW_RATE']['doubleValue'] * 100), 2);
                                    }

                                    if (isset($metricValues['IMPRESSIONS']['integerValue'])) {
                                        $report_data['report_impressions'] = $metricValues['IMPRESSIONS']['integerValue'];
                                    }

                                    if (isset($metricValues['IMPRESSION_CTR']['doubleValue'])) {
                                        $report_data['report_impression_ctr'] = number_format(($metricValues['IMPRESSION_CTR']['doubleValue'] * 100), 2);
                                    }

                                    if (isset($metricValues['CLICKS']['integerValue'])) {
                                        $report_data['report_clicks'] = $metricValues['CLICKS']['integerValue'];
                                    }

                                    $report_date_format = DateTime::createFromFormat('Ymd', $report_date);
                                    $report_data['report_date'] = $report_date_format->format('Y-m-d');

                                    $current_datetime = date("Y-m-d H:i:s");
                                    $report_data['report_created_at'] = $current_datetime;
                                    $report_data['report_updated_at'] = $current_datetime;

                                    // check entry exist
                                    $option_check_exist_report = array(
                                        'select' => 'report_id',
                                        'from' => 'tbl_report',
                                        'where' => array(
                                            'report_au_auto_id' => $report_data['report_au_auto_id'],
                                            'report_country_auto_id' => $report_data['report_country_auto_id'],
                                            'report_date' => $report_data['report_date']
                                        )
                                    );

                                    $select_exist_report = $this->mdl_common->select($option_check_exist_report);

                                    if (isset($select_exist_report[0]['report_id'])) { // record exist

                                        unset($report_data['report_au_auto_id']);
                                        unset($report_data['report_country_auto_id']);
                                        unset($report_data['report_date']);
                                        unset($report_data['report_created_at']);

                                        $option_update = array(
                                            'from' => 'tbl_report',
                                            'update_data' => $report_data,
                                            'where' => array(
                                                'report_id' => $select_exist_report[0]['report_id']
                                            )
                                        );
                                        $update_record = $this->mdl_common->update($option_update);
                                        echo "UPDATE => <br>";
                                        if (!$update_record) { // print if any error occurred
                                            echo "<pre>";
                                            print_r($option_update);
                                            var_dump($update_record);
                                            echo "</pre><br>";
                                        }
                                    } else { // insert new report entry

                                        $option_insert = array(
                                            'from' => 'tbl_report',
                                            'insert_data' => $report_data
                                        );
                                        $insert_record = $this->mdl_common->insert($option_insert);

                                        echo "INSERT => <br>";
                                        if (!$insert_record) { // print if any error occurred
                                            echo "<pre>";
                                            print_r($option_insert);
                                            var_dump($insert_record);
                                            echo "</pre><br>";
                                        }
                                    }
                                } else {
                                    echo "<br> Ad id '$au_id' not found in DB.<br>";
                                }
                            }
                        }
                    } else {
                        echo "<br> Report data not found.<br>";
                    }

                    sleep(2);
                }
            } else {
                echo "<br> Apps not found for fetch report.<br>";
            }

            // Update next app_auto_id in tbl_settings
            $last_app_record = end($fetch_db_apps);

            if ($last_app_record) {
                $CRON_FETCH_APP_NETWORK_REPORT = $last_app_record['app_auto_id'];
            } else {
                $CRON_FETCH_APP_NETWORK_REPORT = 0;
            }
            $this->update_setting_value("CRON_FETCH_APP_NETWORK_REPORT", $CRON_FETCH_APP_NETWORK_REPORT);
        } else {
            echo "Settings not found";
        }
    }

    /*Send Mail Report*/
    function send_mail_report_daily()
    {
        $get_report_auto_id = $this->get_setting_value("CRON_SEND_MAIL_REPORT_DAILY");

        if ($get_report_auto_id != null) {

            $option_for_fetch = array(
                'from' => 'tbl_report_send',
                'where' => "report_status = 1 AND report_schedule = 1 AND (report_send_datetime IS NULL OR DATE_FORMAT(report_send_datetime,'%Y-%m-%d') != '" . date('Y-m-d') . "') AND report_auto_id > $get_report_auto_id ",
                'pagination' => array(
                    'limit' => 1
                ),
                'order_by' => array(
                    'key' => 'report_auto_id',
                    'order' => 'ASC'
                )
            );
            $fetch_report_send = $this->mdl_common->select($option_for_fetch);

            // echo $this->db->last_query();
            // $this->plog($fetch_report_send);

            if ($fetch_report_send) {
                $total_fetch_report_send = count($fetch_report_send);

                foreach ($fetch_report_send as $single) {
                    // $this->plog($single, 1);

                    $query = "SELECT t1.app_auto_id,t1.admob_app_id,t2.admob_access_token,
                    (SELECT GROUP_CONCAT(ad_unit_display_name) FROM tbl_ad_units as t3 WHERE t3.au_auto_id IN (" . ($single['report_au_auto_id']) . ")) as all_ad_units 
                    FROM tbl_apps as t1 
                    JOIN tbl_admob_account as t2 ON t2.admob_auto_id = t1.admob_auto_id 
                    WHERE t1.app_auto_id = " . $single['report_app_auto_id'];
                    $app_info = $this->mdl_common->custom_query($query)->row_array();

                    if ($app_info) {
                        // $this->plog($app_info);

                        // [Changable Variables]
                        $access_token = $app_info['admob_access_token'];
                        $app_id = $app_info['admob_app_id'];
                        $adUnitdisplayLabel_array = explode(',', $app_info['all_ad_units']);

                        $from_email = DEFAULT_FROM_EMAIL;
                        $from_email_name = DEFAULT_FROM_EMAIL_NAME;

                        $to_email_array = [];

                        if ($single['report_send_to_email']) {
                            $report_cc_email_json = json_decode($single['report_send_to_email']);
                            foreach ($report_cc_email_json as $singleToMail) {
                                $to_email_array[] = array("Email" => $singleToMail->email, "Name" => $singleToMail->name);
                            }
                        }

                        // [/Changable Variables]

                        $custom_data = array(
                            'admob_access_token' => $access_token,
                            'app_id' => $app_id,
                            'ad_unit_display_lables' => $adUnitdisplayLabel_array,
                            'report_range_type' => $single['report_range_type'],
                            'mail_setting' => array(
                                'from' => array(
                                    'email_id' => $from_email,
                                    'name' => $from_email_name
                                ),
                                'to' => $to_email_array
                            )
                        );

                        if ($single['report_cc_email']) {
                            $cc_json = json_decode($single['report_cc_email']);

                            $custom_data['mail_setting']['cc'] = array(
                                'email_id' => $cc_json->email,
                                'name' => $cc_json->name
                            );
                        }

                        // $this->plog($custom_data);
                        $mail_send_res = $this->mail_report($custom_data);

                        if ($mail_send_res['status_code'] == 1) {
                            // Update report_send_datetime column
                            $this->mdl_common->custom_query("UPDATE tbl_report_send SET report_send_datetime = '" . date('Y-m-d H:i:s') . "' WHERE report_auto_id = " . $single['report_auto_id']);
                        }
                    }

                    if ($total_fetch_report_send > 1) {
                        sleep(1);
                    }
                }

                $last_report_id = end($fetch_report_send)['report_auto_id'];
                $this->update_setting_value("CRON_SEND_MAIL_REPORT_DAILY", $last_report_id);
            } else {
                $this->update_setting_value("CRON_SEND_MAIL_REPORT_DAILY", 0);
            }
        } else {
            echo "Settings not found";
        }
    }

    function send_mail_report_weekly()
    {

        $current_day = date('l');
        // $current_day = "Monday";

        if (($current_day == 'Monday')) {

            $get_report_auto_id = $this->get_setting_value("CRON_SEND_MAIL_REPORT_WEEKLY");

            if ($get_report_auto_id != null) {

                $option_for_fetch = array(
                    'select' => '*',
                    'from' => 'tbl_report_send',
                    'where' => "report_status = 1 AND report_schedule = 2 AND (report_send_datetime IS NULL OR DATE_FORMAT(report_send_datetime,'%Y-%m-%d') != '" . date('Y-m-d') . "') AND report_auto_id > $get_report_auto_id ",
                    'pagination' => array(
                        'limit' => 1
                    ),
                    'order_by' => array(
                        'key' => 'report_auto_id',
                        'order' => 'ASC'
                    )
                );
                $fetch_report_send = $this->mdl_common->select($option_for_fetch);

                // echo $this->db->last_query();
                // $this->plog($fetch_report_send);

                if ($fetch_report_send) {
                    $total_fetch_report_send = count($fetch_report_send);

                    foreach ($fetch_report_send as $single) {
                        // $this->plog($single, 1);

                        $query = "SELECT t1.app_auto_id,t1.admob_app_id,t2.admob_access_token,
                                (SELECT GROUP_CONCAT(ad_unit_display_name) FROM tbl_ad_units as t3 WHERE t3.au_auto_id IN (" . ($single['report_au_auto_id']) . ")) as all_ad_units 
                                FROM tbl_apps as t1 
                                JOIN tbl_admob_account as t2 ON t2.admob_auto_id = t1.admob_auto_id 
                                WHERE t1.app_auto_id = " . $single['report_app_auto_id'];
                        $app_info = $this->mdl_common->custom_query($query)->row_array();

                        if ($app_info) {
                            // $this->plog($app_info);

                            // [Changable Variables]
                            $access_token = $app_info['admob_access_token'];
                            $app_id = $app_info['admob_app_id'];
                            $adUnitdisplayLabel_array = explode(',', $app_info['all_ad_units']);

                            $from_email = DEFAULT_FROM_EMAIL;
                            $from_email_name = DEFAULT_FROM_EMAIL_NAME;

                            $to_email_array = [];

                            if ($single['report_send_to_email']) {
                                $report_cc_email_json = json_decode($single['report_send_to_email']);
                                foreach ($report_cc_email_json as $singleToMail) {
                                    $to_email_array[] = array("Email" => $singleToMail->email, "Name" => $singleToMail->name);
                                }
                            }

                            // [/Changable Variables]

                            $custom_data = array(
                                'admob_access_token' => $access_token,
                                'app_id' => $app_id,
                                'ad_unit_display_lables' => $adUnitdisplayLabel_array,
                                'report_range_type' => $single['report_range_type'],
                                'mail_setting' => array(
                                    'from' => array(
                                        'email_id' => $from_email,
                                        'name' => $from_email_name
                                    ),
                                    'to' => $to_email_array
                                )
                            );

                            if ($single['report_cc_email']) {
                                $cc_json = json_decode($single['report_cc_email']);

                                $custom_data['mail_setting']['cc'] = array(
                                    'email_id' => $cc_json->email,
                                    'name' => $cc_json->name
                                );
                            }

                            // $this->plog($custom_data);
                            // $this->mail_report($custom_data);
                            $mail_send_res = $this->mail_report($custom_data);

                            if ($mail_send_res['status_code'] == 1) {
                                // Update report_send_datetime column
                                $this->mdl_common->custom_query("UPDATE tbl_report_send SET report_send_datetime = '" . date('Y-m-d H:i:s') . "' WHERE report_auto_id = " . $single['report_auto_id']);
                            }
                        }

                        if ($total_fetch_report_send > 1) {
                            sleep(1);
                        }
                    }

                    $last_report_id = end($fetch_report_send)['report_auto_id'];
                    $this->update_setting_value("CRON_SEND_MAIL_REPORT_WEEKLY", $last_report_id);
                } else {
                    echo 'All done';
                    $this->update_setting_value("CRON_SEND_MAIL_REPORT_WEEKLY", 0);
                }
            } else {
                echo "Settings not found";
            }
        } else {
            echo "This is not weekdays to run this method.";
        }
    }

    function send_mail_report_monthly()
    {

        $current_month_day = date('d');
        // $current_month_day = "01";

        if (($current_month_day == '01')) {

            $get_report_auto_id = $this->get_setting_value("CRON_SEND_MAIL_REPORT_MONTHLY");

            if ($get_report_auto_id != null) {

                $option_for_fetch = array(
                    'select' => '*',
                    'from' => 'tbl_report_send',
                    'where' => "report_status = 1 AND report_schedule = 3 AND (report_send_datetime IS NULL OR DATE_FORMAT(report_send_datetime,'%Y-%m-%d') != '" . date('Y-m-d') . "') AND report_auto_id > $get_report_auto_id ",
                    'pagination' => array(
                        'limit' => 1
                    ),
                    'order_by' => array(
                        'key' => 'report_auto_id',
                        'order' => 'ASC'
                    )
                );
                $fetch_report_send = $this->mdl_common->select($option_for_fetch);

                // echo $this->db->last_query();
                // $this->plog($fetch_report_send);

                if ($fetch_report_send) {
                    $total_fetch_report_send = count($fetch_report_send);

                    foreach ($fetch_report_send as $single) {
                        // $this->plog($single, 1);

                        $query = "SELECT t1.app_auto_id,t1.admob_app_id,t2.admob_access_token,
                                (SELECT GROUP_CONCAT(ad_unit_display_name) FROM tbl_ad_units as t3 WHERE t3.au_auto_id IN (" . ($single['report_au_auto_id']) . ")) as all_ad_units 
                                FROM tbl_apps as t1 
                                JOIN tbl_admob_account as t2 ON t2.admob_auto_id = t1.admob_auto_id 
                                WHERE t1.app_auto_id = " . $single['report_app_auto_id'];
                        $app_info = $this->mdl_common->custom_query($query)->row_array();

                        if ($app_info) {
                            // $this->plog($app_info);

                            // [Changable Variables]
                            $access_token = $app_info['admob_access_token'];
                            $app_id = $app_info['admob_app_id'];
                            $adUnitdisplayLabel_array = explode(',', $app_info['all_ad_units']);

                            $from_email = DEFAULT_FROM_EMAIL;
                            $from_email_name = DEFAULT_FROM_EMAIL_NAME;

                            $to_email_array = [];

                            if ($single['report_send_to_email']) {
                                $report_cc_email_json = json_decode($single['report_send_to_email']);
                                foreach ($report_cc_email_json as $singleToMail) {
                                    $to_email_array[] = array("Email" => $singleToMail->email, "Name" => $singleToMail->name);
                                }
                            }

                            // [/Changable Variables]

                            $custom_data = array(
                                'admob_access_token' => $access_token,
                                'app_id' => $app_id,
                                'ad_unit_display_lables' => $adUnitdisplayLabel_array,
                                'report_range_type' => $single['report_range_type'],
                                'mail_setting' => array(
                                    'from' => array(
                                        'email_id' => $from_email,
                                        'name' => $from_email_name
                                    ),
                                    'to' => $to_email_array
                                )
                            );

                            if ($single['report_cc_email']) {
                                $cc_json = json_decode($single['report_cc_email']);

                                $custom_data['mail_setting']['cc'] = array(
                                    'email_id' => $cc_json->email,
                                    'name' => $cc_json->name
                                );
                            }

                            // $this->plog($custom_data);
                            $mail_send_res = $this->mail_report($custom_data);

                            if ($mail_send_res['status_code'] == 1) {
                                // Update report_send_datetime column
                                $this->mdl_common->custom_query("UPDATE tbl_report_send SET report_send_datetime = '" . date('Y-m-d H:i:s') . "' WHERE report_auto_id = " . $single['report_auto_id']);
                            }
                        }

                        if ($total_fetch_report_send > 1) {
                            sleep(1);
                        }
                    }

                    $last_report_id = end($fetch_report_send)['report_auto_id'];
                    $this->update_setting_value("CRON_SEND_MAIL_REPORT_MONTHLY", $last_report_id);
                } else {
                    echo 'All done';
                    $this->update_setting_value("CRON_SEND_MAIL_REPORT_MONTHLY", 0);
                }
            } else {
                echo "Settings not found";
            }
        } else {
            echo "This is not good day to run this method.";
        }
    }

    function send_mail_report_every_3_month()
    {

        $current_month_day = date('m-d');
        // $current_month_day = "01-01";
        $current_month_day_array = ["01-01", "04-01", "07-01", "10-01"];

        if (in_array($current_month_day, $current_month_day_array)) {

            $get_report_auto_id = $this->get_setting_value("CRON_SEND_MAIL_REPORT_EVERY_3_MONTH");

            if ($get_report_auto_id != null) {

                $option_for_fetch = array(
                    'select' => '*',
                    'from' => 'tbl_report_send',
                    'where' => "report_status = 1 AND report_schedule = 4 AND (report_send_datetime IS NULL OR DATE_FORMAT(report_send_datetime,'%Y-%m-%d') != '" . date('Y-m-d') . "') AND report_auto_id > $get_report_auto_id ",
                    'pagination' => array(
                        'limit' => 1
                    ),
                    'order_by' => array(
                        'key' => 'report_auto_id',
                        'order' => 'ASC'
                    )
                );
                $fetch_report_send = $this->mdl_common->select($option_for_fetch);

                // echo $this->db->last_query();
                // $this->plog($fetch_report_send);

                if ($fetch_report_send) {
                    $total_fetch_report_send = count($fetch_report_send);

                    foreach ($fetch_report_send as $single) {
                        // $this->plog($single, 1);

                        $query = "SELECT t1.app_auto_id,t1.admob_app_id,t2.admob_access_token,
                                (SELECT GROUP_CONCAT(ad_unit_display_name) FROM tbl_ad_units as t3 WHERE t3.au_auto_id IN (" . ($single['report_au_auto_id']) . ")) as all_ad_units 
                                FROM tbl_apps as t1 
                                JOIN tbl_admob_account as t2 ON t2.admob_auto_id = t1.admob_auto_id 
                                WHERE t1.app_auto_id = " . $single['report_app_auto_id'];
                        $app_info = $this->mdl_common->custom_query($query)->row_array();

                        if ($app_info) {
                            // $this->plog($app_info);

                            // [Changable Variables]
                            $access_token = $app_info['admob_access_token'];
                            $app_id = $app_info['admob_app_id'];
                            $adUnitdisplayLabel_array = explode(',', $app_info['all_ad_units']);

                            $from_email = DEFAULT_FROM_EMAIL;
                            $from_email_name = DEFAULT_FROM_EMAIL_NAME;

                            $to_email_array = [];

                            if ($single['report_send_to_email']) {
                                $report_cc_email_json = json_decode($single['report_send_to_email']);
                                foreach ($report_cc_email_json as $singleToMail) {
                                    $to_email_array[] = array("Email" => $singleToMail->email, "Name" => $singleToMail->name);
                                }
                            }

                            // [/Changable Variables]

                            $custom_data = array(
                                'admob_access_token' => $access_token,
                                'app_id' => $app_id,
                                'ad_unit_display_lables' => $adUnitdisplayLabel_array,
                                'report_range_type' => $single['report_range_type'],
                                'mail_setting' => array(
                                    'from' => array(
                                        'email_id' => $from_email,
                                        'name' => $from_email_name
                                    ),
                                    'to' => $to_email_array
                                )
                            );

                            if ($single['report_cc_email']) {
                                $cc_json = json_decode($single['report_cc_email']);

                                $custom_data['mail_setting']['cc'] = array(
                                    'email_id' => $cc_json->email,
                                    'name' => $cc_json->name
                                );
                            }

                            // $this->plog($custom_data);
                            $mail_send_res = $this->mail_report($custom_data);

                            if ($mail_send_res['status_code'] == 1) {
                                // Update report_send_datetime column
                                $this->mdl_common->custom_query("UPDATE tbl_report_send SET report_send_datetime = '" . date('Y-m-d H:i:s') . "' WHERE report_auto_id = " . $single['report_auto_id']);
                            }
                        }

                        if ($total_fetch_report_send > 1) {
                            sleep(1);
                        }
                    }

                    $last_report_id = end($fetch_report_send)['report_auto_id'];
                    $this->update_setting_value("CRON_SEND_MAIL_REPORT_EVERY_3_MONTH", $last_report_id);
                } else {
                    echo 'All done';
                    $this->update_setting_value("CRON_SEND_MAIL_REPORT_EVERY_3_MONTH", 0);
                }
            } else {
                echo "Settings not found";
            }
        } else {
            echo "This is not good day to run this method.";
        }
    }

    function send_mail_report_every_6_month()
    {

        $current_month_day = date('m-d');
        // $current_month_day = "01-01";
        $current_month_day_array = ["01-01", "07-01"];

        if (in_array($current_month_day, $current_month_day_array)) {

            $get_report_auto_id = $this->get_setting_value("CRON_SEND_MAIL_REPORT_EVERY_6_MONTH");

            if ($get_report_auto_id != null) {

                $option_for_fetch = array(
                    'select' => '*',
                    'from' => 'tbl_report_send',
                    'where' => "report_status = 1 AND report_schedule = 5 AND (report_send_datetime IS NULL OR DATE_FORMAT(report_send_datetime,'%Y-%m-%d') != '" . date('Y-m-d') . "') AND report_auto_id > $get_report_auto_id ",
                    'pagination' => array(
                        'limit' => 1
                    ),
                    'order_by' => array(
                        'key' => 'report_auto_id',
                        'order' => 'ASC'
                    )
                );
                $fetch_report_send = $this->mdl_common->select($option_for_fetch);

                // echo $this->db->last_query();
                // $this->plog($fetch_report_send);

                if ($fetch_report_send) {
                    $total_fetch_report_send = count($fetch_report_send);

                    foreach ($fetch_report_send as $single) {
                        // $this->plog($single, 1);

                        $query = "SELECT t1.app_auto_id,t1.admob_app_id,t2.admob_access_token,
                                (SELECT GROUP_CONCAT(ad_unit_display_name) FROM tbl_ad_units as t3 WHERE t3.au_auto_id IN (" . ($single['report_au_auto_id']) . ")) as all_ad_units 
                                FROM tbl_apps as t1 
                                JOIN tbl_admob_account as t2 ON t2.admob_auto_id = t1.admob_auto_id 
                                WHERE t1.app_auto_id = " . $single['report_app_auto_id'];
                        $app_info = $this->mdl_common->custom_query($query)->row_array();

                        if ($app_info) {
                            // $this->plog($app_info);

                            // [Changable Variables]
                            $access_token = $app_info['admob_access_token'];
                            $app_id = $app_info['admob_app_id'];
                            $adUnitdisplayLabel_array = explode(',', $app_info['all_ad_units']);

                            $from_email = DEFAULT_FROM_EMAIL;
                            $from_email_name = DEFAULT_FROM_EMAIL_NAME;

                            $to_email_array = [];

                            if ($single['report_send_to_email']) {
                                $report_cc_email_json = json_decode($single['report_send_to_email']);
                                foreach ($report_cc_email_json as $singleToMail) {
                                    $to_email_array[] = array("Email" => $singleToMail->email, "Name" => $singleToMail->name);
                                }
                            }

                            // [/Changable Variables]

                            $custom_data = array(
                                'admob_access_token' => $access_token,
                                'app_id' => $app_id,
                                'ad_unit_display_lables' => $adUnitdisplayLabel_array,
                                'report_range_type' => $single['report_range_type'],
                                'mail_setting' => array(
                                    'from' => array(
                                        'email_id' => $from_email,
                                        'name' => $from_email_name
                                    ),
                                    'to' => $to_email_array
                                )
                            );

                            if ($single['report_cc_email']) {
                                $cc_json = json_decode($single['report_cc_email']);

                                $custom_data['mail_setting']['cc'] = array(
                                    'email_id' => $cc_json->email,
                                    'name' => $cc_json->name
                                );
                            }

                            // $this->plog($custom_data);
                            $mail_send_res = $this->mail_report($custom_data);

                            if ($mail_send_res['status_code'] == 1) {
                                // Update report_send_datetime column
                                $this->mdl_common->custom_query("UPDATE tbl_report_send SET report_send_datetime = '" . date('Y-m-d H:i:s') . "' WHERE report_auto_id = " . $single['report_auto_id']);
                            }
                        }


                        if ($total_fetch_report_send > 1) {
                            sleep(1);
                        }
                    }

                    $last_report_id = end($fetch_report_send)['report_auto_id'];
                    $this->update_setting_value("CRON_SEND_MAIL_REPORT_EVERY_6_MONTH", $last_report_id);
                } else {
                    echo 'All done';
                    $this->update_setting_value("CRON_SEND_MAIL_REPORT_EVERY_6_MONTH", 0);
                }
            } else {
                echo "Settings not found";
            }
        } else {
            echo "This is not good day to run this method.";
        }
    }

    function send_mail_report_every_1_year()
    {

        $current_month_day = date('m-d');
        // $current_month_day = "01-01";

        $current_month_day_array = ["01-01"];

        if (in_array($current_month_day, $current_month_day_array)) {

            $get_report_auto_id = $this->get_setting_value("CRON_SEND_MAIL_REPORT_EVERY_YEAR");

            if ($get_report_auto_id != null) {

                $option_for_fetch = array(
                    'select' => '*',
                    'from' => 'tbl_report_send',
                    'where' => "report_status = 1 AND report_schedule = 6 AND (report_send_datetime IS NULL OR DATE_FORMAT(report_send_datetime,'%Y-%m-%d') != '" . date('Y-m-d') . "') AND report_auto_id > $get_report_auto_id ",
                    'pagination' => array(
                        'limit' => 1
                    ),
                    'order_by' => array(
                        'key' => 'report_auto_id',
                        'order' => 'ASC'
                    )
                );
                $fetch_report_send = $this->mdl_common->select($option_for_fetch);

                // echo $this->db->last_query();
                // $this->plog($fetch_report_send);

                if ($fetch_report_send) {
                    $last_report_id = end($fetch_report_send)['report_auto_id'];

                    $total_fetch_report_send = count($fetch_report_send);

                    foreach ($fetch_report_send as $single) {
                        // $this->plog($single, 1);

                        $query = "SELECT t1.app_auto_id,t1.admob_app_id,t2.admob_access_token,
                                (SELECT GROUP_CONCAT(ad_unit_display_name) FROM tbl_ad_units as t3 WHERE t3.au_auto_id IN (" . ($single['report_au_auto_id']) . ")) as all_ad_units 
                                FROM tbl_apps as t1 
                                JOIN tbl_admob_account as t2 ON t2.admob_auto_id = t1.admob_auto_id 
                                WHERE t1.app_auto_id = " . $single['report_app_auto_id'];
                        $app_info = $this->mdl_common->custom_query($query)->row_array();

                        if ($app_info) {
                            // $this->plog($app_info);

                            // [Changable Variables]
                            $access_token = $app_info['admob_access_token'];
                            $app_id = $app_info['admob_app_id'];
                            $adUnitdisplayLabel_array = explode(',', $app_info['all_ad_units']);

                            $from_email = DEFAULT_FROM_EMAIL;
                            $from_email_name = DEFAULT_FROM_EMAIL_NAME;

                            $to_email_array = [];

                            if ($single['report_send_to_email']) {
                                $report_cc_email_json = json_decode($single['report_send_to_email']);
                                foreach ($report_cc_email_json as $singleToMail) {
                                    $to_email_array[] = array("Email" => $singleToMail->email, "Name" => $singleToMail->name);
                                }
                            }

                            // [/Changable Variables]

                            $custom_data = array(
                                'admob_access_token' => $access_token,
                                'app_id' => $app_id,
                                'ad_unit_display_lables' => $adUnitdisplayLabel_array,
                                'report_range_type' => $single['report_range_type'],
                                'mail_setting' => array(
                                    'from' => array(
                                        'email_id' => $from_email,
                                        'name' => $from_email_name
                                    ),
                                    'to' => $to_email_array
                                )
                            );

                            if ($single['report_cc_email']) {
                                $cc_json = json_decode($single['report_cc_email']);

                                $custom_data['mail_setting']['cc'] = array(
                                    'email_id' => $cc_json->email,
                                    'name' => $cc_json->name
                                );
                            }

                            $mail_send_res = $this->mail_report($custom_data);

                            if ($mail_send_res['status_code'] == 1) {
                                // Update report_send_datetime column
                                $this->mdl_common->custom_query("UPDATE tbl_report_send SET report_send_datetime = '" . date('Y-m-d H:i:s') . "' WHERE report_auto_id = " . $single['report_auto_id']);
                            }
                        }

                        if ($total_fetch_report_send > 1) {
                            sleep(1);
                        }
                    }

                    $this->update_setting_value("CRON_SEND_MAIL_REPORT_EVERY_YEAR", $last_report_id);
                } else {
                    echo 'All done';
                    $this->update_setting_value("CRON_SEND_MAIL_REPORT_EVERY_YEAR", 0);
                }
            } else {
                echo "Settings not found";
            }
        } else {
            echo "This is not good day to run this method.";
        }
    }
    /*Send Mail Report*/
}
