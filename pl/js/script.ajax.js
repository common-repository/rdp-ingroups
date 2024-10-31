var $j=jQuery.noConflict();
// Use jQuery via $j(...)

var rdp_lig_totalPosts = null;
var rdp_lig_currentPage = 1;
var rdp_lig_commentsStart = 0;
var rdp_lig_hasPreviousComments = false;
var rdp_lig_totalPages = null;
var rdp_lig_currentGroupId = null;
var rdp_lig_groupProfile = null;
var rdp_lig_currentCommentPage = 1;
var rdp_lig_totalCommentPages = null;
var rdp_lig_totalComments = null;
var rdp_lig_targetCommentID = null;
var rdp_lig_siteGroupPostUrl = null
var rdp_lig_viewPosts = true;
var rdp_lig_isOpenGroup = false;


$j(document).ready(rdp_lig_ajax_onReady);

function rdp_lig_ajax_onReady(){
    $j('body').addClass(rdp_lig.platform);
    var browser = rdp_lig.browser_name.toString().toLowerCase().replace(' ', '-');
    if(browser == 'internet-explorer')browser = 'ie';
    $j('body').addClass(browser);
    $j('body').addClass(browser + '-v' + rdp_lig.browser_version); 

    $j(".rdp-lig-loginout").on('click', function() {
        var oMenu = $j("#rdp-lig-sub-wrapper");
        if(oMenu.hasClass('hidden')){
            oMenu.addClass('visible').removeClass('hidden');
            var pos = $j.PositionCalculator( {
                target: this,
                targetAt: "bottom right",
                item: oMenu,
                itemAt: "top right",
                flip: "both"
            }).calculate();

            oMenu.css({
                top: parseInt(oMenu.css('top')) + pos.moveBy.y + "px",
                left: parseInt(oMenu.css('left')) + pos.moveBy.x + "px"
            });        
        }else{
            oMenu.addClass('hidden').removeClass('visible');
        }
    });
    
    $j("#rdp-lig-sub-wrapper").on('mouseleave',function(){$j(this).addClass('hidden').removeClass('visible');})

    oRDPLIGContainer = $j('#rdp-lig-main');
    if(!oRDPLIGContainer.length)return;
    var groupID = $j('.defaultGroupID').attr('id');
    rdp_lig_isOpenGroup = JSON.parse($j('#txtIsOpenGroup').val().toLowerCase());

    if(typeof rdp_lig_single != 'undefined'){
        $j('#rdp-lig-main-header').addClass('single');
        $j('#rdp-lig-main').addClass('single');
        rdp_lig_currentGroupId = groupID;
        if(!rdp_lig_isOpenGroup)return;
        if(rdp_lig.logged_in == 'false')return;
        setTimeout(function(){
            rdp_lig_post_fetch();
        },600);        
    }else{
        if(groupID > 0){
            var e = jQuery.Event( "keydown", {target: $j('.defaultGroupID')} );
            rdp_lig_group_fetch(e);
        }       
    }
    

    $j('#btnLIGDashboard').click(function(event){
        event.preventDefault();
        var sHREF = $j( this ).attr('href');
        var baseURL = url('protocol', sHREF) + "://" + url('hostname', sHREF) + url('path', sHREF);
        var queryObject = jQuery.query.load(sHREF);
        queryObject.SET('rdpingroupspostsorder',$j('#txtPostsOrder').val());
        var params = queryObject.toString();
        window.location.href = baseURL+params;       
    });
    
    
    $j('.rdp-lig-paging-link').click(function(){
        if($j(this).hasClass('rdp-lig-paging-more')){
            rdp_lig_currentPage = $j(this).attr('pg');
            $j('#txtCurrentPage').val($j(this).attr('pg'));   
            rdp_lig_group_fetch(this);             
        }else{
            var sHREF = window.location.href;
            var baseURL = url('protocol', sHREF) + "://" + url('hostname', sHREF) + url('path', sHREF);
            var queryObject = jQuery.query.load(sHREF);
            queryObject.SET('rdpingroupscurrentpage',$j(this).attr('pg'));
            var params = queryObject.toString();
            $j('.rdp-lig-paging-container').hide();
            $j('#rdp-lig-main').hide();
            $j('#rdp-lig-main-header').hide();
            $j('.rdp-lig-paging-more-bottom').addClass('loading').css('display','block');             
            window.location.href = baseURL+params;                
        }
    });
    
    
    $j(".rdp-lig-paging-select").change(function(){
        var sHREF = window.location.href;
        var baseURL = url('protocol', sHREF) + "://" + url('hostname', sHREF) + url('path', sHREF);
        var queryObject = jQuery.query.load(sHREF);
        queryObject.SET('rdpingroupscurrentpage',$j(this).val());
        var params = queryObject.toString();
        $j('.rdp-lig-paging-container').hide();
        $j('#rdp-lig-main').hide();
        $j('#rdp-lig-main-header').hide();
        $j('.rdp-lig-paging-more-bottom').addClass('loading').css('display','block');             
        window.location.href = baseURL+params;
    });
    
   
    rdp_lig_attach_event_listeners();
}

function rdp_lig_group_fetch(e){
    $j('.rdp-lig-paging-more-bottom .show-more-text').text('SHOW MORE DISCUSSIONS');
    $j('.rdp-lig-paging-container').hide();
    if(rdp_lig.paging_style != 'infinity')$j('#rdp-lig-main').hide();
    $j('.rdp-lig-paging-more-bottom').addClass('loading').css('display','block');    
    var target = rdp_lig_get_source_element(e);
    var groupID = ($j(target).hasClass('defaultGroupID'))? $j(target).attr('id'): target.id;
    $j('#rdp-lig-main-header').removeClass('single');
    $j('#rdp-lig-main').removeClass('single');    
    if(e != null && !isNaN(parseInt(groupID))){
        $j('#rdp-lig-main').empty();
        rdp_lig_currentGroupId = groupID;
    }
    $j('#rdp-lig-top-bar .rdp-lig-group').addClass('disabled').attr('disabled','disabled');
    var temp_currentPage = $j('#txtCurrentPage').val(); 
   
    if(rdp_lig.paging_style == 'infinity' && $j('#txtFromSingle').val() == '1'){
        $j('#txtCurrentPage').val('1');
        $j('#txtFromSingle').val(0);
    }
    if(rdp_lig.paging_style != 'infinity')$j('#list-view-container').hide();

    dataIn = {
        action: 'rdp_lig_group_discussions_fetch',
        key: rdp_lig.key,
        group_id: rdp_lig_currentGroupId,
        page: $j('#txtCurrentPage').val(),
        paging_style: rdp_lig.paging_style,
        order: $j('#txtPostsOrder').val(),        
        security: $j('#commentToken').val(),
        wp_post_id: rdp_lig.wp_post_id
    };
    setTimeout(function(){
        jQuery.post(rdp_lig.ajax_url, dataIn, rdp_lig_group_discussions_fetch_result);
    },600);

    $j('#txtCurrentPage').val(temp_currentPage);
}//group_fetch

function rdp_lig_group_discussions_fetch_result(data,status, xhr){ 
    if (status == "error") {
	var msg = "Sorry, but there was an error: ";
        alert(msg + xhr.status + " " + xhr.statusText);
    }
    else
    {
        if(!rdp_lig_viewPosts)return;

        myData = (new Function("return " + data))();
      
        if(myData.code == 401){
            $j('#rdp-lig-main').html(myData.html).show();
            $j('.rdp-lig-paging-more-bottom').removeClass('loading').css('display','none');
            return;
        }
        
        if(myData.code != 200){
            $j('.rdp-lig-paging-more-bottom').removeClass('loading').css('display','none');
            alert(myData.message);
            return;
        } 
        
        
        if(typeof rdp_lig_single == 'undefined')$j('.view-choice-wrapper').delay(300).show(300);
        $j('#rdp-lig-main').show();
        $j('#rdp-lig-top-bar .header').html(myData.headerHTML);
        if(typeof rdp_lig_single == 'undefined')$j('#rdp-lig-top-bar .rdp-lig-group').addClass('disabled').attr('disabled','disabled');
        var nWidth = $j('#rdp-lig-head').width()*.65;
        $j('#rdp-lig-top-bar .left-entity').width(nWidth);
        $j('#rdp-lig-top-bar').show(300);

        rdp_lig_isOpenGroup = myData.isOpenGroup;
        var sMsg = '';
        if(!rdp_lig_isOpenGroup )sMsg = '<div class="alert notice" role="alert"><span></span>This is a private group and discussions are not publicly visible.</div>';
        if(sMsg.length != 0 ){
            $j('.rdp-lig-paging-more-bottom').removeClass('loading').css('display','none');
            $j('#rdp-lig-main').html(sMsg);
             return;
        }

        rdp_lig_nextPage = myData.next_page;

        if(myData.paging_style == 'infinity' && myData.start != 1){
            if($j('#rdp-lig-main').html() == "") {
                $j('#rdp-lig-main').html('<div id="list-view-container"><ul class="discussion-item-list"></ul><!--end discussion-item-list--></div><!--end list-view-container--><div class="clear before-paging"></div>');
            }
            $j('#list-view-container .discussion-item-list').append(myData.html)
        }else{
            $j('#rdp-lig-main').html(myData.html);
        }
        
        if(rdp_lig.logged_in == 'false'){
            $j('#rdp-lig-main a').each(function( index ) {
                $j( this ).removeAttr('href').addClass('btnLGILogin');
            });
        }

        if(myData.paging_style != 'infinity'){
           var oOffset = document.getElementById("rdp-lig-head");
           $j(".rdp-lig-paging-link").removeAttr('style');
           $j("html, body").animate({
            scrollTop: oOffset.offsetTop
            }, 600);
            $j('#list-view-container').show(300);            
        }

        $j('#rdp-lig-main').show();
        
        $j('.view-choice-wrapper').css('display','inline-block');
        $j('.rdp-lig-actions-container').show();        
        rdp_lig_toggle_posts_order();        
        $j('.rdp-lig-paging-more-bottom').removeClass('loading').css('display','none');
        rdp_lig_handle_posts_paging();
        
        if (rdp_lig.wcr_active && typeof rdp_wcr_handle_links == 'function') { 
            rdp_wcr_handle_links(); 
          }
        var sLastDiscussionID = $j('#txtLastDiscussionID').val();

        if(sLastDiscussionID){
            oOffset = document.getElementById(sLastDiscussionID);
            $j("html, body").animate({
             scrollTop: oOffset.offsetTop
             }, 300);
             $j('#txtLastDiscussionID').val('');
        }
    }
}//group_fetch_result


function rdp_lig_post_fetch(){
    rdp_lig_hasPreviousComments = false;
    rdp_lig_commentsStart = 0;
    rdp_lig_currentCommentPage = 1;
    rdp_lig_totalCommentPages = null;
    rdp_lig_totalComments = null;
    rdp_lig_viewPosts = true;        
    rdp_lig_targetCommentID = (rdp_lig_single.comment_id && rdp_lig_single.comment_id != 0) ? rdp_lig_single.comment_id : null;
    rdp_lig_viewPosts = true;        
    $j('.rdp-lig-paging-more-middle').removeClass('first-page-reached');
    $j('.rdp-lig-paging-more-bottom').hide();

    $j('.rdp-lig-paging-container').hide();
    var postID = rdp_lig_single.post_id;
    dataIn = {
        action: 'rdp_lig_discussion_content_fetch',
        key: rdp_lig.key,
        id: postID,
        wp_post_id: rdp_lig.wp_post_id,
        group_id: rdp_lig_currentGroupId
    };
    jQuery.post(rdp_lig.ajax_url, dataIn, rdp_lig_discussion_content_fetch_result);
    $j('.rdp-lig-paging-more-bottom').addClass('loading').css('display','block');
    
}//rdp_lig_post_fetch

function rdp_lig_discussion_content_fetch_result(data,status, xhr){
    if (status == "error") {
	var msg = "Sorry, but there was an error: ";
        alert(msg + xhr.status + " " + xhr.statusText);
    }
    else
    {
        myData = (new Function("return " + data))();

        if(myData.code == 401){
            $j('#rdp-lig-main').html(myData.html).show();
            $j('.rdp-lig-paging-more-bottom').removeClass('loading').css('display','none');
            return;
        }
        
        if(myData.code != 200){
            $j('.rdp-lig-paging-more-bottom').removeClass('loading').css('display','none');
            alert(myData.message);
            return;
        }         

        $j('#rdp-lig-main .discussion-post').html(myData.html);
        $j('#rdp-lig-main .discussion-comments').html(myData.comments_header);

        rdp_lig_totalComments = myData.comments_total;
        rdp_lig_hasPreviousComments = myData.has_previous_comments
        rdp_lig_totalCommentPages = Math.ceil(rdp_lig_totalComments/rdp_lig.page_size);  
        rdp_lig_currentCommentPage = ((rdp_lig_totalCommentPages-1) <= 0)? 1 : rdp_lig_totalCommentPages-1; 
        rdp_lig_commentsStart = rdp_lig_totalComments - (1 * rdp_lig.page_size);
        
        $j('#rdp-lig-top-bar .rdp-lig-group').removeClass('disabled').removeAttr('disabled','disabled').addClass('from-single');
        $j('#rdp-lig-top-bar .rdp-lig-group img').addClass('from-single');

        var e = jQuery.Event( "keydown" );
        setTimeout(function(){
            rdp_lig_short_url_fetch();
            rdp_lig_discussion_comments_fetch(e);
        },300);
        
        var oOffset = document.getElementById("rdp-lig-head");
        $j("html, body").animate({
            scrollTop: oOffset.offsetTop + $j(oOffset).height()
         }, 300);
        $j('#new-comment-text').autosize();  

        if (rdp_lig.wcr_active && typeof rdp_wcr_handle_links == 'function') { 
            rdp_wcr_handle_links(); 
          }
    }
}//rdp_lig_discussion_content_fetch_result

function rdp_lig_short_url_fetch(){
    dataIn = {
        action: 'rdp_lig_short_url_fetch',
        key: rdp_lig.key,
        security: $j('#shortenURL').val(),
        url: window.location.href
    };  
    jQuery.post(rdp_lig.ajax_url, dataIn, rdp_lig_short_url_fetch_result);    
}//rdp_lig_short_url_fetch

function rdp_lig_short_url_fetch_result(data,status, xhr){
    if (status == "error") {
	var msg = "Sorry, but there was an error: ";
        alert(msg + xhr.status + " " + xhr.statusText);
    }
    else
    {
        if(data.length){
            $j('li.share').show();
            $j('.short-url').val(data);
            pageURL = 'https://apis.google.com/se/0/_/+1/fastbutton?usegapi=1&size=small&count=false&hl=en_US&origin=';
            pageURL += window.location.protocol + "//" + window.location.host;
            pageURL += '&url='  + $j('.short-url').val();
            pageURL += '&gsrc=3p&ic=1&jsh=m;/_/scs/apps-static/_/js/k=oz.gapi.en_US.Na_XPK6h-MY.O/m=__features__/am=AQ/rt=j/d=1/t=zcms/rs=AItRSTMxSho3DyuWFzwBos_UnkcDpVs9ZQ#_methods=onPlusOne,_ready,_close,_open,_resizeMe,_renderstart,oncircled,drefresh,erefresh,onload&id=I0_1410449966861&parent=';
            pageURL += window.location.protocol + "//" + window.location.host;
            pageURL += '&pfname=&rpctoken=13174281';
            $j('#I0_1410449966861').attr('src',pageURL);
        }
    }    
}//rdp_lig_short_url_fetch_result

function rdp_lig_discussion_comments_fetch(e){
    var target = $j('#rdp-lig-main .discussion-post');
    $j('#rdp-lig-main .comment-container').hide();
    var navDirection = (e.rel)? e.rel : '';
    if(navDirection == 'previous'){
        if(!$j('.rdp-lig-paging-more-middle').hasClass('loading'))$j('.rdp-lig-paging-more-middle').addClass('loading').css('display','block');
    }else{
        if(!$j('.rdp-lig-paging-more-bottom').hasClass('loading'))$j('.rdp-lig-paging-more-bottom').addClass('loading').css('display','block');
    }
    var tally = $j('li.comment-item').length;
    var count = Number(rdp_lig.page_size);
    var start = (rdp_lig_currentCommentPage-1)*rdp_lig.page_size;

    dataIn = {
        action: 'rdp_lig_discussion_comments_fetch',
        key: rdp_lig.key,
        id: $j(target).attr('id'),
        security: $j('#commentToken').val(),
        groupid: rdp_lig_currentGroupId,
        count: count,
        start: rdp_lig_commentsStart,
        total: rdp_lig_totalComments,
        tally: tally,      
        wp_post_id: rdp_lig.wp_post_id       
    };

    jQuery.post(rdp_lig.ajax_url, dataIn, rdp_lig_discussion_comments_fetch_result);
}//rdp_lig_discussion_comments_fetch

function rdp_lig_discussion_comments_fetch_result(data,status, xhr){
    if (status == "error") {
	var msg = "Sorry, but there was an error: ";
        alert(msg + xhr.status + " " + xhr.statusText);
    }
    else
    {
        myData = (new Function("return " + data))();
        $j('.rdp-lig-paging-more').removeClass('loading').css('display','none');        
        
        if(myData.code == 401){
            $j('#rdp-lig-main').html(myData.html).show();
            return;
        }
        
        if(myData.code != 200){
            alert(myData.message);
            return;
        }           

        rdp_lig_commentsStart = myData.start;
        rdp_lig_totalComments = myData.comments_total;
        rdp_lig_hasPreviousComments = myData.has_previous_comments;
      
        rdp_lig_totalCommentPages = Math.ceil(rdp_lig_totalComments/rdp_lig.page_size);
        if(rdp_lig_totalCommentPages <= 0)rdp_lig_totalCommentPages = 1;

        if(!rdp_lig_hasPreviousComments)$j('.rdp-lig-paging-more-middle').addClass('first-page-reached');

        if(myData.comments_returned != 0){
            if(myData.tally != 0){
                $j('#rdp-lig-main .discussion-comments .rdp-lig-comments-list').prepend(myData.html);
            }else{
                $j('#rdp-lig-main .discussion-comments .rdp-lig-comments-list').html(myData.html); 
            }
        }
       
        if($j('li.comment-item').length > 3){
            $j('.rdp-lig-most-recent-comment-link').show();
        }else $j('.rdp-lig-most-recent-comment-link').hide();
        
        var isClosed = $j('#discussion-state-wrapper-CLOSED').length;
        if(!isClosed && !$j('.rdp-lig-comments-message').length){
            $j('.comment-items').append(myData.message);
        }

        if (rdp_lig.wcr_active && typeof rdp_wcr_handle_links == 'function') { 
            rdp_wcr_handle_links(); 
        }
        if(rdp_lig_targetCommentID){
            if(rdp_lig_targetCommentID == 'most-recent')rdp_lig_targetCommentID = $j('li.comment-item:last').attr('id');
            oDiscussionComment = $j('#'+rdp_lig_targetCommentID);
            if(oDiscussionComment.length){
                var oDiscussionComments = document.getElementById('discussion-comments');
                var oOffset = document.getElementById(rdp_lig_targetCommentID);
                $j("html, body").animate({
                 scrollTop: oDiscussionComments.offsetTop + oOffset.offsetTop
                 }, 300);
                 rdp_lig_targetCommentID = null;
            }
        }
        rdp_lig_handle_comments_paging();          
    }
}//rdp_lig_discussion_comments_fetch_result

function rdp_lig_toggle_posts_order(){
    sOrder =  $j('#txtPostsOrder').val();
    
    switch(sOrder) {
    case 'popular':
        $j('.view-choice-wrapper ').addClass('most-popular').removeClass('most-recent');
        $j('.view-choice-wrapper a.popular').removeAttr('href').css('cursor','default');  
        break;
    case 'recent':
    default:
        $j('.view-choice-wrapper ').removeClass('most-popular').addClass('most-recent');
        $j('.view-choice-wrapper a.recent').removeAttr('href').css('cursor','default');      
        break;
    }    
}//rdp_lig_toggle_posts_order

function rdp_lig_previous_discussion_comments_fetch_handler(e){
    var target = rdp_lig_get_source_element(e.target);
    if('show-more-text' == $j(target).attr('class'))target = $j(target).parent();
    rdp_lig_currentCommentPage = $j(target).attr('pg');
    var e2 = jQuery.Event( "click",{rel: $j(target).attr('rel')} );
    rdp_lig_discussion_comments_fetch(e2);   
}//rdp_lig_previous_discussion_comments_fetch_handler


function rdp_lig_bodyOnClick_handler(e){
    $j( '.rdp-lig-profile-link-wrap:not(.loading)' ).removeClass('open');
    oRDPLIGContainer = $j('#rdp-lig-main #itemActions .share #third-party-sharing');
    if(!oRDPLIGContainer.length)return;    
    var target = rdp_lig_get_source_element(e.target);
    if($j(target).hasClass('rdp-lig-discussion-share-link'))return;
    if($j(target).parent().hasClass('share-button'))return;
    if($j(target).parent().hasClass('third-party'))return;    
    $j('#itemActions .share #third-party-sharing').removeClass('open');
    
}//rdp_lig_bodyOnClick_handler

function getURL(url){
    return $j.ajax({
        type: "GET",
        url: url,
        cache: false,
        async: false
    }).responseText;
}

function rdp_lig_attach_event_listeners(){
    $j('#rdp-lig-main').on( "click", '.rdp-lig-post-link' , function(event){
        event.preventDefault();
        var sHREF = $j( this ).attr('href');
        var baseURL = url('protocol', sHREF) + "://" + url('hostname', sHREF) + url('path', sHREF);
        var queryObject = jQuery.query.load(sHREF);
        queryObject.SET('rdpingroupscurrentpage',$j('#txtCurrentPage').val());
        queryObject.SET('rdpingroupspostsorder',$j('#txtPostsOrder').val());
        $j('#txtLastDiscussionID').val($j( this ).attr('postid'));
        $j('#txtFromSingle').val(1);
        
        if($j( this ).attr('commentid'))queryObject.SET('rdpingroupscommentid',$j( this ).attr('commentid'));
        var params = queryObject.toString();
        window.location.href = baseURL+params;
    });
    
    $j('#rdp-lig-main').on( "click", '.rdp-lig-discussion-share-link' , function(){
        $j('#itemActions .share #third-party-sharing').addClass('open');
    });  
    $j('body').on( "click", rdp_lig_bodyOnClick_handler );  
    $j('#rdp-lig-main').on( "click", '.rdp-lig-paging-more-middle' , rdp_lig_previous_discussion_comments_fetch_handler );
   
   
    $j('#rdp-lig-main-header').on( "click", '.rdp-lig-posts-order' , function(event){ 
        event.preventDefault();
        var sHREF = $j( this ).attr('href');
        if(typeof sHREF == 'undefined')return true;
        $j('.rdp-lig-paging-container').hide();
        $j('#rdp-lig-main').hide();
        $j('#rdp-lig-main-header').hide();
        $j('.rdp-lig-paging-more-bottom').addClass('loading').css('display','block'); 
        window.location.href = sHREF;        
    });
    
    $j('#rdp-lig-main-header').on( "click", '.rdp-lig-group' , function(event){
        event.preventDefault();
        if($j(this).hasClass('disabled'))return true;
        var sHREF = $j( this ).attr('href');
        var baseURL = url('protocol', sHREF) + "://" + url('hostname', sHREF) + url('path', sHREF);
        var queryObject = jQuery.query.load(sHREF);
        queryObject.SET('rdpingroupscurrentpage',$j('#txtCurrentPage').val());
        queryObject.SET('rdpingroupspostsorder',$j('#txtPostsOrder').val());
        queryObject.SET('rdpingroupslastpostid',$j('#txtLastDiscussionID').val());        
        var fromSingle = ($j('#rdp-lig-main-header').hasClass('single'))? 1 : 0;
        queryObject.SET('rdpingroupsfromsingle', fromSingle);
        var params = queryObject.toString();
        window.location.href = baseURL+params;
    });

    $j('#rdp-lig-main').on( "click", '#anetShareTwitter' , function(){
        var text = '';
        oRDPLIGContainer = $j('#rdp-lig-main .user-contributed');
        if(oRDPLIGContainer.length){
            text = $j('#rdp-lig-main .user-contributed h3 a').text();
        }         
        pageURL = 'http://twitter.com/share?url=' + $j('.short-url').val() + '&text=' + text;
        rdp_lig_openPopupCenter(pageURL, 'Share a link on Twitter', 550, 380);
    });

    $j('#rdp-lig-main').on( "click", '#anetShareFB' , function(){
        pageURL = 'http://www.facebook.com/sharer.php?u=' + $j('.short-url').val();
        rdp_lig_openPopupCenter(pageURL, 'Share a link on Facebook', 650, 380);
    });
    
}//rdp_lig_attach_event_listeners
