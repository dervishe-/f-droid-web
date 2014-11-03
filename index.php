<?php
/**
 *	vim: foldmarker{{{,}}}
 *
 *	@author A. Keledjian	<dervishe@yahoo.fr>
 *	@license http://opensource.org/licenses/LGPL-3.0 GNU Public License
 *	@version 1.0
 *
 *	Lightweight website for f-droid server
 *
 */
session_start();
//{{{ Configuration
// DIRECTORIES
define('ICONS_DIR', 'icons-480');
define('QRCODES_DIR', 'qrcodes');
define('CACHE', 'cache');
// FILES
define('CATEGORIES', 'categories.txt');
define('DATA', 'index.xml');
define('REPOS_FILE', CACHE.DIRECTORY_SEPARATOR.'repository');
define('CAT_FILE', CACHE.DIRECTORY_SEPARATOR.'categories'); // store categories as an array
define('REL_FILE', CACHE.DIRECTORY_SEPARATOR.'relations'); // store relations between categories and apps as an array
define('MANIFEST', CACHE.DIRECTORY_SEPARATOR.'Manifest'); // store index.xml hash
// PARAMETERS
define('HASH_ALGO', 'whirlpool');
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
		$application['requirements'] = (string) $app->requirements;
		$package = array();
		$package['version'] = (string) $app->package->version;
		$package['apkname'] = (string) $app->package->apkname;
		$package['size'] = (int) $app->package->size;
		$package['permissions'] = (string) $app->package->permissions;
		$package['sdkver'] = (string) $app->package->sdkver;
		$application['package'] = $package;
		$repos['list'][(string) $app->id] = $application;
	};
	file_put_contents(REPOS_FILE, serialize($repos));
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
function build_lang_selector($lang_label, $lang) { //{{{
	echo "<dl><dt>{$lang['iface']['language']}: </dt><dd><ul>";
	if ($dh = opendir("lang")) {
		while (false !== ($file = readdir($dh))) {
			if (is_file("lang".DIRECTORY_SEPARATOR.$file)) {
				$file = substr($file, 0, 2);
				echo ($file != $lang_label) ? "<li><a href=\"?lang={$file}\">{$file}</a></li>" : "<li>{$file}</li>";
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
function build_categories($repos) { //{{{
	$cat = array();
	if (is_file(CATEGORIES) && is_readable(CATEGORIES)) {
		$cat = array_flip(file(CATEGORIES, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
	} else {	// Fallback: if CATEGORIES isn't present we get the categories from DATA or from $repos structure
		if (($data = simplexml_load_file(DATA)) !== false) {
			foreach ($data->application as $app) {
				$ls_cat = explode(',', (string) $app->categories);
				foreach ($ls_cat as $ct) { if (!isset($cat[$ct])) $cat[] = $ct; };
			};
		} elseif (count($repos) > 0) {		// Fallback: if DATA is not present, then we use $repos structure
			foreach ($repos as $app) {
				$ls_cat = explode(',', $app['categories']);
				foreach ($ls_cat as $ct) { if (!isset($cat[$ct])) $cat[] = $ct; };
			};
		};
	};
	ksort($cat);
	if (count($cat) > 0) file_put_contents(CAT_FILE, serialize($cat));
	return $cat;
};
//}}}
function build_relations($repos) { //{{{
	$rel = array();
	if (is_file(DATA) && is_readable(DATA) && ($data = simplexml_load_file(DATA)) !== false) {
		foreach ($data->application as $app) {
			$ls_cat = explode(',', (string) $app->categories);
			foreach ($ls_cat as $cat) {
				if (!isset($rel[$cat])) $rel[$cat] = array();
				$rel[$cat][] = (string) $app->id;
			};
		};
	} elseif (count($repos) > 0) {		// Fallback: if DATA is not present, then we use $repos structure
		foreach ($repos as $app) {
			$ls_cat = explode(',', $app['categories']);
			foreach ($ls_cat as $cat) {
				if (!isset($rel[$cat])) $rel[$cat] = array();
				$rel[$cat][] = $app['id'];
			};
		};
	};
	if (count($rel) > 0) file_put_contents(REL_FILE, serialize($rel));
	return $rel;
};
//}}}
function translate_perm($item) { //{{{
	global $lang;
	return ($lang['perms'][$item]) ? $lang['perms'][$item] : $item;
};
//}}}
function translate_cat($item) { //{{{
	global $lang;
	return ($lang['cat'][$item]) ? $lang['cat'][$item] : $item;
};
//}}}
function decore_app($app, $lang) { //{{{
	if (USE_QRCODE) {
		include_once('phpqrcode/phpqrcode.php');
		$qrcode = QRCODES_DIR.DIRECTORY_SEPARATOR.$app['id'].".png";
		if (!is_file($qrcode)) {
			QRCode::png("https://{$_SERVER['SERVER_NAME']}/{$app['package']['apkname']}", $qrcode);
		};
		$tag_qrcode = "
		<dt><img src=\"{$qrcode}\" alt=\"QR-Code {$app['name']}\" title=\"QR-Code {$app['name']}\" /></dt>
		<dd><a href=\"{$app['package']['apkname']}\">{$lang['iface']['download']}</a></dd>";
	} else {
		$tag_qrcode = "<dt><a href=\"{$app['package']['apkname']}\">{$lang['iface']['download']}</a></dt>";
	};
	$icon = ICONS_DIR.DIRECTORY_SEPARATOR.$app['icon'];
	$version = "<dt>{$lang['iface']['version']}:</dt><dd>{$app['package']['version']} - {$lang['iface']['added']}: {$app['updated']}</dd>";
	$license = ($app['license'] != 'Unknown') ? "<dt>{$lang['iface']['license']}:</dt><dd>{$app['license']}</dd>" : '';
	$updated = "<dt>{$lang['iface']['updated']}:</dt><dd>{$app['updated']}</dd>";
	$summary = "<dt>{$lang['iface']['summary']}:</dt><dd>{$app['summary']}</dd>";
	$desc = "<dt>{$lang['iface']['desc']}:</dt><dd>{$app['desc']}</dd>";
	$requirements = (strlen($app['requirements']) > 0) ? "<dt>{$lang['iface']['requirements']}:</dt><dd>{$app['requirements']}</dd>" : '';
	$size = $app['package']['size'];
	if (($size / 1048572) > 1) {
		$size /= 1048572;
		$size = "<dt>{$lang['iface']['size']}:</dt><dd>".round($size, 2)." MB</dd>";
	} else {
		$size /= 1024;
		$size = "<dt>{$lang['iface']['size']}:</dt><dd>".round($size, 2)." kB</dd>";
	};
	
	$categories = $app['categories'];
	$cats = (strlen($categories) > 0 && $categories != 'None') ? "<ul><li>".implode('</li><li>', array_map('translate_cat', explode(',', $categories)))."</li></ul>" : '';
	$categories = "<dt>{$lang['iface']['categories']}:</dt><dd>{$cats}</dd>";
	
	
	$permissions = $app['package']['permissions'];
	$perms = (strlen($permissions) > 0) ? "<ul><li>".implode('</li><li>', array_map('translate_perm', explode(',', $permissions)))."</li></ul>" : '';
	$permissions = "<dt>{$lang['iface']['permissions']}:</dt><dd>{$perms}</dd>";
	
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
		{$desc}
		{$requirements}
		{$tag_qrcode}
	</dl>
</fieldset>";
};
//}}}
function build_list($data, $params=null) { //{{{
	if (isset($params['categories'])) {
		$list = array();
		foreach ($params['categories'] as $app) {
			$list[] = $data[$app];
		};
	} elseif (isset($params['search'])) {
		$list = $data;
	} else {
		$list = $data;
	};
	return $list;
};//}}}
function build_cache_data($hash) { //{{{
	file_put_contents(MANIFEST, $hash);
	$repos = build_structure(simplexml_load_file(DATA));
	$cat = build_categories($repos['list']);
	$rel = build_relations($repos['list']);
	return array('repos'=>$repos, "cat"=>$cat, 'rel'=>$rel);
};
//}}}
function apply_filters($relations, $categories) { //{{{
	if (!isset($_REQUEST['property'])) return null;
	$property = $_REQUEST['property'];
	if ($property == 'cat') {
		if (!isset($_REQUEST['value'])) return null;
		$value = $_REQUEST['value'];
		if (isset($categories[$value])) {
			return array('categories'=>$relations[$value]);
		} else {
			return null;
		};
	} elseif ($predicate == 'desc') {
		// search
	} else {
		return null;
	};
};
//}}}
//}}}
//{{{Select lang
if (isset($_GET['lang'])) {
	$lang_label = filter_var($_GET['lang'], FILTER_VALIDATE_REGEXP, array('options'=>array('regexp'=>'/^[a-z]{2}$/')));
	if ($lang_label === false || !is_file('lang'.DIRECTORY_SEPARATOR."{$lang_label}.php")) $lang_label = DEFAULT_LANG;
	$_SESSION['lang'] = $lang_label;
} elseif (isset($_SESSION['lang'])) {
	$lang_label = (is_file('lang'.DIRECTORY_SEPARATOR."{$_SESSION['lang']}.php")) ? $_SESSION['lang'] : DEFAULT_LANG;
} else {
	$lang_label = DEFAULT_LANG;
};
include_once("lang/{$lang_label}.php");
//}}}
//{{{ Retrieve data from cache
libxml_use_internal_errors(true);
if (!is_file(DATA) || !is_readable(DATA) || simplexml_load_file(DATA) === false) {
	if (!is_file(REPOS_FILE)) {
		build_headers($lang['iface']['error_label'], $lang['iface']['error_message']);
		build_footers();
		exit;
	} else {
		$repos = unserialize(file_get_contents(REPOS_FILE));
		$categories = (is_file(CAT_FILE)) ? unserialize(file_get_contents(CAT_FILE)) : build_categories($repos['list']);
		$relations = (is_file(REL_FILE)) ? unserialize(file_get_contents(REL_FILE)) : build_relations($repos['list']);
	};
} else {
	$hash = hash_file(HASH_ALGO, DATA);
	if ((is_file(MANIFEST) && $hash != file_get_contents(MANIFEST)) || !is_file(REPOS_FILE)) {
		$data = build_cache_data($hash);
		$repos = $data['repos'];
		$categories = $data['cat'];
		$relations = $data['rel'];
	} else {
		file_put_contents(MANIFEST, $hash);
		$repos = unserialize(file_get_contents(REPOS_FILE));
		$categories = (is_file(CAT_FILE)) ? unserialize(file_get_contents(CAT_FILE)) : build_categories($repos['list']);
		$relations = (is_file(REL_FILE)) ? unserialize(file_get_contents(REL_FILE)) : build_relations($repos['list']);
	};
};
//}}}
$liste = build_list($repos['list'], apply_filters($relations, $categories));
//{{{Select page
if (isset($_GET['page'])) {
	$page = filter_var($_GET['page'], FILTER_VALIDATE_INT);
	if ($page === false || ((int) $page - 1) * RECORDS_PER_PAGE > count($liste)) $page = 1;
} else {
	$page = 1;
};
$tampon = array_slice($liste, ($page - 1) * RECORDS_PER_PAGE, RECORDS_PER_PAGE);
//}}}
build_headers($repos['name'], $repos['desc']);
build_lang_selector($lang_label, $lang);
build_pager($page, ceil(count($liste) / RECORDS_PER_PAGE));
foreach($tampon as $app) {
	decore_app($app, $lang);
};
build_pager($page, ceil(count($liste) / RECORDS_PER_PAGE));
build_footers();
?>
