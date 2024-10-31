var $j=jQuery.noConflict();
// Use jQuery via $j(...)

function rdp_lig_handle_comments_paging(){

    var fShowTopPaging = !$j('.rdp-lig-paging-more-middle').hasClass('first-page-reached');
    
    if(fShowTopPaging){
        $j('.rdp-lig-paging-more-middle').attr('pg',parseInt(rdp_lig_currentCommentPage) - 1);
        $j('.rdp-lig-paging-more-middle').css('display','block');         
    }    

}//rdp_lig_handle_comments_paging


