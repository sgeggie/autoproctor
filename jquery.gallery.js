 var mode = 'small';
 var current = 0;
function getImages(studentid, quizid) {
 
    /* mode is small or expanded, depending on the picture size  */
    /* this is the index of the last clicked picture */

	
    /* first, let's build the thumbs for the selected album */
    buildThumbs(studentid, quizid);


    /*
    clicking on a thumb loads the image
    (alt attribute of the thumb is the source of the large image);
    mouseover and mouseout for a nice spotlight hover effect
    */
    // for latest version of jquery - replace .live with .on
    
    /*
    $('#thumbsContainer img').live('click',function(){
        loadPhoto($(this),'cursorPlus');
    }).live('mouseover',function(){
        var $this   = $(this);
        $this.stop().animate({
            'opacity':'1.0'
        },200);
    }).live('mouseout',function(){
        var $this   = $(this);
        $this.stop().animate({
            'opacity':'0.4'
        },200);
    });
    */
    $(document).on('click','#thumbsContainer img', function(){
        loadPhoto($(this),'cursorPlus');
    }).on('mouseover','#thumbsContainer img', function(){
        var $this   = $(this);
        $this.stop().animate({
            'opacity':'1.0'
        },200);
    }).on('mouseout','#thumbsContainer img',function(){
        var $this   = $(this);
        $this.stop().animate({
            'opacity':'0.7'
        },200);
    });
    /* when resizing the window resize the picture */
    $(window).bind('resize', function() {
        resize($('#displayed'),0);
    });

    /*
	when hovering the main image change the mouse icons (left,right,plus,minus) 
	also when clicking on the image, expand it or make it smaller depending on the mode
	*/
    $(document).on('mousemove', '#displayed', function(e){
        var $this 	= $(this);
        var imageWidth 	= parseFloat($this.css('width'),10);
		
        var x = e.pageX - $this.offset().left;
        if(x<(imageWidth/3))
            $this.addClass('cursorLeft')
            .removeClass('cursorPlus cursorRight cursorMinus');
        else if(x>(2*(imageWidth/3)))
            $this.addClass('cursorRight')
            .removeClass('cursorPlus cursorLeft cursorMinus');
        else{
            if(mode=='expanded'){
                $this.addClass('cursorMinus')
                .removeClass('cursorLeft cursorRight cursorPlus');
            }
            else if(mode=='small'){
                $this.addClass('cursorPlus')
                .removeClass('cursorLeft cursorRight cursorMinus');
            }
        }
    }).on('click','#displayed', function(){
        var $this = $(this);
        if(mode=='expanded' && $this.is('.cursorMinus')){
            mode='small';
            $this.addClass('cursorPlus')
            .removeClass('cursorLeft cursorRight cursorMinus');
            $('#thumbsWrapper').stop().animate({
                'bottom':'0px'
            },300);
            resize($this,0);
        }
        else if(mode=='small' && $this.is('.cursorPlus')){
            mode='expanded';
            $this.addClass('cursorMinus')
            .removeClass('cursorLeft cursorRight cursorPlus');
            $('#thumbsWrapper').stop().animate({
                'bottom':'-85px'
            },300);
            resize($this,1);
        }
        else if($this.is('.cursorRight')){
			
            var $thumb = $('#thumbsContainer img:nth-child('+parseInt(current+1)+')');
            if($thumb.length){
                ++current;
                loadPhoto($thumb,'cursorRight');
            }
        }
        else if($this.is('.cursorLeft')){
            var $thumb = $('#thumbsContainer img:nth-child('+parseInt(current-1)+')');
            if($thumb.length){
                --current;
                loadPhoto($thumb,'cursorLeft');
            }
        }
    });
}
    /*
    function to build the thumbs container
    An AJAX request is made to retrieve the
    photo locations of the selected album
    */


    function buildThumbs(studentid, quizid){
    	
        current=1;
        $('#imageWrapper').empty();
        $('#loading').show();
        $.get('thumbs.php?studentid='+studentid+"&quizid="+quizid, function(data) {
            var countImages = data.length;
            var count = 0;

            var $tContainer = $('<div/>',{
                id	: 'thumbsContainer',
                style	: 'visibility:hidden;'
            });
  

            
            for(var i = 0; i < countImages; ++i){
                try{
                    var description = data[i].snaptime;

                }catch(e){
                    description='';
                }
                var img = new Image();
                img.title = data[i].snaptime;
                img.src = data[i].filename;
               $('<img />').attr('src',data[i].filename).appendTo('body').css('display','none');
              
                
                $('<img title="'+description+'" alt="'+data[i].filename+'" height="75" />').on("load",function(){
                    	
                	var $this = $(this);
                    $tContainer.append($this);
            
                 
                      ++count;

                    if(count==1){
                        
                        $('<img id="displayed" class="cursorPlus"/>').on("load",function(){
                            var $first = $(this);
                            $('#loading').hide();
                            resize($first,0);
                            $('#imageWrapper').append($first);
                           $('#description').html($this.attr('title'));

                        }).attr('src',$this.attr('alt'));
                        
                    }
                    
                    if(count == countImages){
                        $('#thumbsWrapper').empty().append($tContainer);
                        thumbsDim($tContainer);
                        makeScrollable($('#thumbsWrapper'),$tContainer,15);
                    }
                }).attr('src',data[i].filename);

 
            }
        //    $(document).delay(1000);
    
        },'json');
        
    }
    
    
    /* adjust the size (width) of the scrollable container
    - this depends on all its images widths
    */
    function thumbsDim($elem){
        var finalW = 0;
        $elem.find('img').each(function(i){
            var $img 		= $(this);
            finalW+=$img.width()+5;
        //plus 5 -> 4 margins + 1 to avoid rounded calculations
        });
        $elem.css('width',finalW+'px').css('visibility','visible');
    }
    /*
    loads a picture into the imageWrapper
    the image source is in the thumb's alt attribute
    */
    function loadPhoto($thumb,cursorClass){
        current		= $thumb.index()+1;
        $('#imageWrapper').empty();
        $('#loading').show();
        
        $('<img id="displayed" title="'+$thumb.attr('title')+'" class="'+cursorClass+'" />').on("load",function(){
            var $this = $(this);
            $('#loading').hide();
            
            if (mode == "expanded"){
            	resize($this,1);
            }
            else
           	{
            	resize($this,0);
        	}
            
            if(!$('#imageWrapper').find('img').length){
                $('#imageWrapper').append($this.fadeIn(1000));
                $('#description').html($this.attr('title'));
            }
        }).attr('src',$thumb.attr('alt'));
        
    }

    //Get our elements for faster access and set overlay width
    function makeScrollable($wrapper, $container, contPadding){
        //Get menu width
        var divWidth = $wrapper.width();

        //Remove scrollbars
        $wrapper.css({
            overflow: 'hidden'
        });

        //Find last image container
        var lastLi = $container.find('img:last-child');
        $wrapper.scrollLeft(0);
        //When user move mouse over menu
        $wrapper.unbind('mousemove').bind('mousemove',function(e){

            //As images are loaded ul width increases,
            //so we recalculate it each time
            var ulWidth = lastLi[0].offsetLeft + lastLi.outerWidth() + contPadding;

            var left = (e.pageX - $wrapper.offset().left) * (ulWidth-divWidth) / divWidth;
            $wrapper.scrollLeft(left);
        });
    }
    /* function to resize an image based on the windows width and height */
    function resize($image, type){
        var widthMargin     = 10;
        var heightMargin    = 0;
        if(mode=='expanded')
            heightMargin = 60;
        else if(mode=='small')
            heightMargin = 150;
        //type 1 is animate, type 0 is normal
        var windowH      = $(window).height()-heightMargin;
        var windowW      = $(window).width()-widthMargin;
        var theImage     = new Image();
        theImage.src     = $image.attr("src");
        var imgwidth     = theImage.width;
        var imgheight    = theImage.height;

        if((imgwidth > windowW)||(imgheight > windowH)){
            if(imgwidth > imgheight){
                var newwidth = windowW;
                var ratio = imgwidth / windowW;
                var newheight = imgheight / ratio;
                theImage.height = newheight;
                theImage.width= newwidth;
                if(newheight>windowH){
                    var newnewheight = windowH;
                    var newratio = newheight/windowH;
                    var newnewwidth =newwidth/newratio;
                    theImage.width = newnewwidth;
                    theImage.height= newnewheight;
                }
            }
            else{
                var newheight = windowH;
                var ratio = imgheight / windowH;
                var newwidth = imgwidth / ratio;
                theImage.height = newheight;
                theImage.width= newwidth;
                if(newwidth>windowW){
                    var newnewwidth = windowW;
                    var newratio = newwidth/windowW;
                    var newnewheight =newheight/newratio;
                    theImage.height = newnewheight;
                    theImage.width= newnewwidth;
                }
            }
        }
  //      if((type==1)&&(!$.browser().browser == 'MSIE'))
        if (type==1)
        	
            $image.stop(true).animate({
                'width':theImage.width+'px',
                'height':theImage.height+'px'
                   },100);
        else
            $image.css({
                'width':theImage.width/1.25+'px',
                'height':theImage.height/1.25+'px',
                'align':  'left'
              
            });
    }

