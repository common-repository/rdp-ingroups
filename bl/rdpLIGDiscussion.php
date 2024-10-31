<?php if ( ! defined('WP_CONTENT_DIR')) exit('No direct script access allowed'); ?>
<?php

/**
 * Description of rdpLIGDiscussion
 *
 * @author Robert
 */
class RDP_LIG_Discussion {
    static function fetchContent(){
        $key = (isset($_POST['key']))? $_POST['key'] : '';
        $oDatapass = RDP_LIG_DATAPASS::get($key);
        $postID = (isset($_POST['wp_post_id']))? $_POST['wp_post_id'] : '';
        $oDatapass->wpPostID_set($postID);

        $gid = (isset($_POST['group_id']))? $_POST['group_id'] : $_POST['id']; 
        
        /* @var $oGroupProfile RDP_LIG_GROUP_PROFILE */
        $oGroupProfile = RDP_LIG_GROUP_PROFILE::get($gid);
        if(!$oGroupProfile->dataFilled()){
            $oGroupProfile->load();
            $oGroupProfile->save();
        }        
        
        $discussionURL = sprintf('https://www.linkedin.com/grp/post/%s?json=true', $_POST['id']);
        $response = wp_remote_get($discussionURL);
        
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

        $dataPass = array(
            'html' => self::postToHTML($JSON->post,$gid,$oDatapass,$oGroupProfile->isOpenToNonMembers()),
            'comments_header' => self::renderCommentsHeader($JSON->post,$oDatapass),
            'comments_total' => (int)$JSON->paging->total,
            'has_previous_comments' => $JSON->paging->hasPreviousComments,
            'code' => 200,
            'message' => 'OK'
        );            

        echo json_encode($dataPass);
        die();
    }//fetchContent
    
    static function renderCommentsHeader($Post){
        $sHTML = '<div class="section-header"><h3>Comments</h3></div>';
        $sHTML .= '<div id="comments-wrapper-' . $Post->id . '" style="position:relative" class="comment-items">';
        $sHTML .= '<a class="rdp-lig-paging-link rdp-lig-paging-more rdp-lig-paging-more-middle show-more-items" rel="previous" style="display: none;"><span class="show-more-text">SHOW PREVIOUS COMMENTS</span></a>';
        $sHTML .= '<ul class="rdp-lig-comments-list"></ul>';
        $sHTML .= '</div><!-- commentItems -->';

       return $sHTML;

    }    

    static function postToHTML($Post,$gid,$Datapass,$isOpenGroup){
        $sFullName = (!empty($Post->author) && property_exists($Post->author, 'fullName'))? $Post->author->fullName : '';
        $sHTML = '<div id="anetItemSubject" class="discussion-item subject">';
        $sHTML .= '<div class="discussion-author">';
        $sHTML .= RDP_LIG_Group::getMiniprofileSection($Post, $sFullName);
        $sHTML .= '</div><!--end .discussion-author-->';

        $sHTML .= '<div class="discussion-content">';
        $sHTML .= '<div class="discussion-article">';
       
        $sDiscussionType = (!empty($Post->objectSummary) && property_exists($Post->objectSummary, 'type'))? $Post->objectSummary->type : 'SIMPLE_DISCUSSION' ;
        $sHTML .= RDP_LIG_Group::getUserContributedSection($Post,$sFullName,$gid,$Datapass,false);
        if($sDiscussionType == 'SHARED_LINK') $sHTML .= RDP_LIG_Group::getReferencedItemSection($Post);        

        $sHTML .= '</div><!--end .discussion-article--></div><!--end .discussion-content-->';
        $sHTML .= '</div><!--end #anetItemSubject-->';
        $sHTML .= '<div id="itemActions" class="item-actions">';
        $sHTML .= self::getItemActionsSection($Post,$isOpenGroup);
        $sHTML .= '</div>';
        return $sHTML;
    }//postToHTML
    

    static function getItemActionsSection($Post,$isOpenGroup = false){
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
        
        if($isOpenGroup){
            $options = get_option( 'rdp_lig_options' );
            $text_string = empty($options['sLIGBitlyAccessToken'])? '' : $options['sLIGBitlyAccessToken'];
            if(!empty($text_string)){
                $sHTML .= '<li class="share" style="display: none;">';
                $sHTML .= '<a id="share-link-' . $Post->id . '" postid="' . $Post->id . '" class="rdp-lig-discussion-share-link share">Share</a>';

                $sHTML .= '<div id="third-party-sharing">';
                $sHTML .= '<div class="content">';
                $sHTML .= '<div class="third-party">';
                $sHTML .= '<h4>Share this discussion</h4>';
                $sHTML .= '<div class="buttons">';
                $sHTML .= '<a id="anetShareTwitter" class="share-button" data-count="none" target="_blank"><span class="lig-site twitter"></span></a>';
                $sHTML .= '<a name="fb_share" id="anetShareFB" type="icon" class="share-button"><span class="FBConnectButton_Simple"></span></a>';

                $sHTML .= '<div class="share-button plusone">
    <div style="text-indent: 0px; margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px; background-attachment: scroll; background-repeat: repeat; background-image: none; background-position: 0% 0%; background-size: auto; background-origin: padding-box; background-clip: border-box; background-color: transparent; border-top-style: none; border-right-style: none; border-bottom-style: none; border-left-style: none; float: none; line-height: normal; font-size: 1px; vertical-align: baseline; display: inline-block; width: 24px; height: 15px;" id="___plusone_0">
    <div class="g-plusone" data-size="small" data-annotation="none"></div>
    </div><!--end #___plusone_0-->
    </div><!--end .plusone--><script src="https://apis.google.com/js/platform.js" async defer></script>';


                $sHTML .= '</div>';
                $sHTML .= '<input type="text" value="" class="short-url"/>';
                $sHTML .= '</div><!--end .third-party-->';
                $sHTML .= '</div><!--end .content-->';
                $sHTML .= '<div class="arrow"></div>';
                $sHTML .= '</div><!--end #third-party-sharing-->';

                $sHTML .= '</li>';                  
            }//if(!empty($text_string))
        }//if($isOpenGroup)


         // Creation Time
         $sHTML .= '<li class="create-date timestamp last">' . $Post->fmtCreatedDate . '</li>';
         $sHTML .= '</ul><!--end item-actions-->';
         return $sHTML;
    } //getItemActionsSection     


    static function fetchComments(){
        $key = (isset($_POST['key']))? $_POST['key'] : '';
        check_ajax_referer( 'rdp-lig-group-comment-'.$key, 'security' );
        $oDatapass = RDP_LIG_DATAPASS::get($key);
        
        $count = (isset($_POST['count']))? (int)$_POST['count'] : 10;
        $start = (isset($_POST['start']))? (int)$_POST['start'] : 0; 
        if($start < 0)$start = 0;
        $tally = (int)$_POST['tally'];
        $total = (int)$_POST['total'];
        if($start == 0 && $total)$count = $total - $tally;
        
        $dataPass = array();
        $discussionURL = "https://www.linkedin.com/grp/post/{$_POST['id']}";
        $commentsURL = sprintf('https://www.linkedin.com/grp/post/%s/comments?start=%d&count=%d&json=true',$_POST['id'], $start,$count);
        $response = wp_remote_get($commentsURL);
        
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

        $dataPass['comments_returned'] = (property_exists($JSON, 'comments') && !empty($JSON->comments))? count($JSON->comments) : 0;        
        $dataPass['comments_total'] = (int)$JSON->totalCount;
        $dataPass['tally'] = (int)$_POST['tally'];
        $dataPass['html'] = (property_exists($JSON, 'comments') && !empty($JSON->comments))? self::commentsToHTML($JSON->comments,$oDatapass) : '';
        $dataPass['message'] = '';
        $newStart = $start - $count;
        if($newStart < 0)$newStart = 0;
        $dataPass['start'] = $newStart;
        $dataPass['has_previous_comments'] = $JSON->paging->hasPreviousComments;
        
        
        if($dataPass['comments_returned'] == 0){
            $dataPass['message'] = '<h3 class="no-comments-yet rdp-lig-comments-message"><a style="color: blue;text-decoration: underline;" href="' . $discussionURL . '" target="_new">Be the first to comment!</a></h3>';
        }else{
            $dataPass['message'] = '<h3 class="no-comments-yet rdp-lig-comments-message">To continue this discussion, <a style="color: blue;text-decoration: underline;" href="' . $discussionURL . '" target="_new">visit LinkedIn</a></h3>';
        }

        echo json_encode($dataPass);
        die();

    }//fetchComments


    private static function commentsToHTML($Comments,$Datapass){
        $sHTML = '';
        $nCount = count($Comments);
        $postID = (isset($_POST['wp_post_id']))? $_POST['wp_post_id'] : '';
        $Datapass->wpPostID_set($postID);   
        for($i = 0; $i < $nCount; $i++){
            $oComment = $Comments[$i];
            $sHTML .= self::createCommentItem($oComment,$Datapass);
        }

        return $sHTML;
    }//commentsToHTML

    private static function createCommentItem($Comment,$Datapass){
        $sCommentText = trim(nl2br ($Comment->text));
        if(strlen(trim($sCommentText)) == 0) return '';
        $sCommentText = preg_replace("/(<br\/>)+/", "", $sCommentText);
        $sCommentID = $Comment->commentId;
        $sPictureUrl = empty($Comment->author->pictureID)? RDP_LIG_Utilities::mysteryPicUrl() : 'https://media.licdn.com/mpr/mpr'.$Comment->author->pictureID ;
        $sCommentCreatorFullName = $Comment->author->fullName;
        $sProfileURL = '';
        if(!empty($Comment->author->links) && property_exists($Comment->author->links,'profile'))$sProfileURL = 'href="' . $Comment->author->links->profile . '"' ;

        $sCommentItem = '<li class="comment-item" id="' . $sCommentID . '">';

        $sCommentItem .= '<div class="comment-entity">';

        $sCommentItem .= '<a ' . $sProfileURL . ' target="_new" class="rdp-lig-member-info-link" commentid="' . $sCommentID . '">';
        $sCommentItem .= '<img src="' . $sPictureUrl . '" alt="' . $sCommentCreatorFullName . '" width="60" height="60" class="photo" />';
        $sCommentItem .= '</a>';
        $sCommentItem .= '</div><!--end comment-entity-->';
        $sCommentItem .= '<div class="comment-content show-contributor-badge">';

        $sCommentItem .= '<p class="commenter">';
        $sCommentItem .= '<a ' . $sProfileURL . ' target="_new" class="rdp-lig-member-info-link" commentid="' . $sCommentID . '" title="See this member&apos;s bio" class="commenter">';
        $sCommentItem .= $sCommentCreatorFullName;
        $sCommentItem .= '</a>';
        $sCommentItem .= '</p>';
       // if(property_exists($Comment->author, 'headline'))$sCommentItem .= '<p class="commenter-headline">' . $Comment->author->headline . '</p>';
        
        $sCommentText = preg_replace ( 
            "/(?<!a href=\")(?<!src=\")((http)+(s)?:\/\/[^<>\s]+)/i",
            "<a href=\"\\0\" target=\"_blank\">\\0</a>",
            $sCommentText
        );        

        $sCommentItem .= '<p class="comment-body">' . $sCommentText . '</p>';
        $sCommentItem .= '</div><!--end comment-content -->';
        $sCommentItem .= '</li><!--end comment-item-->';
        return $sCommentItem;

    }//createCommentItem




}//RDP_LIG_Discussion

/* EOF */
