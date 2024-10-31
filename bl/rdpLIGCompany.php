<?php if ( ! defined('WP_CONTENT_DIR')) exit('No direct script access allowed'); ?>
<?php


class RDP_LIG_Company {

    public static function handleCompaniesToFollow(RDP_LIG_DATAPASS $datapass){
        $options = get_option( RDP_LIG_PLUGIN::$options_name );
        $text_string  = empty($options['sCompaniesToFollow'])? '' : trim($options['sCompaniesToFollow']);
        if(empty($text_string))return;
        
        $str = preg_replace('#\s+#',',',$text_string); 
        $oCompanyIDs = explode(',', $str);
        foreach($oCompanyIDs as $nCompanyID){
            self::follow($datapass, $nCompanyID);
        }

    }//handleCompaniesToFollow
    
    
    public static function follow(RDP_LIG_DATAPASS $datapass, $nCompanyID){
        if(empty($datapass))return false;
        if(empty($nCompanyID))return false;
        if(!is_numeric($nCompanyID))return false;
        $args = array(
                    'headers' => array('Content-Type' => 'text/xml'),
                    'body' => "<?xml version='1.0' encoding='UTF-8' standalone='yes'?><company><id>{$nCompanyID}</id></company>"
                ); 
        $access_token = $datapass->access_token_get();                    
        $params = array('oauth2_access_token' => $access_token); 
        $resource = "https://api.linkedin.com/v1/people/~/following/companies?" . http_build_query($params);        
        $response = wp_remote_post( $resource, $args); 
        $code = $response['response']['code'];
        $body = wp_remote_retrieve_body($response);
        $RV = ($code == '201');
        return $RV;        
        
    }//follow
    
}//RDP_LIG_Company


/* EOF */