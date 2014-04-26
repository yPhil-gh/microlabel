<?php

function getRoot() {
    $rootMusicDir = 'MUSIC';
    return $rootMusicDir;
}

function microlabelError($text, $suggestion) {
echo '
<p class="thought bubble">'.$suggestion.'</p>
		<div id="horizon">
			<div id="error">
			<img src="/microlabel/img/instruments/horns.png"/>
				<h1 id="error">Uh-oh</h1>
                '.$text.' :(
			    <div id="back_home">
                <a href="/">&#8962;</a>
			    </div>
			</div>
		</div>
';
}

?>
