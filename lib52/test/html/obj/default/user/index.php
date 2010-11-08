<html>
<head>
<title>测试1</title>
</head>
<body>
<div id="main">
<h1><?php echo $scalar;?></h1>
<?php foreach ((array)$array AS $val) { ?>
<p><?php echo $val;?></p>
<?php } ?>
<hr />
<?php foreach ((array)$array AS $key => $val) { ?>
<?php if($key % 2 == 0) { ?>
<p>偶数键 : <?php echo $key;?> -&gt; <?php echo $val;?></p>
<?php } elseif ($val % 3 == 0) { ?>
<p>被3整除 : <?php echo $key;?> -&gt; <?php echo $val;?></p>
<? } else { ?>
<p>I'm lucky.</p>
<?php } ?>
<?php } ?>
</div>
</body>
<?php include($this->template('footer', '_element'));?>
