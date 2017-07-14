/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var canvas;
var context;

//マウスダウン判定用
var dragFlg = false;

//touchEnd暴発防止用フラグ
var touchOKFlg = false;

var oldX = 0; //前の点のx座標
var oldY = 0; //前の点のy座標
var canX = 0; //canvasの左上x座標
var canY = 0; //canvasの左上y座標
var lineColor = 'black';
var lineSize = 1;

var splitCount = 1000;

function showCanvas(canvasDiv,canvasImg){

    jQuery('html,body').animate({scrollTop: 0}, 'slow');

    var winX = jQuery(window).width();
    var winY = jQuery(window).height();


    jQuery.blockUI({fadeIn:false,
        message:false,
	allowBodyStretch:false,
        bindEvents:false,
        constrainTabKey:true});
    var div = jQuery(canvasDiv);
    canvas = jQuery(canvasDiv).children('canvas');
    var img = jQuery(canvasImg);

    var canWidth = (winY/3)*4;

    canvas.width(canWidth);
    canvas.height(winY);

    canvas.css({'background-color':'#ffffff'});

    div.css({'position':'absolute',
             'top':0 + 'px',
             'left':0 + 'px',
             'display':'inline',
             'z-index':1002});

    canX = canvas.offset().left;
    canY = canvas.offset().top;

    context = canvas[0].getContext('2d');
    context.lineJoin = 'round';
    context.lineCap = 'round';

    if(img[0].src.match("noimage.png")){
        context.fillStyle = 'white';
    }else{
        context.drawImage(img[0].src,0,0);
    }

    context.fillRect(0, 0, canWidth, winY);

    touchOKFlg =true;

    canvas[0].addEventListener('touchmove',draw,true);
    canvas[0].addEventListener('touchstart',function(e){
            dragFlg = true;
            oldX = returnAdjustX(e);
            oldY = returnAdjustY(e);
    },false);
    canvas[0].addEventListener('touchend',drawArc,false);
    canvas[0].addEventListener('touchcancel',function(e){
            dragFlg = false;
    },false);

    var pen = div.children('.palette1').children('.pencil-black');
    var eraser = div.children('.palette1').children('.eraser-black');
    var select = div.children('select');
    var cancelButton = div.children('.canvas-cancel-button');

    pen.click(writeMode);
    eraser.click(eraseMode);
    select.change(changeLineSize);
    cancelButton.click(function(){cancelCanvas(div)});

    pen.removeClass('pencil-black');
    pen.addClass('pencil-red');


}

function draw(e){
    if(!dragFlg) return;
    e.preventDefault();
    var x = returnAdjustX(e);
    var y = returnAdjustY(e);
    context.lineWidth = lineSize;
    strokeLine(x,y);
    oldX = x;
    oldY = y;
}

function strokeLine(x,y){
    context.beginPath();
    context.moveTo(oldX, oldY);
    context.lineTo(x, y);
    context.closePath();
    context.strokeStyle = lineColor;
    context.stroke();
}

function drawArc(e){
    if(!touchOKFlg) return;
    e.preventDefault();
    var x = e.changedTouches[0].pageX;
    var y = e.changedTouches[0].pageY;
    context.fillStyle = lineColor;
    context.beginPath();
    context.arc(x,y,lineSize/2,0,2*Math.PI,true);
    context.closePath();
    context.fill();
    dragFlg=false;
}

function returnAdjustX(e){
    var returnX = 0;
    var isiPad = navigator.userAgent.match(/iPad/i) != null;
    var isiPhone = navigator.userAgent.match(/iPhone/i) != null;

    if(isiPad || isiPhone){
        returnX = e.touches[0].pageX;
    }else{
        returnX = e.clientX - canX;
    }

    return returnX;

}

function returnAdjustY(e){
    var returnY = 0;
    var isiPad = navigator.userAgent.match(/iPad/i) != null;
    var isiPhone = navigator.userAgent.match(/iPhone/i) != null;

    if(isiPad){
        returnY = e.touches[0].pageY;
    }else{
        returnY = e.clientY - canY;
    }

    return returnY;

}

function saveCanvas(url,canvasImage,canvasDiv,name,width,height){
    var absPath = jQuery('#abspath');
    var canImage = jQuery(canvasImage);
    var canImageWidth = canImage.width();
    var canImageHeight = canImage.height();
    var myDiv = jQuery(canvasDiv);
    var myCanvas = myDiv.children('canvas');
    var myContext = myCanvas[0].getContext('2d');
    var img = myCanvas[0].toDataURL("image/png").replace("data:image/png;base64,", "");
    var content = myDiv.children(".my-html");

    var base64args = "";

    for(var i=0;i<(img.length/splitCount);i++){
        base64args = base64args + "&file" + i + "=" +img.substring(i*splitCount,((i+1)*(splitCount)));
    }
    
    jQuery.ajax({
    url: url,
    data:"name="+name+"&abspath=" + absPath.val() + base64args,
    type: 'POST',
    beforeSend:function(){
    },
    success:function(e){
        img = e;
    },
    complete:function(e){
        canImage.attr('src',e.responseText);
        canImage.width(canImageWidth);
        canImage.height(canImageHeight);
        if(!content.val().match(e.responseText)){
            content.val(content.val()+'<br>'+'<img src="'+e.responseText+'" width="'+ width +'" height="'+ height +'">');
        }
        jQuery.unblockUI();
        myDiv.css({'display':'none'});
        touchOKFlg = false;
    },
    error:function(){
        jQuery('.blockMsg').children("h1").text = "An error occurred.";
    }
    });
}

function writeMode(){
    var me = jQuery(this);
    me.removeClass('pencil-black');
    me.addClass('pencil-red');
    me.next().removeClass('eraser-red');
    me.next().addClass('eraser-black');
    lineColor = 'black';
}

function eraseMode(){
    var me = jQuery(this);
    me.removeClass('eraser-black');
    me.addClass('eraser-red');
    me.prev().removeClass('pencil-red');
    me.prev().addClass('pencil-black');
    lineColor = 'white';
}

function changeLineSize(){
    var me = jQuery(this);
    lineSize = me.val();

    var className = me.attr('class');
    var classes = jQuery('.' + className);
    classes.each(function(){
        jQuery(this).val(lineSize)
    });

}

function cancelCanvas(myDiv){

    jQuery.unblockUI();
    myDiv.css({'display':'none'});
    touchOKFlg = false;
}