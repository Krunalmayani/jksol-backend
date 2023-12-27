<?php

use Google\Service\CloudSourceRepositories\Repo;

defined('BASEPATH') or exit('No direct script access allowed');
include_once(dirname(__FILE__) . "/Common.php");

class Mail_report extends Common
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
        $client->setRedirectUri(current_url());
        $client->setAccessToken(trim($access_token));

        $tokenValid = 0;
        $getAccessToken = array();
        if ($client->getAccessToken()) {
            $getAccessToken = $client->getAccessToken();

            // echo "<pre>";
            // print_r($getAccessToken);
            // exit;

            if ($client->isAccessTokenExpired()) {
                $getRefreshToken = $client->getRefreshToken();

                echo "<pre>";
                print_r($getAccessToken);
                // exit;

                if ($getRefreshToken) {

                    $authData = $client->fetchAccessTokenWithRefreshToken($getRefreshToken);

                    echo "Auth Data";
                    print_r($authData);

                    if (array_key_exists('error', $authData)) {
                        // throw new Exception(join(', ', $authData));
                        print_r($authData);
                        $tokenValid = 0;
                    } else {
                        $tokenValid = 1;
                    }
                } else {
                    echo "Refresh Token not fetched";
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
                'service' => $service
            );
        } else {
            echo "Invalid Access Token";
            exit;
        }
    }

    function send_mail_report($custom_data)
    {

        $current_time = date('H:i');

        if (($current_time >= '05:30') && ($current_time <= '23:30')) {

            if ((isset($custom_data['access_token']) && !empty($custom_data['access_token'])) &&
                (isset($custom_data['app_id']) && !empty($custom_data['app_id'])) &&
                (isset($custom_data['mail_setting']['from']['email_id']) && !empty($custom_data['mail_setting']['from']['email_id'])) &&
                (isset($custom_data['mail_setting']['to']['email_id']) && !empty($custom_data['mail_setting']['to']['email_id']))
            ) {

                // [Changable Variables]
                $access_token = $custom_data['access_token'];
                $app_id = $custom_data['app_id'];
                $adUnitdisplayLabel_array = $custom_data['ad_unit_display_lables'];

                $from_email  = $custom_data['mail_setting']['from']['email_id'];
                $from_email_name  = isset($custom_data['mail_setting']['from']['name']) ? $custom_data['mail_setting']['from']['name'] : "Admob Report";

                $to_email  = $custom_data['mail_setting']['to']['email_id'];
                $to_email_name  = isset($custom_data['mail_setting']['to']['name']) ? $custom_data['mail_setting']['to']['name'] : "Admob Report";

                $cc_email  = isset($custom_data['mail_setting']['cc']['email_id']) ? $custom_data['mail_setting']['cc']['email_id'] : "";
                $cc_email_name  = isset($custom_data['mail_setting']['cc']['name']) ? $custom_data['mail_setting']['cc']['name'] : "";
                // [/Changable Variables]

                $app_id_parts = explode("~", $app_id);
                if (isset($app_id_parts[0])) {
                    $app_id_parts = explode("-", $app_id_parts[0]);
                    if (isset($app_id_parts[3])) {
                        $admob_pub_id = $app_id_parts[2] . "-" . $app_id_parts[3];
                    }
                }

                $google_client_init = $this->google_client_init($access_token);

                $getAccessToken = $google_client_init['getAccessToken'];
                $service = $google_client_init['service'];

                // Get list of accounts.
                $result = $service->accounts->listAccounts();
                $accounts = $result->account;

                // Return first account name.
                $accountName = $accounts[0]['name'];

                if (isset($accountName)) {

                    // $startDate = $this->toDate(getdate(strtotime(date('Y-m-01'))));
                    // $endDate = $this->today();

                    $startDate = $this->thirtyDaysBeforeToday();
                    $endDate = $this->yesterday();

                    // Specify date range.
                    $dateRange = new \Google_Service_AdMob_DateRange();
                    $dateRange->setStartDate($startDate);
                    $dateRange->setEndDate($endDate);

                    // Specify dimension filters.
                    $apps = new \Google_Service_AdMob_StringList();
                    $apps->setValues([$app_id]);
                    $dimensionFilterMatches = new \Google_Service_AdMob_MediationReportSpecDimensionFilter();
                    $dimensionFilterMatches->setDimension('APP');
                    $dimensionFilterMatches->setMatchesAny($apps);

                    // Create network report specification.
                    $dimensions = ['APP', 'COUNTRY', 'AD_UNIT', 'DATE'];
                    $metrics = ['ESTIMATED_EARNINGS', 'IMPRESSION_RPM', 'MATCHED_REQUESTS', 'SHOW_RATE', 'IMPRESSIONS', 'IMPRESSION_CTR', 'CLICKS'];  // IMPRESSION_RPM - for OBSERVED_ECPM as (OBSERVED_ECPM) metric not working in network report
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

                    $networkReportResponse = $service->accounts_networkReport->generate(
                        $accountName,
                        $networkReportRequest
                    );

                    // Convert network report response to a simple object.
                    $networkReportResponse = $networkReportResponse->tosimpleObject();

                    $finalData = array();

                    // Print each record in the report.
                    if (!empty($networkReportResponse)) {
                        $currencyCode = "USD";

                        foreach ($networkReportResponse as $record) {
                            // printf("'%s' \n", json_encode($record));

                            if (isset($record['row'])) {

                                foreach ($record as $row) {
                                    $reportData = array();

                                    $dimension = $row['dimensionValues'];
                                    $metric = $row['metricValues'];

                                    if (isset($dimension['APP']['value']) && isset($dimension['AD_UNIT']['value']) && isset($dimension['DATE']['value']) && $dimension['DATE']['value'] != '') {

                                        $adAppId = $dimension['APP']['value'];
                                        $adUnitdisplayLabel = $dimension['AD_UNIT']['displayLabel'];

                                        if (in_array($adUnitdisplayLabel, $adUnitdisplayLabel_array)) {

                                            $adUnitId = $dimension['AD_UNIT']['value'];
                                            $reportDate = $dimension['DATE']['value'];
                                            $countryName = (isset($dimension['COUNTRY']['value']) ? $dimension['COUNTRY']['value'] : 'UR');

                                            $reportData['app_name'] = $dimension['APP']['displayLabel'];
                                            $reportData['ad_type'] = $adUnitdisplayLabel;
                                            $reportData['report_country'] = $this->get_country_name($countryName);

                                            $dateFormat = substr($reportDate, 0, 4) . '-' . substr($reportDate, 4, 2) . '-' . substr($reportDate, 6, 7);
                                            $reportData['report_date'] = $dateFormat;

                                            $est_earn = 0;
                                            if (isset($metric['ESTIMATED_EARNINGS']['microsValue'])) {
                                                $est_earn = $this->microValueConvert($metric['ESTIMATED_EARNINGS']['microsValue'], $currencyCode);
                                            }
                                            $reportData['report_estimate_earnings'] = number_format($est_earn, 2);

                                            $reportData['report_observed_ecpm'] = (isset($metric['IMPRESSION_RPM']['doubleValue']) ? number_format($metric['IMPRESSION_RPM']['doubleValue'], 2) : 0);
                                            $reportData['report_ad_request'] = (isset($metric['AD_REQUESTS']['integerValue']) ? $metric['AD_REQUESTS']['integerValue'] : 0);

                                            $matchRate = 0;
                                            $isMatchRate = 0;
                                            if (isset($metric['MATCH_RATE']['doubleValue'])) {
                                                $matchRate = ($metric['MATCH_RATE']['doubleValue'] * 100);
                                                $isMatchRate = 1;
                                            }
                                            $reportData['report_match_rate'] = ($isMatchRate == 1 ? number_format($matchRate, 2) : "0.00") . "%";

                                            $reportData['report_matched_request'] = (isset($metric['MATCHED_REQUESTS']['integerValue']) ? $metric['MATCHED_REQUESTS']['integerValue'] : 0);

                                            $showRate = 0;
                                            $isShowRate = 0;
                                            if (isset($metric['SHOW_RATE']['doubleValue'])) {
                                                $showRate = ($metric['SHOW_RATE']['doubleValue'] * 100);
                                                $isShowRate = 1;
                                            }
                                            $reportData['report_show_rate'] = ($isShowRate == 1 ? number_format($showRate, 2) : "0.00") . "%";

                                            $reportData['report_impression'] = (isset($metric['IMPRESSIONS']['integerValue']) ? $metric['IMPRESSIONS']['integerValue'] : 0);

                                            $ctrValue = 0;
                                            $isCTR = 0;
                                            if (isset($metric['IMPRESSION_CTR']['doubleValue'])) {
                                                $ctrValue = ($metric['IMPRESSION_CTR']['doubleValue'] * 100);
                                                $isCTR = 1;
                                            }
                                            $reportData['report_ctr'] = ($isCTR == 1 ? number_format($ctrValue, 2) : "0.00") . "%";

                                            $reportData['report_clicks'] = (isset($metric['CLICKS']['integerValue']) ? $metric['CLICKS']['integerValue'] : 0);

                                            $finalData[] = $reportData;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if (count($finalData) > 0) {

                        $reportHeader = array(
                            'App', 'Ad Unit', 'Country', 'Date', 'Est. earnings (USD)', 'Observed eCPM (USD)', 'Requests', 'Matched Rate (%)', 'Matched Requests', 'Show Rate (%)',
                            'Impressions', 'CTR (%)', 'Clicks'
                        );
                        array_unshift($finalData, $reportHeader);
                        $fileName = "admob-report-" . rand(11111, 99999) . ".csv";

                        $file = fopen("uploads/$fileName", 'w');
                        foreach ($finalData as $value) {
                            fputcsv($file, $value);
                        }
                        fclose($file);

                        $base64_content = base64_encode(file_get_contents("uploads/$fileName"));

                        $email_subject = "Admob Report of Publisher $admob_pub_id";
                        $filename_for_mail = "Report_$admob_pub_id" . ".csv";
                        $html_part = "<h3>Admob report </h3>";

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, 'https://api.mailjet.com/v3.1/send');
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            'Content-Type: application/json',
                        ]);
                        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                        curl_setopt($ch, CURLOPT_USERPWD, '5b49a0ae4c59e4dfae5179bb89d1bf1a:d215e12f4a00ae428b820e792abe01f6');
                        curl_setopt($ch, CURLOPT_POSTFIELDS, '{"Messages":[{"From":{"Email": "' . $from_email . '","Name": "' . $from_email_name . '"},"To": [{"Email": "' . $to_email . '","Name": "' . $to_email_name . '"}],"Cc": [{"Email": "' . $cc_email . '","Name": "' . $cc_email_name . '"}],"Subject": "' . $email_subject . '","TextPart": "","HTMLPart": "' . $html_part . '","Attachments":[{"ContentType": "text/csv","Filename": "' . $filename_for_mail . '","Base64Content": "' . $base64_content . '"}]}]}');
                        $response = curl_exec($ch);
                        curl_close($ch);

                        unlink('uploads/' . $fileName);

                        echo "<pre>";
                        print_r($response);
                    } else {
                        echo "No data available";
                    }
                } else {
                    echo 'Please specify the account_name, which should follow a format of
                        "accounts/pub-XXXXXXXXXXXXXXXX".
                        See https://support.google.com/admob/answer/2784578
                        Sfor instructions on how to find your account name.';
                }

                echo '</pre>';
            } else {
                echo "Requierd parameters are missing....";
            }
        } else {
            echo "Not running at this time.";
        }
    }
    // [/Common Methods for Cron]

    function send_report_for_test()
    {

        // [Changable Variables] TESTING
        $access_token = '{"access_token":"ya29.a0AVvZVspAoHjnpJjbmnYVRLn4eqoDT75tOD1eqgGirvXfjTx_xP_GhysVWLMImexSBDABx8eLEMFyFu3K8ULelfbgXftDHF1JP0r73MVbO_w7pqnFvy4U8hpi634ELoMSSEL4WHnfQ1TV3KqkbD3G1fSjxlbgaCgYKAWISARISFQGbdwaImMz6S31F1XGLyR13-DO6vQ0163","expires_in":3599,"refresh_token":"1//0g6Iv1uTZth_PCgYIARAAGBASNwF-L9Ir3hkeOyQQG_U5rxJW6UNzC-VhmOM0SlnkBPkCc8SXF3ZZbvtcaNF9DxWnCU2sMnzldjQ","token_type":"Bearer"}';
        $app_id = "ca-app-pub-2728254433592450~9982131339";
        $adUnitdisplayLabel_array = array(
            "bank interstitial",
            "bank banner"
        );

        $from_email = "pratik@jksol.com";
        $from_email_name = "Kalpesh Padshala";

        $to_email = "pratik.jksol@gmail.com";
        $to_email_name = "Report Calldorado";

        $cc_email = "pratik@jksol.com";
        $cc_email_name = "Pratik CC";
        // [/Changable Variables]

        $custom_data = array('access_token' => $access_token, 'app_id' => $app_id, 'ad_unit_display_lables' => $adUnitdisplayLabel_array, 'mail_setting' => array('from' => array('email_id' => $from_email, 'name' => $from_email_name), 'to' => array('email_id' => $to_email, 'name' => $to_email_name), 'cc' => array('email_id' => $cc_email, 'name' => $cc_email_name)));
        $this->send_mail_report($custom_data);
    }
}
