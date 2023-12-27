<?php
defined('BASEPATH') or exit('No direct script access allowed');
include_once(dirname(__FILE__) . "/Common.php");

class Api extends Common
{

    function __construct()
    {
        parent::__construct();

        $this->msg = "";
        $this->result = array();
    }

    function verify_user($user_id, $user_token)
    {
        $option = array(
            'select' => 'token_auto_id',
            'from' => 'tbl_user_token',
            'where' => array(
                'token_user_id' => $user_id,
                'user_token' => $user_token
            ),
            'pagination' => array(
                'limit' => 1
            )
        );
        $token_info = $this->mdl_common->select($option);

        if (isset($token_info[0]['token_auto_id'])) {

            $option_user = array(
                'from' => 'tbl_user',
                'where' => array(
                    'user_id' => $user_id
                ),
                'pagination' => array(
                    'limit' => 1
                )
            );
            $user_info = $this->mdl_common->select($option_user);

            return $user_info[0];
        } else {
            $this->msg = 'Unauthorised User.';
            $this->_sendResponse(2);
        }
    }

    function required_parameter($data, $parameter)
    {
        if (isset($data[$parameter]) && !empty($data[$parameter])) {
            return $data[$parameter];
        } else {
            $this->msg = "$parameter is missing or it should not null";
            $this->_sendResponse(9);
        }
    }

    function _sendResponse($status_code = 200)
    {
        $this->result['msg'] = $this->msg;
        $this->result['status_code'] = $status_code;

        $result = empty($this->result) ? '' : $this->result;
        $this->cors_header();
        echo json_encode($result);
        die();
    }

    // API Methods for Web Panel
    public function web_login()
    {
        $postData = $this->input->post();

        $user_email = $this->required_parameter($postData, 'user_email');
        $user_password = $this->required_parameter($postData, 'user_password');

        $option = array(
            'select' => 'user_id,user_email,user_name,user_role,user_status',
            'from' => 'tbl_user',
            'where' => array(
                'user_email' => $user_email,
                'user_password' => md5($user_password)
            ),
            'pagination' => array(
                'limit' => 1
            )
        );

        $user_info = $this->mdl_common->select($option);

        if (isset($user_info[0]['user_id'])) {

            if ($user_info[0]['user_status'] == 0) {
                $this->msg = '<b>Error:</b> Please contact administrator to reactivate your account.';
                $this->_sendResponse(0);
            } else {

                // Create & Save user_token
                $user_token = $this->get_random_string(10);
                $current_datetime = date('Y-m-d H:i:s');

                $token_option = array(
                    'from' => 'tbl_user_token',
                    'insert_data' => array(
                        'token_user_id' => $user_info[0]['user_id'],
                        'user_token' => $user_token,
                        'token_created_at' => $current_datetime
                    )
                );
                $insert = $this->mdl_common->insert($token_option);
                if ($insert) {

                    $final_user_info = $user_info[0];
                    $final_user_info['user_token'] = $user_token;

                    $this->result['info'] = $final_user_info;
                    $this->msg = 'success.';
                    $this->_sendResponse(1);
                } else {
                    $this->msg = '<b>Error:</b> Server busy now, Please try again.';
                    $this->_sendResponse(0);
                }
            }
        } else {
            $this->msg = '<b>Error:</b> Your email and password do not match. Please try again..';
            $this->_sendResponse(0);
        }
    }

    public function web_logout()
    {
        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $this->verify_user($user_id, $user_token);

        $option = array(
            'from' => 'tbl_user_token',
            'where' => array(
                'token_user_id' => $user_id,
                'user_token' => $user_token
            )
        );

        $this->mdl_common->delete($option);

        $this->msg = "Successfully logout.";
        $this->_sendResponse(1);
    }

    // For User section

    public function add_user()
    {
        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        if ($user_info['user_role'] == 1) {

            $user_name = $this->required_parameter($postData, 'user_name');
            $user_email = $this->required_parameter($postData, 'user_email');
            $user_password = $this->required_parameter($postData, 'user_password');
            $user_role = $this->required_parameter($postData, 'user_role');

            // check user exist by email
            $checkOption = array(
                'select' => 'user_id',
                'from' => 'tbl_user',
                'where' => "user_email = '$user_email' ",
                'pagination' => array(
                    'limit' => 1
                )
            );

            $existRes = $this->mdl_common->select($checkOption);

            if (isset($existRes[0]['user_id'])) {
                $this->msg = "Email already exist";
                $this->_sendResponse(0);
            } else {

                $current_datetime = date('Y-m-d H:i:s');

                $option = array(
                    'from' => 'tbl_user',
                    'insert_data' => array(
                        'user_email' => $user_email,
                        'user_name' => $user_name,
                        'user_password' => md5($user_password),
                        'user_role' => $user_role,
                        'user_added_by' => $user_id,
                        'user_created_at' => $current_datetime,
                        'user_updated_at' => $current_datetime
                    )
                );

                $insert = $this->mdl_common->insert($option);

                if ($insert) {
                    $this->msg = "User added successfully";
                    $this->_sendResponse(1);
                } else {

                    $this->msg = "Database error";
                    $this->_sendResponse(0);
                }
            }
        } else {
            $this->msg = "Permission denied";
            $this->_sendResponse(0);
        }
    }

    public function update_user()
    {
        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        if ($user_info['user_role'] == 1) {
            $postData = $this->input->post();

            $user_unique_id = $this->required_parameter($postData, 'user_unique_id');
            $user_name = $this->required_parameter($postData, 'user_name');
            $user_email = $this->required_parameter($postData, 'user_email');
            $user_role = $this->required_parameter($postData, 'user_role');

            // check email already exist
            $checkOption = array(
                'select' => 'user_id',
                'from' => 'tbl_user',
                'where' => "user_email = '$user_email' AND user_id != '$user_unique_id'",
                'pagination' => array(
                    'limit' => 1
                )
            );

            $existRes = $this->mdl_common->select($checkOption);

            // $this->plog($existRes);

            if (isset($existRes[0]['user_id'])) {
                $this->msg = "Email already exist";
                $this->_sendResponse(0);
            } else {

                $current_datetime = date('Y-m-d H:i:s');
                $option = array(
                    'from' => 'tbl_user',
                    'update_data' => array(
                        'user_email' => $user_email,
                        'user_name' => $user_name,
                        'user_role' => $user_role,
                        'user_updated_at' => $current_datetime
                    ),
                    'where' => array(
                        'user_id' => $user_unique_id
                    )
                );

                if (isset($postData['user_password']) && !empty($postData['user_password'])) {
                    $option['update_data']['user_password'] = md5($postData['user_password']);
                }

                if (isset($postData['user_status'])) {
                    $option['update_data']['user_status'] = $postData['user_status'];
                }

                $update = $this->mdl_common->update($option);

                // echo $this->db->last_query();
                // exit;

                if ($update) {
                    $this->msg = "User updated successfully";
                    $this->_sendResponse(1);
                } else {

                    $this->msg = "Database error";
                    $this->_sendResponse(0);
                }
            }
        } else {
            $this->msg = "Permission denied";
            $this->_sendResponse(0);
        }
    }

    public function user_list()
    {

        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        $this->load->model('Mdl_datatable', 'mdl_datatable');

        $option = array();
        $aaData = array();

        $total_records = $this->mdl_datatable->get_user_list($option, $is_count = 1);

        $limit = isset($_POST['length']) ? $_POST['length'] : 15;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;

        $resultSet = $this->mdl_datatable->get_user_list($option, $is_count = 0, $start, $limit);

        $singleListArray = array();
        if ($resultSet) {

            $increment_id = ($start == 0) ? 1 : ($start + 1);

            foreach ($resultSet as $row) {
                $singleListArray['increment_id'] = $increment_id;

                $singleListArray['user_id'] = $row->user_id;
                $singleListArray['user_email'] = $row->user_email;
                $singleListArray['user_name'] = $row->user_name;

                $user_role_db = $row->user_role;

                switch ($user_role_db) {
                    case "1":
                        $user_role = '<span class="badge bg-secondary">Admin</span>';
                        break;
                    case "2":
                        $user_role = '<span class="badge badge-soft-info">Normal</span>';
                        break;
                    default:
                        $user_role = '<span class="badge badge-soft-info">Normal</span>';
                        break;
                }
                $singleListArray['user_role'] = $user_role;

                $user_status_db = $row->user_status;
                switch ($user_status_db) {
                    case "0":
                        $user_status = '<span class="badge badge-soft-danger">Inactive</span>';
                        break;
                    case "1":
                        $user_status = '<span class="badge badge-soft-success">Active</span>';
                        break;
                    default:
                        $user_status = '<span class="badge badge-soft-success">Active</span>';
                        break;
                }

                $singleListArray['user_status'] = $user_status;

                $singleListArray['user_created_at'] = date('d-m-Y', strtotime($row->user_created_at));
                $singleListArray['user_updated_at'] = date('d-m-Y', strtotime($row->user_updated_at));

                $action = '<i class="ri-edit-box-line ri-xl editUserBtn cursor-pointer align-middle" title="Edit User" data-bs-toggle="modal" data-bs-target="#editUserModal" data-user_id="' . $row->user_id . '"></i>';

                $singleListArray['action'] = $action;

                $aaData[] = $singleListArray;

                $increment_id++;
            }
        }

        $finalJsonArray['sEcho'] = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;
        $finalJsonArray['iTotalRecords'] = $total_records;
        $finalJsonArray['iTotalDisplayRecords'] = $total_records;
        $finalJsonArray['aaData'] = $aaData;
        $this->cors_header();
        echo json_encode($finalJsonArray);
    }

    public function get_user_detail()
    {
        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        if ($user_info['user_role'] == 1) {

            $postData = $this->input->post();

            $user_unique_id = $this->required_parameter($postData, 'user_unique_id');

            $option = array(
                'from' => "tbl_user",
                'where' => array(
                    'user_id' => $user_unique_id
                ),
                'pagination' => array(
                    'limit' => 1
                )
            );

            $info = $this->mdl_common->select($option);

            if (isset($info[0]['user_id'])) {
                $this->result['info'] = $info[0];
                $this->msg = 'success';
                $this->_sendResponse(1);
            } else {
                $this->msg = 'User not found';
                $this->_sendResponse(0);
            }
        } else {
            $this->msg = 'Permission denied';
            $this->_sendResponse(0);
        }
    }

    // For Admob Account section

    public function auth_admob_account()
    {

        $client = new Google_Client();

        $client->addScope('https://www.googleapis.com/auth/admob.readonly');
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        $client->setIncludeGrantedScopes(true);
        $client->setAuthConfig('application/third_party/client_secrets.json');
        $service = new Google_Service_AdMob($client);

        $client->setRedirectUri(base_url() . 'api/auth_admob_account');

        if (isset($_GET['code'])) {
            $client->authenticate($_GET['code']);
            $access_info = $client->getAccessToken();

            // header('Location: ' . filter_var(current_url(), FILTER_SANITIZE_URL));
            // exit;
        }

        if (isset($access_info) && $access_info) {
            $client->setAccessToken($access_info);
        } else {
            // If we're doing disk storage, generate a URL that forces user approval.
            // This is the only way to guarantee we get back a refresh token.
            $authUrl = $client->createAuthUrl();
        }

        if (isset($authUrl)) {
            // No access token found, show the link to generate one
            // printf("<a class='login' href='%s'>Login!</a>", $authUrl);

            header('Location: ' . $authUrl);
        } else {
            // print "<a class='logout' href='?logout'>Logout</a>";
        }

        if ($client->getAccessToken()) {

            echo '<pre class="result">';
            // Now we're signed in, we can make our requests.

            $result = $service->accounts->listAccounts();
            $accounts = $result->account;

            if (!empty($accounts) && isset($accounts[0])) {
                $account = $accounts[0];

                $token = $client->getAccessToken();

                if ($token != '') {
                    $data['account_name'] = $account['name'];
                    $data['account_pub_id'] = $account['publisherId'];
                    $data['currency_code'] = $account['currencyCode'];
                    $data['reporting_timezone'] = $account['reportingTimeZone'];
                    // $res = $this->formInputField($data);

                    $accessTokenArr = $token;
                    unset($accessTokenArr['scope']);
                    unset($accessTokenArr['created']);

                    $data['access_token'] = json_encode($accessTokenArr);
                    echo "<pre>";
                    print_r($data);
                } else {
                    printf(
                        "Publisher Id: %s \n
                    Currency Code: %s \n
                    Reporting Time Zone: %s \n",
                        $account['publisherId'],
                        $account['currencyCode'],
                        $account['reportingTimeZone']
                    );
                }
            } else {
                echo 'Please specify the account_name, which should follow a format of
                "accounts/pub-XXXXXXXXXXXXXXXX".
                See https://support.google.com/admob/answer/2784578
                Sfor instructions on how to find your account name.';
            }

            // Note that we re-store the access_info bundle, just in case anything
            // changed during the request - the main thing that might happen here is the
            // access token itself is refreshed if the application has offline access.
            // $_SESSION['access_info'] = $client->getAccessToken();
            echo '</pre>';
        }
    }

    public function add_admob_account()
    {
        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        $admob_email = $this->required_parameter($postData, 'admob_email');
        $admob_pub_id = $this->required_parameter($postData, 'admob_pub_id');
        $admob_access_token = $this->required_parameter($postData, 'admob_access_token');

        $option = array(
            'select' => 'admob_auto_id,admob_email,admob_pub_id,admob_access_token',
            'from' => 'tbl_admob_account',
            'where' => "admob_email = '$admob_email' OR admob_pub_id = '$admob_pub_id' OR admob_access_token = '$admob_access_token' ",
            'pagination' => array(
                'limit' => 1
            )
        );

        $res_info = $this->mdl_common->select($option);

        if (isset($res_info[0])) { // Record exist

            if ($res_info[0]['admob_email'] == $admob_email) {
                $error_msg = "Email already exist";
                $filed_name = "admob_email";
            } else if ($res_info[0]['admob_pub_id'] == $admob_pub_id) {
                $error_msg = "Publisher ID already exist";
                $filed_name = "admob_pub_id";
            } else {
                $error_msg = "Access token already exist";
                $filed_name = "admob_access_token";
            }

            $this->result['field_name'] = $filed_name;
            $this->msg = $error_msg;
            $this->_sendResponse(0);
        } else {

            $current_datetime = date('Y-m-d H:i:s');
            $option = array(
                'from' => 'tbl_admob_account',
                'insert_data' => array(
                    'admob_added_by' => $user_id,
                    'admob_email' => $admob_email,
                    'admob_pub_id' => $admob_pub_id,
                    'admob_access_token' => $admob_access_token,
                    'admob_created_at' => $current_datetime,
                    'admob_updated_at' => $current_datetime
                )
            );

            $insert = $this->mdl_common->insert($option);

            if ($insert) {
                $this->msg = "Account added successfully";
                $this->_sendResponse(1);
            } else {

                $this->msg = "Database error";
                $this->_sendResponse(0);
            }
        }
    }

    public function update_admob_account()
    {
        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        $admob_auto_id = $this->required_parameter($postData, 'admob_auto_id');
        $admob_email = $this->required_parameter($postData, 'admob_email');
        $admob_pub_id = $this->required_parameter($postData, 'admob_pub_id');
        $admob_access_token = $this->required_parameter($postData, 'admob_access_token');

        $option = array(
            'select' => 'admob_auto_id,admob_email,admob_pub_id,admob_access_token',
            'from' => 'tbl_admob_account',
            'where' => " (admob_email = '$admob_email' OR admob_pub_id = '$admob_pub_id' OR admob_access_token = '$admob_access_token') AND admob_auto_id != $admob_auto_id ",
            'pagination' => array(
                'limit' => 1
            )
        );

        $res_info = $this->mdl_common->select($option);

        if (isset($res_info[0])) {

            // Record exist
            if ($res_info[0]['admob_email'] == $admob_email) {
                $error_msg = "Email already exist";
                $filed_name = "admob_email";
            } else if ($res_info[0]['admob_pub_id'] == $admob_pub_id) {
                $error_msg = "Publisher ID already exist";
                $filed_name = "admob_pub_id";
            } else {
                $error_msg = "Access token already exist";
                $filed_name = "admob_access_token";
            }

            $this->result['field_name'] = $filed_name;
            $this->msg = $error_msg;
            $this->_sendResponse(0);
        } else {

            $current_datetime = date('Y-m-d H:i:s');
            $option = array(
                'from' => 'tbl_admob_account',
                'update_data' => array(
                    'admob_email' => $admob_email,
                    'admob_pub_id' => $admob_pub_id,
                    'admob_access_token' => $admob_access_token,
                    'admob_updated_at' => $current_datetime
                ),
                'where' => array(
                    'admob_auto_id' => $admob_auto_id
                )
            );

            $update = $this->mdl_common->update($option);

            if ($update) {
                $this->msg = "Account updated successfully";
                $this->_sendResponse(1);
            } else {

                $this->msg = "Database error";
                $this->_sendResponse(0);
            }
        }
    }

    public function get_admob_account_detail()
    {
        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        $admob_pub_id = $this->required_parameter($postData, 'admob_pub_id');

        $option = array(
            'select' => "admob_auto_id,admob_email,admob_pub_id,admob_access_token",
            'from' => "tbl_admob_account",
            'where' => array(
                'admob_pub_id' => $admob_pub_id
            ),
            'pagination' => array(
                'limit' => 1
            )
        );

        $info = $this->mdl_common->select($option);

        if (isset($info[0]['admob_pub_id'])) {
            $this->result['info'] = $info[0];
            $this->msg = 'success.';
            $this->_sendResponse(1);
        } else {
            $this->msg = 'Account not found ';
            $this->_sendResponse(0);
        }
    }

    public function admob_account_list()
    {

        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        $this->load->model('Mdl_datatable', 'mdl_datatable');

        $aaData = array();

        if ($user_info['user_role'] != 1) {
            $option = array(
                'where' => "admob_added_by = " . $user_info['user_id']
            );
        } else {
            $option = array();
        }

        $total_records = $this->mdl_datatable->get_admob_account_list($option, $is_count = 1);

        $limit = isset($_POST['length']) ? $_POST['length'] : 15;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;

        $resultSet = $this->mdl_datatable->get_admob_account_list($option, $is_count = 0, $start, $limit);

        $singleListArray = array();
        if ($resultSet) {

            $increment_id = ($start == 0) ? 1 : ($start + 1);

            foreach ($resultSet as $row) {
                $singleListArray['increment_id'] = $increment_id;
                $singleListArray['admob_email'] = $row->admob_email;
                $singleListArray['admob_pub_id'] = $row->admob_pub_id;
                $singleListArray['admob_created_at'] = date('d-m-Y', strtotime($row->admob_created_at));
                $singleListArray['admob_updated_at'] = date('d-m-Y', strtotime($row->admob_updated_at));
                $singleListArray['action'] = '<i class="ri-edit-box-line ri-xl editAccountBtn cursor-pointer align-middle" title="Edit Account" data-bs-toggle="modal" data-bs-target="#editAccountModal" data-admob_pub_id="' . $row->admob_pub_id . '"></i>';
                $aaData[] = $singleListArray;

                $increment_id++;
            }
        }

        $finalJsonArray['sEcho'] = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;
        $finalJsonArray['iTotalRecords'] = $total_records;
        $finalJsonArray['iTotalDisplayRecords'] = $total_records;
        $finalJsonArray['aaData'] = $aaData;
        $this->cors_header();
        echo json_encode($finalJsonArray);
    }

    // for application section
    public function apps_list()
    {

        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        $this->load->model('Mdl_datatable', 'mdl_datatable');

        $aaData = array();

        $option = array(
            'user_info' => $user_info
        );

        $total_records = $this->mdl_datatable->get_apps_list($option, $is_count = 1);

        $limit = isset($_POST['length']) ? $_POST['length'] : 15;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;

        $resultSet = $this->mdl_datatable->get_apps_list($option, $is_count = 0, $start, $limit);

        $singleListArray = array();
        if ($resultSet) {

            $increment_id = ($start == 0) ? 1 : ($start + 1);

            foreach ($resultSet as $row) {

                $singleListArray['increment_id'] = $increment_id;

                $singleListArray['app_display_name'] = $row->app_display_name;
                $singleListArray['app_icon'] = $row->app_icon;
                $singleListArray['app_auto_id'] = $row->app_auto_id;
                $singleListArray['app_platform'] = $row->app_platform;

                $singleListArray['app_admob_app_id'] = '<span title="' . $row->admob_email . '">' . $row->app_admob_app_id . '</span>';

                $app_approval_state = $this->get_app_approval_state_by_short_id($row->app_approval_state);

                $final_app_approval_state = '';
                switch ($app_approval_state) {
                    case "APPROVED":
                        $final_app_approval_state = '<span class="badge badge-soft-success fw-normal" style="color: #137333 !important;"><i class="ri-checkbox-circle-line fs-17 fw-lighter align-middle"></i> Ready</span>';
                        break;
                    case "ACTION_REQUIRED":
                        $final_app_approval_state = '<span class="badge badge-soft-warning fw-normal" style="color: #4d484be5 !important;"><i class="ri-alert-line fs-17 fw-lighter align-middle"></i> Requires review</span>';
                        break;
                }

                $singleListArray['app_approval_state'] = $final_app_approval_state;
                $singleListArray['app_store_id'] = $row->app_store_id;

                // Query for admob ad ids
                $query = "SELECT t1.au_auto_id,t1.au_display_name,t1.au_id,t2.au_format_display_name 
                FROM tbl_ad_units as t1 
                JOIN tbl_ad_unit_format as t2 ON t2.au_format_auto_id = t1.au_format_auto_id ";

                if ($user_info['user_role'] != 1) {
                    $query .= " JOIN tbl_ad_unit_permissions as t3 ON t3.permission_au_auto_id = t1.au_auto_id ";
                }

                $query .= " WHERE t1.au_app_auto_id = " . $row->app_auto_id;

                $ad_unit_result = $this->mdl_common->custom_query($query)->result_array();
                $singleListArray['ad_units'] = $ad_unit_result;
                $singleListArray['total_ad_units'] = count($ad_unit_result) . ' units';

                $aaData[] = $singleListArray;

                $increment_id++;
            }
        }

        $finalJsonArray['sEcho'] = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;
        $finalJsonArray['iTotalRecords'] = $total_records;
        $finalJsonArray['iTotalDisplayRecords'] = $total_records;
        $finalJsonArray['aaData'] = $aaData;
        $this->cors_header();
        echo json_encode($finalJsonArray);
    }

    public function app_settings()
    {

        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        $app_auto_id = $this->required_parameter($postData, 'app_auto_id');

        $where = " WHERE t1.app_auto_id = $app_auto_id ";

        $option = "SELECT t1.* FROM tbl_apps as t1 
        JOIN tbl_ad_units as t2 ON t2.au_app_auto_id = t1.app_auto_id ";

        if ($user_info['user_role'] != 1) {
            $option .= " JOIN tbl_ad_unit_permissions as t3 ON t3.permission_au_auto_id = t2.au_auto_id ";
            $where .= " AND t3.permission_user_id = " . $user_info['user_id'];
        }

        $option .= " $where ";

        $info = $this->mdl_common->custom_query($option)->result_array();

        if (isset($info[0]['app_auto_id'])) {
            $this->result['app_info'] = $info[0];
            $this->msg = 'success.';
            $this->_sendResponse(1);
        } else {
            $this->msg = 'App not found ';
            $this->_sendResponse(0);
        }
    }

    public function list_app_ad_units()
    {

        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        $app_auto_id = $this->required_parameter($postData, 'app_auto_id');

        $where = " WHERE t1.au_app_auto_id = $app_auto_id ";

        $option = "SELECT t1.*,t3.au_format_display_name FROM tbl_ad_units as t1 
        JOIN tbl_ad_unit_format as t3 ON t3.au_format_auto_id = t1.au_format_auto_id ";

        if ($user_info['user_role'] != 1) {
            $option .= " JOIN tbl_ad_unit_permissions as t2 ON t2.permission_au_auto_id = t1.au_auto_id ";
            $where .= " AND t2.permission_user_id = " . $user_info['user_id'];
        }

        $option .= " $where ";

        $info = $this->mdl_common->custom_query($option)->result_array();

        if (isset($info[0]['au_auto_id'])) {
            $this->result['info'] = $info;
            $this->msg = 'success.';
            $this->_sendResponse(1);
        } else {
            $this->msg = 'App not found ';
            $this->_sendResponse(0);
        }
    }

    public function app_overview()
    {

        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        $app_auto_id = $this->required_parameter($postData, 'app_auto_id');

        $where = " WHERE t1.app_auto_id = $app_auto_id ";

        $option = "SELECT t1.app_auto_id,t1.app_display_name,t1.app_store_id,t1.app_icon,
        t1.app_platform,t1.app_approval_state,t1.app_console_name 
        FROM tbl_apps as t1 
        JOIN tbl_ad_units as t2 ON t2.au_app_auto_id = t1.app_auto_id ";

        if ($user_info['user_role'] != 1) {
            $option .= " JOIN tbl_ad_unit_permissions as t3 ON t3.permission_au_auto_id = t2.au_auto_id ";
            $where .= " AND t3.permission_user_id = " . $user_info['user_id'];
        }

        $option .= " $where ";

        $info = $this->mdl_common->custom_query($option)->result_array();

        if (isset($info[0]['app_auto_id'])) {
            $this->result['app_info'] = $info[0];

            // [for summary]
            $common_where = "";

            $common_query_string = "SELECT FORMAT(SUM(t1.report_estimated_earnings),2) as total_estimated_earnings 
            FROM tbl_report as t1 
            JOIN tbl_ad_units as t2 ON t2.au_auto_id = t1.report_au_auto_id 
            JOIN tbl_apps as t3 ON t3.app_auto_id = t2.au_app_auto_id ";

            if ($user_info['user_role'] != 1) {
                $common_query_string .= " JOIN tbl_ad_unit_permissions as t4 ON t4.permission_au_auto_id = t2.au_auto_id ";
                $common_where .= " AND t4.permission_user_id = " . $user_info['user_id'];
            }

            // QUERY FOR TODAY's Record
            $where_today_start_date = date("Y-m-d");
            $where_today_end_date = date("Y-m-d");
            $where_today = " t1.report_date >= '$where_today_start_date' AND t1.report_date <= '$where_today_end_date' ";

            $query_today = "$common_query_string WHERE t3.app_auto_id = $app_auto_id AND $where_today $common_where";
            $result_today = $this->mdl_common->custom_query($query_today)->result();

            // QUERY FOR YESTERDAY's Record
            $where_yesterday_start_date = date("Y-m-d", strtotime("1 days ago"));
            $where_yesterday_end_date = date("Y-m-d", strtotime("1 days ago"));

            $where_yesterday = " t1.report_date >= '$where_yesterday_start_date' AND t1.report_date <= '$where_yesterday_end_date' ";

            $query_yesterday = "$common_query_string WHERE t3.app_auto_id = $app_auto_id AND $where_yesterday $common_where";
            $result_yesterday = $this->mdl_common->custom_query($query_yesterday)->result();

            // QUERY FOR This month Record
            $where_this_month_start_date = date("Y-m-01");
            $where_this_month_end_date = date("Y-m-d");

            $where_this_month = " t1.report_date >= '$where_this_month_start_date' AND t1.report_date <= '$where_this_month_end_date' ";

            $query_this_month = "$common_query_string WHERE t3.app_auto_id = $app_auto_id AND $where_this_month $common_where";
            $result_this_month = $this->mdl_common->custom_query($query_this_month)->result();

            // QUERY FOR Last month Record
            $where_last_month_start_date = date("Y-m-d", strtotime('first day of last month'));
            $where_last_month_end_date = date("Y-m-d", strtotime('last day of last month'));

            $where_last_month = " t1.report_date >= '$where_last_month_start_date' AND t1.report_date <= '$where_last_month_end_date' ";

            $query_last_month = "$common_query_string WHERE t3.app_auto_id = $app_auto_id AND $where_last_month $common_where";
            $result_last_month = $this->mdl_common->custom_query($query_last_month)->result();

            $final_res = array(
                'app_info_today_so_far' => ($result_today[0]->total_estimated_earnings == null || $result_today[0]->total_estimated_earnings == '') ? '$0.00' : "$" . $this->indian_number_format($result_today[0]->total_estimated_earnings),
                'app_info_yesterday_so_far' => ($result_yesterday[0]->total_estimated_earnings == null || $result_yesterday[0]->total_estimated_earnings == '') ? '$0.00' : "$" . $this->indian_number_format($result_yesterday[0]->total_estimated_earnings),
                'app_info_this_month_so_far' => ($result_this_month[0]->total_estimated_earnings == null || $result_this_month[0]->total_estimated_earnings == '') ? '$0.00' : "$" . $this->indian_number_format($result_this_month[0]->total_estimated_earnings),
                'app_info_last_month_so_far' => ($result_last_month[0]->total_estimated_earnings == null || $result_last_month[0]->total_estimated_earnings == '') ? '$0.00' : "$" . $this->indian_number_format($result_last_month[0]->total_estimated_earnings),
            );
            $this->result['app_info_eastimated_earnings'] = $final_res;
            // [/for summary]

            $this->msg = 'success.';
            $this->_sendResponse(1);
        } else {
            $this->msg = 'Account not found ';
            $this->_sendResponse(0);
        }
    }

    public function get_app_info_performances()
    {

        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        $type = $this->required_parameter($postData, 'type');
        $app_auto_id = $this->required_parameter($postData, 'app_auto_id');

        switch ($type) {
            case 1: // Today so far

                $date_range_from = date("Y-m-d");
                $date_range_to = date("Y-m-d");

                $compare_date_range_from = date("Y-m-d", strtotime("-1 day"));
                $compare_date_range_to = date("Y-m-d", strtotime("-1 day"));

                break;
            case 2: // Yesterday vs same day last week

                $date_range_from = date("Y-m-d", strtotime("-1 day"));
                $date_range_to = date("Y-m-d", strtotime("-1 day"));

                $compare_date_range_from = date("Y-m-d", strtotime("-8 day"));
                $compare_date_range_to = date("Y-m-d", strtotime("-8 days"));

                break;

            case 3: // Last 7 days vs previous 7 days

                $date_range_from = date("Y-m-d", strtotime("-7 days"));
                $date_range_to = date("Y-m-d", strtotime("-1 days"));

                $compare_date_range_from = date("Y-m-d", strtotime("-14 days"));
                $compare_date_range_to = date("Y-m-d", strtotime("-8 days"));

                break;
            case 4: // Last 28 days vs previous 28 days

                $date_range_from = date("Y-m-d", strtotime("-28 days"));
                $date_range_to = date("Y-m-d", strtotime("-1 days"));

                $compare_date_range_from = date("Y-m-d", strtotime("-56 days"));
                $compare_date_range_to = date("Y-m-d", strtotime("-29 days"));

                break;
        }

        $common_where = "";

        $common_query_string = "SELECT FORMAT(SUM(t1.report_estimated_earnings),2) as total_estimated_earnings,
        SUM(t1.report_ad_requests) as total_ad_requests,
        FORMAT(((SUM(t1.report_matched_requests)/SUM(t1.report_ad_requests))*100),2) as total_match_rate,
        FORMAT(((SUM(t1.report_estimated_earnings)/SUM(t1.report_impressions))*1000),2) as total_observed_ecpm,
        SUM(t1.report_impressions) as total_impressions 
        FROM tbl_report as t1 
        JOIN tbl_ad_units as t2 ON t2.au_auto_id = t1.report_au_auto_id 
        JOIN tbl_apps as t3 ON t3.app_auto_id = t2.au_app_auto_id ";

        if ($user_info['user_role'] != 1) {
            $common_query_string .= " JOIN tbl_ad_unit_permissions as t4 ON t4.permission_au_auto_id = t2.au_auto_id ";
            $common_where .= " AND t4.permission_user_id = " . $user_info['user_id'];
        }

        // QUERY FOR CURRENT Record
        $query_current = "$common_query_string 
        WHERE t3.app_auto_id = $app_auto_id AND t1.report_date >= '$date_range_from' AND t1.report_date <= '$date_range_to' 
        $common_where";
        $result_current = $this->mdl_common->custom_query($query_current)->row_array();

        // echo $this->db->last_query();
        // $this->plog($result_current);

        if (empty($result_current['total_estimated_earnings'])) {
            $result_current['total_estimated_earnings'] = 0;
        }
        if (empty($result_current['total_ad_requests'])) {
            $result_current['total_ad_requests'] = 0;
        }
        if (empty($result_current['total_match_rate'])) {
            $result_current['total_match_rate'] = 0;
        }
        if (empty($result_current['total_observed_ecpm'])) {
            $result_current['total_observed_ecpm'] = 0;
        }
        if (empty($result_current['total_impressions'])) {
            $result_current['total_impressions'] = 0;
        }

        // QUERY FOR COMPARE Record
        $query_compare = "$common_query_string 
        WHERE t3.app_auto_id = $app_auto_id AND t1.report_date >= '$compare_date_range_from' AND t1.report_date <= '$compare_date_range_to' 
        $common_where";
        $result_compare = $this->mdl_common->custom_query($query_compare)->row_array();

        // echo $this->db->last_query();
        // $this->plog($result_compare);

        if (empty($result_compare['total_estimated_earnings'])) {
            $result_compare['total_estimated_earnings'] = 0;
        }
        if (empty($result_compare['total_ad_requests'])) {
            $result_compare['total_ad_requests'] = 0;
        }
        if (empty($result_compare['total_match_rate'])) {
            $result_compare['total_match_rate'] = 0;
        }
        if (empty($result_compare['total_observed_ecpm'])) {
            $result_compare['total_observed_ecpm'] = 0;
        }
        if (empty($result_compare['total_impressions'])) {
            $result_compare['total_impressions'] = 0;
        }

        // [For Est earning]
        $total_estimated_earnings_previous_calc = number_format((($result_compare['total_estimated_earnings']) - ($result_current['total_estimated_earnings'])), 2, '.', '');

        $total_estimated_earnings_percentage = $this->cal_percentage($result_compare['total_estimated_earnings'], $total_estimated_earnings_previous_calc);

        $total_estimated_earnings_previous = ($total_estimated_earnings_previous_calc >= 0) ? '<span class="text-danger">-$' . $this->abs($total_estimated_earnings_previous_calc) . ' (-' . $this->abs($total_estimated_earnings_percentage) . '%)</span>' : '<span class="text-success">+$' . $this->abs($total_estimated_earnings_previous_calc) . ' (+' . $this->abs($total_estimated_earnings_percentage) . '%)</span>';

        $total_estimated_earnings_tooltip_current = $this->indian_number_format($result_current['total_estimated_earnings']);
        $total_estimated_earnings_tooltip_previous = $this->indian_number_format($result_compare['total_estimated_earnings']);

        $app_info_performance_est_earnings = '<h2 class="mb-1 fw-normal"><span data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="right" data-bs-html="true">$' . (!empty($result_current['total_estimated_earnings']) ? $result_current['total_estimated_earnings'] : 0) . '</span></h2><h6>' . $total_estimated_earnings_previous . '</h6>';
        // [/For Est earning]

        // [For impressions]
        $total_performance_impr_calc = ($result_current['total_impressions']) - ($result_compare['total_impressions']);

        if ($total_performance_impr_calc > 999) {

            $performance_impr_devide_compare = ($total_performance_impr_calc / 1000);

            if ($performance_impr_devide_compare > 99) {
                $total_performance_impr = ceil($performance_impr_devide_compare) . 'K';
            } else if ($performance_impr_devide_compare > 0) {

                if ($performance_impr_devide_compare > 9) {
                    $total_performance_impr = $this->abs(round($performance_impr_devide_compare, 1)) . 'K';
                } else if ($performance_impr_devide_compare > 0) {
                    $total_performance_impr = $this->abs(round($performance_impr_devide_compare, 2)) . 'K';
                } else {
                    $total_performance_impr = $this->abs(round($performance_impr_devide_compare, 0));
                }
            } else {

                if ($performance_impr_devide_compare < -9) {
                    $total_performance_impr = $this->abs(round($performance_impr_devide_compare, 1)) . 'K';
                } else if ($performance_impr_devide_compare < 0) {
                    $total_performance_impr = $this->abs(round($performance_impr_devide_compare, 2)) . 'K';
                } else {
                    $total_performance_impr = $this->abs(round($performance_impr_devide_compare, 0));
                }
            }
        } else {
            $total_performance_impr = $total_performance_impr_calc;
        }


        $total_performance_impr_percentage = $this->cal_percentage($result_compare['total_impressions'], $total_performance_impr_calc);
        $total_performance_impr = ($total_performance_impr_calc >= 0) ? '<span class="text-success">+' . $total_performance_impr . ' (+' . $this->abs($total_performance_impr_percentage) . '%)</span>' : '<span class="text-danger">-' . $total_performance_impr . ' (-' . $this->abs($total_performance_impr_percentage) . '%)</span>';

        if ($result_current['total_impressions'] > 999) {

            $performance_impr_devide = ($result_current['total_impressions'] / 1000);

            if ($performance_impr_devide > 99) {
                if ($result_current['total_impressions'] < 1000000) {
                    $impr_round_figure = ceil($performance_impr_devide) . 'K';
                } else if ($result_current['total_impressions'] < 1000000000) {
                    $impr_round_figure = number_format($result_current['total_impressions'] / 1000000, 2) . 'M';
                } else {
                    $impr_round_figure = number_format($result_current['total_impressions'] / 1000000000, 2) . 'B';
                }
            } else if ($performance_impr_devide > 0) {

                if ($performance_impr_devide > 9) {
                    $impr_round_figure = $this->abs(round($performance_impr_devide, 1)) . 'K';
                } else if ($performance_impr_devide > 0) {
                    $impr_round_figure = $this->abs(round($performance_impr_devide, 2)) . 'K';
                } else {
                    $impr_round_figure = $this->abs(round($performance_impr_devide, 0));
                }
            } else {

                if ($performance_impr_devide < -9) {
                    $impr_round_figure = $this->abs(round($performance_impr_devide, 1)) . 'K';
                } else if ($performance_impr_devide < 0) {
                    $impr_round_figure = $this->abs(round($performance_impr_devide, 2)) . 'K';
                } else {
                    $impr_round_figure = $this->abs(round($performance_impr_devide, 0));
                }
            }
        } else {
            $impr_round_figure = $result_current['total_impressions'];
        }

        $total_estimated_impr_tooltip_current = $this->indian_number_format($result_current['total_impressions']);
        $total_estimated_impr_tooltip_previous = $this->indian_number_format($result_compare['total_impressions']);

        $app_info_performance_impr = '<h2 class="mb-1 fw-normal"><span data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="right" data-bs-html="true">' . (!empty($impr_round_figure) ? $impr_round_figure : 0) . '</span></h2><h6>' . $total_performance_impr . '</h6>';
        // [/For impressions]

        // [For ecpm]
        $total_observed_ecpm_previous_calc = number_format((float) ($result_compare['total_observed_ecpm']) - ($result_current['total_observed_ecpm']), 2, '.', '');
        $total_observed_ecpm_percentage = $this->cal_percentage($result_compare['total_observed_ecpm'], $total_observed_ecpm_previous_calc);

        $total_observed_ecpm_previous = ($total_observed_ecpm_previous_calc >= 0) ? '<span class="text-danger">-$' . $this->abs($total_observed_ecpm_previous_calc) . ' (-' . $this->abs($total_observed_ecpm_percentage) . '%)</span>' : '<span class="text-success">+$' . $this->abs($total_observed_ecpm_previous_calc) . ' (+' . $this->abs($total_observed_ecpm_percentage) . '%)</span>';

        $total_estimated_ecpm_tooltip_current = $result_current['total_observed_ecpm'];
        $total_estimated_ecpm_tooltip_previous = $result_compare['total_observed_ecpm'];

        $app_info_performance_ecpm = '<h2 class="mb-1 fw-normal"><span data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="right" data-bs-html="true">$' . (!empty($result_current['total_observed_ecpm']) ? $result_current['total_observed_ecpm'] : 0) . '</span></h2><h6>' . $total_observed_ecpm_previous . '</h6>';
        // [/For ecpm]

        // [For requests]
        $total_performance_requests_calc_round = ($result_current['total_ad_requests']) - ($result_compare['total_ad_requests']);
        $total_performance_requests_percentage = $this->cal_percentage($result_compare['total_ad_requests'], $total_performance_requests_calc_round);

        $performance_req_devide = ($total_performance_requests_calc_round / 1000);

        if ($performance_req_devide > 99) {

            $total_performance_requests_calc = ceil($performance_req_devide) . 'K';
        } else if ($performance_req_devide > 0) {

            if ($performance_req_devide > 9) {
                $total_performance_requests_calc = $this->abs(round($performance_req_devide, 1)) . 'K';
            } elseif ($performance_req_devide > 0) {
                $total_performance_requests_calc = $this->abs(round($performance_req_devide, 2)) . 'K';
            } else {
                $total_performance_requests_calc = $this->abs(round($performance_req_devide, 0));
            }
        } else {

            if ($performance_req_devide < -9) {
                $total_performance_requests_calc = $this->abs(round($performance_req_devide, 1)) . 'K';
            } else if ($performance_req_devide < 0) {
                $total_performance_requests_calc = $this->abs(round($performance_req_devide, 2)) . 'K';
            } else {
                $total_performance_requests_calc = $this->abs(round($performance_req_devide, 0));
            }
        }

        $total_performance_req_devide = ($result_current['total_ad_requests'] / 1000);

        if ($total_performance_req_devide > 99) {
            if ($result_current['total_ad_requests'] < 1000000) {
                $total_performance_req = round($total_performance_req_devide) . 'K';
            } else if ($result_current['total_ad_requests'] < 1000000000) {
                $total_performance_req = number_format($result_current['total_ad_requests'] / 1000000, 2) . 'M';
            } else {
                $total_performance_req = number_format($result_current['total_ad_requests'] / 1000000000, 2) . 'B';
            }
        } else if ($total_performance_req_devide > 0) {
            $total_performance_req = round($total_performance_req_devide, 1) . 'K';
        } else {
            $total_performance_req = $result_current['total_ad_requests'];
        }

        $total_performance_requests = ($total_performance_requests_calc_round >= 0) ? '<span class="text-success">+' . $total_performance_requests_calc . ' (+' . $this->abs($total_performance_requests_percentage) . '%)</span>' : '<span class="text-danger">-' . $total_performance_requests_calc . ' (-' . $this->abs($total_performance_requests_percentage) . '%)</span>';

        $total_estimated_requests_tooltip_current = $this->indian_number_format($result_current['total_ad_requests']);
        $total_estimated_requests_tooltip_previous = $this->indian_number_format($result_compare['total_ad_requests']);

        $app_info_performance_requests = '<h2 class="mb-1 fw-normal"><span data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="right" data-bs-html="true">' . (!empty($total_performance_req) ? $total_performance_req : 0) . '</span></h2><h6>' . $total_performance_requests . '</h6>';
        // [/For requests]

        // [For match_rate]
        $total_performance_match_rate_calc = number_format((float) ($result_current['total_match_rate']) - ($result_compare['total_match_rate']), 2, '.', '');
        $total_performance_match_rate_percentage = $this->cal_percentage($result_compare['total_match_rate'], $total_performance_match_rate_calc);
        $total_performance_match_rate = ($total_performance_match_rate_calc >= 0) ? '<span class="text-success">+' . $total_performance_match_rate_calc . '% (+' . $this->abs($total_performance_match_rate_percentage) . '%)</span>' : '<span class="text-danger">-' . $total_performance_match_rate_calc . '% (-' . $this->abs($total_performance_match_rate_percentage) . '%)</span>';

        $total_estimated_match_rate_tooltip_current = $result_current['total_match_rate'];
        $total_estimated_match_rate_tooltip_previous = $result_compare['total_match_rate'];

        $app_info_performance_match_rate = '<h2 class="mb-1 fw-normal"><span data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="right" data-bs-html="true">' . (!empty($result_current['total_match_rate']) ? $result_current['total_match_rate'] : 0) . '%</span></h2><h6>' . $total_performance_match_rate . '</h6>';
        // [/For match_rate]

        $final_res = array(
            'est_earnings' => array(
                'app_info_performance_est_earnings' => $app_info_performance_est_earnings,
                'total_estimated_earnings_tooltip_current' => $total_estimated_earnings_tooltip_current,
                'total_estimated_earnings_tooltip_previous' => $total_estimated_earnings_tooltip_previous
            ),
            'requests' => array(
                'app_info_performance_requests' => $app_info_performance_requests,
                'total_estimated_requests_tooltip_current' => $total_estimated_requests_tooltip_current,
                'total_estimated_requests_tooltip_previous' => $total_estimated_requests_tooltip_previous,

            ),
            'impr' => array(
                'app_info_performance_impr' => $app_info_performance_impr,
                'total_estimated_impr_tooltip_current' => $total_estimated_impr_tooltip_current,
                'total_estimated_impr_tooltip_previous' => $total_estimated_impr_tooltip_previous

            ),
            'match_rate' => array(
                'app_info_performance_match_rate' => $app_info_performance_match_rate,
                'total_estimated_match_rate_tooltip_current' => $total_estimated_match_rate_tooltip_current . '%',
                'total_estimated_match_rate_tooltip_previous' => $total_estimated_match_rate_tooltip_previous . '%'
            ),
            'ecpm' => array(
                'app_info_performance_ecpm' => $app_info_performance_ecpm,
                'total_estimated_ecpm_tooltip_current' => $total_estimated_ecpm_tooltip_current,
                'total_estimated_ecpm_tooltip_previous' => $total_estimated_ecpm_tooltip_previous
            ),
        );

        $this->cors_header();
        echo json_encode($final_res);
    }

    public function app_overview_ads_performance_list()
    {

        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        $app_auto_id = $this->required_parameter($postData, 'app_auto_id');
        $type = $this->required_parameter($postData, 'type');

        switch ($type) {
            case 1: // Today so far

                $date_range_from = date("Y-m-d");
                $date_range_to = date("Y-m-d");

                $compare_date_range_from = date("Y-m-d", strtotime("-1 day"));
                $compare_date_range_to = date("Y-m-d", strtotime("-1 day"));

                break;
            case 2: // Yesterday vs same day last week

                $date_range_from = date("Y-m-d", strtotime("-1 day"));
                $date_range_to = date("Y-m-d", strtotime("-1 day"));

                $compare_date_range_from = date("Y-m-d", strtotime("-8 day"));
                $compare_date_range_to = date("Y-m-d", strtotime("-8 days"));

                break;

            case 3: // Last 7 days vs previous 7 days

                $date_range_from = date("Y-m-d", strtotime("-7 days"));
                $date_range_to = date("Y-m-d", strtotime("-1 days"));

                $compare_date_range_from = date("Y-m-d", strtotime("-14 days"));
                $compare_date_range_to = date("Y-m-d", strtotime("-8 days"));

                break;
            case 4: // Last 28 days vs previous 28 days

                $date_range_from = date("Y-m-d", strtotime("-28 days"));
                $date_range_to = date("Y-m-d", strtotime("-1 days"));

                $compare_date_range_from = date("Y-m-d", strtotime("-56 days"));
                $compare_date_range_to = date("Y-m-d", strtotime("-29 days"));

                break;
        }

        $aaData = array();

        $common_where = "";

        $common_query_string = "SELECT FORMAT(SUM(t1.report_estimated_earnings),2) as total_estimated_earnings,
        t2.au_display_name, t2.au_format_auto_id, t5.au_format_display_name 
        FROM tbl_report as t1 
        JOIN tbl_ad_units as t2 ON t2.au_auto_id = t1.report_au_auto_id 
        JOIN tbl_apps as t3 ON t3.app_auto_id = t2.au_app_auto_id 
        JOIN tbl_ad_unit_format as t5 ON t5.au_format_auto_id = t2.au_format_auto_id ";

        if ($user_info['user_role'] != 1) {
            $common_query_string .= " JOIN tbl_ad_unit_permissions as t4 ON t4.permission_au_auto_id = t2.au_auto_id ";
            $common_where .= " AND t4.permission_user_id = " . $user_info['user_id'];
        }

        $common_limit = 5;

        // QUERY FOR CURRENT Record
        $query_current = "$common_query_string 
        WHERE t3.app_auto_id = $app_auto_id AND t1.report_date >= '$date_range_from' AND t1.report_date <= '$date_range_to' 
        $common_where GROUP BY t2.au_auto_id  ORDER BY total_estimated_earnings DESC LIMIT $common_limit";
        $result_current = $this->mdl_common->custom_query($query_current)->result_array();

        // echo $this->db->last_query();
        // $this->plog($result_current, 1);

        // QUERY FOR COMPARE Record
        $query_compare = "$common_query_string 
        WHERE t3.app_auto_id = $app_auto_id AND t1.report_date >= '$compare_date_range_from' AND t1.report_date <= '$compare_date_range_to' 
        $common_where GROUP BY t2.au_auto_id  ORDER BY total_estimated_earnings DESC LIMIT $common_limit";
        $result_compare = $this->mdl_common->custom_query($query_compare)->result_array();

        // echo $this->db->last_query();
        // $this->plog($result_compare);

        $singleListArray = array();
        if ($result_current && $result_compare) {

            foreach ($result_current as $key => $row) {

                // $this->plog($row);

                if (isset($result_current[$key]) && isset($result_compare[$key])) {

                    $singleListArray['au_display_name'] = $result_current[$key]['au_display_name'] . '<br><span class="text-muted">' . ucwords(str_replace("_", " ", strtolower($result_current[$key]['au_format_display_name']))) . '</span>';

                    // [For Est earning]
                    $total_estimated_earnings_previous_calc = number_format((($result_compare[$key]['total_estimated_earnings']) - ($result_current[$key]['total_estimated_earnings'])), 2, '.', '');
                    $total_estimated_earnings_percentage = $this->cal_percentage($result_compare[$key]['total_estimated_earnings'], $total_estimated_earnings_previous_calc);
                    $total_estimated_earnings_previous = ($total_estimated_earnings_previous_calc >= 0) ? '<span class="text-danger">-$' . $this->abs($total_estimated_earnings_previous_calc) . ' (-' . $this->abs($total_estimated_earnings_percentage) . '%)</span>' : '<span class="text-success">+$' . $this->abs($total_estimated_earnings_previous_calc) . ' (+' . $this->abs($total_estimated_earnings_percentage) . '%)</span>';
                    $dashboard_performance_est_earnings = '<h5 class="mb-1 fw-normal">$' . $result_current[$key]['total_estimated_earnings'] . '</h5><h6>' . $total_estimated_earnings_previous . '</h6>';

                    $singleListArray['est_earnings'] = $dashboard_performance_est_earnings;
                    // [/For Est earning]

                    $aaData[] = $singleListArray;
                }
            }
        }

        $finalJsonArray['sEcho'] = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;
        $finalJsonArray['iTotalRecords'] = $common_limit;
        $finalJsonArray['iTotalDisplayRecords'] = $common_limit;
        $finalJsonArray['aaData'] = $aaData;
        $this->cors_header();
        echo json_encode($finalJsonArray);
    }

    // Ad Unit Permission module

    public function list_all_apps()
    {

        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        $where = "";

        $option = "SELECT t1.app_auto_id,t1.app_display_name,t1.app_store_id,t1.app_admob_app_id 
        FROM tbl_apps as t1 ";

        if ($user_info['user_role'] != 1) {
            $where = "WHERE t3.permission_user_id = " . $user_info['user_id'];

            $option .= " JOIN tbl_ad_units as t2 ON t2.au_app_auto_id = t1.app_auto_id ";
            $option .= " JOIN tbl_ad_unit_permissions as t3 ON t3.permission_au_auto_id = t2.au_auto_id ";
        }

        $option .= "$where GROUP BY t1.app_auto_id ";
        $info = $this->mdl_common->custom_query($option)->result_array();

        $this->result['info'] = $info;
        $this->msg = 'success.';
        $this->_sendResponse(1);
    }

    public function list_ad_units()
    {

        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        $app_auto_id = $this->required_parameter($postData, 'app_auto_id');

        $where = "WHERE t1.au_app_auto_id = '$app_auto_id' ";

        $option = "SELECT t1.au_auto_id,t1.au_display_name,t1.au_id,t4.au_format_display_name
        FROM tbl_ad_units as t1 
        JOIN tbl_apps as t2 ON t2.app_auto_id = t1.au_app_auto_id 
        JOIN tbl_ad_unit_format as t4 ON t4.au_format_auto_id = t1.au_format_auto_id ";

        if ($user_info['user_role'] != 1) {
            $where .= " AND t3.permission_user_id = " . $user_info['user_id'];

            $option .= " JOIN tbl_ad_unit_permissions as t3 ON t3.permission_au_auto_id = t1.au_auto_id ";
        }

        $option .= "$where";

        $info = $this->mdl_common->custom_query($option)->result_array();

        $this->result['info'] = $info;
        $this->msg = 'success.';
        $this->_sendResponse(1);
    }

    public function add_user_permission()
    {
        $json = $this->input->post();
        $postData = (array) json_decode($json['json_data']);

        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        $user_unique_id = $this->required_parameter($postData, 'user_unique_id');

        if (isset($postData['permission_au_auto_id'][0]) && $postData['permission_au_auto_id'][0] != "") {

            $permission_au_auto_id = $postData['permission_au_auto_id'];

            // select already added ad unit ids
            $selectionQ = "SELECT GROUP_CONCAT(permission_au_auto_id) as existing_ad_unit_ids FROM tbl_ad_unit_permissions  
                            WHERE permission_au_auto_id IN (" . implode(',', $permission_au_auto_id) . ") AND permission_user_id = $user_unique_id";
            $resultQ = $this->mdl_common->custom_query($selectionQ)->row_array();

            $existing_ad_unit_ids = [];
            if ($resultQ['existing_ad_unit_ids'] != "") {
                $existing_ad_unit_ids = explode(',', $resultQ['existing_ad_unit_ids']);
            }

            $final_insert_array = array_diff($permission_au_auto_id, $existing_ad_unit_ids);

            $current_datetime = $this->current_datetime();

            $insertValues = [];
            foreach ($final_insert_array as $single) {
                $insertValues[] = "($user_unique_id,$user_id,$single,'$current_datetime')";
            }

            if (count($insertValues) > 0) {

                $queryInsert = "INSERT INTO tbl_ad_unit_permissions (permission_user_id,permission_given_by,permission_au_auto_id,permission_created_at) VALUES " . implode(',', $insertValues);

                $insert = $this->mdl_common->custom_query($queryInsert);

                if ($insert) {
                    $this->msg = "Added successfully";
                    $this->_sendResponse(1);
                } else {

                    $this->msg = "Database error";
                    $this->_sendResponse(0);
                }
            } else {
                $this->msg = "Added successfully";
                $this->_sendResponse(1);
            }
        } else {
            $this->msg = "Please enter all required fields.";
            $this->_sendResponse(0);
        }
    }

    public function permission_apps_list()
    {

        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        $this->load->model('Mdl_datatable', 'mdl_datatable');

        $aaData = array();

        if ($user_info['user_role'] != 1) {
            $option = array(
                'where' => "t1.permission_user_id = " . $user_id
            );
        } else {
            $option = array();
        }

        $total_records = $this->mdl_datatable->get_permission_apps_list($option, $is_count = 1);

        $limit = isset($_POST['length']) ? $_POST['length'] : 15;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;

        $resultSet = $this->mdl_datatable->get_permission_apps_list($option, $is_count = 0, $start, $limit);

        $singleListArray = array();
        if ($resultSet) {

            $increment_id = ($start == 0) ? 1 : ($start + 1);

            foreach ($resultSet as $row) {

                $singleListArray['increment_id'] = $increment_id;

                $singleListArray['user_email'] = $row->user_email;
                $singleListArray['user_name'] = $row->user_name;
                $singleListArray['user_unique_id'] = $row->user_unique_id;
                $singleListArray['app_display_name'] = $row->app_display_name;
                $singleListArray['app_icon'] = $row->app_icon;
                $singleListArray['app_auto_id'] = $row->app_auto_id;
                $singleListArray['app_platform'] = $row->app_platform;

                $singleListArray['app_admob_app_id'] = $row->app_admob_app_id;
                $singleListArray['admob_email'] = $row->admob_email;

                $app_approval_state = $this->get_app_approval_state_by_short_id($row->app_approval_state);

                $final_app_approval_state = '';
                switch ($app_approval_state) {
                    case "APPROVED":
                        $final_app_approval_state = '<span class="badge badge-soft-success fw-normal" style="color: #137333 !important;"><i class="ri-checkbox-circle-line fs-17 fw-lighter align-middle"></i> Ready</span>';
                        break;
                    case "ACTION_REQUIRED":
                        $final_app_approval_state = '<span class="badge badge-soft-warning fw-normal" style="color: #4d484be5 !important;"><i class="ri-alert-line fs-17 fw-lighter align-middle"></i> Requires review</span>';
                        break;
                }

                $singleListArray['app_approval_state'] = $final_app_approval_state;
                $singleListArray['app_store_id'] = $row->app_store_id;

                // Query for admob ad ids
                $query = "SELECT t2.au_auto_id,t2.au_display_name,t2.au_id,t3.au_format_display_name 
                FROM tbl_ad_unit_permissions as t1 
                JOIN tbl_ad_units as t2 ON t2.au_auto_id = t1.permission_au_auto_id 
                JOIN tbl_ad_unit_format as t3 ON t3.au_format_auto_id = t2.au_format_auto_id 
                WHERE t2.au_app_auto_id = " . $row->app_auto_id . " AND t1.permission_user_id = " . $row->user_unique_id;

                $ad_unit_result = $this->mdl_common->custom_query($query)->result_array();

                $singleListArray['ad_units'] = $ad_unit_result;
                $singleListArray['total_ad_units'] = count($ad_unit_result) . ' units';

                $aaData[] = $singleListArray;

                $increment_id++;
            }
        }

        $finalJsonArray['sEcho'] = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;
        $finalJsonArray['iTotalRecords'] = $total_records;
        $finalJsonArray['iTotalDisplayRecords'] = $total_records;
        $finalJsonArray['aaData'] = $aaData;
        $this->cors_header();
        echo json_encode($finalJsonArray);
    }
    public function remove_ad_unit_permission()
    {
        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        if ($user_info['user_role'] == 1) {

            $user_unique_id = $this->required_parameter($postData, 'user_unique_id');
            $permission_au_auto_id = $this->required_parameter($postData, 'au_auto_id');

            $option = array(
                'from' => 'tbl_ad_unit_permissions',
                'where' => array(
                    'permission_user_id' => $user_unique_id,
                    'permission_au_auto_id' => $permission_au_auto_id
                )
            );

            $delete = $this->mdl_common->delete($option);

            if ($delete) {
                $this->msg = "Deleted successfully";
                $this->_sendResponse(1);
            } else {

                $this->msg = "Database error";
                $this->_sendResponse(0);
            }
        } else {
            $this->msg = "Permission denied";
            $this->_sendResponse(0);
        }
    }

    public function get_all_users()
    {
        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        $option = array(
            'select' => "user_id,user_email,user_name",
            'from' => "tbl_user",
        );

        $info = $this->mdl_common->select($option);

        $this->result['info'] = $info;
        $this->msg = 'success.';
        $this->_sendResponse(1);
    }

    // 
    public function app_info()
    {

        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        $app_auto_id = $this->required_parameter($postData, 'app_auto_id');

        $where = " WHERE t1.app_auto_id = $app_auto_id ";

        $option = "SELECT t1.app_auto_id,t1.app_display_name,t1.app_store_id,t1.app_icon,t1.app_platform,t1.app_approval_state,t1.app_console_name  
        FROM tbl_apps as t1 
        JOIN tbl_ad_units as t2 ON t2.au_app_auto_id = t1.app_auto_id ";

        if ($user_info['user_role'] != 1) {
            $option .= " JOIN tbl_ad_unit_permissions as t3 ON t3.permission_au_auto_id = t2.au_auto_id ";
            $where .= " AND t3.permission_user_id = " . $user_info['user_id'];
        }

        $option .= " $where ";

        $info = $this->mdl_common->custom_query($option)->result_array();

        if (isset($info[0]['app_auto_id'])) {
            $this->result['app_info'] = $info[0];

            // [for summary]
            $common_where = "";

            $common_query_string = "SELECT FORMAT(SUM(t1.report_estimated_earnings),2) as total_estimated_earnings 
            FROM tbl_report as t1 
            JOIN tbl_ad_units as t2 ON t2.au_auto_id = t1.report_au_auto_id 
            JOIN tbl_apps as t3 ON t3.app_auto_id = t2.au_app_auto_id ";

            if ($user_info['user_role'] != 1) {
                $common_query_string .= " JOIN tbl_ad_unit_permissions as t4 ON t4.permission_au_auto_id = t2.au_auto_id ";
                $common_where .= " AND t4.permission_user_id = " . $user_info['user_id'];
            }

            // QUERY FOR TODAY's Record
            $where_today_start_date = date("Y-m-d");
            $where_today_end_date = date("Y-m-d");
            $where_today = " t1.report_date >= '$where_today_start_date' AND t1.report_date <= '$where_today_end_date' ";

            $query_today = "$common_query_string WHERE t3.app_auto_id = $app_auto_id AND $where_today $common_where";
            $result_today = $this->mdl_common->custom_query($query_today)->result();

            // QUERY FOR YESTERDAY's Record
            $where_yesterday_start_date = date("Y-m-d", strtotime("1 days ago"));
            $where_yesterday_end_date = date("Y-m-d", strtotime("1 days ago"));

            $where_yesterday = " t1.report_date >= '$where_yesterday_start_date' AND t1.report_date <= '$where_yesterday_end_date' ";

            $query_yesterday = "$common_query_string WHERE t3.app_auto_id = $app_auto_id AND $where_yesterday $common_where";
            $result_yesterday = $this->mdl_common->custom_query($query_yesterday)->result();

            // QUERY FOR This month Record
            $where_this_month_start_date = date("Y-m-01");
            $where_this_month_end_date = date("Y-m-d");

            $where_this_month = " t1.report_date >= '$where_this_month_start_date' AND t1.report_date <= '$where_this_month_end_date' ";

            $query_this_month = "$common_query_string WHERE t3.app_auto_id = $app_auto_id AND $where_this_month $common_where";
            $result_this_month = $this->mdl_common->custom_query($query_this_month)->result();

            // QUERY FOR Last month Record
            $where_last_month_start_date = date("Y-m-d", strtotime('first day of last month'));
            $where_last_month_end_date = date("Y-m-d", strtotime('last day of last month'));

            $where_last_month = " t1.report_date >= '$where_last_month_start_date' AND t1.report_date <= '$where_last_month_end_date' ";

            $query_last_month = "$common_query_string WHERE t3.app_auto_id = $app_auto_id AND $where_last_month $common_where";
            $result_last_month = $this->mdl_common->custom_query($query_last_month)->result();

            $final_res = array(
                'app_info_today_so_far' => ($result_today == null) ? '0' : "$" . $this->indian_number_format($result_today[0]->total_estimated_earnings),
                'app_info_yesterday_so_far' => ($result_yesterday == null) ? '0' : "$" . $this->indian_number_format($result_yesterday[0]->total_estimated_earnings),
                'app_info_this_month_so_far' => ($result_this_month == null) ? '0' : "$" . $this->indian_number_format($result_this_month[0]->total_estimated_earnings),
                'app_info_last_month_so_far' => ($result_last_month == null) ? '0' : "$" . $this->indian_number_format($result_last_month[0]->total_estimated_earnings),
            );
            $this->result['app_info_eastimated_earnings'] = $final_res;
            // [/for summary]

            $this->msg = 'success.';
            $this->_sendResponse(1);
        } else {
            $this->msg = 'App not found ';
            $this->_sendResponse(0);
        }
    }

    public function app_ad_list_for_report()
    {

        $user_info = $this->verify_user();

        $postData = $this->input->post();
        $app_auto_id = $this->required_parameter($postData, 'id');

        $where = "WHERE t1.app_auto_id = '$app_auto_id' ";

        $option = "SELECT t1.ad_unit_auto_id,t1.au_display_name,t1.ad_unit_value,t4.ad_format_name
        FROM tbl_ad_units as t1 
        JOIN tbl_apps as t2 ON t2.app_auto_id = t1.app_auto_id 
        JOIN tbl_ad_unit_format as t4 ON t4.ad_format_id = t1.ad_unit_format ";

        if ($user_info['user_role'] != 1) {
            $where .= " AND t3.user_id = " . $user_info['user_id'];

            $option .= " JOIN tbl_ad_unit_permissions as t3 ON t3.ad_unit_auto_id = t1.ad_unit_auto_id ";
        }

        $option .= "$where";

        $info = $this->mdl_common->custom_query($option)->result_array();

        $this->result['info'] = $info;
        $this->msg = 'success.';
        $this->_sendResponse(1);
    }

    // for report section 
    public function report_list()
    {

        $user_info = $this->verify_user();

        $this->load->model('Mdl_datatable', 'mdl_datatable');

        $aaData = array();

        if ($user_info['user_role'] != 1) {
            $option = array(
                'where' => "t1.report_created_by = " . $user_info['user_id']
            );
        } else {
            $option = array();
        }

        $total_records = $this->mdl_datatable->get_report_list($option, $is_count = 1);

        $limit = isset($_POST['iDisplayLength']) ? $_POST['iDisplayLength'] : 15;
        $start = isset($_POST['iDisplayStart']) ? $_POST['iDisplayStart'] : 0;

        $resultSet = $this->mdl_datatable->get_report_list($option, $is_count = 0, $start, $limit);

        $singleListArray = array();
        if ($resultSet) {

            $increment_id = ($start == 0) ? 1 : ($start + 1);

            foreach ($resultSet as $row) {
                $singleListArray['increment_id'] = $increment_id;

                $singleListArray['report_name'] = $row->report_name;
                $singleListArray['app_display_name'] = $row->app_display_name;

                $report_range_type = $row->report_range_type;
                $final_report_range_type = "Today";

                switch ($report_range_type) {
                    case "1":
                        $final_report_range_type = 'Today';
                        break;
                    case "2":
                        $final_report_range_type = 'Yesterday';
                        break;
                    case "3":
                        $final_report_range_type = 'Last 7 Days';
                        break;
                    case "4":
                        $final_report_range_type = 'Last 30 Days';
                        break;
                    case "5":
                        $final_report_range_type = 'This Month';
                        break;
                }

                $singleListArray['report_range_type'] = $final_report_range_type;

                $report_schedule = $row->report_schedule;
                $final_report_schedule = "Daily";

                switch ($report_schedule) {
                    case "1":
                        $final_report_schedule = 'Daily';
                        break;
                    case "2":
                        $final_report_schedule = 'Weekly';
                        break;
                    case "3":
                        $final_report_schedule = 'Monthly';
                        break;
                    case "41":
                        $final_report_schedule = 'Every 3 Month';
                        break;
                    case "5":
                        $final_report_schedule = 'Half Yearly';
                        break;
                    case "6":
                        $final_report_schedule = 'Yearly';
                        break;
                }

                $singleListArray['report_schedule'] = $final_report_schedule;

                $report_send_to_email_array = json_decode($row->report_send_to_email);
                $send_to_html = "<ol>";
                foreach ($report_send_to_email_array as $key => $single_send_to) {
                    $send_to_html .= "<li class='" . (($key != 0) ? 'mt-2' : '') . "'>" . $single_send_to->name . "<br>" . $single_send_to->email . "</li>";
                }
                $send_to_html .= "</ol>";

                $singleListArray['report_send_to_email'] = $send_to_html;

                $report_cc_html = '';
                if ($row->report_cc_email && $row->report_cc_email != null) {
                    $report_cc_email_array = json_decode($row->report_cc_email);
                    $report_cc_html = $report_cc_email_array->name . "<br>" . $report_cc_email_array->email . "</li>";
                }
                $singleListArray['report_cc_email'] = $report_cc_html;

                $singleListArray['report_created_by'] = $row->user_name;
                $singleListArray['report_created_at'] = date('d-m-Y', strtotime($row->report_created_at));
                $singleListArray['report_updated_at'] = date('d-m-Y', strtotime($row->report_updated_at));

                $action = '<i class="ri-edit-box-line ri-xl editReportBtn cursor-pointer" title="Edit Report" data-bs-toggle="modal" data-bs-target="#editReportModal" data-report_auto_id="' . $row->report_auto_id . '"></i>';
                $action .= '<i class="ri-delete-bin-line ms-2 ri-xl deleteReportBtn text-danger cursor-pointer" title="Delete Report" data-report_auto_id="' . $row->report_auto_id . '"></i>';

                $singleListArray['action'] = $action;

                $aaData[] = $singleListArray;

                $increment_id++;
            }
        }

        $finalJsonArray['sEcho'] = $_POST['sEcho'] ? $_POST['sEcho'] : 1;
        $finalJsonArray['iTotalRecords'] = $total_records;
        $finalJsonArray['iTotalDisplayRecords'] = $total_records;
        $finalJsonArray['aaData'] = $aaData;
        $this->cors_header();
        echo json_encode($finalJsonArray);
    }

    public function add_new_report()
    {
        $user_info = $this->verify_user();

        $json = $this->input->post();
        $postData = (array) json_decode($json['json_data']);

        $report_name = $this->required_parameter($postData, 'report_name');
        $report_app_auto_id = $this->required_parameter($postData, 'report_app_auto_id');
        $report_ad_unit_auto_id = $this->required_parameter($postData, 'report_ad_unit_auto_id');
        $report_range_type = $this->required_parameter($postData, 'report_range_type');
        $report_schedule = $this->required_parameter($postData, 'report_schedule');

        $report_send_to_name = $this->required_parameter($postData, 'report_send_to_name');
        $report_send_to_email = $this->required_parameter($postData, 'report_send_to_email');

        if (count($report_send_to_email) == count($report_send_to_name)) {

            $report_send_to_array = [];
            foreach ($report_send_to_email as $key => $report_send_to) {
                $report_send_to_array[] = array(
                    'name' => $report_send_to_name[$key],
                    'email' => $report_send_to_email[$key],
                );
            }

            $report_send_to_email_json = json_encode($report_send_to_array);

            $current_datetime = date('Y-m-d H:i:s');
            $option = array(
                'from' => 'tbl_report_send',
                'insert_data' => array(
                    'report_name' => $report_name,
                    'report_app_auto_id' => $report_app_auto_id,
                    'report_ad_unit_auto_id' => implode(",", $report_ad_unit_auto_id),
                    'report_range_type' => $report_range_type,
                    'report_schedule' => $report_schedule,
                    'report_send_to_email' => $report_send_to_email_json,
                    'report_created_by' => $user_info['user_id'],
                    'report_created_at' => $current_datetime,
                    'report_updated_at' => $current_datetime
                )
            );

            if (isset($postData['report_cc_email']) && !empty($postData['report_cc_email']) && isset($postData['report_cc_email_name']) && !empty($postData['report_cc_email_name'])) {
                $report_cc_email = array(
                    'name' => $postData['report_cc_email_name'],
                    'email' => $postData['report_cc_email'],
                );
                $option['insert_data']['report_cc_email'] = json_encode($report_cc_email);
            }

            $insert = $this->mdl_common->insert($option);

            if ($insert) {
                $this->msg = "Report added successfully";
                $this->_sendResponse(1);
            } else {

                $this->msg = "Database error";
                $this->_sendResponse(0);
            }
        } else {
            $this->msg = "Invalid email paremeters";
            $this->_sendResponse(0);
        }
    }

    public function get_report_detail()
    {
        $user_info = $this->verify_user();

        $postData = $this->input->post();

        $report_auto_id = $this->required_parameter($postData, 'report_auto_id');

        if ($user_info['user_role'] != 1) {
            $where = "AND t1.report_created_by = " . $user_info['user_id'];
        } else {
            $where = "";
        }

        $option = "SELECT t1.* FROM tbl_report_send as t1 WHERE report_auto_id = " . $report_auto_id . " $where LIMIT 1";

        $info = $this->mdl_common->custom_query($option)->result_array();

        if (isset($info[0]['report_auto_id'])) {
            $this->result['info'] = $info[0];
            $this->msg = 'success.';
            $this->_sendResponse(1);
        } else {
            $this->msg = 'Account not found ';
            $this->_sendResponse(0);
        }
    }

    public function update_report()
    {
        $user_info = $this->verify_user();

        $json = $this->input->post();
        $postData = (array) json_decode($json['json_data']);

        $report_auto_id = $this->required_parameter($postData, 'report_auto_id');
        $report_name = $this->required_parameter($postData, 'report_name_edit');
        $report_app_auto_id = $this->required_parameter($postData, 'report_app_auto_id_edit');
        $report_ad_unit_auto_id = $this->required_parameter($postData, 'report_ad_unit_auto_id_edit');
        $report_range_type = $this->required_parameter($postData, 'report_range_type_edit');
        $report_schedule = $this->required_parameter($postData, 'report_schedule_edit');

        $report_send_to_name = $this->required_parameter($postData, 'report_send_to_name_edit');
        $report_send_to_email = $this->required_parameter($postData, 'report_send_to_email_edit');

        if (count($report_send_to_email) == count($report_send_to_name)) {

            $report_send_to_array = [];
            foreach ($report_send_to_email as $key => $report_send_to) {
                $report_send_to_array[] = array(
                    'name' => $report_send_to_name[$key],
                    'email' => $report_send_to_email[$key],
                );
            }

            $report_send_to_email_json = json_encode($report_send_to_array);

            $current_datetime = date('Y-m-d H:i:s');
            $option = array(
                'from' => 'tbl_report_send',
                'update_data' => array(
                    'report_name' => $report_name,
                    'report_app_auto_id' => $report_app_auto_id,
                    'report_ad_unit_auto_id' => implode(",", $report_ad_unit_auto_id),
                    'report_range_type' => $report_range_type,
                    'report_schedule' => $report_schedule,
                    'report_send_to_email' => $report_send_to_email_json,
                    'report_updated_at' => $current_datetime
                ),
                'where' => array(
                    'report_auto_id' => $report_auto_id
                )
            );

            if ($user_info['user_role'] != 1) {
                $option['where']['report_created_by'] = $user_info['user_id'];
            }

            if (isset($postData['report_cc_email_edit']) && !empty($postData['report_cc_email_edit']) && isset($postData['report_cc_email_name_edit']) && !empty($postData['report_cc_email_name_edit'])) {
                $report_cc_email = array(
                    'name' => $postData['report_cc_email_name_edit'],
                    'email' => $postData['report_cc_email_edit'],
                );
                $option['update_data']['report_cc_email'] = json_encode($report_cc_email);
            } else {
                $option['update_data']['report_cc_email'] = '';
            }

            $update = $this->mdl_common->update($option);

            if ($update) {
                $this->msg = "Report updated successfully";
                $this->_sendResponse(1);
            } else {

                $this->msg = "Database error";
                $this->_sendResponse(0);
            }
        } else {
            $this->msg = "Invalid email paremeters";
            $this->_sendResponse(0);
        }
    }

    public function delete_report()
    {
        $user_info = $this->verify_user();

        $postData = $this->input->post();

        $report_auto_id = $this->required_parameter($postData, 'report_auto_id');

        $current_datetime = date('Y-m-d H:i:s');
        $option = array(
            'from' => 'tbl_report_send',
            'update_data' => array(
                'report_status' => 0,
                'report_updated_at' => $current_datetime
            ),
            'where' => array(
                'report_auto_id' => $report_auto_id
            )
        );

        if ($user_info['user_role'] != 1) {
            $option['where']['report_created_by'] = $user_info['user_id'];
        }

        $update = $this->mdl_common->update($option);

        if ($update) {
            $this->msg = "Report deleted successfully";
            $this->_sendResponse(1);
        } else {

            $this->msg = "Database error";
            $this->_sendResponse(0);
        }
    }

    // For analytics section
    public function analytics_report()
    {
        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');
        $user_info = $this->verify_user($user_id, $user_token);

        $this->load->model('Mdl_datatable', 'mdl_datatable');

        $aaData = array();

        $start_date = date("Y-m-d", strtotime("7 days ago"));
        $end_date = date("Y-m-d", strtotime("1 days ago"));

        $analytics_date_range = isset($_POST['analytics_date_range']) ? str_replace("+", "", $_POST['analytics_date_range']) : "";
        if ($analytics_date_range) {
            $analytics_date_range_parts = explode("-", $analytics_date_range);

            if (isset($analytics_date_range_parts[0])) {
                $myDateTime = DateTime::createFromFormat('d/m/Y', trim($analytics_date_range_parts[0]));
                $start_date = $myDateTime->format('Y-m-d');
            }

            if (isset($analytics_date_range_parts[1])) {
                $myDateTime = DateTime::createFromFormat('d/m/Y', trim($analytics_date_range_parts[1]));
                $end_date = $myDateTime->format('Y-m-d');
            }
        }

        $option = array(
            'where' => " t1.report_date >= '$start_date' AND t1.report_date <= '$end_date' "
        );
        $option['user_info'] = $user_info;
        if ($user_info['user_role'] != 1) {
            $option['where'] = $option['where'] . " AND t6.permission_user_id = " . $user_info['user_id'];
        }

        $selected_dimension_array = isset($_POST['selected_dimension']) ? explode(",", $_POST['selected_dimension']) : array();
        $final_group_by_string = "";
        if ($selected_dimension_array) {

            $add_t1 = [
                "report_au_auto_id",
                "report_date",
            ];

            $add_t2 = [
                "au_display_name",
                "au_format_auto_id",
            ];

            $add_t3 = [
                "app_display_name",
            ];

            $add_t5 = [
                "country_name",
            ];

            foreach ($selected_dimension_array as $key => $singleData) {
                if (in_array($singleData, $add_t1)) {
                    $final_group_by_string .= "t1.$singleData,";
                } else if (in_array($singleData, $add_t2)) {
                    $final_group_by_string .= "t2.$singleData,";
                } else if (in_array($singleData, $add_t3)) {
                    $final_group_by_string .= "t3.$singleData,";
                } else if (in_array($singleData, $add_t5)) {
                    $final_group_by_string .= "t5.$singleData,";
                }
            }

            $final_group_by_string = rtrim($final_group_by_string, ',');
        }

        if ($final_group_by_string == "") {
            $final_group_by_string = "t1.report_au_auto_id,t1.report_date";
        }

        $option['group_by'] = $final_group_by_string;

        $total_records = $this->mdl_datatable->get_analytics_list($option, 1);

        $total_records_data = array();

        if ($total_records > 1) {
            $total_records_data = $this->mdl_datatable->get_analytics_list($option, 2);

            // echo $this->db->last_query();
            // $this->plog($total_records_data);

            if ($total_records_data) {

                $needToNumberFormatArray = [
                    "total_ad_requests",
                    "total_matched_requests",
                    "total_impressions",
                    "total_clicks",
                ];

                $needToAddCurrencySymbolArray = [
                    "total_estimated_earnings",
                    "total_observed_ecpm"
                ];

                $needToAddPercentageSymbolArray = [
                    "total_match_rate",
                    "total_show_rate",
                    "total_impression_ctr"
                ];

                $currency_symbol_for_total = $this->get_currency_symbol($total_records_data['admob_currency_code']);

                foreach ($total_records_data as $key => $singleData) {

                    if (in_array($key, $needToNumberFormatArray)) {
                        $total_records_data[$key] = $this->indian_number_format($singleData);
                    } else if (in_array($key, $needToAddCurrencySymbolArray)) {
                        $total_records_data[$key] = $currency_symbol_for_total . ((!empty($singleData)) ? $singleData : '0.00');
                    } else if (in_array($key, $needToAddPercentageSymbolArray)) {
                        $total_records_data[$key] = number_format((float) $singleData, 2, '.', '') . '%';
                    }
                }
            }
        }

        $limit = isset($_POST['length']) ? $_POST['length'] : 15;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;

        $resultSet = $this->mdl_datatable->get_analytics_list($option, 0, $start, $limit);

        // $this->plog($resultSet);

        $singleListArray = array();
        if ($resultSet) {

            $get_all_ad_formats = $this->mdl_common->custom_query("SELECT * FROM tbl_ad_unit_format")->result_array();

            // $this->plog($get_all_ad_formats, 1);

            foreach ($resultSet as $row) {

                $currency_symbol = $this->get_currency_symbol($row->admob_currency_code);

                $singleListArray['app_icon'] = $row->app_icon;
                $singleListArray['app_display_name'] = $row->app_display_name;
                $singleListArray['app_platform'] = $row->app_platform;
                $singleListArray['au_display_name'] = $row->au_display_name;

                $formatKey = array_search($row->au_format_auto_id, array_column($get_all_ad_formats, "au_format_auto_id"));
                if ($formatKey !== false) {
                    $singleListArray['ad_unit_format'] = $get_all_ad_formats[$formatKey]['au_format_display_name'];
                } else {
                    $singleListArray['ad_unit_format'] = '-';
                }

                $singleListArray['country_name'] = $row->country_name;
                $singleListArray['report_date'] = date('d/m/Y', strtotime($row->report_date));
                $singleListArray['estimated_earnings'] = $currency_symbol . number_format((float) $row->total_estimated_earnings, 2, '.', '');
                $singleListArray['observed_ecpm'] = $currency_symbol . (($row->total_observed_ecpm) ? $row->total_observed_ecpm : '0.00');
                $singleListArray['ad_requests'] = $this->indian_number_format($row->total_ad_requests);
                $singleListArray['match_rate'] = $row->total_match_rate . '%';
                $singleListArray['matched_requests'] = $this->indian_number_format($row->total_matched_requests);
                $singleListArray['show_rate'] = ($row->total_show_rate) ? $row->total_show_rate . '%' : '-';
                $singleListArray['impressions'] = $this->indian_number_format($row->total_impressions);
                $singleListArray['impression_ctr'] = ($row->total_impression_ctr) ? $row->total_impression_ctr . '%' : '-';
                $singleListArray['clicks'] = $this->indian_number_format($row->total_clicks);
                $aaData[] = $singleListArray;
            }

            if (count($resultSet) == 1) {
                $total_records_data = array(
                    'total_estimated_earnings' => $singleListArray['estimated_earnings'],
                    'total_observed_ecpm' => $singleListArray['observed_ecpm'],
                    'total_ad_requests' => $singleListArray['ad_requests'],
                    'total_match_rate' => $singleListArray['match_rate'],
                    'total_matched_requests' => $singleListArray['matched_requests'],
                    'total_show_rate' => $singleListArray['show_rate'],
                    'total_impressions' => $singleListArray['impressions'],
                    'total_impression_ctr' => $singleListArray['impression_ctr'],
                    'total_clicks' => $singleListArray['clicks'],
                    'admob_currency_code' => 'USD'
                );
            }
        } else {
            $total_records_data = array(
                'total_estimated_earnings' => '$0.00',
                'total_observed_ecpm' => '$0.00',
                'total_ad_requests' => '0',
                'total_match_rate' => '0.00%',
                'total_matched_requests' => '0',
                'total_show_rate' => "-",
                'total_impressions' => '0',
                'total_impression_ctr' => '-',
                'total_clicks' => '0',
                'admob_currency_code' => 'USD'
            );
        }

        $finalJsonArray['sEcho'] = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;
        $finalJsonArray['iTotalRecords'] = $total_records;
        $finalJsonArray['iTotalDisplayRecords'] = $total_records;
        $finalJsonArray['total_records_data'] = $total_records_data;
        $finalJsonArray['aaData'] = $aaData;
        $this->cors_header();
        echo json_encode($finalJsonArray);
    }

    public function get_analytics_filtering_data()
    {
        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');
        $user_info = $this->verify_user($user_id, $user_token);

        $where = 'WHERE t1.app_store_id != "" ';

        $apps_list_q = "SELECT t1.app_auto_id,t1.app_display_name,t1.app_platform,t1.app_store_id,
        GROUP_CONCAT(CONCAT(t2.au_auto_id,'#',t2.au_display_name)) as ad_units
        FROM tbl_apps as t1
        JOIN tbl_ad_units as t2 ON t2.au_app_auto_id = t1.app_auto_id ";

        if ($user_info['user_role'] != 1) {
            $apps_list_q .= " JOIN tbl_ad_unit_permissions as t3 ON t3.permission_au_auto_id = t2.au_auto_id ";
            $where .= " AND t3.permission_user_id = " . $user_info['user_id'];
        }

        $apps_list_q .= " $where GROUP BY t1.app_auto_id ORDER BY t1.app_display_name ASC";
        $this->result['all_app_list'] = $this->mdl_common->custom_query($apps_list_q)->result_array();

        $get_all_ad_formats = $this->mdl_common->custom_query("SELECT * FROM tbl_ad_unit_format")->result_array();
        $this->result['all_ad_formats'] = $get_all_ad_formats;

        $get_all_countries = $this->mdl_common->custom_query("SELECT * FROM tbl_country")->result_array();
        $this->result['all_countries'] = $get_all_countries;

        $this->msg = "Success";
        $this->_sendResponse(1);
    }

    public function get_dashboard_eastimated_earnings()
    {
        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        $common_where = "";

        $common_query_string = "SELECT FORMAT(SUM(t1.report_estimated_earnings),2) as total_estimated_earnings,
        SUM(t1.report_estimated_earnings) as total_estimated_earnings_original 
        FROM tbl_report as t1 
        JOIN tbl_ad_units as t2 ON t2.au_auto_id = t1.report_au_auto_id ";

        if ($user_info['user_role'] != 1) {
            $common_query_string .= " JOIN tbl_ad_unit_permissions as t3 ON t3.permission_au_auto_id = t2.au_auto_id ";

            $common_where .= " AND t3.permission_user_id = " . $user_info['user_id'];
        }

        // QUERY FOR TODAY's Record
        $where_today_start_date = date("Y-m-d");
        $where_today_end_date = date("Y-m-d");
        $where_today = " t1.report_date >= '$where_today_start_date' AND t1.report_date <= '$where_today_end_date' ";

        $query_today = "$common_query_string WHERE $where_today $common_where";
        $result_today = $this->mdl_common->custom_query($query_today)->result();

        if ($result_today[0]->total_estimated_earnings == null || $result_today[0]->total_estimated_earnings == '') {
            $dashboard_today_so_far = "$0.00";
            $dashboard_today_so_far_tooltip = "$0.00";
        } else {

            if (($result_today[0]->total_estimated_earnings / 1000) > 9) {
                $dashboard_today_so_far = $this->indian_number_format(round($result_today[0]->total_estimated_earnings, 1)) . 'K';
            } else if (($result_today[0]->total_estimated_earnings / 1000) > 0) {
                $dashboard_today_so_far = $this->indian_number_format(round($result_today[0]->total_estimated_earnings, 2)) . 'K';
            } else {
                $dashboard_today_so_far = $this->indian_number_format(round($result_today[0]->total_estimated_earnings, 0));
            }

            $dashboard_today_so_far = "$" . $dashboard_today_so_far;
            $dashboard_today_so_far_tooltip = "$" . $this->indian_number_format($result_today[0]->total_estimated_earnings);
        }

        // QUERY FOR YESTERDAY's Record
        $where_yesterday_start_date = date("Y-m-d", strtotime("1 days ago"));
        $where_yesterday_end_date = date("Y-m-d", strtotime("1 days ago"));

        $where_yesterday = " t1.report_date >= '$where_yesterday_start_date' AND t1.report_date <= '$where_yesterday_end_date' ";

        $query_yesterday = "$common_query_string WHERE $where_yesterday $common_where";
        $result_yesterday = $this->mdl_common->custom_query($query_yesterday)->result();

        // QUERY FOR This month Record
        $where_this_month_start_date = date("Y-m-01");
        $where_this_month_end_date = date("Y-m-d");

        $where_this_month = " t1.report_date >= '$where_this_month_start_date' AND t1.report_date <= '$where_this_month_end_date' ";

        $query_this_month = "$common_query_string WHERE $where_this_month $common_where";
        $result_this_month = $this->mdl_common->custom_query($query_this_month)->result();

        // QUERY FOR Last month Record
        $where_last_month_start_date = date("Y-m-d", strtotime('first day of last month'));
        $where_last_month_end_date = date("Y-m-d", strtotime('last day of last month'));

        $where_last_month = " t1.report_date >= '$where_last_month_start_date' AND t1.report_date <= '$where_last_month_end_date' ";

        $query_last_month = "$common_query_string WHERE $where_last_month $common_where";
        $result_last_month = $this->mdl_common->custom_query($query_last_month)->result();

        $final_res = array(
            'dashboard_today_so_far' => $dashboard_today_so_far,
            'dashboard_today_so_far_tooltip' => $dashboard_today_so_far_tooltip,
            'dashboard_yesterday_so_far' => ($result_yesterday[0]->total_estimated_earnings == null || $result_yesterday[0]->total_estimated_earnings == '') ? '$0.00' : "$" . $this->indian_number_format($result_yesterday[0]->total_estimated_earnings),
            'dashboard_yesterday_so_far_tooltip' => ($result_yesterday[0]->total_estimated_earnings == null || $result_yesterday[0]->total_estimated_earnings == '') ? '$0.00' : "$" . $this->indian_number_format($result_yesterday[0]->total_estimated_earnings),
            'dashboard_this_month_so_far' => ($result_this_month[0]->total_estimated_earnings == null || $result_this_month[0]->total_estimated_earnings == '') ? '$0.00' : "$" . $this->indian_number_format($result_this_month[0]->total_estimated_earnings),
            'dashboard_this_month_so_far_tooltip' => ($result_this_month[0]->total_estimated_earnings == null || $result_this_month[0]->total_estimated_earnings == '') ? '$0.00' : "$" . $this->indian_number_format($result_this_month[0]->total_estimated_earnings),
            'dashboard_last_month_so_far' => ($result_last_month[0]->total_estimated_earnings == null || $result_last_month[0]->total_estimated_earnings == '') ? '$0.00' : "$" . $this->indian_number_format($result_last_month[0]->total_estimated_earnings),
            'dashboard_last_month_so_far_tooltip' => ($result_last_month[0]->total_estimated_earnings == null || $result_last_month[0]->total_estimated_earnings == '') ? '$0.00' : "$" . $this->indian_number_format($result_last_month[0]->total_estimated_earnings),
        );

        $this->cors_header();
        echo json_encode($final_res);
    }

    public function get_dashboard_performances()
    {

        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        $type = $this->required_parameter($postData, 'type');

        switch ($type) {
            case 1: // Today so far

                $date_range_from = date("Y-m-d");
                $date_range_to = date("Y-m-d");

                $compare_date_range_from = date("Y-m-d", strtotime("-1 day"));
                $compare_date_range_to = date("Y-m-d", strtotime("-1 day"));

                break;
            case 2: // Yesterday vs same day last week

                $date_range_from = date("Y-m-d", strtotime("-1 day"));
                $date_range_to = date("Y-m-d", strtotime("-1 day"));

                $compare_date_range_from = date("Y-m-d", strtotime("-8 day"));
                $compare_date_range_to = date("Y-m-d", strtotime("-8 days"));

                break;

            case 3: // Last 7 days vs previous 7 days

                $date_range_from = date("Y-m-d", strtotime("-7 days"));
                $date_range_to = date("Y-m-d", strtotime("-1 days"));

                $compare_date_range_from = date("Y-m-d", strtotime("-14 days"));
                $compare_date_range_to = date("Y-m-d", strtotime("-8 days"));

                break;
            case 4: // Last 28 days vs previous 28 days

                $date_range_from = date("Y-m-d", strtotime("-28 days"));
                $date_range_to = date("Y-m-d", strtotime("-1 days"));

                $compare_date_range_from = date("Y-m-d", strtotime("-56 days"));
                $compare_date_range_to = date("Y-m-d", strtotime("-29 days"));

                break;
        }

        $common_where = "";

        $common_query_string = "SELECT FORMAT(SUM(t1.report_estimated_earnings),2) as total_estimated_earnings,
        SUM(t1.report_ad_requests) as total_ad_requests,
        FORMAT(((SUM(t1.report_matched_requests)/SUM(t1.report_ad_requests))*100),2) as total_match_rate,
        FORMAT(((SUM(t1.report_estimated_earnings)/SUM(t1.report_impressions))*1000),2) as total_observed_ecpm,
        SUM(t1.report_impressions) as total_impressions 
        FROM tbl_report as t1 
        JOIN tbl_ad_units as t2 ON t2.au_auto_id = t1.report_au_auto_id ";

        if ($user_info['user_role'] != 1) {
            $common_query_string .= " JOIN tbl_ad_unit_permissions as t3 ON t3.permission_au_auto_id = t2.au_auto_id ";

            $common_where .= " AND t3.permission_user_id = " . $user_info['user_id'];
        }

        // QUERY FOR CURRENT Record
        $query_current = "$common_query_string 
        WHERE t1.report_date >= '$date_range_from' AND t1.report_date <= '$date_range_to' 
        $common_where";
        $result_current = $this->mdl_common->custom_query($query_current)->row_array();

        // echo $this->db->last_query();
        // $this->plog($result_current);

        if (empty($result_current['total_estimated_earnings'])) {
            $result_current['total_estimated_earnings'] = 0;
        }
        if (empty($result_current['total_ad_requests'])) {
            $result_current['total_ad_requests'] = 0;
        }
        if (empty($result_current['total_match_rate'])) {
            $result_current['total_match_rate'] = 0;
        }
        if (empty($result_current['total_observed_ecpm'])) {
            $result_current['total_observed_ecpm'] = 0;
        }
        if (empty($result_current['total_impressions'])) {
            $result_current['total_impressions'] = 0;
        }

        // QUERY FOR COMPARE Record
        $query_compare = "$common_query_string 
        WHERE t1.report_date >= '$compare_date_range_from' AND t1.report_date <= '$compare_date_range_to' 
        $common_where";
        $result_compare = $this->mdl_common->custom_query($query_compare)->row_array();

        // echo $this->db->last_query();
        // $this->plog($result_compare);

        if (empty($result_compare['total_estimated_earnings'])) {
            $result_compare['total_estimated_earnings'] = 0;
        }
        if (empty($result_compare['total_ad_requests'])) {
            $result_compare['total_ad_requests'] = 0;
        }
        if (empty($result_compare['total_match_rate'])) {
            $result_compare['total_match_rate'] = 0;
        }
        if (empty($result_compare['total_observed_ecpm'])) {
            $result_compare['total_observed_ecpm'] = 0;
        }
        if (empty($result_compare['total_impressions'])) {
            $result_compare['total_impressions'] = 0;
        }


        // [For Est earning]
        $total_estimated_earnings_previous_calc = number_format((($result_compare['total_estimated_earnings']) - ($result_current['total_estimated_earnings'])), 2, '.', '');
        $total_estimated_earnings_percentage = $this->cal_percentage($result_compare['total_estimated_earnings'], $total_estimated_earnings_previous_calc);

        $total_estimated_earnings_previous = ($total_estimated_earnings_previous_calc >= 0) ? '<span class="text-danger">-$' . $this->abs($total_estimated_earnings_previous_calc) . ' (-' . $this->abs($total_estimated_earnings_percentage) . '%)</span>' : '<span class="text-success">+$' . $this->abs($total_estimated_earnings_previous_calc) . ' (+' . $this->abs($total_estimated_earnings_percentage) . '%)</span>';

        $total_estimated_earnings_tooltip_current = $this->indian_number_format($result_current['total_estimated_earnings']);
        $total_estimated_earnings_tooltip_previous = $this->indian_number_format($result_compare['total_estimated_earnings']);

        $dashboard_performance_est_earnings = '<h2 class="mb-1 fw-normal"><span data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="right" data-bs-html="true">$' . (!empty($result_current['total_estimated_earnings']) ? $result_current['total_estimated_earnings'] : 0) . '</span></h2><h6>' . $total_estimated_earnings_previous . '</h6>';
        // [/For Est earning]


        // [For impressions]
        $total_performance_impr_calc = ($result_current['total_impressions']) - ($result_compare['total_impressions']);

        $performance_impr_devide_compare = ($total_performance_impr_calc / 1000);

        if ($total_performance_impr_calc > 999) {

            if ($performance_impr_devide_compare > 99) {
                $total_performance_impr = ceil($performance_impr_devide_compare) . 'K';
            } else if ($performance_impr_devide_compare > 0) {

                if ($performance_impr_devide_compare > 9) {
                    $total_performance_impr = $this->abs(round($performance_impr_devide_compare, 1)) . 'K';
                } else if ($performance_impr_devide_compare > 0) {
                    $total_performance_impr = $this->abs(round($performance_impr_devide_compare, 2)) . 'K';
                } else {
                    $total_performance_impr = $this->abs(round($performance_impr_devide_compare, 0));
                }
            } else {

                if ($performance_impr_devide_compare < -9) {
                    $total_performance_impr = $this->abs(round($performance_impr_devide_compare, 1)) . 'K';
                } else if ($performance_impr_devide_compare < 0) {
                    $total_performance_impr = $this->abs(round($performance_impr_devide_compare, 2)) . 'K';
                } else {
                    $total_performance_impr = $this->abs(round($performance_impr_devide_compare, 0));
                }
            }
        } else {

            if ($performance_impr_devide_compare < -9) {
                $total_performance_impr = $this->abs(round($performance_impr_devide_compare, 1)) . 'K';
            } else if ($performance_impr_devide_compare < 0) {
                $total_performance_impr = $this->abs(round($performance_impr_devide_compare, 2)) . 'K';
            } else {
                $total_performance_impr = $this->abs(round($performance_impr_devide_compare, 0));
            }
        }

        $total_performance_impr_percentage = $this->cal_percentage($result_compare['total_impressions'], $total_performance_impr_calc);
        $total_performance_impr = ($total_performance_impr_calc >= 0) ? '<span class="text-success">+' . $total_performance_impr . ' (+' . $this->abs($total_performance_impr_percentage) . '%)</span>' : '<span class="text-danger">-' . $total_performance_impr . ' (-' . $this->abs($total_performance_impr_percentage) . '%)</span>';

        if ($result_current['total_impressions'] > 999) {

            $performance_impr_devide = ($result_current['total_impressions'] / 1000);

            if ($performance_impr_devide > 99) {
                if ($result_current['total_impressions'] < 1000000) {
                    $impr_round_figure = ceil($performance_impr_devide) . 'K';
                } else if ($result_current['total_impressions'] < 1000000000) {
                    $impr_round_figure = number_format($result_current['total_impressions'] / 1000000, 2) . 'M';
                } else {
                    $impr_round_figure = number_format($result_current['total_impressions'] / 1000000000, 2) . 'B';
                }
            } else if ($performance_impr_devide > 0) {

                if ($performance_impr_devide > 9) {
                    $impr_round_figure = $this->abs(round($performance_impr_devide, 1)) . 'K';
                } else if ($performance_impr_devide > 0) {
                    $impr_round_figure = $this->abs(round($performance_impr_devide, 2)) . 'K';
                } else {
                    $impr_round_figure = $this->abs(round($performance_impr_devide, 0));
                }
            } else {

                if ($performance_impr_devide < -9) {
                    $impr_round_figure = $this->abs(round($performance_impr_devide, 1)) . 'K';
                } else if ($performance_impr_devide < 0) {
                    $impr_round_figure = $this->abs(round($performance_impr_devide, 2)) . 'K';
                } else {
                    $impr_round_figure = $this->abs(round($performance_impr_devide, 0));
                }
            }
        } else {
            $impr_round_figure = $result_current['total_impressions'];
        }

        $total_estimated_impr_tooltip_current = $this->indian_number_format($result_current['total_impressions']);
        $total_estimated_impr_tooltip_previous = $this->indian_number_format($result_compare['total_impressions']);

        $dashboard_performance_impr = '<h2 class="mb-1 fw-normal"><span data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="right" data-bs-html="true">' . (!empty($impr_round_figure) ? $impr_round_figure : 0) . '</span></h2><h6>' . $total_performance_impr . '</h6>';
        // [/For impressions]

        // [For ecpm]
        $total_observed_ecpm_previous_calc = number_format((float) ($result_compare['total_observed_ecpm']) - ($result_current['total_observed_ecpm']), 2, '.', '');
        $total_observed_ecpm_percentage = $this->cal_percentage($result_compare['total_observed_ecpm'], $total_observed_ecpm_previous_calc);

        $total_observed_ecpm_previous = ($total_observed_ecpm_previous_calc >= 0) ? '<span class="text-danger">-$' . $this->abs($total_observed_ecpm_previous_calc) . ' (-' . $this->abs($total_observed_ecpm_percentage) . '%)</span>' : '<span class="text-success">+$' . $this->abs($total_observed_ecpm_previous_calc) . ' (+' . $this->abs($total_observed_ecpm_percentage) . '%)</span>';

        $total_estimated_ecpm_tooltip_current = $result_current['total_observed_ecpm'];
        $total_estimated_ecpm_tooltip_previous = $result_compare['total_observed_ecpm'];

        $dashboard_performance_ecpm = '<h2 class="mb-1 fw-normal"><span data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="right" data-bs-html="true">$' . (!empty($result_current['total_observed_ecpm']) ? $result_current['total_observed_ecpm'] : 0) . '</span></h2><h6>' . $total_observed_ecpm_previous . '</h6>';
        // [/For ecpm]

        // [For requests]
        $total_performance_requests_calc_round = ($result_current['total_ad_requests']) - ($result_compare['total_ad_requests']);
        $total_performance_requests_percentage = $this->cal_percentage($result_compare['total_ad_requests'], $total_performance_requests_calc_round);

        $performance_req_devide = ($total_performance_requests_calc_round / 1000);

        if ($performance_req_devide > 99) {

            $total_performance_requests_calc = ceil($performance_req_devide) . 'K';
        } else if ($performance_req_devide > 0) {

            if ($performance_req_devide > 9) {
                $total_performance_requests_calc = $this->abs(round($performance_req_devide, 1)) . 'K';
            } elseif ($performance_req_devide > 0) {
                $total_performance_requests_calc = $this->abs(round($performance_req_devide, 2)) . 'K';
            } else {
                $total_performance_requests_calc = $this->abs(round($performance_req_devide, 0));
            }
        } else {

            if ($performance_req_devide < -9) {
                $total_performance_requests_calc = $this->abs(round($performance_req_devide, 1)) . 'K';
            } else if ($performance_req_devide < 0) {
                $total_performance_requests_calc = $this->abs(round($performance_req_devide, 2)) . 'K';
            } else {
                $total_performance_requests_calc = $this->abs(round($performance_req_devide, 0));
            }
        }

        $total_performance_req_devide = ($result_current['total_ad_requests'] / 1000);

        if ($total_performance_req_devide > 99) {
            if ($result_current['total_ad_requests'] < 1000000) {
                $total_performance_req = round($total_performance_req_devide) . 'K';
            } else if ($result_current['total_ad_requests'] < 1000000000) {
                $total_performance_req = number_format($result_current['total_ad_requests'] / 1000000, 2) . 'M';
            } else {
                $total_performance_req = number_format($result_current['total_ad_requests'] / 1000000000, 2) . 'B';
            }
        } else if ($total_performance_req_devide > 0) {
            $total_performance_req = round($total_performance_req_devide, 1) . 'K';
        } else {
            $total_performance_req = $result_current['total_ad_requests'];
        }

        $total_performance_requests = ($total_performance_requests_calc_round >= 0) ? '<span class="text-success">+' . $total_performance_requests_calc . ' (+' . $this->abs($total_performance_requests_percentage) . '%)</span>' : '<span class="text-danger">-' . $total_performance_requests_calc . ' (-' . $this->abs($total_performance_requests_percentage) . '%)</span>';

        $total_estimated_requests_tooltip_current = $this->indian_number_format($result_current['total_ad_requests']);
        $total_estimated_requests_tooltip_previous = $this->indian_number_format($result_compare['total_ad_requests']);

        $dashboard_performance_requests = '<h2 class="mb-1 fw-normal"><span data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="right" data-bs-html="true">' . (!empty($total_performance_req) ? $total_performance_req : 0) . '</span></h2><h6>' . $total_performance_requests . '</h6>';
        // [/For requests]

        // [For match_rate]
        $total_performance_match_rate_calc = number_format((float) ($result_current['total_match_rate']) - ($result_compare['total_match_rate']), 2, '.', '');
        $total_performance_match_rate_percentage = $this->cal_percentage($result_compare['total_match_rate'], $total_performance_match_rate_calc);
        $total_performance_match_rate = ($total_performance_match_rate_calc >= 0) ? '<span class="text-success">+' . $total_performance_match_rate_calc . '% (+' . $this->abs($total_performance_match_rate_percentage) . '%)</span>' : '<span class="text-danger">-' . $total_performance_match_rate_calc . '% (-' . $this->abs($total_performance_match_rate_percentage) . '%)</span>';

        $total_estimated_match_rate_tooltip_current = $result_current['total_match_rate'];
        $total_estimated_match_rate_tooltip_previous = $result_compare['total_match_rate'];

        $dashboard_performance_match_rate = '<h2 class="mb-1 fw-normal"><span data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="right" data-bs-html="true">' . (!empty($result_current['total_match_rate']) ? $result_current['total_match_rate'] : 0) . '%</span></h2><h6>' . $total_performance_match_rate . '</h6>';
        // [/For match_rate]

        $final_res = array(
            'est_earnings' => array(
                'dashboard_performance_est_earnings' => $dashboard_performance_est_earnings,
                'total_estimated_earnings_tooltip_current' => $total_estimated_earnings_tooltip_current,
                'total_estimated_earnings_tooltip_previous' => $total_estimated_earnings_tooltip_previous
            ),
            'requests' => array(
                'dashboard_performance_requests' => $dashboard_performance_requests,
                'total_estimated_requests_tooltip_current' => $total_estimated_requests_tooltip_current,
                'total_estimated_requests_tooltip_previous' => $total_estimated_requests_tooltip_previous,

            ),
            'impr' => array(
                'dashboard_performance_impr' => $dashboard_performance_impr,
                'total_estimated_impr_tooltip_current' => $total_estimated_impr_tooltip_current,
                'total_estimated_impr_tooltip_previous' => $total_estimated_impr_tooltip_previous

            ),
            'match_rate' => array(
                'dashboard_performance_match_rate' => $dashboard_performance_match_rate,
                'total_estimated_match_rate_tooltip_current' => $total_estimated_match_rate_tooltip_current . '%',
                'total_estimated_match_rate_tooltip_previous' => $total_estimated_match_rate_tooltip_previous . '%'
            ),
            'ecpm' => array(
                'dashboard_performance_ecpm' => $dashboard_performance_ecpm,
                'total_estimated_ecpm_tooltip_current' => $total_estimated_ecpm_tooltip_current,
                'total_estimated_ecpm_tooltip_previous' => $total_estimated_ecpm_tooltip_previous
            ),
        );

        $this->cors_header();
        echo json_encode($final_res);
    }

    public function dashboard_app_performance_list()
    {

        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        $type = $this->required_parameter($postData, 'type');

        switch ($type) {
            case 1: // Today so far

                $date_range_from = date("Y-m-d");
                $date_range_to = date("Y-m-d");

                $compare_date_range_from = date("Y-m-d", strtotime("-1 day"));
                $compare_date_range_to = date("Y-m-d", strtotime("-1 day"));

                break;
            case 2: // Yesterday vs same day last week

                $date_range_from = date("Y-m-d", strtotime("-1 day"));
                $date_range_to = date("Y-m-d", strtotime("-1 day"));

                $compare_date_range_from = date("Y-m-d", strtotime("-8 day"));
                $compare_date_range_to = date("Y-m-d", strtotime("-8 days"));

                break;

            case 3: // Last 7 days vs previous 7 days

                $date_range_from = date("Y-m-d", strtotime("-7 days"));
                $date_range_to = date("Y-m-d", strtotime("-1 days"));

                $compare_date_range_from = date("Y-m-d", strtotime("-14 days"));
                $compare_date_range_to = date("Y-m-d", strtotime("-8 days"));

                break;
            case 4: // Last 28 days vs previous 28 days

                $date_range_from = date("Y-m-d", strtotime("-28 days"));
                $date_range_to = date("Y-m-d", strtotime("-1 days"));

                $compare_date_range_from = date("Y-m-d", strtotime("-56 days"));
                $compare_date_range_to = date("Y-m-d", strtotime("-29 days"));

                break;
        }

        $aaData = array();

        $common_where = "";

        $common_query_string = "SELECT FORMAT(SUM(t1.report_estimated_earnings),2) as total_estimated_earnings,
        SUM(t1.report_ad_requests) as total_ad_requests,
        FORMAT(((SUM(t1.report_matched_requests)/SUM(t1.report_ad_requests))*100),2) as total_match_rate,
        FORMAT(((SUM(t1.report_estimated_earnings)/SUM(t1.report_impressions))*1000),2) as total_observed_ecpm,
        SUM(t1.report_impressions) as total_impressions,
        t3.app_display_name, t3.app_store_id,t3.app_icon,t3.app_platform,t3.app_auto_id   
        FROM tbl_report as t1 
        JOIN tbl_ad_units as t2 ON t2.au_auto_id = t1.report_au_auto_id 
        JOIN tbl_apps as t3 ON t3.app_auto_id = t2.au_app_auto_id ";

        if ($user_info['user_role'] != 1) {
            $common_query_string .= " JOIN tbl_ad_unit_permissions as t4 ON t4.permission_au_auto_id = t2.au_auto_id ";

            $common_where .= " AND t4.permission_user_id = " . $user_info['user_id'];
        }

        $common_limit = 5;

        // QUERY FOR CURRENT Record
        $query_current = "$common_query_string 
        WHERE t1.report_date >= '$date_range_from' AND t1.report_date <= '$date_range_to' 
        $common_where GROUP BY t3.app_auto_id HAVING total_estimated_earnings > 0 ORDER BY total_estimated_earnings DESC LIMIT $common_limit";
        $result_current = $this->mdl_common->custom_query($query_current)->result_array();

        // echo $this->db->last_query();
        // $this->plog($result_current, 1);

        // QUERY FOR COMPARE Record
        $query_compare = "$common_query_string 
        WHERE t1.report_date >= '$compare_date_range_from' AND t1.report_date <= '$compare_date_range_to' 
        $common_where GROUP BY t3.app_auto_id HAVING total_estimated_earnings > 0 ORDER BY total_estimated_earnings DESC LIMIT $common_limit";
        $result_compare = $this->mdl_common->custom_query($query_compare)->result_array();

        // $this->plog($result_compare);

        $singleListArray = array();
        if ($result_current && $result_compare) {

            foreach ($result_current as $key => $row) {

                // $this->plog($row);

                if (isset($result_current[$key]) && isset($result_compare[$key])) {

                    $admob_app_name = '<div class="d-flex">';

                    if ($row['app_icon']) {
                        $admob_app_name .= '<div class="flex-shrink-0 avatar-xs"><img src="' . $row['app_icon'] . '" alt="" class="avatar-xs rounded"></div>';
                    } else {
                        if ($row['admob_app_platform'] == 1) {
                            $admob_app_name .= '<div class="flex-shrink-0 avatar-xs"><div class="avatar-title bg-soft-light text-info rounded" style="font-size: xx-large"><i class="ri-app-store-line"></i></div></div>';
                        } else {
                            $admob_app_name .= '<div class="flex-shrink-0 avatar-xs"><div class="avatar-title bg-soft-light text-primary rounded" style="font-size: xx-large"><i class="ri-google-play-line"></i></div></div>';
                        }
                    }

                    $admob_app_name .= '<div class="flex-grow-1 overflow-hidden ms-2"><a href="app-details/' . $row['app_auto_id'] . '" class="text-truncate text-decoration-none mb-0 fs-6" title="' . $row['app_display_name'] . '">' . $this->truncate($row['app_display_name'], 25) . '</a><br><small class="text-truncate text-muted" title="' . $row['app_store_id'] . '">' . (($row['app_store_id']) ? $this->truncate($row['app_store_id'], 25) : "-") . '</small></div></div>';
                    $singleListArray['app_display_name'] = $admob_app_name;

                    // [For Est earning]
                    $total_estimated_earnings_previous_calc = number_format((($result_compare[$key]['total_estimated_earnings']) - ($result_current[$key]['total_estimated_earnings'])), 2, '.', '');
                    $total_estimated_earnings_percentage = $this->cal_percentage($result_compare[$key]['total_estimated_earnings'], $total_estimated_earnings_previous_calc);
                    $total_estimated_earnings_previous = ($total_estimated_earnings_previous_calc >= 0) ? '<span class="text-danger">-$' . $this->abs($total_estimated_earnings_previous_calc) . ' (-' . $this->abs($total_estimated_earnings_percentage) . '%)</span>' : '<span class="text-success">+$' . $this->abs($total_estimated_earnings_previous_calc) . ' (+' . $this->abs($total_estimated_earnings_percentage) . '%)</span>';
                    $dashboard_performance_est_earnings = '<h5 class="mb-1 fw-normal">$' . $result_current[$key]['total_estimated_earnings'] . '</h5><h6>' . $total_estimated_earnings_previous . '</h6>';

                    $singleListArray['est_earnings'] = $dashboard_performance_est_earnings;
                    // [/For Est earning]

                    // [For impressions]

                    $total_performance_impr_calc = ($result_current[$key]['total_impressions']) - ($result_compare[$key]['total_impressions']);
                    $performance_impr_devide_compare = ($total_performance_impr_calc / 1000);
                    if ($total_performance_impr_calc > 999) {

                        if ($performance_impr_devide_compare > 99) {
                            $total_performance_impr = ceil($performance_impr_devide_compare) . 'K';
                        } else if ($performance_impr_devide_compare > 0) {

                            if ($performance_impr_devide_compare > 9) {

                                $total_performance_impr = $this->abs(round($performance_impr_devide_compare, 1)) . 'K';
                            } else if ($performance_impr_devide_compare > 0) {

                                $total_performance_impr = $this->abs(round($performance_impr_devide_compare, 2)) . 'K';
                            } else {

                                $total_performance_impr = $this->abs(round($performance_impr_devide_compare, 0));
                            }
                        } else {

                            if ($performance_impr_devide_compare < -9) {
                                $total_performance_impr = $this->abs(round($performance_impr_devide_compare, 1)) . 'K';
                            } else if ($performance_impr_devide_compare < 0) {
                                $total_performance_impr = $this->abs(round($performance_impr_devide_compare, 2)) . 'K';
                            } else {
                                $total_performance_impr = $this->abs(round($performance_impr_devide_compare, 0));
                            }
                        }
                    } else {

                        if ($performance_impr_devide_compare < -9) {
                            $total_performance_impr = $this->abs(round($performance_impr_devide_compare, 1)) . 'K';
                        } else if ($performance_impr_devide_compare < 0) {
                            $total_performance_impr = $this->abs(round($performance_impr_devide_compare, 2)) . 'K';
                        } else {
                            $total_performance_impr = $this->abs(round($performance_impr_devide_compare, 0));
                        }
                    }

                    $total_performance_impr_percentage = $this->cal_percentage($result_compare[$key]['total_impressions'], $total_performance_impr_calc);

                    $total_performance_impr = ($total_performance_impr_calc >= 0) ? '<span class="text-success">+' . $total_performance_impr . ' (+' . $this->abs($total_performance_impr_percentage) . '%)</span>' : '<span class="text-danger">-' . $total_performance_impr . ' (-' . $this->abs($total_performance_impr_percentage) . '%)</span>';

                    if ($result_current[$key]['total_impressions'] > 999) {

                        $performance_impr_devide = ($result_current[$key]['total_impressions'] / 1000);

                        if ($performance_impr_devide > 99) {
                            $impr_round_figure = ceil($performance_impr_devide) . 'K';
                        } else if ($performance_impr_devide > 0) {

                            if ($performance_impr_devide > 9) {
                                $impr_round_figure = $this->abs(round($performance_impr_devide, 1)) . 'K';
                            } else if ($performance_impr_devide > 0) {
                                $impr_round_figure = $this->abs(round($performance_impr_devide, 2)) . 'K';
                            } else {
                                $impr_round_figure = $this->abs(round($performance_impr_devide, 0));
                            }
                        } else {

                            if ($performance_impr_devide < -9) {
                                $impr_round_figure = $this->abs(round($performance_impr_devide, 1)) . 'K';
                            } else if ($performance_impr_devide < 0) {
                                $impr_round_figure = $this->abs(round($performance_impr_devide, 2)) . 'K';
                            } else {
                                $impr_round_figure = $this->abs(round($performance_impr_devide, 0));
                            }
                        }
                    } else {
                        $impr_round_figure = $result_current[$key]['total_impressions'];
                    }

                    $dashboard_performance_impr = '<h5 class="mb-1 fw-normal">' . $impr_round_figure . '</h5><h6>' . $total_performance_impr . '</h6>';

                    $singleListArray['impr'] = $dashboard_performance_impr;

                    // [/For impressions]

                    $aaData[] = $singleListArray;
                }
            }
        }

        $finalJsonArray['sEcho'] = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;
        $finalJsonArray['iTotalRecords'] = $common_limit;
        $finalJsonArray['iTotalDisplayRecords'] = $common_limit;
        $finalJsonArray['aaData'] = $aaData;
        $this->cors_header();
        echo json_encode($finalJsonArray);
    }

    // Manage Cron Settings
    public function add_cron()
    {
        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        if ($user_info['user_role'] == 1) {

            $cron_command = $this->required_parameter($postData, 'cron_command');
            $cron_command = "curl " . $cron_command;

            $cron_minute = $this->required_parameter($postData, 'cron_minute');
            $cron_hour = $this->required_parameter($postData, 'cron_hour');
            $cron_day = $this->required_parameter($postData, 'cron_day');
            $cron_month = $this->required_parameter($postData, 'cron_month');
            $cron_weekday = $this->required_parameter($postData, 'cron_weekday');

            // check exist
            $checkOption = array(
                'select' => 'cron_auto_id',
                'from' => 'tbl_cron',
                'where' => array(
                    "cron_command" => $cron_command,
                    "cron_minute" => $cron_minute,
                    "cron_hour" => $cron_hour,
                    "cron_day" => $cron_day,
                    "cron_month" => $cron_month,
                    "cron_weekday" => $cron_weekday
                ),
                'pagination' => array(
                    'limit' => 1
                )
            );

            $existRes = $this->mdl_common->select($checkOption);

            if (isset($existRes[0]['cron_auto_id'])) {
                $this->msg = "Same cron exist with same settings.";
                $this->_sendResponse(0);
            } else {

                $current_datetime = date('Y-m-d H:i:s');

                $option = array(
                    'from' => 'tbl_cron',
                    'insert_data' => array(
                        'cron_command' => $cron_command,
                        'cron_minute' => $cron_minute,
                        'cron_hour' => $cron_hour,
                        'cron_day' => $cron_day,
                        'cron_month' => $cron_month,
                        'cron_weekday' => $cron_weekday,
                        'cron_created_by' => $user_id,
                        'cron_created_at' => $current_datetime,
                        'cron_updated_at' => $current_datetime
                    )
                );

                $insert = $this->mdl_common->insert($option);

                if ($insert) {
                    $this->msg = "Cron added successfully";
                    $this->_sendResponse(1);
                } else {

                    $this->msg = "Database error";
                    $this->_sendResponse(0);
                }
            }
        } else {
            $this->msg = "Permission denied";
            $this->_sendResponse(0);
        }
    }

    public function update_cron()
    {
        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        if ($user_info['user_role'] == 1) {

            $cron_auto_id = $this->required_parameter($postData, 'cron_auto_id');

            $cron_command = $this->required_parameter($postData, 'cron_command');
            $cron_command = "curl " . $cron_command;

            $cron_minute = $this->required_parameter($postData, 'cron_minute');
            $cron_hour = $this->required_parameter($postData, 'cron_hour');
            $cron_day = $this->required_parameter($postData, 'cron_day');
            $cron_month = $this->required_parameter($postData, 'cron_month');
            $cron_weekday = $this->required_parameter($postData, 'cron_weekday');

            // check exist
            $checkOption = array(
                'select' => 'cron_auto_id',
                'from' => 'tbl_cron',
                'where' => "cron_auto_id != $cron_auto_id AND (cron_command = '$cron_command' AND cron_minute = '$cron_minute' AND cron_hour = '$cron_hour' AND cron_day = '$cron_day' AND cron_month = '$cron_month' AND cron_weekday = '$cron_weekday')",
                'pagination' => array(
                    'limit' => 1
                )
            );

            $existRes = $this->mdl_common->select($checkOption);

            if (isset($existRes[0]['cron_auto_id'])) {
                $this->msg = "Same cron exist with same settings.";
                $this->_sendResponse(0);
            } else {

                $current_datetime = date('Y-m-d H:i:s');

                $option = array(
                    'from' => 'tbl_cron',
                    'update_data' => array(
                        'cron_command' => $cron_command,
                        'cron_minute' => $cron_minute,
                        'cron_hour' => $cron_hour,
                        'cron_day' => $cron_day,
                        'cron_month' => $cron_month,
                        'cron_weekday' => $cron_weekday,
                        'cron_updated_at' => $current_datetime
                    ),
                    'where' => array(
                        'cron_auto_id' => $cron_auto_id
                    )
                );

                $update = $this->mdl_common->update($option);

                if ($update) {
                    $this->msg = "Cron updated successfully";
                    $this->_sendResponse(1);
                } else {

                    $this->msg = "Database error";
                    $this->_sendResponse(0);
                }
            }
        } else {
            $this->msg = "Permission denied";
            $this->_sendResponse(0);
        }
    }

    public function delete_cron()
    {
        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        if ($user_info['user_role'] == 1) {

            $cron_auto_id = $this->required_parameter($postData, 'cron_auto_id');

            $option = array(
                'from' => 'tbl_cron',
                'where' => array(
                    'cron_auto_id' => $cron_auto_id
                )
            );

            $delete = $this->mdl_common->delete($option);

            if ($delete) {
                $this->msg = "Cron deleted successfully";
                $this->_sendResponse(1);
            } else {

                $this->msg = "Database error";
                $this->_sendResponse(0);
            }
        } else {
            $this->msg = "Permission denied";
            $this->_sendResponse(0);
        }
    }

    public function cron_list()
    {

        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        $this->load->model('Mdl_datatable', 'mdl_datatable');

        $option = array();
        $aaData = array();

        $total_records = $this->mdl_datatable->get_cron_list($option, $is_count = 1);

        $limit = isset($_POST['length']) ? $_POST['length'] : 15;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;

        $resultSet = $this->mdl_datatable->get_cron_list($option, $is_count = 0, $start, $limit);

        $singleListArray = array();
        if ($resultSet) {

            $increment_id = ($start == 0) ? 1 : ($start + 1);

            foreach ($resultSet as $row) {
                $singleListArray['increment_id'] = $increment_id;

                $singleListArray['cron_auto_id'] = $row->cron_auto_id;
                $singleListArray['cron_command'] = $row->cron_command;
                $singleListArray['cron_minute'] = $row->cron_minute;
                $singleListArray['cron_hour'] = $row->cron_hour;
                $singleListArray['cron_day'] = $row->cron_day;
                $singleListArray['cron_month'] = $row->cron_month;
                $singleListArray['cron_weekday'] = $row->cron_weekday;
                $singleListArray['cron_created_by'] = $row->user_name;

                $singleListArray['cron_created_at'] = date('d-m-Y', strtotime($row->cron_created_at));
                $singleListArray['cron_updated_at'] = date('d-m-Y', strtotime($row->cron_updated_at));

                $action = '<i class="ri-edit-box-line ri-xl editCronBtn cursor-pointer align-middle" title="Edit Cron" data-bs-toggle="modal" data-bs-target="#editCronModal" data-cron_auto_id="' . $row->cron_auto_id . '"></i>';
                $action .= '<i class="ri-delete-bin-line ms-2 ri-xl deleteCronBtn text-danger cursor-pointer" title="Delete Cron" data-cron_auto_id="' . $row->cron_auto_id . '"></i>';

                $singleListArray['action'] = $action;

                $aaData[] = $singleListArray;

                $increment_id++;
            }
        }

        $finalJsonArray['sEcho'] = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;
        $finalJsonArray['iTotalRecords'] = $total_records;
        $finalJsonArray['iTotalDisplayRecords'] = $total_records;
        $finalJsonArray['aaData'] = $aaData;
        $this->cors_header();
        echo json_encode($finalJsonArray);
    }

    public function get_cron_detail()
    {
        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');

        $user_info = $this->verify_user($user_id, $user_token);

        if ($user_info['user_role'] == 1) {

            $cron_auto_id = $this->required_parameter($postData, 'cron_auto_id');

            $checkOption = array(
                'from' => 'tbl_cron',
                'where' => "cron_auto_id = $cron_auto_id",
                'pagination' => array(
                    'limit' => 1
                )
            );

            $existRes = $this->mdl_common->select($checkOption);

            if (isset($existRes[0]['cron_auto_id'])) {
                $this->msg = "success";
                $this->result['info'] = $existRes[0];
                $this->_sendResponse(1);
            } else {

                $this->msg = "Cron not found";
                $this->_sendResponse(0);
            }
        } else {
            $this->msg = "Permission denied";
            $this->_sendResponse(0);
        }
    }

    // Search from header
    function search_from_header()
    {
        $postData = $this->input->post();
        $user_id = $this->required_parameter($postData, 'user_id');
        $user_token = $this->required_parameter($postData, 'user_token');
        $search = $this->required_parameter($postData, 'search');

        $user_info = $this->verify_user($user_id, $user_token);
        $where = "";

        // Query for Apps
        $option_for_apps = "SELECT t1.app_auto_id,t1.app_display_name,t1.app_store_id,t1.app_icon,t1.app_admob_app_id,
        t1.app_platform,t1.app_approval_state 
        FROM tbl_apps as t1 ";

        if ($user_info['user_role'] != 1) {
            $where = " AND t3.permission_user_id = " . $user_info['user_id'];

            $option_for_apps .= " JOIN tbl_ad_units as t2 ON t2.au_app_auto_id = t1.app_auto_id
                    JOIN  tbl_ad_unit_permissions as t3 ON t3.permission_au_auto_id = t2.au_auto_id ";
        }

        $option_for_apps .= " WHERE (t1.app_display_name LIKE '%" . $search . "%' 
        OR t1.app_store_id LIKE '%" . $search . "%' 
        OR t1.app_admob_app_id LIKE '%" . $search . "%' 
        )  $where LIMIT 2";

        $list_apps = $this->mdl_common->custom_query($option_for_apps)->result_array();


        // Query for Ad Units
        $where_for_ad_units = "";
        $option_for_ad_units = "SELECT t1.au_auto_id,t1.au_app_auto_id,t1.au_display_name,t1.au_id 
        FROM tbl_ad_units as t1 ";

        if ($user_info['user_role'] != 1) {
            $where_for_ad_units = " AND t3.permission_user_id = " . $user_info['user_id'];

            $option_for_ad_units .= " JOIN tbl_ad_unit_permissions as t3 ON t3.permission_au_auto_id = t1.au_auto_id ";
        }

        $option_for_ad_units .= " WHERE (t1.au_display_name LIKE '%" . $search . "%' 
            OR t1.au_id LIKE '%" . $search . "%' 
            )  $where_for_ad_units LIMIT 3";

        $list_ad_units = $this->mdl_common->custom_query($option_for_ad_units)->result_array();

        $this->result['apps'] = $list_apps;
        $this->result['ad_units'] = $list_ad_units;
        $this->msg = 'success.';
        $this->_sendResponse(1);
    }
}
