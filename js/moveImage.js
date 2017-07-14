/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

totalnum = 0;
movenum = 322;

function moveLeft(num){

    if(totalnum>(movenum*(num-1)*-1)){
        for(i=0;i<num;i++){
            jQuery("#canvas-image"+i).animate({left:"-=" + movenum +  "px"},"slow");
        }
        totalnum -= movenum;
    }
}

function moveRight(num){

    if(totalnum<0){
        for(i=0;i<num;i++){
            jQuery("#canvas-image"+i).animate({left:"+=" + movenum + "px"},"slow");
        }
        totalnum += movenum;
    }
}