/*
eval((function(x){var d="";var p=0;while(p<x.length){if(x.charAt(p)!="`")d+=x.charAt(p++);else{var l=x.charCodeAt(p+3)-28;if(l>4)d+=d.substr(d.length-x.charCodeAt(p+1)*96-x.charCodeAt(p+2)+3104-l,l);else d+="`";p+=4}}return d})("function assignCanvas() {$(\"#canv\").drag(` @%(ev, dd) {if (Math.abs(dd.deltaY) < 10) {` )% = ` \"&* 10 / ` E/;}` \\1X` `-X` f'X` X5X);}newPos = {};if `!#& < 0`!{#startSele`\":!Dot.y +`!f'` D\"` d\".top = 0;} else` (+` K;;}` .D`\"d\"`\"9&`!Q8x` x'` B$` y#left`!Z0` 1#` M;`!^-` =6`#a$.width =`#x1`!gH- ` d)`\"))` |$` ;9left`!x%if (`!x(+` p*> $(this)` *\"()` z.` 2+` x,` &#height`\"K0Y`\"Q*`&?<` v%` k#`\"S)` }%` =9top`\"L0top`\"U&` c#`\"V&` +\"`\"U(`!$%` 3,` {+$(\".s`!C$\").last().css`!3#);ev.preventDefault();}`+V#\"`\"'!\", `+S/var `\"=% = document.createElement(\"div\");`%B0= $.browser.mozilla ? ev.layerX : ev.offsetX` N/y` B;Y` U(Y;$(` U%).addClass(\"`\"r(css({top:` z/, left` '/x, `&Z!:\"10px\", `$%\"` (#})`!*+ppendTo`$Q\"`#_&end`#X+`/f\"`$J0remove`!m/`\",&theBox\").transformable({un` J\"Handler:\"un`!W\"Boxes\", box` +\"` 9%onB` ,$` :\"Delete` 2*` .\"\"}).hover`1K'`(h#!`' %as`!m)ed\")`\"F!` 9\"stop().animate({opacity:\"1\"}, \"fast\");}}`# (` ;d0.8` w*);});}"))
*/

function assignCanvas() {
    $("#canv").drag(function(ev, dd) {
        if (Math.abs(dd.deltaY) < 10) {
            dd.deltaY = dd.deltaY * 10 / Math.abs(dd.deltaY);
        }
        if (Math.abs(dd.deltaX) < 10) {
            dd.deltaX = dd.deltaX * 10 / Math.abs(dd.deltaX);
        }
        newPos = {};
        if (dd.deltaY < 0) {
            if (startSelectionDot.y + dd.deltaY < 0) {
                newPos.top = 0;
            } else {
                newPos.top = startSelectionDot.y + dd.deltaY;
            }
        } else {
            newPos.top = startSelectionDot.y;
        }
        if (dd.deltaX < 0) {
            if (startSelectionDot.x + dd.deltaX < 0) {
                newPos.left = 0;
            } else {
                newPos.left = startSelectionDot.x + dd.deltaX;
            }
        } else {
            newPos.left = startSelectionDot.x;
        }
        newPos.width = Math.abs(dd.deltaX);
        if (dd.deltaX < 0) {
            if (startSelectionDot.x - newPos.width < 0) {
                newPos.width = startSelectionDot.x - newPos.left;
            }
        } else if (newPos.left + newPos.width > $(this).width()) {
            newPos.width = $(this).width() - newPos.left;
        }
        newPos.height = Math.abs(dd.deltaY);
        if (dd.deltaY < 0) {
            if (startSelectionDot.y - newPos.height < 0) {
                newPos.height = startSelectionDot.y - newPos.top;
            }
        } else if (newPos.top + newPos.height > $(this).height()) {
            newPos.height = $(this).height() - newPos.top;
        }
        $(".selection").last().css(newPos);
        ev.preventDefault();
    }).drag("start", function(ev, dd) {
        var Selection = document.createElement("div");
        startSelectionDot.x = $.browser.mozilla ? ev.layerX : ev.offsetX;
        startSelectionDot.y = $.browser.mozilla ? ev.layerY : ev.offsetY;
        $(Selection).addClass("selection").css({
            top: startSelectionDot.y,
            left: startSelectionDot.x,
            width: "10px",
            height: "10px"
        });
        $(Selection).appendTo(this);
    }).drag("end", function(ev) {
        $(".selection").last().removeClass("selection").addClass("theBox").transformable({
            unselectHandler: "unSelectBoxes",
            boxSelectHandler: "onBoxSelect",
            boxDeleteHandler: "onBoxDelete"
        }).hover(function() {
            if (!$(this).hasClass("selected")) {
                $(this).stop().animate({
                    opacity: "1"
                }, "fast");
            }
        }, function() {
            if (!$(this).hasClass("selected")) {
                $(this).stop().animate({
                    opacity: "0.8"
                }, "fast");
            }
        });
    });
}