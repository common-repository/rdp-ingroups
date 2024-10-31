<?php

class RDP_LIG_Login{
    private $_redirectURI = '';
    const _scope = 'r_basicprofile r_emailaddress';
    private $_API_KEY = '';
    private $_API_SECRET = '';
    private $_datapass = null;
    
    function __construct() {
        
        // OAuth 2 Control Flow
        if (isset($_GET['error'])) {
            // LinkedIn returned an error
            print $_GET['error'] . ': ' . $_GET['error_description'];
            exit;
        }         
        
        $this->_redirectURI = esc_url( home_url( '/rdpingroupsaction/authorize' ) );
        
        $options = false;
        if(false === ($options = get_option( RDP_LIG_PLUGIN::$options_name ))){
            $this->handleMissingSettingsMessage();
        }

        $this->_API_KEY = $options['sLGIAPIKey'];
        $this->_API_SECRET = $options['sLGIAPISecretKey'];
        
        if(!isset($_GET['code']))$this->handleAuthorizationCode();

        $authPass = $this->handleAuthToken();

        if($authPass === false)$this->renderLoginFailMessage();

        // Congratulations! You have a valid token. Now fetch profile
        $response = RDP_LIG_Utilities::fetch('GET', '/v1/people/~:(firstName,lastName,email-address,picture-url,id,public-profile-url,location,headline,summary)',$authPass['access_token']);
        $data = wp_remote_retrieve_body($response);
        $user = json_decode($data);
        
        if(!is_object($user) || !property_exists($user, 'emailAddress') || empty($user->emailAddress)){
            $this->renderLoginFailMessage();
        } 

        do_action('rdp_lig_before_user_login', $user); 
        
        /* Create and load up a new datapass object */
        $key = md5($user->emailAddress);
        $this->_datapass = RDP_LIG_DATAPASS::get_new($key);
        $this->_datapass->firstName_set($user->firstName);
        $this->_datapass->lastName_set($user->lastName);
        $this->_datapass->emailAddress_set($user->emailAddress);
        $this->_datapass->pictureUrl_set($user->pictureUrl);
        $this->_datapass->publicProfileUrl_set($user->publicProfileUrl);
        $this->_datapass->personID_set($user->id);
        $this->_datapass->expires_in_set($authPass['expires_in']);
        $this->_datapass->expires_at_set($authPass['expires_at']);
        $this->_datapass->access_token_set($authPass['access_token']);
        $this->_datapass->ipAddress_set(RDP_LIG_Utilities::getClientIP());
        $this->_datapass->sessionNonce_set('new');
        $this->_datapass->save();

        $nGroupID = (isset($authPass['rdpingroupsid']))? $authPass['rdpingroupsid'] : 0;
        if(!is_numeric($nGroupID))$nGroupID = 0;
        
        $sPostID = (isset($authPass['rdpingroupspostid']))? $authPass['rdpingroupspostid'] : 0;
        
        $fLIGRegisterNewUser = isset($options['fLIGRegisterNewUser'])? $options['fLIGRegisterNewUser'] : 'off';
        if($fLIGRegisterNewUser == 'on'){
            add_action('rdp_lig_after_insert_user', array( &$this, 'afterUserInsert' ) );
            RDP_LIG_Utilities::handleUserRegistration ($user);
            RDP_LIG_Utilities::handleRegisteredUserSignOn($user);
        }

        do_action('rdp_lig_after_user_login', $this->_datapass);

        $this->renderCloseScript($nGroupID,$sPostID);
    }//__construct
    
    public function afterUserInsert($user){
        RDP_LIG_Company::handleCompaniesToFollow($this->_datapass);
    }//afterUserInsert
    
    private function renderCloseScript($nGroupID,$sPostID){
        $JS = <<<EOS
<script type='text/javascript'>
    function rdp_lig_login_onReady(){
        var redirectPath = jQuery.cookie('rdp_lig_login_redirect');
        if(!redirectPath){
            window.opener.location.reload();
        }else{
            jQuery.removeCookie('rdp_lig_login_redirect', { path: '/' });
            if(typeof(window.opener.rdp_lig_login_onClose) === typeof(Function)){
                window.opener.rdp_lig_login_onClose(redirectPath);    
            }else{
               window.opener.location.href = redirectPath;     
            } 
        }

        window.close();
    }
    jQuery(document).ready(rdp_lig_login_onReady);
</script>  

EOS;
        
        $pre_load_scripts = array('jquery','jquery-query','jquery-cookie');

        echo '<html><head>';
        foreach ( $pre_load_scripts as $script ) {
                wp_print_scripts( $script );
        }        
        echo $JS;
        echo '</head><body></body></html>';
        exit;        
    }//renderCloseScript
    
    private function handleMissingSettingsMessage(){
        $sMsg = <<<EOD
<p>RDP inGroups+ settings not found.<br />
Visit 'Settings > RDP inGroups+' and:<br />
1. Get a LinkedIn Application API key using the link and settings shown in the white box.<br />
2. Enter API Key.<br />
3. Enter Secret Key.<br />
4. Set other configurations as desired.<br />
5. Click 'Save Changes' button.</p>
EOD;
        
        print $sMsg;
        exit;        
    }//handleMissingSettingsMessage
    
    private function renderLoginFailMessage(){
        print 'Unable to complete login process.<br />Please try again.';
        exit;        
    }//handleLoginFailMessage
    
    private function handleAuthToken(){
        // User authorized your application
        if(!isset($_GET['code']))return false;
        $state = (isset($_GET['state']))?$_GET['state']:'';

        $loginPass = get_transient( $state );
        $authPass = false;
        if (false !== $loginPass ) {
            $authPass = $this->getAccessToken($this->_redirectURI,$_GET['code']);
            if(false !== $authPass){
                $authPass['rdpingroupsid'] = (isset($loginPass['rdpingroupsid']))? $loginPass['rdpingroupsid'] : 0;
                $authPass['rdpingroupspostid'] = (isset($loginPass['rdpingroupspostid']))? $loginPass['rdpingroupspostid'] : 0;            
            }
        }

        return $authPass;
    }//handleAuthToken
    
    private function getAccessToken($redirectURI,$code)
    {   
        $params = array('grant_type' => 'authorization_code',
                        'client_id' => $this->_API_KEY,
                        'client_secret' => $this->_API_SECRET,
                        'code' => $code,
                        'redirect_uri' => $redirectURI,
                  );

        // Access Token request
        $url = 'https://www.linkedin.com/uas/oauth2/accessToken?' . http_build_query($params);

        // Retrieve access token information
        $response = wp_remote_get( $url );
        $json = wp_remote_retrieve_body( $response );
        // Native PHP object, please
        $token = json_decode($json);
        
        if(!is_object($token)):
            // Try again
            $response = wp_remote_get( $url );
            $json = wp_remote_retrieve_body( $response );
            // Native PHP object, please
            $token = json_decode($json);            
        endif;
        
        $authPass = false;
        if(is_object($token) && property_exists($token, 'access_token') && !empty($token->access_token)){
            // Store access token and expiration time
            $authPass = array(
                'access_token' => $token->access_token,
                'expires_in' => $token->expires_in, // relative time (in seconds)
                'expires_at' => time() + $token->expires_in  // absolute time  
            );
        }

        return $authPass;
    }  //getAccessToken    
    
    private function handleAuthorizationCode(){
        // Start authorization process
        $params = array('response_type' => 'code',
                        'client_id' => $this->_API_KEY ,
                        'scope' => self::_scope,
                        'state' => uniqid('', true), // unique long string
                        'redirect_uri' => $this->_redirectURI,
                  );

        // Authentication request
        $url = 'https://www.linkedin.com/uas/oauth2/authorization?' . http_build_query($params);

        // Needed to identify request when it returns to us
        $loginPass = array(
            'state' => $params['state'],
            'rdpingroupsid' => 0,
            'rdpingroupspostid' => 0
            );
        
        // Needed to identify default group when request returns to us
        $rdpingroupsid = (isset($_GET['rdpingroupsid']))?$_GET['rdpingroupsid']:'';
        if(is_numeric($rdpingroupsid)){
            $loginPass['rdpingroupsid'] = $rdpingroupsid;
        }
        
        // Needed to identify default discussion when request returns to us        
        $rdpingroupspostid = (isset($_GET['rdpingroupspostid']))?$_GET['rdpingroupspostid']:'';
        if(!empty($rdpingroupspostid)){
            $loginPass['rdpingroupspostid'] = $rdpingroupspostid;
        }        

        set_transient( $params['state'], $loginPass, 60 );
        // Redirect user to authenticate
        header("Location: $url");
        exit;
    }//handleAuthorizationCode
    
    
}//RDP_LIG_Login

$wfLogin = new RDP_LIG_Login();





/* EOF */
