$jq=jQuery.noConflict();
 // Check setLocation is add product to cart
	// issend Variable check request on send
	var toolbarsend	=	false;
	var toolbarBaseurl	='';
	var ajaxtoolbar	=	function(){
		function lockshowloading(){
			$jq("body").append("<div class='lockshow-bg'></div>");
			$jq(".lockshow-bg").css('height', $jq("body").outerHeight());
			img	=	"<div class='lockshowloading'><img src='"+toolbarBaseurl+"frontend/default/default/VS/images/ajaxloading.gif'/></div>";
			$jq("section.category-products").append(img);
		}
		return {
			onReady:function(){
				setLocation=function(link){
					if(link.search("limit=")!=-1||link.search("mode=")!=-1||link.search("dir=")!=-1||link.search("order=")!=-1){
						if(toolbarsend==false){
							ajaxtoolbar.onSend(link,'get');
						
						}
					}else{
                        window.location.href=link;
                    }
                    
				};
				$jq('a').on('click.vs', function(event) {
					link	=	$jq(this).attr('href');
					if((link.search("limit=")!=-1||link.search("mode=")!=-1||link.search("dir=")!=-1||link.search("p=")!=-1)&&(toolbarsend==false)){
						event.preventDefault();
						ajaxtoolbar.onSend(link,'get');
					}
				});
				$jq('.toolbar .sort-by .sbOptions a').off('click.vs');
				
			},//End onReady
			onSend:function(url,typemethod){
				new Ajax.Request(url,
					{parameters:{ajaxtoolbar:1},
					method:typemethod,
					onLoading:function(cp){
						toolbarsend=true;
						lockshowloading();
					},
					onComplete:function(cp){
						toolbarsend=false;
						if(200!=cp.status){
							return false;
						}else{
							// Get success	
							var list	=	cp.responseJSON;
							$$("section.category-products").invoke("replace",list.toolbarlistproduct);
							ajaxtoolbar.onReady();
							url = url.replace(new RegExp("ajaxtoolbar=1&",'g'), "").replace(new RegExp("&ajaxtoolbar=1",'g'), "");
							history.pushState({}, '', url);
						}
						jQuery("html, body").animate({ scrollTop: 0 }, "fast");
						try{
							ConfigurableSwatchesList.init();
						}catch(err){}
					}
					
				});
			}//End onSend	
		}
	}();
Prototype.Browser.IE?Event.observe(window,"load",function(){ajaxtoolbar.onReady()}):document.observe("dom:loaded",function(){ajaxtoolbar.onReady()});
