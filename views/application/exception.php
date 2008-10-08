<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="PoweredBy" content="Maveric 0.0 (PHP/<?php echo phpversion(); ?>)" />
<title>Uncaught <?php echo $e->type(); ?></title>
</head>
<style type="text/css">
<!--
pre {
	background-color: #ddd;
}
div.hide {
	display: none;
}
div.show {
	display: block;
}
-->
</style>
<body>
<h1>Uncaught <?php echo $e->type(); ?></h1>

<p><?php echo $e->type(); ?> thrown at line <strong><?php echo $e->getLine(); ?></strong> in file <strong><?php echo $e->getFile(); ?></strong>.</p>

<?php if ( $msg = $e->message() ) echo "<p>Message:</p>\n<pre>$msg</pre>\n"; ?>

<p><a href="javascript:;" onclick="var div = document.getElementById('stack-trace'); div.style.className = (div.style.className == 'hide' ? 'show' : 'hide');">Stack trace:</a></p>
<div class="hide" id="stack-trace">
<pre>
<?php echo $e->getTraceAsString(); ?>
</pre>
</div>


</body>
</html>
