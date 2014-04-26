<?php

function microlabelError($text) {
echo '
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
