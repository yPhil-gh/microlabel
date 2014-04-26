<?php

function microlabelError($text, $suggestion) {
echo '
<p class="thought bubble">'.$suggestion.'</p>
		<div id="horizon">
			<div id="error">
			<img src="/microlabel/img/instruments/horns.png"/>
				<h1 id="error">Uh-oh</h1>
                ERROR: '.$text.' :(
			</div>
		</div>
';
}

?>
