<?php

$head = array('body_class' => 'beammeup primary', 
              'title'      => 'Beam Me Up!');
head($head);
?>

<h1><?php echo $head['title']; ?></h1>

<div id="primary">

<?php echo flash(); ?>
    
	<p>Status of items recently beamed to the Internet Archive</p>

</div>

<?php foot(); ?>