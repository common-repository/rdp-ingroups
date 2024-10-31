<?php if ( ! defined('WP_CONTENT_DIR')) exit('No direct script access allowed'); ?>
<?php

/**
 * Description of RDP_LIG_DATAPASS
 *
 * @author Robert
 */
class RDP_LIG_DATAPASS {
    private $_access_token = null;
    private $_expires_at = 0;
    private $_expires_in = 0; 
    private $_firstName = '';
    private $_lastName = '';
    private $_emailAddress = '';
    private $_pictureUrl = '';
    private $_personID = '';
    private $_publicProfileUrl = '';
    private $_data_filled = false;
    private $_data_saved = false;
    private $_key = '';
    private $_ipAddress = 0;
    private $_wp_post_id = 0;
    private $_rdpligrt = '';
    private $_submenu_code = '';
    
    private function __construct($key = '',$props = null) {
       if(empty($key))return ;
       $this->_key = $key;
       if(!$props)return ;
       $oProps = get_object_vars($this);
       foreach ($oProps as $key => $value ) {
           $newvalue = (isset($props[$key])) ? $props[$key] : null;
           if ($newvalue === null) continue;
           if ($newvalue === "true") $newvalue = true;
           if ($newvalue === "false") $newvalue = false;
           $this->$key = $newvalue;
       }
       
       $this->_data_filled = true;
    }//__construct    
    
    
    public static function get($key) {
        $options = get_transient($key);
        return new self($key,$options);
    }   
    
    public static function get_new($key) {
        return new self($key);
    }
    
    public static function delete($key) {
        delete_transient( $key );
    }
    
    public function tokenExpired(){
        $expires = (!empty($this->_expires_at))? $this->_expires_at : time()-5 ;
        $tokenExpired = (time() < $expires)? false : true;
        return $tokenExpired;
    }
    
    public function save() {
        $this->_data_saved = false;
        if($this->tokenExpired()) return false;
        $this->_data_saved = set_transient($this->_key, get_object_vars($this),$this->_expires_in);
    }     
    
    public function key(){
        $token = (isset($this->_key))? $this->_key : '';
        return $token;
    }
    
    public function sessionNonce_get(){
        $token = (isset($this->_rdpligrt))? $this->_rdpligrt : '';
        return $token;
    }
    
    public function sessionNonce_set($value){
        $this->_rdpligrt = $value;
    }
    
    public function data_filled(){
        $filled = (isset($this->_data_filled))? $this->_data_filled : false;
        return $filled;        
    }
    
    public function data_saved(){
        $saved = (isset($this->_data_saved))? $this->_data_saved : false;
        return $saved;        
    }  
    
    public function access_token_get(){
        $token = (isset($this->_access_token))? $this->_access_token : '';
        return $token;
    }
    
    public function access_token_set($value){
        $this->_access_token = $value;
    }

    public function expires_at_get(){
        $expires = (isset($this->_expires_at))? $this->_expires_at : time()-5 ;
        return $expires;
    }
    
    public function expires_at_set($value){
        $this->_expires_at = $value;
    }  
    
    public function expires_in_get(){
        $expires = (isset($this->_expires_in))? $this->_expires_in : 0 ;
        return $expires;
    }
    
    public function expires_in_set($value){
        $this->_expires_in = $value;
    }     
    
    public function firstName_get(){
        $name = (isset($this->_firstName))? $this->_firstName : '';
        return $name;
    }
    
    public function fullName_get(){
        $fName = (isset($this->_firstName))? $this->_firstName : '';
        $lName = (isset($this->_lastName))? $this->_lastName : '';
        return trim($fName . ' ' . $lName);
    }

    public function firstName_set($value){
        $this->_firstName = $value;
    }    
    
    public function lastName_get(){
        $name = (isset($this->_lastName))? $this->_lastName : '';
        return $name;
    }
    
    public function lastName_set($value){
        $this->_lastName = $value;
    }    
    
    public function emailAddress_get(){
        $email = (isset($this->_emailAddress))? $this->_emailAddress : '';
        return $email;
    }
    
    public function emailAddress_set($value){
        $this->_emailAddress = $value;
    }    
    
    public function pictureUrl_get(){
        $url = (isset($this->_pictureUrl))? $this->_pictureUrl : '';
        return $url;
    }
    
    public function pictureUrl_set($value){
        $this->_pictureUrl = $value;
    }   
    
    public function publicProfileUrl_get(){
        $url = (isset($this->_publicProfileUrl))? $this->_publicProfileUrl : '';
        return $url;        
    }

    public function publicProfileUrl_set($value){
        $this->_publicProfileUrl = $value;
    }

    public function personID_get(){
        $id = (isset($this->_personID))? $this->_personID : '';
        return $id;
    }
    
    public function personID_set($value){
        $this->_personID = $value;
    }
    
    public function ipAddress_get(){
        $id = (isset($this->_ipAddress))? $this->_ipAddress : '';
        return $id;
    }
    
    public function ipAddress_set($value){
        $this->_ipAddress = $value;
    }
    
    public function wpPostID_set($value){
        if(!is_numeric($value)) $value = 0;
        $this->_wp_post_id = $value;
    }
    
    public function wpPostID_get(){
        $id = (isset($this->_wp_post_id))? $this->_wp_post_id : 0;
        return $id;
    }
    
    public function submenuCode_set($value){
        $this->_submenu_code = $value;
    }
    
    public function submenuCode_get(){
        $html = (isset($this->_submenu_code))? $this->_submenu_code : '';
        return $html;
    }
    
}//RDP_LIG_DATAPASS

/* EOF */
