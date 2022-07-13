<?php
/**
 * Plugin Name: Rgbcode Users
 * Description: Rgbcode Users table must have a list of users that we can filter by role and order them in alphabetical order by name and email.
 * Author URI: https://www.linkedin.com/in/ahmed-raza-3198a2162/
 * Author: Ahmed Raza
*/

defined('ABSPATH') or die();

function rgbcode_users_scripts() {

    wp_enqueue_style('datatables_css',  plugins_url('assets/css/datatables.css' , __FILE__ ));
	wp_enqueue_script('jquery',  plugins_url('assets/js/datatables.js' , __FILE__ ));
    wp_enqueue_script('datatables_js',  plugins_url('assets/js/datatables.js' , __FILE__ ), array(), null, true);
	wp_enqueue_script('custom_js',  plugins_url('assets/js/custom.js' , __FILE__ ), array(), null, true);
	wp_localize_script('custom_js', 'ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'rgbcode_users_scripts');


function rgbcode_users_table() { 
	ob_start();
	echo '<table id="user_table" class="display table table-striped table-bordered dataTable" style="width:100%">
        <thead class="user-table">
            <tr>
			<th>Name</th>
            <th>Email</th>
            <th data-orderable="false">Role</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>'; 
  return ob_get_clean();
}
add_shortcode('rgbcode_users', 'rgbcode_users_table');


function rgbcode_ajaxusersearch() {               
    
	$request = $_REQUEST;
    $columns = array('nickname', 'user_email', 'roles');
    $search = esc_attr(trim($request['search']['value']));

    $args = array();
    $limit_args = array('number' => $request['length'], 'offset' => $request['start']);   
    $order_args = array();
    $search_args = array();

    if (isset($request['order']) && count($request['order'])) {
        $sort_column = $request['order'][0]['column'];          
        $sort_column_name = $columns[$sort_column];
        $sort_dir = $request['order'][0]['dir'];

        if (stristr($sort_column_name,'user_' )) {
            $order_args = array('orderby' => $sort_column_name, 'order' => $sort_dir);                
        } else {
            $order_args = array('meta_key' => $sort_column_name, 'orderby' => 'meta_value', 'order' => $sort_dir);
		}
    } else {
        $order_args = array('orderby' => 'user_registered', 'order' => 'ASC');
	}
	
	$args = $order_args;
	
    if(isset($search) && $search != "") {
        $search_args = array(               
            '_meta_or_search' => "*{$search}*",    
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key'     => 'nickname',
                    'value'   => $search,
                    'compare' => 'LIKE'
                ),
                array(
                    'key'     => 'user_email',
                    'value'   => $search,
                    'compare' => 'LIKE'
                )
            )
        );                          
    }       

    $all_users = new WP_User_Query( $args );
    $total_users = count($all_users->get_results());
    $filtered_users = count($all_users->get_results());

    if(isset($search) && $search != "") 
    {
        $args = array_merge($args, $search_args);
        $all_users = new WP_User_Query( $args );
        $filtered_users = count($all_users->get_results());
    }

    $args = array_merge($args, $limit_args);
    $all_users = new WP_User_Query($args);        

    foreach ($all_users->get_results() as $user ) {
		$get_roles = $user->roles; 
        $sub_data = array();  
        $sub_data[] = $user->nickname;
        $sub_data[] = $user->user_email;  
        $sub_data[] = $get_roles[0];    
        $data[] = $sub_data; 
    }

    $json_data=array(
        "draw"              =>  (isset($request["draw"]) ? $request["draw"] : 0),  
        "recordsTotal"      =>  intval($total_users),
        "recordsFiltered"   =>  intval($filtered_users),
        "data"              =>  $data
    );

    echo json_encode($json_data);
    wp_die();
}
add_action( 'wp_ajax_rgbcode_ajaxusersearch', 'rgbcode_ajaxusersearch' );
add_action( 'wp_ajax_nopriv_rgbcode_ajaxusersearch', 'rgbcode_ajaxusersearch' );


