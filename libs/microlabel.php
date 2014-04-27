<?php

function getMusicRoot() {
    return 'MUSIC';
}

function getLabelRoot() {
    return '/microlabel';
}

function microlabelError($text, $suggestion) {
echo '
<p id="suggestion">'.$suggestion.'</p>
		<div id="horizon">
			<div id="error">
			<img src="/microlabel/img/instruments/horns.png"/>
				<h1 id="error">Uh-oh</h1>
                '.$text.' :(
			    <div id="back_home">
                <a href="'.getLabelRoot().'/">&#8962;</a>
			    </div>
			</div>
		</div>
';
}

?>
