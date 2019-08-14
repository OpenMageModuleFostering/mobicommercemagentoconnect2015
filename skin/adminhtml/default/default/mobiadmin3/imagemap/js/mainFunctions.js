var zoom = 1;
	var startSelectionDot = {}
	$(function(){
		$('.linkEditor').change(updateSelectedLink);
		$('.linkEditor').keyup(updateSelectedLink);
		
		$('#startBtn').click(function(){
			var imgURL = $('#imageURL').val();
			if(imgURL!=''){
				$('#canv').remove(); // remove canvas if exists
				$('.loader').eq(0).show(); // add loader
			
				var newImg = document.createElement('img');
				$(newImg).attr({
					'src': imgURL
					}).load(function(){
						$('.doneMark').eq(0).fadeIn(function(){
							$('#step1').slideUp(function(){
								$('#step2').slideDown();
							});
						});
						
						var canvas = document.createElement('div');
						$(canvas).attr('id','canv').appendTo('#canvasContatiner').css({
							'width': newImg.width,
							'height': newImg.height,
							'background': 'url('+imgURL+') 0 0 no-repeat'
						});
						$.data(canvas,'realSize',{'width':newImg.width,'height':newImg.height});
						$.data(canvas,'url', imgURL);
						assignCanvas();
						$('.loader').hide(); // hide loader
					}).error(function(){
						$('.loader').hide(); // hide loader
						alert('Not a valid image, try a different URL..');
					});
			}else{
				alert('Please Insert a URL..');
			}
		});
		
		$('.stepTitleLink').css({'cursor':'pointer'}).click(function(){
			var which = $(this).index('.stepTitleLink');
			switch (which){
				case 0:
					if(($('.doneMark').eq(0).is(':visible'))&&(confirmRestart())){
						$('.doneMark').eq(1).hide(); // if going from tab 3 to 1
						switchToTab(this);
					}
					break;
				case 1:
					if($('.doneMark').eq(0).is(':visible')){
						switchToTab(this);
					}else{
						alert('You MUST pick an image first.')
					}
					break;
				case 2:
					if($('.doneMark').eq(0).is(':visible')){
						if($('.doneMark').eq(1).is(':visible')){
							switchToTab(this);
						}else{
							alert('If you\'re done adding the links please click the "Finish" button.')
						}
					}else{
						alert('You MUST pick an image first.')
					}
					break;
			}
		})
		
		$('#step2End').click(function(ev){
			if($('.theBox').length > 0){
				$('.loader').eq(1).show(); // add loader
				var thisTime = ev.timeStamp;
				$('.theBox.selected').removeClass('selected');
				$('#linkAdder').slideUp();
				if(validateLinks()){
					$('.loader').eq(1).hide();
					
					var canvas = $('#canv')[0];
					var realSize = $.data(canvas,'realSize');
					var imgURL = $.data(canvas,'url');
					createCodes(imgURL, realSize, ev.timeStamp);
					
					_gaq.push(['_trackEvent', 'HTML Map', 'Create']);
					$('.doneMark').eq(1).fadeIn(function(){
						$('#step2').slideUp(function(){
							$('#step3').slideDown();
						});
					});
				}else{
					alert('Some of the areas you selected don\'t have links attached to them.');
					$('.loader').eq(1).hide();
				}
			}else{
				alert('You must create at least one linkage area');
			}
		});
		
		$('.outputCode').click(function(ev){
			this.select();
			ev.preventDefault();
		});
	});
	
	function onBoxSelect(element){
		if($(element).hasClass('highlightedBox')){$(element).removeClass('highlightedBox');}
		
		var linkInfo = $.data(element, 'link');
		if (typeof linkInfo == 'undefined'){
			$.data(element, 'link',{
				'href': '',
				'alt': '',
				'target': '_self'
			})
			linkInfo = $.data(element, 'link');
		}
		
		$('#linkURL').val(linkInfo.href);
		$('#linkAlt').val(linkInfo.alt);
		$('#linkTarget option').each(function(){
			if(($(this).val())==(linkInfo.target)){
				$(this).attr('selected','selected')
			}else{
				$(this).removeAttr('selected');
			}
		});
		
		$('#linkAdder').slideDown();
		$('#linkURL').focus();
	}
	
	function onBoxDelete(element){
		$('#linkAdder').slideUp();
	}
	
	function unSelectBoxes(element, swapped){
		$('.theBox').not('.selected').css({'opacity':0.8});
		if(!swapped){
			$('#linkAdder').slideUp();
		}
	}
	
	function updateSelectedLink(){
		var selectedBox = $('.selected')[0];
		$.data(selectedBox,'link',{
				'href': String($('#linkURL').val()),
				'alt': String($('#linkAlt').val()),
				'target': String($('#linkTarget').val())
		});
	}
	
	function validateLinks(){
		var state = true;
		var currentLink;
		$('#canv').children('.theBox').each(function(){
			currentLink = $.data(this, 'link');
			if(currentLink.href == ''){
				state = false;
				$(this).addClass('highlightedBox');
			}
		});
		return state;
	}
	
	function confirmRestart(){
		var answer = confirm("This will restart the process and delete everthing you've done so far, are you sure?")
		return answer;
	}
	
	function switchToTab(element){
		$(element).next().next().hide();
		$('.steps').not($(element).parent().next()).slideUp();
		$(element).parent().next().slideDown();
	}
	
	function createCodes(url, size, timeStamp){
		var htmlCode = '&lt;img src="'+url+'" alt="" usemap="#map'+timeStamp+'"&gt;\r\n&lt;map id="map'+timeStamp+'" name="map'+timeStamp+'"&gt;';
		var cssCode = '&lt;div style="position:relative; height:'+size.height+'px; width:'+size.width+'px; background:url('+url+') 0 0 no-repeat;"&gt;';
		
		$('.theBox').each(function(){
			var linkInfo = $.data(this,'link');
			var linkPosition = $.data(this,'position');
			htmlCode += '&lt;area shape="rect" coords="'+linkPosition.left+','+linkPosition.top+','+(linkPosition.left+linkPosition.width)+','+(linkPosition.top+linkPosition.height)+'" title="'+linkInfo.alt+'" alt="'+linkInfo.alt+'" href="'+linkInfo.href+'" target="'+linkInfo.target+'"&gt;';
			cssCode += '&lt;a style="position:absolute; top:'+linkPosition.top+'px; left:'+linkPosition.left+'px; width:'+linkPosition.width+'px; height:'+linkPosition.height+'px;" title="'+linkInfo.alt+'" alt="'+linkInfo.alt+'" href="'+linkInfo.href+'" target="'+linkInfo.target+'"&gt;&lt;/a&gt;';
		});
		
		htmlCode += '&lt;area shape="rect" coords="'+(size.width-2)+','+(size.height-2)+','+(size.width-1)+','+(size.height-1)+'" alt="Image HTML map generator" title="HTML Map creator" href="http://www.html-map.com/" target="_self"&gt;&lt;/map&gt;';
		cssCode += '&lt;a style="position:absolute; overflow:hidden; top:'+(size.height-2)+'px; left:'+(size.width-2)+'px; width:1px; height:1px;" title="HTML Map creator" alt="Image HTML map generator" href="http://www.html-map.com/" target="_self">&lt;/a>&lt;/div&gt;';
		
		$('#HTMLCode').html(htmlCode);
		$('#CSSCode').html(cssCode);
	}