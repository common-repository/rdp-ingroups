<?php

/*
Plugin Name: RDP inGroups+
Plugin URI: http://robert-d-payne.com/
Description: Integrate LinkedIn groups into WordPress
Version: 1.0.6
Author: Robert D Payne
Author URI: http://robert-d-payne.com/
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if ( ! defined( 'WPINC' ) ) {
	die;
}


// Turn off all error reporting
//error_reporting(E_ALL^ E_WARNING);
$dir = plugin_dir_path( __FILE__ );
define('RDP_LIG_PLUGIN_BASEDIR', $dir);
define('RDP_LIG_PLUGIN_BASEURL',plugins_url( null, __FILE__ ) );
define('RDP_LIG_PLUGIN_BASENAME', plugin_basename(__FILE__));

global $sLIGAction;
$sLIGAction = isset($_GET['rdpingroupsaction'])?$_GET['rdpingroupsaction']:'';
include_once 'bl/rdpLIGUtilities.php' ;
ob_start();

class RDP_LIG_PLUGIN{
    public static $plugin_slug = 'rdp-ligroups'; 
    public static $options_name = 'rdp_lig_options';    
    public static $version = '1.0.6';    
    private $_options = array();
    
    public function __construct() {
        $options = get_option( RDP_LIG_PLUGIN::$options_name );
        if(is_array($options))$this->_options = $options;        
        $this->load_dependencies();
    }//__construct
    
    private function load_dependencies() {
        if (is_admin()){
            include_once 'pl/rdpLIGAdminMenu.php' ;
            include_once 'pl/rdpLIGShortcodePopup.php' ;    
        } 
        
        include_once 'bl/rdpLIGDatapass.php';
        include_once 'bl/rdpLIGBrowser.php' ;
        require_once 'bl/simple_html_dom.php';
        include_once 'bl/rdpLIGCompany.php' ;            
        include_once 'pl/rdpLIG.php' ; 
        include_once 'bl/rdpLIGGroupProfile.php' ;
        include_once 'bl/rdpLIGGroups.php' ;
        include_once 'bl/rdpLIGGroup.php' ;
        include_once 'bl/rdpLIGReferencedItem.php' ;
        include_once 'bl/rdpLIGDiscussion.php' ;             
    }//load_dependencies
    
    
    private function define_front_hooks(){
        if(defined( 'DOING_AJAX' ))return;
        $oLIG = new RDP_LIG(self::$version,$this->_options);
        add_action( 'wp_enqueue_scripts', array($oLIG, 'scriptsEnqueue') ); 
        $oLIG->run();        
    }//define_front_hooks
    
    private function define_admin_hooks() {
        if(!is_admin())return;
        if(defined( 'DOING_AJAX' ))return;
        add_action( 'admin_footer', 'RDP_LIG_Shortcode_Popup::renderPopupForm' );
        add_action( 'media_buttons', 'RDP_LIG_Shortcode_Popup::addMediaButton' );          
        add_action('admin_menu', 'RDP_LIG_AdminMenu::add_menu_item');
        add_action('admin_init', 'RDP_LIG_AdminMenu::admin_page_init');        
    }//define_admin_hooks
    
    private function define_ajax_hooks(){
        if(!defined( 'DOING_AJAX' ))return;
        add_action('wp_ajax_nopriv_rdp_lig_short_url_fetch','RDP_LIG_Utilities::bitlyShorten_ajax');
        add_action('wp_ajax_rdp_lig_short_url_fetch','RDP_LIG_Utilities::bitlyShorten_ajax');     

        add_action('wp_ajax_nopriv_rdp_lig_group_discussions_fetch','RDP_LIG_Group::fetchDiscussionItemList');
        add_action('wp_ajax_rdp_lig_group_discussions_fetch','RDP_LIG_Group::fetchDiscussionItemList');

        add_action('wp_ajax_nopriv_rdp_lig_group_profile_fetch','RDP_LIG_Groups::fetchGroupProfile');
        add_action('wp_ajax_rdp_lig_group_profile_fetch','RDP_LIG_Groups::fetchGroupProfile');

        add_action('wp_ajax_nopriv_rdp_lig_fetch_referenced_item','RDP_LIG_ReferencedItem::fetch');
        add_action('wp_ajax_rdp_lig_fetch_referenced_item','RDP_LIG_ReferencedItem::fetch');

        add_action('wp_ajax_nopriv_rdp_lig_discussion_content_fetch','RDP_LIG_Discussion::fetchContent');
        add_action('wp_ajax_rdp_lig_discussion_content_fetch','RDP_LIG_Discussion::fetchContent');

        add_action('wp_ajax_nopriv_rdp_lig_discussion_comments_fetch','RDP_LIG_Discussion::fetchComments');
        add_action('wp_ajax_rdp_lig_discussion_comments_fetch','RDP_LIG_Discussion::fetchComments');
         
    }//define_ajax_hooks
    
    public function run() {
        $this->define_front_hooks();
        $this->define_admin_hooks();
        $this->define_ajax_hooks();         
        if(defined( 'DOING_AJAX' ))return;
        $fLIGRegisterNewUser = empty($this->_options['fLIGRegisterNewUser'])? '' : $this->_options['fLIGRegisterNewUser'];
        if($fLIGRegisterNewUser == 'on'  && RDP_LIG_Utilities::$sessionIsValid )add_action( 'wp_before_admin_bar_render', 'RDP_LIG_Utilities::tweakAdminBar' );
    }//run  
    
}//RDP_LIG_PLUGIN


function rdp_lig_run() {
    // prevent running code unnecessarily
    $wp_action = RDP_LIG_Utilities::globalRequest('action');
    if($wp_action == 'heartbeat')return;
    $isScriptStyleImg = RDP_LIG_Utilities::isScriptStyleImgRequest();
    if($isScriptStyleImg)return;     
    
    $oRDP_LIG_PLUGIN = new RDP_LIG_PLUGIN(); 
    if(!wp_script_is('jquery-cookie')){
        wp_register_script('jquery-cookie', plugins_url( 'pl/js/jquery.cookie.js' , __FILE__ ), array( 'jquery' ), '1.4.1', TRUE);
        wp_enqueue_script('jquery-cookie');
        
    }
    $slug = '/rdpingroupsaction/authorize';
    $uri = $_SERVER['REQUEST_URI'];
    $pos = strpos($uri, $slug);    
    global $sLIGAction;
    if(strtolower($sLIGAction) == 'login' || $pos !== false){
        include_once 'pl/rdpLIGLogin.php' ;
    } else {
        $oRDP_LIG_PLUGIN->run();        
    }
}//rdp_lig_run
add_action('wp_loaded','rdp_lig_run');


/* EOF */
