<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'api';
$route['404_override'] = 'api/web-login';
$route['translate_uri_dashes'] = FALSE;

// [Web Panel Routes]
$route['api/web-login'] = 'api/web_login';
$route['api/web-logout'] = 'api/web_logout';


$route['api/add-user'] = 'api/add_user';
$route['api/update-user'] = 'api/update_user';
$route['api/user-list'] = 'api/user_list';
$route['api/get-user-detail'] = 'api/get_user_detail';


$route['api/add-user-permission'] = 'api/add_user_permission';
$route['api/permission-apps-list'] = 'api/permission_apps_list';
$route['api/remove-ad-unit-permission'] = 'api/remove_ad_unit_permission';

$route['api/get-all-users'] = 'api/get_all_users';

$route['api/admob-account-list'] = 'api/admob_account_list';
$route['api/add-admob-account'] = 'api/add_admob_account';
$route['api/get-admob-account-detail'] = 'api/get_admob_account_detail';
$route['api/update-admob-account'] = 'api/update_admob_account';

$route['api/apps-list'] = 'api/apps_list';
$route['api/app-settings'] = 'api/app_settings';
$route['api/list-app-ad-units'] = 'api/list_app_ad_units';
$route['api/app-overview'] = 'api/app_overview';
$route['api/get-app-info-performances'] = 'api/get_app_info_performances';
$route['api/app-info'] = 'api/app_info';
$route['api/app-overview-ads-performance-list'] = 'api/app_overview_ads_performance_list';
$route['api/list-all-apps'] = 'api/list_all_apps';
$route['api/list-ad-units'] = 'api/list_ad_units';

// $route['api/report-list'] = 'api/report_list';
// $route['api/app-list-for-report'] = 'api/app_list_for_report';
// $route['api/app-ad-list-for-report'] = 'api/app_ad_list_for_report';
// $route['api/add-new-report'] = 'api/add_new_report';
// $route['api/get-report-detail'] = 'api/get_report_detail';
// $route['api/update-report'] = 'api/update_report';
// $route['api/delete-report'] = 'api/delete_report';

$route['api/analytics-list'] = 'api/analytics_report';
$route['api/get-analytics-filtering-data'] = 'api/get_analytics_filtering_data';

$route['api/get-dashboard-eastimated-earnings'] = 'api/get_dashboard_eastimated_earnings';
$route['api/get-dashboard-performances'] = 'api/get_dashboard_performances';
$route['api/dashboard-app-performance-list'] = 'api/dashboard_app_performance_list';

$route['api/add-cron'] = 'api/add_cron';
$route['api/update-cron'] = 'api/update_cron';
$route['api/delete-cron'] = 'api/delete_cron';
$route['api/cron-list'] = 'api/cron_list';
$route['api/get-cron-detail'] = 'api/get_cron_detail';

$route['api/search-from-header'] = 'api/search_from_header';

// [/Web Panel Routes]