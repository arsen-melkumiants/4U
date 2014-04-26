<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

#Admin routes
$admin_folder = 'admin/';
$admin_url    = '4U/';

$route['4U'] = $admin_folder.'admin_control_panel';
$route[$admin_url.'global_settings']           = $admin_folder.'admin_control_panel/global_settings';
$route[$admin_url.'change_access']             = $admin_folder.'auth/edit_user/1';
$route[$admin_url.'logout']                    = $admin_folder.'auth/logout';
$route[$admin_url.'manage_menu/(:num)/(:any)'] = $admin_folder.'manage_menu/$2/$1';
$route[$admin_url.'manage_menu/(:any)/add']    = $admin_folder.'manage_menu/add/$1';
$route[$admin_url.'manage_menu/(:any)']        = $admin_folder.'manage_menu/menu/$1';

#General admin routes
$route[$admin_url.'manage_(:any)'] = $admin_folder.'manage_$1';
$route[$admin_url.'(:any)']        = $admin_folder.'$1';

#User routes
$route['category/(:any)'] = 'shop_controller/category/$1';
$route['product/(:any)']  = 'shop_controller/product/$1';
$route['search']          = 'shop_controller/search';
$route['cart']            = 'shop_controller/cart';
$route['cart/(:any)']     = 'shop_controller/cart/$1';
$route['add_to_cart']     = 'shop_controller/add_to_cart';
$route['update_cart']     = 'shop_controller/update_cart';

$route['profile']        = 'profile';
$route['profile/(:any)'] = 'profile/$1';

$route['personal/(:any)'] = 'personal/$1';

$route['media_files/(:any)'] = 'profile/get_media_file/$1';

$route['(:any)'] = 'main_controller/menu_content/$1';


$route['default_controller'] = 'main_controller';

$route['404_override'] = 'not_found';


/* End of file routes.php */
/* Location: ./application/config/routes.php */
