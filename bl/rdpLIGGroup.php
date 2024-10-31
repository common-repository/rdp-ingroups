<?php if ( ! defined('WP_CONTENT_DIR')) exit('No direct script access allowed'); ?>
<?php
/**
 * Description of rdpLIGGroup
 *
 * @author Robert
 */
class RDP_LIG_Group {
    
    static function fetchDiscussionItemList(){
        $key = (isset($_POST['key']))? $_POST['key'] : '';
        check_ajax_referer( 'rdp-lig-group-comment-'.$key, 'security' );
        $dataPass = array();
        
        $id = (isset($_POST['group_id']))? $_POST['group_id'] : 0;
        if(!is_numeric($id))$id = 0;
        if(empty($id)){
            $dataPass['code'] = 406 ;
            $dataPass['message'] = 'Invalid Group ID.';            
            echo json_encode($dataPass);
            die();
        }        
        
        $sort = (isset($_POST['order']))? $_POST['order'] : 'recent';
        if($sort != 'recent' && $sort != 'popular')$sort = 'recent';
        $page = (isset($_POST['page']))? $_POST['page'] : 1;
        if(!is_numeric($page))$page = 1;
        if(empty($page))$page = 1;
        
        $resource = sprintf('https://www.linkedin.com/grp/home?gid=%d&sort=%s&page=%d&json=true',$id,$sort,$page);
        $response = wp_remote_get($resource);
        
        if ( is_wp_error( $response ) ) {
            $dataPass['code'] = 403 ;
            $dataPass['message'] = $response->get_error_message(); 
            echo json_encode($dataPass);
            die();            
        }         
        
        $dataPass['code'] = $response['response']['code'] ;
        $dataPass['message'] = $response['response']['message'];
        
        if($dataPass['code'] != 200){
            echo json_encode($dataPass);
            die();
        } 
        
        $data = wp_remote_retrieve_body($response);
        $JSON = json_decode($data);
        $nextPage = 0;
        if(isset($JSON->paging)){
            if($JSON->paging->hasNextPage)$nextPage = (int)$page+1;
            
        }
 
        /* @var $oGroupProfile RDP_LIG_GROUP_PROFILE */
        $oGroupProfile = RDP_LIG_GROUP_PROFILE::get($id);
        if(!$oGroupProfile->dataFilled()){
            $oGroupProfile->fill($JSON);
            $oGroupProfile->save();
        }
        
        /* @var $oDatapass RDP_LIG_DATAPASS */
        $oDatapass = RDP_LIG_DATAPASS::get($key);        
        $postID = (isset($_POST['wp_post_id']))? $_POST['wp_post_id'] : '';
        $oDatapass->wpPostID_set($postID); 

        $options = get_option( 'rdp_lig_options' );
        $fLIGShowManagerChoice = empty($options['fLIGShowManagerChoice'])? 'off' : $options['fLIGShowManagerChoice'];         
        $dataPass = array(
            'headerHTML' => $oGroupProfile->renderHeader($postID),
            'html' => self::postsToHTML($JSON,$_POST['paging_style'],$page,$id,$oDatapass,$fLIGShowManagerChoice),
            'paging_style' => $_POST['paging_style'],
            'start'=>$page,
            'next_page' => (int)$nextPage,
            'isOpenGroup' => $oGroupProfile->isOpenToNonMembers(),
            'viewPosts' => in_array('VIEW_DISCUSSIONS', $oGroupProfile->availableActions()),
            'code' => 200,
            'message' => 'OK'
        );        

        echo json_encode($dataPass);
        die();
    }//fetchDiscussionItemList
    
    static function buildDiscussionItemList($html,$pagingStyle,$start,$gid,$oDatapass,$fLIGShowManagerChoice) {
        $JSON = array();        
        foreach($html->find('#content .disc-post') as $element){
            $sClass = $element->class;
            $aClasses = array();
            if($sClass){
                $aClasses = explode(' ', $sClass);
            }
            if($fLIGShowManagerChoice === 'off' && in_array('is-mgrs-choice', $aClasses) )continue;

            $Post = new stdClass;
            $Post->id = '';
            $Post->title = '';
            $Post->summary = '';
            $Post->creationTimestamp = '';
            
            // discussion title
            $ret = $element->find('h3.post-title a',0);
            if($ret){
                $Post->title = RDP_LIG_Utilities::entitiesPlain($ret->plaintext);
                $href = $ret->href;
                $posA = strrpos($href, '/');
                $posB = strrpos($href, '?');
                $Post->id = substr($href, $posA + 1, $posB - ($posA + 1));
            }
            
            
            // discussion summary
            $ret = $element->find('p.post-details',0);            
            if($ret){
                $anchor = $ret->find('a.toggle-show-more',0);
                if($anchor)$anchor->outertext = '';
                $Post->summary = RDP_LIG_Utilities::entitiesPlain($ret->plaintext);
            }
            
            // discussion timestamp
            $ret = $element->find('div.post-date',0);
            if($ret)$Post->creationTimestamp =  $ret->plaintext;       
            
            // discussion creator
            $Creator = new stdClass;
            $Creator->id = '';
            $Creator->pictureUrl = '';
            $Creator->name = '';
            $Creator->profileURL = ''; 
            $ret = $element->find('div.post-header',0);
            if($ret){
                $img = $ret->find('img.image',0);
                if($img){
                    $Creator->pictureUrl = $img->src;
                    $Creator->name = $img->alt;
                }
                $anchor = $ret->find('a',0);
                if($anchor){
                    $Creator->profileURL = $anchor->href;
                    $url = RDP_LIG_Utilities::entitiesPlain($anchor->href);
                    parse_str(parse_url($url, PHP_URL_QUERY), $output);
                    if(key_exists('id', $output))$Creator->id = $output['id'];                    
                }
            }
            $Post->creator = $Creator;
             
            // discussion attachment
            $ret = $element->find('div.disc-article-preview',0); 
            if($ret){
                $Attachment = new stdClass;
                $Attachment->contentDomain = '';
                $Attachment->contentUrl = '';
                $Attachment->imageUrl = '';
                $Attachment->summary = '';
                $Attachment->title = '';                 
                $img = $ret->find('img',0);
                if($img)$Attachment->imageUrl = $img->src;
                
                $anchor = $ret->find('h4.title a',0);
                if($anchor){
                    parse_str(parse_url($anchor->href, PHP_URL_QUERY), $output);
                    if(in_array('url', $output))$Attachment->contentUrl = rawurldecode($output['url']);
                    $Attachment->title = RDP_LIG_Utilities::entitiesPlain($anchor->plaintext);
                }
                
                $span = $ret->find('span.source',0);
                if($span)$Attachment->contentDomain = $span->plaintext;
                
                $summary = $element->find('p.excerpt span.summary',0);
                if($summary)$Attachment->summary = RDP_LIG_Utilities::entitiesPlain($summary->plaintext);
                $Post->attachment = $Attachment;
            }
          

            $JSON[] = $Post;
        }        

        if($JSON){
            $postID = (isset($_POST['wp_post_id']))? $_POST['wp_post_id'] : '';
            $oDatapass->wpPostID_set($postID); 
            return self::postsToHTML($JSON,$pagingStyle,$start,$gid,$oDatapass);
        }else{
            return '<div class="alert notice" role="alert"><span></span>No discussion items found.</div>';
        }

    }//buildDiscussionItemList

    public static function postsToHTML($JSON,$paging_style,$start,$gid,$Datapass,$fLIGShowManagerChoice) {
	$sHTML = '';
        if($start == 1 || $paging_style != 'infinity')$sHTML .= '<div id="list-view-container"><ul class="discussion-item-list">';
	if($start == 1 && $fLIGShowManagerChoice != 'off'){
            foreach($JSON->managersChoice as $item){
                $sHTML .= self::createDiscussionItem($item,$gid,$Datapass);
            } 
        }
        foreach($JSON->posts as $item){
            $sHTML .= self::createDiscussionItem($item,$gid,$Datapass);
        }//foreach($Posts as $item)
        if($start == 1 || $paging_style != 'infinity')$sHTML .= '</ul><!--end discussion-item-list--></div><!--end list-view-container--><div class="clear before-paging"></div>';

        return $sHTML;
    }//postsToHTML

    private static function createDiscussionItem($Post,$gid,$Datapass){
        $sFullName = (!empty($Post->author) && property_exists($Post->author, 'fullName'))? $Post->author->fullName : '';
        $sHTML = '<li class="discussion-item" id="' . $Post->id . '" postid="' . $Post->id . '">';
        $sHTML .= self::getMiniprofileSection($Post, $sFullName,$Datapass);

        $sHTML .= '<div class="discussion-content "><div class="discussion-article">';
        $sDiscussionType = (!empty($Post->objectSummary) && property_exists($Post->objectSummary, 'type'))? $Post->objectSummary->type : 'SIMPLE_DISCUSSION' ;
        $sHTML .= self::getUserContributedSection($Post,$sFullName,$gid,$Datapass);
        if($sDiscussionType == 'SHARED_LINK') $sHTML .= self::getReferencedItemSection($Post);
        $sHTML .= '</div><!--end discussion-article-->';
        $sHTML .= self::getItemActionsSection($Post);        
        $sHTML .= '</div><!--end discussion-content-->';
        $sHTML .= '</li><!--end discussion-item-->';
        return $sHTML;
    }//createDiscussionItem
    
    
    static function getItemActionsSection($Post){
        
        $sHTML = '<ul class="item-actions">';
        
        // Comments
        $nCount = ' ('. $Post->summary->totalComments . ')';
        $sHTML .= '<li class="timestamp">';
        $sHTML .= 'Comment' . $nCount;
        $sHTML .= '</li>';        
        
         // Likes
        $sHTML .= '<li id="like-link-wrapper-' . $Post->id . '" class="first timestamp">';
        $sLikeCount = ' ('. $Post->summary->totalLikes . ')';
        $sHTML .= 'Like' . $sLikeCount . '</li>';
        
        //State
        if($Post->state == 'CLOSED'){
            $sHTML .= '<li id="discussion-state-wrapper-' . $Post->state . '" class="first timestamp">'; 
            $sHTML .= 'Discussion is closed';  
            $sHTML .= '</li>';              
        }

         // Creation Time
         $sHTML .= '<li class="create-date timestamp last">' . $Post->fmtCreatedDate . '</li>';
         
         $sHTML .= '</ul><!--end item-actions-->';
         return $sHTML;
    } //getItemActionsSection    

    static function getMiniprofileSection($item,$sFullName){
        $sHTML = '<span class="new-miniprofile-container">';        
        if(empty($item->author)){
            $sHTML .= '<img id="img-'. $item->id . '" src="' . RDP_LIG_Utilities::mysteryPicUrl() . '" width="60" height="60" alt="" /></a>';
        }else{
            $sURL = empty($item->author->pictureID)? RDP_LIG_Utilities::mysteryPicUrl() : 'https://media.licdn.com/mpr/mpr'.$item->author->pictureID ;
            $sProfileURL = '';
            if(!empty($item->author->links) && property_exists($item->author->links,'profile'))$sProfileURL = 'href="' . $item->author->links->profile . '"' ;
            $memberId = (property_exists($item->author,'memberId'))? $item->author->memberId : '';
            if($sProfileURL)$sHTML .= '<a ' . $sProfileURL . ' target="_new" class="rdp-lig-member-info-link" memberid="' . $memberId . '" postid="'. $item->id . '" title="Click to see this member&apos;s bio">';
            $sHTML .= '<img id="img-'. $item->id . '" src="' . $sURL . '" width="60" height="60" alt="' . $sFullName . '" /></a>';
            if($sProfileURL)$sHTML .= '</a>';            
        }
        $sHTML .= '</span>';
        return $sHTML;
    }//getMiniprofileSection

    static function getUserContributedSection($Post,$sFullName,$gid,$Datapass, $fTruncate = true){
        $sHTML = '<div class="user-contributed">';
        $postID = $Datapass->wpPostID_get();
        $url = get_permalink($postID);
        $params = RDP_LIG_Utilities::clearQueryParams();
        $params['rdpingroupspostid'] = $Post->id;
        $params['rdpingroupsid'] = $gid;       
        $url = add_query_arg($params,$url);  
        $sHTML .= '<h3>';
        $sHTML .= '<a href="' . $url . '" target="_new" class="rdp-lig-post-link" postid="' . $Post->id . '" >';
        
        if(empty($Post->verb)){
            $sHTML .= stripslashes($Post->title);
        }else{
            $sHTML .= stripslashes($Post->verb->commentary);
        }
        
        $sHTML .= '</a>';
        $sHTML .= '</h3>';
        $sHTML .= '<div class="originator">';
        
        $sHTML .= '<p class="poster-name">';
        $sProfileURL = '';
        if(!empty($Post->author->links) && property_exists($Post->author->links,'profile'))$sProfileURL = 'href="' . $Post->author->links->profile . '"' ;
        if($sProfileURL)$sHTML .= '<a href="' . $Post->author->links->profile . '" target="_new" class="rdp-lig-member-info-link" postid="' . $Post->id . '" memberid="' . $Post->author->memberId . '" title="Click to see this member&apos;s bio">';
        $sHTML .= $sFullName;
        if($sProfileURL)$sHTML .= '</a>';
        $sHTML .= '</p>';
        
        if(!empty($Post->author) && property_exists($Post->author, 'headline') && !empty($Post->author->headline)) $sHTML .= '<p class="headline">' . $Post->author->headline . '</p>';
        $sHTML .= '</div><!--end originator--><p class="user-details">';
        
        
        if($fTruncate){
            $sSummary = empty($Post->details)? '' : stripslashes($Post->details);
            $sHTML .= preg_replace ( 
                "/(?<!a href=\")(?<!src=\")((http)+(s)?:\/\/[^<>\s]+)/i",
                "<a href=\"\\0\" target=\"_blank\">\\0</a>",
                $sSummary
            );             
        }else{
            $sSummary = empty($Post->detailsHtml)? '' : stripslashes($Post->detailsHtml);
            $html = new rdp_simple_html_dom();
            $html->load('<html><body>'.$sSummary.'</body></html>');   
            $body = $html->find('body',0);
            foreach ($body->find('a') as $anchor) {
                parse_str(parse_url($anchor->href, PHP_URL_QUERY), $output);
                if(key_exists('url', $output))$anchor->href = rawurldecode($output['url']);
                $anchor->target = '_blank';            
            }
            $sPreHTML = $html->find('body',0)->innertext; 
            $sHTML .= $sPreHTML; 
        }
        
        $sHTML .= '</p></div><!--end user-contributed-->';
        return $sHTML;
    }//getUserContributedSection

    static function getReferencedItemSection($Post){
        $sHTML = '<div class="referenced-item">';
        
        if(!empty($Post->objectSummary->image)){
            if(property_exists($Post->objectSummary->image, 'url') && !empty($Post->objectSummary->image->url))$sHTML .= '<img src="' . $Post->objectSummary->image->url . '" onerror="javascript:this.style.display=\'none\'" />';
        }
        
        $sHTML .= '<div class="wrap"><h4 class="article-title">';
        
        $contentUrl = '';
        parse_str(parse_url($Post->objectSummary->url, PHP_URL_QUERY), $output);
        if(key_exists('url', $output))$contentUrl = rawurldecode($output['url']);        
        
        $sHTML .= '<a href="' . $contentUrl . '" target="_blank" alt="View details for this item">' . $Post->objectSummary->title . '</a>';
        $sHTML .= ' <span class="article-source">' . $Post->objectSummary->site_name . '</span>';
        $sHTML .= '</h4></div>';
        $sAttachmentSummary = (!empty($Post->objectSummary) && property_exists($Post->objectSummary, 'description'))? $Post->objectSummary->description : '' ;
        $sHTML .= '<p class="article-summary">' . stripslashes($sAttachmentSummary). '</p>';
        $sHTML .= '</div><!--end referenced-item-->';
        return $sHTML;
    }//getReferencedItemSection


}//RDP_LIG_Group


/* EOF */