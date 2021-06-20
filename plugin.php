<?php
/*
Plugin Name: Roles & Permissions Demo
Plugin URI: https://github.com/luthfor-Rahma-Jubel
Description: Demonstration of Roles API
Version: 1.0
Author: Jubel Ahmed
Author URI: https://jubel-ahmed.xyz
License: GPLv2 or later
Text Domain: jbl_roles
Domain Path: /languages/
 */

 class user_roles_and_permissions_page{
    public function __construct(){
        add_action('activated_plugin', array($this, 'jbl_roles_redirect_activated_page') );
        add_action('plugin_action_links_'.plugin_basename(__FILE__), array($this,'jbl_roles_settings_link') );
        add_action('plugins_loaded', array($this, 'jbl_roles_bootstrap') );
        add_action('admin_enqueue_scripts', array($this, 'jbl_roles_script_loadeds') );
        add_action('admin_menu', array($this, 'jbl_roles_admin_menu') );
        add_action('wp_ajax_roles_display_result',array($this,'jbl_roles_ajax_action') );
        
    }

    public function jbl_roles_redirect_activated_page( $plugin ){
        if( plugin_basename(__FILE__) == $plugin ) {
            wp_redirect( admin_url( 'admin.php?page=roles_and_permissions' ) );
            die();
        }
    }

    public function jbl_roles_settings_link( $links ){
        $newlink = sprintf("<a href='%s'>%s</a>",'options-general.php?page=roles_and_permissions',__('Settings','jbl_roles') );
        $links[] = $newlink;
        return $links;
    }

    public function jbl_roles_bootstrap(){
        load_plugin_textdomain('jbl_roles', false, plugin_dir_path(__FILE__). "/languages" );
    }
    public function jbl_roles_script_loadeds( $hook ){
        if('toplevel_page_roles_and_permissions' == $hook ){
            wp_enqueue_style( 'pure-grid-css', '//unpkg.com/purecss@1.0.1/build/grids-min.css' );
            wp_enqueue_style('roles-demo-css', plugin_dir_url(__FILE__). "assets/css/style.css", null, time() );
            wp_enqueue_script('roles-demo-js', plugin_dir_url(__FILE__). "assets/js/main.js", array('jquery'), time(), true);
            $nonce = wp_create_nonce('roles_display_result');
            wp_localize_script( 
                'roles-demo-js',
                 'plugin_obj',
                  array('ajax_url'=> admin_url('admin-ajax.php'), 'nonce'=>$nonce)
            );
        }
    }

    public function jbl_roles_ajax_action(){
        global $roles;
        if(wp_verify_nonce($_POST['nonce'], 'roles_display_result')){
            $task = $_POST['task'];
            if($task == 'current-user-details'){
                if( is_user_logged_in()){
                    _e('Loged in user Details bellow <br> <br>','jbl_roles');
                }
                $user = wp_get_current_user();
                printf( __('User Name: %s', 'jbl_roles'), esc_html($user->user_login) .'<br>' );
                printf( __('User Email:%s','jbl_roles'), esc_html($user->user_email). '<br>' );
                printf( __('User Display Name: %s','jbl_roles'), esc_html($user->display_name). '<br>' );
                printf( __('User ID:%s','jbl_roles'), esc_html($user->ID). '<br>' );

                
            }elseif ('any-user-detail'==$task) {
               $user = new WP_User(1);
               echo $user->user_email."<br>";
               print_r($user);

            }elseif ('current-role'==$task) {
                $user = new WP_User(1);
                echo $user->roles[0]."<br>";
               
            }elseif ('all-roles'==$task) {
                global $wp_roles;
                $_roles = $wp_roles->roles;
                echo "All Roles: <br><br>";
                foreach ($_roles as $role => $details) {
                    echo "{$role}<br> ";
                }
                $roles = get_editable_roles();
                echo "<hr>";
                echo "Editable Roles: <br><br>";
                foreach ($roles as $role => $details) {
                    echo "{$role}<br>";
                }
            }elseif ('current-capabilities'==$task) {
                $current_user = wp_get_current_user();
                print_r($current_user->allcaps);
            }elseif ('check-user-cap'==$task) {
                $cap = "manage_sites";
                $cap = "manage_options";
                if(current_user_can($cap) ){
                    $current_user = wp_get_current_user();
                    echo " {$current_user->display_name} can do {$cap} <br><br>";
                }else {
                    echo "No He/She Can't do {$cap} <br>";
                }

                 $user = new WP_User(1);
                 $d_post = "delete_posts";
                 if($user->has_cap($d_post) ){
                    echo " {$user->user_nicename} Can do {$d_post}<br>";
                 }else {
                     'No {$user->user_nicename} can not do {$d_post} <br>';
                 }
            }elseif ('create-user'==$task) {
             echo wp_create_user('Ayan Minhaz ', 'ayan@jiuy87', 'ayan@minhaz.com');
            }elseif ('set-role'==$task) {
                $user = new WP_User(4);
                $user->remove_role('subscriber');
                $user->add_role('author');
                print_r($user);
            }elseif ('login'==$task) {

            /* $user = wp_authenticate('janedoe','abcd1234');
            if(is_wp_error($user)){
            echo "Failed";
            }else{
            wp_set_current_user($user->ID);
            echo wp_get_current_user()->user_email;
            wp_set_auth_cookie($user->ID);
            //echo "Success";
            } */

            /* $user = wp_signon( array(
            'user_login'    => 'janedoe',
            'user_password' => 'abcd1234',
            'remember'      => true,
            ) );
            if ( is_wp_error( $user ) ) {
            echo "Failed";
            } else {
            wp_set_current_user( $user->ID );
            echo wp_get_current_user()->user_email;
            //wp_set_auth_cookie( $user->ID );
            //echo "Success";
            } */
            wp_set_auth_cookie(1);

            }elseif ('users-by-role'==$task) {
                $user = get_users( array('role'=>'subscriber', 'orderby'=>'user_email', 'order'=>'asc') );
                print_r($user);
            }elseif ('change-role'==$task) {
                $user = new WP_User(4);
                $user->remove_role('author');
                $user->add_role('editor');
                print_r($user);
            }elseif ('create-role' == $task) {
                $role = add_role('super_author', __('Super Author','jbl_roles'), [
                    'read'=>true,
                    'delete_posts'=>true,
                    'edit_posts'=>true,
                    'custom_cap_one'=>true,
                    'custom_cap_two'=>false
                ] );
                print_r($role);
                $user = new WP_User(3);
                $user->add_role('super_author');
                if($user->has_cap('custom_cap_one') ){
                    echo "{$user->user_nicename} can do 'custom cap one' <br>";
                }

                if(!$user->has_cap('custom_cap_two') ){
                    echo "{$user->user_nicename} can not do 'custom cap two ' <br>";
                }
                print_r($user);
            }
        }
        die(0);

    }



   public function jbl_roles_admin_menu(){
       add_menu_page('roles demo', 'Roles Demo', 'manage_options', 'roles_and_permissions', array($this,'roles_admin_page') );
   }

   public function roles_admin_page(){
       ?>
      <div class="container" style="padding-top:20px;">
        <div class="heading">
          <h1><?php _e('Roles Demo', 'jbl_roles') ?></h1>
        </div>
            <div class="pure-g">
                <div class="pure-u-1-4" style='height:100vh;'>
                    <div class="plugin-side-options">
                        <button class="action-button" data-task='current-user-details'>Get Current User Details</button>
                        <button class="action-button" data-task='any-user-detail'>Get Any User Details</button>
                        <button class="action-button" data-task='current-role'>Detect Any User Role</button>
                        <button class="action-button" data-task='all-roles'>Get All Roles List</button>
                        <button class="action-button" data-task='current-capabilities'>Current User Capability</button>
                        <button class="action-button" data-task='check-user-cap'>Check User Capability</button>
                        <button class="action-button" data-task='create-user'>Create A New User</button>
                        <button class="action-button" data-task='set-role'>Assign Role To A New User</button>
                        <button class="action-button" data-task='login'>Login As A User</button>
                        <button class="action-button" data-task='users-by-role'>Find All Users From Role</button>
                        <button class="action-button" data-task='change-role'>Change User Role</button>
                        <button class="action-button" data-task='create-role'>Create New Role</button>
                    </div>
                </div>
                <div class="pure-u-3-4">
                    <div class="plugin-demo-content">
                        <h3 class="plugin-result-title"><?php _e('Result', 'jbl_roles')?></h3>
                        <div id="plugin-demo-result" class="plugin-result st-output"></div>
                    </div>
                </div>
            </div>
        </div>

       <?php
   }

 }

 new user_roles_and_permissions_page();


