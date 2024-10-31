<?php if ( ! defined('WP_CONTENT_DIR')) exit('No direct script access allowed'); ?>
<?php

/**
 * Description of rdpLIGGroups
 *
 * @author Robert
 */
class RDP_LIG_Groups {

//    static function fetchGroupProfile(){
//        $dataPass = array();
//        $id = (isset($_POST['groupid']))? $_POST['groupid'] : 0;
//        $oGroupProfile = RDP_LIG_GROUP_PROFILE::get($id);
//        
//        if (!$oGroupProfile->dataFilled()) {
//            $oGroupProfile->load();
//            $oGroupProfile->save();
//        }    
//
//        if(!$oGroupProfile->dataFilled()){
//            $dataPass['code'] = $oGroupProfile->statusCode();
//            $dataPass['message'] = $oGroupProfile->lastError();
//        } else if($oGroupProfile->hasErrors()){
//            $dataPass['code'] = $oGroupProfile->statusCode();
//            $dataPass['message'] = $oGroupProfile->lastError();
//        } else {
//            $url = get_permalink($_POST['wp_post_id']);
//            $dataPass = array(
//                'code' => '200',
//                'headerHTML' => self::renderHeader($oGroupProfile,$url),
//                'isOpenGroup' => $oGroupProfile->isOpenToNonMembers()
//            );            
//        }
//
//        echo json_encode($dataPass);
//        die();
//    }//fetchGroupProfile 
//    
//    public static function renderHeader($JSON,$postURL){
//        $params['rdpingroupsid'] = $JSON->id();
//        $url = add_query_arg($params,$postURL);        
//        $sHTML = '<a href="' . $url . '" class="image-wrapper rdp-lig-group" class="disabled" disabled="disabled" id="' . $JSON->id() . '"><img src="' . $JSON->largeLogoUrl() . '" width="100" height="50" alt="" title="" class="image"/></a>';
//        $sHTML .= '<div class="left-entity">';
//        $sHTML .= '<div class="content-wrapper"><h1 class="group-name public">' . $JSON->name() . '</h1></div><!-- .content-wrapper -->';
//        $sHTML .= '</div><!-- .left-entity --><div style="clear: both;"></div>';
//        $sHTML .= '<div class="right-entity"><div class="content-wrapper">';
//        $sHTML .= '<a class="groups-terms-of-use-link" href="https://www.linkedin.com/legal/user-agreement" target="_new"><img align="left" src="' . plugins_url(dirname(RDP_LIG_PLUGIN_BASENAME) . '/pl/images/groups-terms-of-use.png' ) . '" /></a>';
//        $sHTML .= '<a href="http://www.linkedin.com/groups?groupDashboard=&gid=' . $JSON->id() . '" target="_new" title="Statistics about ' . $JSON->name() . '" ><span class="member-count">' . $JSON->numMembersToString() . '</span></a>';
//        
//        if($JSON->isOpenToNonMembers()){
//            $sHTML .= '<div class="top-bar-actions">';
//            $params = RDP_LIG_Utilities::clearQueryParams();
//            $url = add_query_arg($params,$url);
//            $options = get_option( 'rdp_lig_options' );
//            $fLIGShowManagerChoice = empty($options['fLIGShowManagerChoice'])? 'off' : $options['fLIGShowManagerChoice'];            
//            
//            $rssParams = array('id' => $JSON->id(),'link' => $url,'mgr' => $fLIGShowManagerChoice);
//            
//            $sRSSURL = plugins_url(dirname(RDP_LIG_PLUGIN_BASENAME) . '/ws/rss.php?'. http_build_query($rssParams));            
//             
//            $sHTML .= '<a href="' . $sRSSURL . '" target="_new" title="RSS feed for ' . $JSON->name() . '" class="view-group-rss"><span>&nbsp;</span></a></div>';
//        }
//        
//        $sHTML .= '</div><!-- .content-wrapper --></div><!-- .right-entity -->';
//        
//        return $sHTML;
//    }

    
}//RDP_LIG_Groups


/* EOF */
