/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

allowOnlyiPad();
detectRotation();

window.onorientationchange = function () {
    
    detectRotation();
}

function detectRotation(){
        switch ( window.orientation ) {
        case 0:
            jQuery.blockUI({message:'change your iPad to landscape style',overlayCSS:{zIndex:2000},fadeIn:false,fadeOut:false});
            break;
        case 90:
            jQuery.unblockUI();
            break;
        case -90:
            jQuery.unblockUI();
            break;
    }
}

function allowOnlyiPad(){
    var isiPad = navigator.userAgent.match(/iPad/i) != null;

    if(!isiPad){
        jQuery.blockUI({message:'this plugin only can use iPad. back your browser.',overlayCSS:{zIndex:2000},fadeIn:false,fadeOut:false});
    }
}