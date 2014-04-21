// player.js - Based on the fine
// PagePlayer v 0.5.2 - copyright 2010 jezra j lickter
// licensed GPL3
// http://www.gnu.org/licenses/gpl-3.0.html
// Modified 2011 by pX
// Loading visual feedback
// MSK integration (continuous play)
// Keyboard control
// Styling

var loading_bar;
var loading_background;
var nextAlbum;

//what are the global variables
var audio_duration;
var audio_info = new Array();
var autoplay = false;
var has_audio = false;
var loaded_index;
var audio_volume=0.7;
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
var page_player_list_item_start = "PagePlayerListItem_";
//make an easy namer function
function named(id)
{
    return document.getElementById(id);
}

//try create the PagePlayer
function PagePlayer( list )
{
    //can the browser handle audio tags?
    if(typeof Audio=='function')
	{
            has_audio=true;
            audio_element = document.createElement('audio');
            audio_element.volume=audio_volume;
            //set the durationchange callback
            audio_element.addEventListener("timeupdate", function(){ updateDurationControl(); },false );
            audio_element.addEventListener("ended",function(){trackEnded();},false);
            audio_element.addEventListener("progress",function(){ updateLoadingBar(); },false);
            //audio_element.addEventListener("canplaythrough",myAutoPlay,false);
            ;
	}else{
        //bummer, if you are using IE, consider upgrading to chrome or firefox
        return;
    }
    //does this div exist?
    ulist = named(list);
    if(ulist==null)
	{
            PagePlayerError("The ul \""+ulist+"\" does not exist in the web page" );
	}else{
        var ulist_parent = ulist.parentNode;
        //create a wrapper for the player components
        var wrapper = document.createElement("div");
        wrapper.setAttribute("id","PagePlayerWrapper");

        /* build the elements */

        //create the loading div
        //        l = document.createElement("div");
        //        l.setAttribute("id","loading");
        //create the loading bar
        loading_bar = document.createElement("div");
        loading_bar.setAttribute('id',"loading_bar");

        //create the duration div
        d = document.createElement("div");
        d.setAttribute("id","duration");
        //create the duration bar
        duration_bar = document.createElement("div");
        duration_bar.setAttribute('id',"duration_bar");
        //create the duration background
        // duration_background = document.createElement("div");
        // duration_background.setAttribute('id',"duration_background");
        d.onclick = function(event){ durationClicked(event); }
        // //put the duration elements together
        // duration_background.appendChild(duration_bar);
        // duration_background.appendChild(loading_bar);
        //        d.appendChild(duration_background);
        d.appendChild(loading_bar);
        d.appendChild(duration_bar);


        //create the loading background
        //        loading_background = document.createElement("div");
        //        loading_background.setAttribute('id',"loading_background");
        //loading_background.onclick = function(event){ loadingClicked(event); }
        //put the duration elements together
        //        loading_background.appendChild(loading_bar);
        //        d.appendChild(loading_bar);
        //append the duration to the wrapper
        //        wrapper.appendChild(l);


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
        //create the volume bar
        volume_bar = document.createElement("div");
        volume_bar.setAttribute('id',"volume_bar");
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
        listDescWrapper.setAttribute("id","PagePlayerListDescWrapper");
        //append the lIW to the wrapper
        wrapper.appendChild(listDescWrapper);
        //create a div to hold the new list
        listdiv = document.createElement("div");
        listdiv.setAttribute("id","PagePlayerList");
        //make an Desc for the list info
        description_div = document.createElement("div");
        description_div.setAttribute("id","PagePlayerDescription");
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
                list_item.setAttribute("class","PagePlayerListItem");
                list_item.setAttribute("id",page_player_list_item_start+i);
                list_item.onclick=function(){listItemClicked( this.id );}
                var title = "Unknown";
                var desc = "";                
                var myspan = "";
                var audio_path = "";
                var nodes = track_list[i].childNodes;
                var nodes_len = nodes.length;
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
                                        desc = node.innerHTML;
                                        //list_item.appendChild(node);
                                        break;
                                    case "span":
                                        myspan = node.innerHTML;
                                        break;
                                    case "h5":
                                        myNextAlbum = node.innerHTML;
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
                audio_info[i]["desc"]=desc;
                audio_info[i]["myspan"]=myspan;
                audio_info[i]["audio_path"]=audio_path;
                listdiv.appendChild(list_item);
            }
        //load the initial track
        //        load_track(0);
        //update the volume bar
        update_volume_bar();
    }
}

function PagePlayerSetDescriptionHeight()
{
    //determine the height of the PagePlayerList
    var list= named("PagePlayerList");
    var height = list.offsetHeight;
    named("PagePlayerDescription").style.height="250px";
}


function load_track(id)
{
    if(id < 0) {
        id = 0;
    }
    //    alert(id);
    if(id!=loaded_index)
	{
            highlightListItem(id);
            loaded_index = id;
            //what is the audio_path?
            audio_path = audio_info[id]["audio_path"];
            //set the audio_elements src
            audio_element.src=audio_path;
            //load the new audio 
            audio_element.load();
            description = audio_info[id]["desc"];
            description_div.innerHTML = description;
	}
}

function highlightListItem(id) {
    var item = named(page_player_list_item_start+id);
    if(current_selected_list_item!=null)
	{
            current_selected_list_item.setAttribute("class","PagePlayerListItem");
	}
    current_selected_list_item = item;
    item.setAttribute("class","PagePlayerListItemSelected");
}

function playAudio() {
    audio_element.play();
    //show the pause button
    showPauseButton();
}

function nextClicked()
{
    //increment the index
    var index = loaded_index+1;
    if(index >= audio_info.length)
	{
            index=0;
	}
    //message("playing track: "+(index+1));
    load_track( index );
    playAudio();
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

function PagePlayerError( errorMessage ) {
    alert ("PagePlayer Error:\n"+errorMessage);
}

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

function update_volume_bar() {
    new_width= 100*audio_volume;
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

// function playClicked() {
//     alert(loaded_index);
//     if (loaded_index == undefined) {
//         load_track(0);
//         playAudio();     
//     } else {
//     playAudio();
//     }
// }

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
    //alert((loaded_index+2));
    if ((loaded_index+2) > tl_len) {
        //        play the next album
        //            window.location = myNextAlbum+'&s=2';
    } else {
    //play the next track
        nextClicked();
    }
    //alert(tl_len);
}

function jstest() {
    alert('myNextAlbum: '+myNextAlbum);
}

function volumeClicked(event) {
    //get the position of the event
    clientX = event.clientX;
    left = event.currentTarget.offsetLeft;
    clickoffset = clientX - left;
    audio_volume = clickoffset/event.currentTarget.offsetWidth;
    //audio_volume = percent*event.currentTarget.offsetWidth;
    set_volume(audio_volume);
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

function onPagePlayerLoad() {
    if(has_audio)
	{
            //PagePlayerSetDescriptionHeight();
            albuminfo = audio_info[0]["myspan"];
            //albuminfo = "yowza";
            //            alert("plop");
            description_div.innerHTML = albuminfo;
	}
}

function message(str) {
    named("message").innerHTML=str;
}

