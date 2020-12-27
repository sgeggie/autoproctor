<script type="text/javascript">


var App = {
    start: handler = function (stream) {
        App.video.addEventListener('canplay', function () {
            App.video.removeEventListener('canplay', handler);
            setTimeout(function () {
                App.video.play();
                App.canvas.style.display = 'inline';
                App.info.style.display = 'none';
            	App.canvas.width = App.video.videoWidth;
                App.canvas.height = App.video.videoHeight;

                App.drawToCanvas();
            }, 500);
        }, true);

        var domURL = window.URL || window.webkitURL || window.mozURL || window.msURL;
        App.video.src = domURL ? domURL.createObjectURL(stream) : stream;
    },
    denied: function () {
        App.info.innerHTML = 'Camera access denied!<br>Please reload and try again.';
    },
    error: function (e) {
        if (e) {
            console.error(e);
        }
        App.info.innerHTML = 'Please go to about:flags in Google Chrome and enable the &quot;MediaStream&quot; flag.';
    },
    drawToCanvas: function () {
//        requestAnimationFrame(App.drawToCanvas);
		setInterval( function () {
        var video = App.video,
            ctx = App.context;
    	  
   
        ctx.drawImage(video, 0, 0, App.canvas.width, App.canvas.height);
        
        
		}, 1000/60);  /* 60 frames per second */
        

    }
};

App.init = function () {
    App.video = document.createElement('video');
    App.canvas = document.querySelector('#output');
    App.canvas.style.display = 'none';
    App.context = App.canvas.getContext('2d');
    App.info = document.querySelector('#info');

    navigator.getUserMedia_ = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia;

    
    try {
        navigator.getUserMedia_({
            video: true,
            audio: false
        }, App.start, App.denied);   /* pass a stream to App.start */
    } catch (e) {
        try {
            navigator.getUserMedia_('video', App.start, App.denied);
        } catch (e) {
            App.error(e);
        }
    }

    App.video.loop = App.video.muted = true;
    App.video.load();

};

App.init();


function btnCapture() {
	$('#button').hide();

    
    var img = App.canvas.toDataURL("image/png");
    var userid = "<?php echo $userid; ?>";
    var ajax = new XMLHttpRequest();
    window.open(img, "_blank", "menubar=0,titlebar=0,toolbar=0,width=" + App.canvas.width + ",height=" + App.canvas.height);
    ajax.open("POST", 'save.php',false);
    //    ajax.setRequestHeader('Content-Type', 'application/upload');
    ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
 //   postData = "&studentid="+userid+"&quizid="+quizid+"&snapshot="+encodeURIComponent(img)+"&id_res=true";  /*  id_res is for full resolution */
 	postData = "&studentid="+userid+"&quizid="+quizid+"&snapshot="+encodeURIComponent(img);
    ajax.send(postData);
    interval = <?php echo rand(MIN_INTERVAL*60*1000, MAX_INTERVAL*60*1000) ?>;  
//    interval = 30000;
    timer = setInterval(timerCapture,interval);
    exptime = expiration * 1000 * 60;
	setTimeout(function(){
		clearInterval(timer);
		open(location, '_self').close();
	}, exptime); 
    
}
function timerCapture() {

    var img = App.canvas.toDataURL("image/png");
    var userid = <?php echo $userid; ?>;
    var ajax = new XMLHttpRequest();
    ajax.open("POST", 'save.php',false);
    ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    postData = "studentid="+userid+"&quizid="+quizid+"&snapshot="+encodeURIComponent(img);
    ajax.send(postData);
    
   
//    window.open(img, "_blank", "menubar=0,titlebar=0,toolbar=0,width=" + App.canvas.width + ",height=" + App.canvas.height);
}

function btnPlayPause() {
    if (App.video.paused)
        App.video.play();
    else
        App.video.pause();
}

</script>

