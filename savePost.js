/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


function savePost(){
    var openningText = jQuery('#openningtext');
    var content = jQuery('#content');
    var images = jQuery('.my-html');

    images.each(function(){
        content.val(content.val() + jQuery(this).val());
    });

    content.val('<p>'+openningText.val()+'</p>'+content.val());



    jQuery('#post').submit();

}