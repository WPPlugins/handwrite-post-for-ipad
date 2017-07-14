/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


function savePost(){
    var leadText = jQuery('#leadtext');
    var content = jQuery('#content');
    var myDiv = jQuery('.canvas-div');

    myDiv.each(function(){
        var html = jQuery(this).children('.my-html');
        var readmore ='';

        if(0<jQuery(this).children('.readmore').size()){
            if(jQuery(this).children('.readmore').attr('checked')){
                readmore = '<!--more-->';
            }
        }

        content.val(content.val() + html.val() + readmore);
    });

    var readmoreAfterLeadtext = '';

    if(jQuery('#readmore-after-leadtext').attr('checked')){
        readmoreAfterLeadtext ='<!--more-->';
    }

    content.val('<p>'+leadText.val()+'</p>' + readmoreAfterLeadtext + content.val());



    jQuery('#post').submit();

}