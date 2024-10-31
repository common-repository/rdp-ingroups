<?php if ( ! defined('WP_CONTENT_DIR')) exit('No direct script access allowed'); ?>
<?php

class RDP_LIG_GROUP_PROFILE {
    private $_id = 0;
    private $_status_code = 200;
    private $_data_filled = false;
    private $_data_saved = false;    
    private $_has_errors = false;
    private $_last_error = '';
    private $_numMembers = 0;
    private $_memberCountToString = '';
    private $_name = '';
    private $_largeLogoUrl = '';
    private $_isOpenToNonMembers = false;
    private $_availableAcctions = array();
    public static $_key_prefix = 'rdp-lig-group-profile-';
    
    private function __construct($id = 0,$props = null){
        if(!is_numeric($id)){
            $this->_status_code = 406;
            $this->_last_error = 'Not Acceptable - Invalid group id given.';
            $this->_has_errors = true;
            return;
        }
        $this->_id = $id;
        
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
    
    public static function get($id) {
        $key = self::$_key_prefix . $id;
        $options = get_transient($key);
        return new self($id,$options);
    }    
    
    public static function getNew($id) {
        return new self($id);
    } 
    
    public function save() {
        $this->_data_saved = false;
        if($this->_has_errors) return false;
        if(!$this->_data_filled) return false;
        $key = self::$_key_prefix . $this->_id;
        $this->_data_saved = set_transient($key, get_object_vars($this), HOUR_IN_SECONDS);
    }      
    
    public function load() {
        if(!is_numeric($this->_id)) return;
        $resource = "https://www.linkedin.com/groups?newItemsAbbr=&gid={$this->_id}";
        $html = rdp_file_get_html($resource);

        if(!$html){
            $this->_status_code = 503;
            $this->_last_error = 'Service Unavailable - Unable to retrieve group data';
            $this->_has_errors = true;
            return;
        }

        $this->parse($html);
        if(!$this->_data_filled) $this->_data_filled = true;
        
    }//load
    
    public function fill($json){
        $this->_data_filled = false;
        if(!isset($json->group))return;
        $this->_numMembers = $json->group->numMembersTotal;
        $this->_name = $json->group->name;
        $this->_largeLogoUrl = 'https://media.licdn.com/mpr/mpr' . $json->group->largeLogoId;
        if(isset($json->group->settings)){
            if(isset($json->group->settings->generalSettings) && property_exists($json->group->settings->generalSettings, 'visibilityInDirectory')){
                $this->_isOpenToNonMembers = ($json->group->settings->generalSettings->visibilityInDirectory == "PUBLIC");
            }
        }
        if(isset($json->availableActions)){
            foreach($json->availableActions as $action){
                $this->_availableAcctions[] = $action->action;
            }
        }
        
        $this->_data_filled = true;
    }//fill
    
    
    private function parse($html){
        $oMemberCount = $html->find('div.header .right-entity .member-count',0);
        if(!$oMemberCount){
            $this->_last_error = 'Unable to retrieve member count';
            $this->_has_errors = true;
       }else{
            $this->_memberCountToString = $oMemberCount->plaintext;
            $this->_numMembers = filter_var($oMemberCount->plaintext, FILTER_SANITIZE_NUMBER_INT);            
       }

       $oGroupName = $html->find('h1.group-name span',0);
        if(!$oGroupName){
           $this->_last_error = 'Unable to retrieve group name';
           $this->_has_errors = true;
       } else $this->_name = $oGroupName->plaintext;
       
       $oGroupPrivate = $html->find('div.left-entity h1.private',0);
       $this->_isOpenToNonMembers = empty($oGroupPrivate);
       
       $oGroupLogoURL = $html->find('div.header a.image-wrapper img',0);
        if(!$oGroupLogoURL){
           $this->_last_error = 'Unable to retrieve group logo';
           $this->_has_errors = true;
        } else $this->_largeLogoUrl = $oGroupLogoURL->src;       
    }//parse
    
    public function renderHeader($post_id){
        if(empty($this->_memberCountToString)){
            $this->load();
            $this->save();
        }
        $params['rdpingroupsid'] = $this->_id;
        $postURL = get_permalink($post_id);
        $url = add_query_arg($params,$postURL);        
        $sHTML = '<a href="' . $url . '" class="image-wrapper rdp-lig-group" class="disabled" disabled="disabled" id="' . $this->_id . '"><img src="' . $this->_largeLogoUrl . '" width="100" height="50" alt="" title="" class="image"/></a>';
        $sHTML .= '<div class="left-entity">';
        $sHTML .= '<div class="content-wrapper"><h1 class="group-name public">' . $this->_name . '</h1></div><!-- .content-wrapper -->';
        $sHTML .= '</div><!-- .left-entity --><div style="clear: both;"></div>';
        $sHTML .= '<div class="right-entity"><div class="content-wrapper">';
        $sHTML .= '<a class="groups-terms-of-use-link" href="https://www.linkedin.com/legal/user-agreement" target="_new"><img align="left" src="' . plugins_url(dirname(RDP_LIG_PLUGIN_BASENAME) . '/pl/images/groups-terms-of-use.png' ) . '" /></a>';
        $sHTML .= '<a href="http://www.linkedin.com/groups?groupDashboard=&gid=' . $this->_id . '" target="_new" title="Statistics about ' . $this->_name . '" ><span class="member-count">' . $this->_memberCountToString . '</span></a>';
        
        if($this->_isOpenToNonMembers){
            $sHTML .= '<div class="top-bar-actions">';
            $params = RDP_LIG_Utilities::clearQueryParams();
            $url = add_query_arg($params,$url);
            $options = get_option( 'rdp_lig_options' );
            $fLIGShowManagerChoice = empty($options['fLIGShowManagerChoice'])? 'off' : $options['fLIGShowManagerChoice'];            
            
            $rssParams = array('id' => $this->_id,'link' => $url,'mgr' => $fLIGShowManagerChoice);
            
            $sRSSURL = plugins_url(dirname(RDP_LIG_PLUGIN_BASENAME) . '/ws/rss.php?'. http_build_query($rssParams));            
             
            $sHTML .= '<a href="' . $sRSSURL . '" target="_new" title="RSS feed for ' . $this->_name . '" class="view-group-rss"><span>&nbsp;</span></a></div>';
        }
        
        $sHTML .= '</div><!-- .content-wrapper --></div><!-- .right-entity -->';
        
        return $sHTML;
    }    
    
    public function dataFilled(){
        $filled = (isset($this->_data_filled))? $this->_data_filled : false;
        return $filled;        
    }
    
    public function hasErrors(){
        $errors = (isset($this->_has_errors))? $this->_has_errors : false;
        return $errors;        
    } 
    
    public function statusCode(){
        $code = (isset($this->_status_code))? $this->_status_code : 0 ;
        return $code;        
    } 
    
    public function lastError(){
        $text = (isset($this->_last_error))? $this->_last_error : '';
        return $text;
    }      
    
    public function id(){
        $id = (isset($this->_id))? $this->_id : 0 ;
        return $id;        
    }
    
    public function numMembers(){
        $num = (isset($this->_numMembers))? $this->_numMembers : 0;
        return $num;
    }   
    
    public function numMembersToString(){
        $text = (isset($this->_memberCountToString))? $this->_memberCountToString : '';
        return $text;
    }   
    
    public function largeLogoUrl() {
        $url = (isset($this->_largeLogoUrl))? $this->_largeLogoUrl : '';
        return $url;        
    }

    public function name(){
        $text = (isset($this->_name))? $this->_name : '';
        return $text;
    }    

    public function isOpenToNonMembers(){
        $f = (isset($this->_isOpenToNonMembers))? $this->_isOpenToNonMembers : false;
        return $f;        
    }
    public function availableActions() {
        return $this->_availableAcctions;
    }
}//rdpLIGGroupProfile

/*  EOF */
