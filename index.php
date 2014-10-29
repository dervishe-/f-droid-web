<?php
/**
 *	vim: foldmarker{{{,}}}
 *
 *	@author A. Keledjian	<dervishe@yahoo.fr>
 *	@license GNU LEsser General Public License
 *	@version 1.0
 *
 *	Lightweight website for f-droid server
 *
 */
session_start();
//{{{ Configuration
$data = "index.xml";
$cache = "cache";
$icons_dir = "icons-480";
$qrcodes_dir = "qrcodes";
$manifest = $cache.DIRECTORY_SEPARATOR."Manifest";
$repos_file = $cache.DIRECTORY_SEPARATOR."repository";
$hash_algo = "whirlpool";

define('USE_QRCODE', true);
define('RECORDS_PER_PAGE',  3);
define('DEFAULT_LANG', 'fr');
//}}}
//{{{ Library
function build_structure($_xml) { //{{{
	$repos = array();
	$repos['name'] = (string) $_xml->repo['name'];
	$repos['icon'] = (string) $_xml->repo['icon'];
	$repos['desc'] = (string) $_xml->repo->description;
	$repos['list'] = array();
	foreach($_xml->application as $app) {
		$application = array();
		$application['id'] = (string) $app->id;
		$application['added'] = (string) $app->added;
		$application['updated'] = (string) $app->lastupdated;
		$application['name'] = (string) $app->name;
		$application['summary'] = (string) $app->summary;
		$application['icon'] = (string) $app->icon;
		$application['desc'] = (string) $app->desc;
		$application['license'] = (string) $app->license;
		$application['categories'] = (string) $app->categories;
		$application['category'] = (string) $app->category;
		$application['web'] = (string) $app->web;
		$application['source'] = (string) $app->source;
		$package = array();
		$package['version'] = (string) $app->package->version;
		$package['apkname'] = (string) $app->package->apkname;
		$package['size'] = (int) $app->package->size;
		$package['permissions'] = (string) $app->package->permissions;
		$package['sdkver'] = (string) $app->package->sdkver;
		$application['package'] = $package;
		$repos['list'][] = $application;
	};
	return $repos;
};//}}}
function build_headers($title, $description=null, $favicon=null) { //{{{
	echo "<!DOCTYPE html>
<html lang=\"fr\">
	<head>
		<meta charset=\"UTF-8\">
		<title>{$title}</title>
		".((!is_null($favicon)) ? "<link type=\"image/png\" rel=\"icon\" href=\"{favicon}\">" : '')."
		<!--<link type=\"text/css\" rel=\"stylesheet\" href=\"deco/style.css\">
		<script src=\"deco/action.js\" type=\"text/javascript\">-->
	</head>
	<body>
		<h1>{$title}</h1>
		<img src=\"icons/fdroid-icon.png\" alt=\"logo\" /><div>$description</div>
	";
};//}}}
function build_footers() { //{{{
	echo '</body></html>';
};//}}}
function build_lang_selector($file_lang, $lang) { //{{{
	echo "<dl><dt>{$lang['language']}: </dt><dd><ul>";
	if ($dh = opendir("lang")) {
		while (false !== ($file = readdir($dh))) {
			if (is_file("lang".DIRECTORY_SEPARATOR.$file)) {
				$file = substr($file, 0, 2);
				echo ($file != $file_lang) ? "<li><a href=\"?lang={$file}\">{$file}</a></li>" : "<li>{$file}</li>";
			};
		};
		closedir($dh);
	};
	echo '</ul></dd></dl>';
};//}}}
function build_pager($page_number, $max) { //{{{
	echo "<dl><dt>Pages:</dt><dd><ul>";
	for ($i = 1; $i < $page_number; $i++) { echo "<li><a href=\"?page={$i}\">{$i}</a></li>"; };
	echo "<li>{$page_number}</li>";
	for ($i = $page_number + 1; $i <= $max; $i++) { echo "<li><a href=\"?page={$i}\">{$i}</a></li>"; };
	echo "</ul></dd></dl>";
};//}}}
function translate($item) { //{{{
	global $lang;
	return ($lang[$item]) ? $lang[$item] : $item;
};
//}}}
function build_app($app, $qrcodes_dir, $icons_dir, $lang) { //{{{
	if (USE_QRCODE) {
		include_once('phpqrcode/phpqrcode.php');
		$qrcode = $qrcodes_dir.DIRECTORY_SEPARATOR.$app['id'].".png";
		if (!is_file($qrcode)) {
			QRCode::png("https://{$_SERVER['SERVER_NAME']}/{$app['package']['apkname']}", $qrcode);
		};
		$tag_qrcode = "
		<dt><img src=\"{$qrcode}\" alt=\"QR-Code {$app['name']}\" title=\"QR-Code {$app['name']}\" /></dt>
		<dd><a href=\"{$app['package']['apkname']}\">{$lang['download']}</a></dd>";
	} else {
		$tag_qrcode = "<dt><a href=\"{$app['package']['apkname']}\">{$lang['download']}</a></dt>";
	};
	$icon = $icons_dir.DIRECTORY_SEPARATOR.$app['icon'];
	$version = "<dt>{$lang['version']}:</dt><dd>{$app['package']['version']} - {$lang['added']}: {$app['updated']}</dd>";
	$license = ($app['license'] != 'Unknown') ? "<dt>{$lang['license']}:</dt><dd>{$app['license']}</dd>" : '';
	$updated = "<dt>{$lang['updated']}:</dt><dd>{$app['updated']}</dd>";
	$summary = "<dt>{$lang['summary']}:</dt><dd>{$app['summary']}</dd>";
	
	$size = $app['package']['size'];
	if (($size / 1048572) > 1) {
		$size /= 1048572;
		$size = "<dt>{$lang['size']}:</dt><dd>".round($size, 2)." MB</dd>";
	} else {
		$size /= 1024;
		$size = "<dt>{$lang['size']}:</dt><dd>".round($size, 2)." kB</dd>";
	};
	
	$categories = $app['categories'];
	$cats = (strlen($categories) > 0 && $categories != 'None') ? "<ul><li>".implode('</li><li>', explode(',', $categories))."</li></ul>" : '';
	$categories = "<dt>{$lang['categories']}:</dt><dd>{$cats}</dd>";
	
	
	$permissions = $app['package']['permissions'];
	$perms = (strlen($permissions) > 0) ? "<ul><li>".implode('</li><li>', array_map('translate', explode(',', $permissions)))."</li></ul>" : '';
	$permissions = "<dt>{$lang['permissions']}:</dt><dd>{$perms}</dd>";
	
	echo "<fieldset id=\"{$app['id']}\">
	<legend>{$app['name']}</legend>
	<img src=\"{$icon}\" alt=\"icone {$app['name']}\" title=\"icone {$app['name']}\" />
	<dl>
		{$size}
		{$version}
		{$updated}
		{$summary}
		".(($cats != '') ? $categories : '')."
		{$license}
		".(($perms != '') ? $permissions : '')."
		{$tag_qrcode}
	</dl>
</fieldset>";
};
//}}}
function build_list($data, $param=null) { //{{{
	if (is_null($data)) return $data;
	return $data;
};//}}}
//}}}
if (!is_dir($cache)) {
	mkdir($cache);
	file_put_contents($cache.DIRECTORY_SEPARATOR.".htaccess", 'deny from all');
};
libxml_use_internal_errors(true);
if (!is_file($data) || simplexml_load_file($data) === false) {
	build_headers('Error', "Ooops, this repository is temporarily unavailable. We're sorry. Re-try latter please.");
	build_footers();
	exit;
};

$hash = hash_file($hash_algo, $data);
if (!is_file($manifest) || $hash != file_get_contents($manifest)) {
	file_put_contents($manifest, $hash);
	$repos = build_structure(simplexml_load_file($data));
	file_put_contents($repos_file, serialize($repos));
} else {
	if (is_file($repos_file)) {
		$repos = unserialize(file_get_contents($repos_file));
	} else {
		$repos = build_structure(simplexml_load_file($data));
		file_put_contents($repos_file, serialize($repos));
	};
};
//{{{Select lang
if (isset($_GET['lang'])) {
	$file_lang = filter_var($_GET['lang'], FILTER_VALIDATE_REGEXP, array('options'=>array('regexp'=>'/^[a-z]{2}$/')));
	if ($file_lang === false || !is_file('lang'.DIRECTORY_SEPARATOR."{$file_lang}.php")) {
		$file_lang = DEFAULT_LANG;
	} else {
		$_SESSION['lang'] = $file_lang;
	};
} elseif (isset($_SESSION['lang'])) {
	$file_lang = $_SESSION['lang'];
	if ($file_lang === false || !is_file('lang'.DIRECTORY_SEPARATOR."{$file_lang}.php")) $file_lang = DEFAULT_LANG;
} else {
	$file_lang = DEFAULT_LANG;
};
//}}}
$liste = build_list($repos['list']);
//{{{Select page
if (isset($_GET['page'])) {
	$page = filter_var($_GET['page'], FILTER_VALIDATE_INT);
	if ($page === false || ((int) $page - 1) * RECORDS_PER_PAGE > count($liste)) $page = 1;
} else {
	$page = 1;
};
$tampon = array_slice($liste, ($page - 1) * RECORDS_PER_PAGE, RECORDS_PER_PAGE);
//}}}
include_once("lang/{$file_lang}.php");
build_headers($repos['name'], $repos['desc']);
build_lang_selector($file_lang, $lang);
build_pager($page, ceil(count($liste) / RECORDS_PER_PAGE));
foreach($tampon as $app) {
	build_app($app, $qrcodes_dir, $icons_dir, $lang);
};
build_pager($page, ceil(count($liste) / RECORDS_PER_PAGE));
build_footers();
?>
