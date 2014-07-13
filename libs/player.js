// player.js - Based on the fine
// PagePlayer v 0.5.2 - copyright 2010 jezra j lickter
// licensed GPL3
// http://www.gnu.org/licenses/gpl-3.0.html
// Modified by pX Time-stamp: <2011-12-06 23:37:47 px>
// (visual feedback - MSK integration (continuous play) - Keyboard control - Styling)


var loading_bar;
var loading_background;
var nextAlbum;
var color_volume;

//what are the global variables
var audio_duration;
var audio_info = new Array();
var autoplay = false;
var has_audio = false;
var loaded_index;
var audio_volume=0.7;

var yowz = 'plop';
//what elements will need to be created
var duration_bar;
var duration_background;
var volume_control;
var volume_bar;
var volume_background;
var play;
var pause;
var previous;
var next;
var description_div;

var audio_element=null;
var current_selected_list_item=null;
var page_player_list_item_start = "MlPlayerListItem_";
//make an easy namer function
function named(id)
{
    return document.getElementById(id);
}

//try create the MlPlayer
function MlPlayer( list ) {

    var test_audio= document.createElement("audio") //try and create sample audio element
    var mediasupport={audio: (test_audio.play)? true : false, video: (test_video.play)? true : false}

	//can the browser handle audio tags?
	// if(typeof Audio=='function') {
	if(mediasupport.audio !== false) {
            has_audio=true;
            audio_element = document.createElement('audio');
            audio_element.volume=audio_volume;
            //set the durationchange callback
            audio_element.addEventListener("timeupdate", function(){ updateDurationControl(); },false );
            audio_element.addEventListener("ended",function(){trackEnded();},false);
            audio_element.addEventListener("progress",function(){ updateLoadingBar(); },false);
            //audio_element.addEventListener("canplaythrough",myAutoPlay,false);
	} else {
            return;
	}

	//does this div exist?
	ulist = named(list);
	if (ulist==null) {
            MlPlayerError("The ul \""+ulist+"\" does not exist in the web page" );
	} else {
            var ulist_parent = ulist.parentNode;

            //create a wrapper for the player components
            var wrapper = document.createElement("div");
            wrapper.setAttribute("id","MlPlayerWrapper");

            //create the loading bar
            loading_bar = document.createElement("div");
            loading_bar.setAttribute('id',"loading_bar");

            //create the duration div
            d = document.createElement("div");
            d.setAttribute("id","duration");
            d.setAttribute("title","Click to skip");

            //create the duration bar
            duration_bar = document.createElement("div");
            duration_bar.setAttribute('id',"duration_bar");

            //create the duration background
            // duration_background = document.createElement("div");
            // duration_background.setAttribute('id',"duration_background");
            d.onclick = function(event){ durationClicked(event); }

            // //put the duration elements together
            d.appendChild(loading_bar);
            d.appendChild(duration_bar);

            //append the duration to the wrapper
            wrapper.appendChild(d);

            //replace the ul with the wrapper
            ulist.parentNode.replaceChild(wrapper,ulist);

            //create a div to hold the "buttons"
            button_bar = document.createElement("div");
            button_bar.setAttribute("id","button_bar");

            //add the button bar
            wrapper.appendChild(button_bar);

            //create the "play" button
            play = document.createElement("div");
            play.setAttribute("id","play");
            play.setAttribute("title","play/pause (space)");
            //play.setAttribute("title",txt_play);
            play.setAttribute("class","button");

            play.onclick=pauseClicked;

            //add the button to the buttonbar
            button_bar.appendChild(play);

            //create the pause button
            pause = document.createElement("div");
            pause.setAttribute("id","pause");
            pause.setAttribute("class","button");
            pause.setAttribute("title","play/pause (space)");

            pause.onclick=pauseClicked;
            //add the button to the buttonbar
            button_bar.appendChild(pause);

            //create the "previous" button
            previous = document.createElement("div");
            previous.setAttribute("id","previous");
            previous.setAttribute("class","button");
            previous.setAttribute("title","previous (left)");

            previous.onclick=previousClicked;
            //add the button to the buttonbar
            button_bar.appendChild(previous);
            //create the "next" button
            next = document.createElement("div");
            next.setAttribute("id","next");
            next.setAttribute("class","button");
            next.setAttribute("title","next (right)");

            next.onclick=nextClicked;
            //add the button to the buttonbar
            button_bar.appendChild(next);
            //create the volume div
            volume_control = document.createElement("div");
            volume_control.setAttribute("id","volume_control");
            volume_control.setAttribute("title","Volume");
            //create the volume bar
            volume_bar = document.createElement("div");
            volume_bar.setAttribute("id","volume_bar");
            //create the volume background
            volume_background = document.createElement("div");
            volume_background.setAttribute('id',"volume_background");
            volume_background.onclick = function(event){ volumeClicked(event); }
            //put the volume elements together
            volume_background.appendChild(volume_bar);
            volume_control.appendChild(volume_background);
            //append the volume to the wrapper
            wrapper.appendChild(volume_control);
            //create a div to hold the list and info
            listDescWrapper = document.createElement("div");
            listDescWrapper.setAttribute("id","MlPlayerListDescWrapper");
            //append the lIW to the wrapper
            wrapper.appendChild(listDescWrapper);
            //create a div to hold the new list
            listdiv = document.createElement("div");
            listdiv.setAttribute("id","MlPlayerList");
            //make a desc div for the list info
            description_div = document.createElement("div");
            description_div.setAttribute("id","MlPlayerDescription");
            //append the listdiv to the listDescwrapper
            listDescWrapper.appendChild(listdiv);
            //append the desc to the listDescwrapper
            listDescWrapper.appendChild(description_div);

            //create one last div that we will clear:both
            breaker = document.createElement("div");
            breaker.setAttribute("id","breaker");
            breaker.style.clear="both";
            wrapper.appendChild(breaker);
            track_list = ulist.getElementsByTagName("li");
            tl_len = track_list.length;

            for (var i=0; i< tl_len;i++)
            {
                audio_info[i] = new Array();
                //create a new div to hold this information
                list_item = document.createElement("div");
                list_item.setAttribute("class","MlPlayerListItem");
                list_item.setAttribute("id",page_player_list_item_start+i);
                list_item.onclick=function(){listItemClicked( this.id );}
                var title = "Unknown";
                var desc, myspan, audio_path;
                var nodes = track_list[i].childNodes;
                var nodes_len = nodes.length;
                // var divclass = "";

                //loop through the nodes
                for(var j=0 ; j< nodes_len ; j++)
                {
                    var node = nodes[j];
                    if(node!=null)
                    {
                        node_name = node.nodeName.toLowerCase();
                        switch(node_name)
                        {
                        case "h3":
                            title = node.innerHTML;
                            list_item.appendChild(node);
                            break;
                        case "div":
                            divattrs=node.attributes;
                            divclass=divattrs.getNamedItem("class").value;
                            // alert(divclass);
                            // divclass = node.attr('class');
                            if (divclass == "invisible_if_no_audio") {
                                myDesc = node.innerHTML;
                            } else if (divclass == "visible_if_no_audio") {
                                myAlbumInfo = node.innerHTML;
                            }
                            break;
                        case "h5":
                            myNextAlbum = node.innerHTML;
                            break;
                        case "h1":
                            // cttms=node.attributes;
                            // myTrackComment=cttms.getNamedItem("name").value;
                            myTrackComment = node.innerHTML;
                            break;
                        case "a":
                            attrs=node.attributes;
                            audio_path=attrs.getNamedItem("href").value;
                            break;
                        default:
                            break;
                        }
                    }
                }
                audio_info[i]["title"]=title;
                audio_info[i]["myDesc"]=myDesc;
                audio_info[i]["myAlbumInfo"]=myAlbumInfo;
                audio_info[i]["myTrackComment"]=myTrackComment;
                audio_info[i]["audio_path"]=audio_path;

                listdiv.appendChild(list_item);
            }
            //load the initial track
            //        load_track(0);
            //update the volume bar
            update_volume_bar();
	}
    }


    function onMlPlayerLoad() {
	if(has_audio)
	{
            albuminfo = audio_info[0]["myAlbumInfo"];
            description_div.innerHTML = albuminfo;
	}
    }


    function load_track(id) {
	if(id < 0) {
            id = 0;
	}
	/* alert(id); */
	if(id!=loaded_index) {
            highlightListItem(id);
            loaded_index = id;

            THEtrackComment = audio_info[id]["myTrackComment"];
            comment(THEtrackComment);

            //what is the audio_path?
            audio_path = audio_info[id]["audio_path"];
            // audio_path = 1;
            //set the audio_elements src
            audio_element.src=audio_path;
            // audio_element.src='get_file.php?id=' + audio_path;

            alert(audio_path);

            //load the new audio
            audio_element.load();

            description = audio_info[id]["myDesc"];
            description_div.innerHTML = description;
	}
	// message("loaded_index : "+loaded_index+" lenght : "+audio_info.length);
    }

    function highlightListItem(id) {
	var item = named(page_player_list_item_start+id);
	if(current_selected_list_item!=null)
	{
            current_selected_list_item.setAttribute("class","MlPlayerListItem");
	}
	current_selected_list_item = item;
	item.setAttribute("class","MlPlayerListItemSelected");
    }

    function playAudio() {
	audio_element.play();
	//show the pause button
	showPauseButton();
    }

    function nextClicked() {
	/* increment the index */
	var index = loaded_index+1;

	if(index >= audio_info.length) {
            /* play the next album */
            index = 0;
            // message("plop !");
            window.location.replace(myNextAlbum+"\u0026s=1");
	} else {
            // message("plip !");
            /* play the next track */
            load_track( index );
            playAudio();
	}
    }

    function previousClicked()
    {
	//decrement the index
	index = loaded_index-1;
	if(index < 0 )
	{
            index=audio_info.length-1;
	}
	load_track( index );
	playAudio();
    }

    function MlPlayerError( errorMessage ) {
	alert ("MlPlayer Error:\n"+errorMessage);
    }

    songMenu_div = document.getElementById('songMenu');

    function pageLoaded() {
	audio_element = document.getElementById("aplayer");
	volume_button = document.getElementById('volume_button');
	volume_control = document.getElementById('volume_control');
	//get the duration
	audio_duration = audio_element.duration;
	//set the volume
	set_volume(0.7);
    }

    function set_volume(new_volume) {
	audio_element.volume = new_volume;
	update_volume_bar();
    }

    function set_volume_color(new_volume_color) {
	/* color_volume = new_volume_color.toString(16); */
	color_volume = Math.floor(new_volume_color * 255).toString(16);
	color_volume = "#"+color_volume+"0000";
	volume_background.style.backgroundColor = color_volume;
	/* alert(color_volume); */
    }

    function update_volume_bar() {
	new_width= 100*audio_volume;
	color_volume = Math.floor(audio_volume * 255).toString(16);
	color_volume = "#"+color_volume+"3300";
	volume_background.style.backgroundColor = color_volume;
	volume_bar.style.width=new_width+"%";
    }

    function updateDurationControl() {
	//get the duration of the player
	var dur = audio_element.duration;
	var time = audio_element.currentTime;
	var fraction = time/dur;
	var percent = (fraction*100);
	duration_bar.style.width=percent+"%";
    }

    function updateLoadingBar() {
	var endBuf = audio_element.buffered.end(0);
	var soFar = parseInt(((endBuf / audio_element.duration) * 100));
	loading_bar.style.width=(soFar+5)+"%";
    }

    function showPauseButton() {
	play.style.display="none";
	pause.style.display="block";
    }

    function pauseClicked() {
	if( $(play).is(":visible") ) {
            if (loaded_index == undefined) {
		load_track(0);
		playAudio();
            } else {
		load_track(id);
		playAudio();
            }
	} else {
            audio_element.pause();
            play.style.display="block";
            pause.style.display="none";
	}
    }

    function trackEnded() {
	nextClicked();
    }

    function volumeClicked(event) {
	//get the position of the event
	clientX = event.clientX;
	left = event.currentTarget.offsetLeft;
	clickoffset = clientX - left;
	audio_volume = clickoffset/event.currentTarget.offsetWidth;
	//audio_volume = percent*event.currentTarget.offsetWidth;
	set_volume(audio_volume);
	/* set_volume_color(audio_volume); */
    }

    function durationClicked(event) {
	//get the position of the event
	clientX = event.clientX;
	left = event.currentTarget.offsetLeft;
	clickoffset = clientX - left;
	percent = clickoffset/event.currentTarget.offsetWidth;
	audio_duration = audio_element.duration;
	duration_seek = percent*audio_duration;
	audio_element.currentTime=duration_seek;
    }

    function listItemClicked(item_id) {
	//split the item_id
	splits = item_id.split("_");
	//cast the "id" as an int
	id = parseInt( splits[1] );
	//load the new audio
	load_track(id);
	//play the audio
	playAudio();
    }


// //what is the audio_path?
// audio_path = audio_info[id]["audio_path"];
// //set the audio_elements src
// audio_element.src=audio_path;
// //load the new audio
// audio_element.load();
// description = audio_info[id]["desc"];
// description_div.innerHTML = description;



    function message(str) {
	named("message").innerHTML=str;
    }

    function comment(str) {
	named("trackComment").innerHTML=str;
    }
