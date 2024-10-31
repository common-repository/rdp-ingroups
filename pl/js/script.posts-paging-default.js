var $j=jQuery.noConflict();
// Use jQuery via $j(...)

function rdp_lig_handle_posts_paging(){
    $j('.rdp-lig-paging-container').hide();
    $j('.rdp-lig-paging-more').hide();
    if(!rdp_lig_nextPage && rdp_lig.paging_style == 'infinity'){
        return;
    }

    if(rdp_lig.paging_style != 'infinity'){
        $j('.rdp-lig-paging-sep').show();
        if($j('#txtCurrentPage').val() == 1){
            $j('.rdp-lig-paging-previous').addClass('disabled');
            $j('.rdp-lig-paging-sep').hide();
        }else{
            $j('.rdp-lig-paging-previous').removeClass('disabled').attr('pg',parseInt($j('#txtCurrentPage').val()) - 1);
        }

        if(!rdp_lig_nextPage){
            $j('.rdp-lig-paging-next').addClass('disabled');
            $j('.rdp-lig-paging-sep').hide();
        }else{
            $j('.rdp-lig-paging-next').removeClass('disabled').attr('pg',parseInt($j('#txtCurrentPage').val()) + 1);
        }

        $j('.rdp-lig-paging-container').show();
    }else{
        $j('.rdp-lig-paging-more').attr('pg',parseInt($j('#txtCurrentPage').val()) + 1);        
        $j('.rdp-lig-paging-more-bottom-infinity').css('display','block');
    }
    
}//handle_post_paging


