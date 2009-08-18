<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
	"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<title>Static - <?= $this->vars['pagetitle'] ?></title>
		
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="Content-Language" content="en-GB" />
		<meta http-equiv="imagetoolbar" content="false" />

		<meta name="description" content="" />

		<meta name="keywords" content="" />

		<meta name="MSSmartTagsPreventParsing" content="true" />
		<meta name="ROBOTS" content="ALL" />
		<meta name="Rating" content="General" />
		<meta name="revisit-after" content="1 Day" />
		<meta name="doc-class" content="Living Document" />
		<meta name="author" content="Electric Imagination, http://www.electricimagination.co.uk" />

		<link rel="apple-touch-icon" href="" />
		<link rel="icon" href="<?=WEBROOT ?>/favicon.ico" type="image/vnd.microsoft.icon" />
		<link rel="shortcut icon" href="<?=WEBROOT ?>/favicon.ico" type="image/vnd.microsoft.icon" /> <!-- for IE -->

		<style type="text/css" media="screen">
			@import url(<?=WEBROOT ?>/static/style/screen.css);
		</style>
	</head>
	<body>
		<div id="container">
			<div id="header">
				<h1>Static</h1>
			</div>
			<div id="content">
				<?= $this->content(); ?>
			</div>
			<div class="navigation">
			</div>
			<div id="footer">
			</div>
		</div>
	</body>
</html>