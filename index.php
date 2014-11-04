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
define('ROOT', dirname(__FILE__));
define('ICONS_DIR', 'icons-480');
define('ICONS_DIR_LIGHT', 'icons-240');
define('QRCODES_DIR', 'qrcodes');
define('CACHE', ROOT.DIRECTORY_SEPARATOR.'cache');
// FILES
define('CATEGORIES', ROOT.DIRECTORY_SEPARATOR.'categories.txt');
define('DATA', ROOT.DIRECTORY_SEPARATOR.'index.xml');
define('REPOS_FILE', CACHE.DIRECTORY_SEPARATOR.'repository');
define('CAT_FILE', CACHE.DIRECTORY_SEPARATOR.'categories'); // store categories as an array
define('REL_FILE', CACHE.DIRECTORY_SEPARATOR.'relations'); // store relations between categories and apps as an array
define('LIC_FILE', CACHE.DIRECTORY_SEPARATOR.'license'); // store used licenses as an array
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
	foreach($_xml->application as $app) { $repos['list'][] = (string) $app->id; };
	$repos['nbr'] = count($repos['list']);
	file_put_contents(REPOS_FILE, serialize($repos));
	return $repos;
};//}}}
function build_app($_xml, $id_app) { //{{{
	$app = $_xml->xpath("application[@id='$id_app']");
	$app = $app[0];
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
	file_put_contents(CACHE.DIRECTORY_SEPARATOR.$application['id'], serialize($application));
	return $application;
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
	echo "<dl><dt>".translate('iface', 'language', $lang).": </dt><dd><ul>";
	if ($dh = opendir("lang")) {
		while (false !== ($file = readdir($dh))) {
			if (is_file("lang".DIRECTORY_SEPARATOR.$file)) {
				$file = substr($file, 0, 2);
				echo ($file != $lang_label) ? "<li><a href=\"?lang={$file}\" title=\"".translate('lang', $file, $lang)."\">{$file}</a></li>" : "<li>{$file}</li>";
			};
		};
		closedir($dh);
	};
	echo '</ul></dd></dl>';
};//}}}
function build_pager($page_number, $max, $lang) { //{{{
	echo "<dl><dt>".translate('iface', 'page', $lang).":</dt><dd><ul>";
	for ($i = 1; $i < $page_number; $i++) { echo "<li><a href=\"?page={$i}\" title=\"".translate('iface', 'go_to_page', $lang)." {$i}\">{$i}</a></li>"; };
	echo "<li>{$page_number}</li>";
	for ($i = $page_number + 1; $i <= $max; $i++) { echo "<li><a href=\"?page={$i}\" title=\"".translate('iface', 'go_to_page', $lang)." {$i}\">{$i}</a></li>"; };
	echo "</ul></dd></dl>";
};//}}}
function cache_categories($repos) { //{{{
	$cat = array();
	if (is_file(CATEGORIES) && is_readable(CATEGORIES)) {
		$cat = array_flip(file(CATEGORIES, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
	} else {	// Fallback: if CATEGORIES isn't present we get the categories from DATA or app file stored in cache
		if (($data = simplexml_load_file(DATA)) !== false) {
			foreach ($data->application as $app) {
				$ls_cat = explode(',', (string) $app->categories);
				foreach ($ls_cat as $ct) { if (!isset($cat[$ct])) $cat[$ct] = 1; };
			};
		} elseif (count($repos) > 0) {		// Fallback: if DATA is not present, then we use app file stored in cache
			foreach ($repos as $app) {
				if (is_file(CACHE.DIRECTORY_SEPARATOR.$app) && is_readable(CACHE.DIRECTORY_SEPARATOR.$app)) {
					$app = unserialize(file_get_contents($app));
					$ls_cat = explode(',', $app['categories']);
					foreach ($ls_cat as $ct) { if (!isset($cat[$ct])) $cat[$ct] = 1; };
				};
			};
		};
	};
	ksort($cat);
	if (count($cat) > 0) file_put_contents(CAT_FILE, serialize($cat));
	return $cat;
};
//}}}
function cache_relations($repos) { //{{{
	$rel = array();
	if (is_file(DATA) && is_readable(DATA) && ($data = simplexml_load_file(DATA)) !== false) {
		foreach ($data->application as $app) {
			$ls_cat = explode(',', (string) $app->categories);
			foreach ($ls_cat as $cat) {
				if (!isset($rel[$cat])) $rel[$cat] = array();
				$rel[$cat][] = (string) $app->id;
			};
		};
	} elseif (count($repos) > 0) {		// Fallback: if DATA is not present, then we use app file stored in cache
		foreach ($repos as $app) {
			if (is_file(CACHE.DIRECTORY_SEPARATOR.$app) && is_readable(CACHE.DIRECTORY_SEPARATOR.$app)) {
				$app = unserialize(file_get_contents($app));
				$ls_cat = explode(',', $app['categories']);
				foreach ($ls_cat as $cat) {
					if (!isset($rel[$cat])) $rel[$cat] = array();
					$rel[$cat][] = $app['id'];
				};
			};
		};
	};
	if (count($rel) > 0) file_put_contents(REL_FILE, serialize($rel));
	return $rel;
};
//}}}
function cache_licenses($repos) { //{{{
	$lic = array();
	if (is_file(DATA) && is_readable(DATA) && ($data = simplexml_load_file(DATA)) !== false) {
		foreach ($data->application as $app) {
			$idx = (string) $app->license;
			if (!isset($lic[$idx])) $lic[$idx] = array();
			$lic[$idx][] = (string) $app->id;
		};
	} elseif (count($repos) > 0) {		// Fallback: if DATA is not present, then we use app file stored in cache
		foreach ($repos as $app) {
			if (is_file(CACHE.DIRECTORY_SEPARATOR.$app) && is_readable(CACHE.DIRECTORY_SEPARATOR.$app)) {
				$app = unserialize(file_get_contents($app));
				$idx = $app['license'];
				if (!isset($lic[$idx])) $lic[$idx] = array();
				$lic[$idx][] = $app['id'];
			};
		};
	};
	if (count($lic) > 0) file_put_contents(LIC_FILE, serialize($lic));
	return $lic;
};
//}}}
function translate_perm($item) { //{{{
	global $lang;
	return (isset($lang['perms'][$item])) ? $lang['perms'][$item] : $item;
};
//}}}
function translate_cat($item) { //{{{
	global $lang;
	return (isset($lang['cat'][$item])) ? $lang['cat'][$item] : $item;
};
//}}}
function translate($cat, $item, $lang) { //{{{
	return (isset($lang[$cat][$item])) ? $lang[$cat][$item] : $item;
};
//}}}
function decore_app($app_id, $lang) { //{{{
	if (is_file(CACHE.DIRECTORY_SEPARATOR.$app_id) && is_readable(CACHE.DIRECTORY_SEPARATOR.$app_id)) {
		$app = unserialize(file_get_contents(CACHE.DIRECTORY_SEPARATOR.$app_id));
	} else {
		if (($data = simplexml_load_file(DATA)) !== false) {
			$app = build_app($data, $app_id);
			$data = null;
		} else {
			return false;
		};
	};
	if (USE_QRCODE) {
		include_once('phpqrcode/phpqrcode.php');
		$qrcode = QRCODES_DIR.DIRECTORY_SEPARATOR.$app['id'].".png";
		if (!is_file($qrcode)) {
			QRCode::png("https://{$_SERVER['SERVER_NAME']}/{$app['package']['apkname']}", $qrcode);
		};
		$tag_qrcode = "
		<dt><img src=\"{$qrcode}\" alt=\"QR-Code {$app['name']}\" title=\"QR-Code {$app['name']}\" /></dt>
		<dd><a href=\"{$app['package']['apkname']}\">".translate('iface', 'download', $lang)."</a></dd>";
	} else {
		$tag_qrcode = "<dt><a href=\"{$app['package']['apkname']}\">".translate('iface', 'download', $lang)."</a></dt>";
	};
	$icon = ICONS_DIR.DIRECTORY_SEPARATOR.$app['icon'];
	$version = "<dt>".translate('iface', 'version', $lang).":</dt><dd>{$app['package']['version']} - ".translate('iface', 'added', $lang).": {$app['updated']}</dd>";
	$license = "<dt>".translate('iface', 'license', $lang).":</dt><dd>".translate('lic', $app['license'], $lang)."</dd>";
	$updated = "<dt>".translate('iface', 'updated', $lang).":</dt><dd>{$app['updated']}</dd>";
	$summary = "<dt>".translate('iface', 'summary', $lang).":</dt><dd>{$app['summary']}</dd>";
	$desc = "<dt>".translate('iface', 'desc', $lang).":</dt><dd>{$app['desc']}</dd>";
	$requirements = (strlen($app['requirements']) > 0) ? "<dt>".translate('iface', 'requirements', $lang).":</dt><dd>{$app['requirements']}</dd>" : '';
	$size = $app['package']['size'];
	if (($size / 1048572) > 1) {
		$size /= 1048572;
		$size = "<dt>".translate('iface', 'size', $lang).":</dt><dd>".round($size, 2)." MB</dd>";
	} else {
		$size /= 1024;
		$size = "<dt>".translate('iface', 'size', $lang).":</dt><dd>".round($size, 2)." kB</dd>";
	};
	
	$categories = $app['categories'];
	$cats = (strlen($categories) > 0 && $categories != 'None') ? "<ul><li>".implode('</li><li>', array_map('translate_cat', explode(',', $categories)))."</li></ul>" : '';
	$categories = "<dt>".translate('iface', 'categories', $lang).":</dt><dd>{$cats}</dd>";
	
	$permissions = $app['package']['permissions'];
	$perms = (strlen($permissions) > 0) ? "<ul><li>".implode('</li><li>', array_map('translate_perm', explode(',', $permissions)))."</li></ul>" : '';
	$permissions = "<dt>".translate('iface', 'permissions', $lang).":</dt><dd>{$perms}</dd>";
	
	echo "<fieldset id=\"".str_replace(array('.', ' '), '_', $app['id'])."\">
	<legend>{$app['name']}</legend>
	<a href=\"index.php\" title=\"".translate('iface', 'back', $lang)."\">".translate('iface', 'back', $lang)."</a>
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
	<a href=\"index.php\" title=\"".translate('iface', 'back', $lang)."\">".translate('iface', 'back', $lang)."</a>
</fieldset>";
};
//}}}
function decore_app_light($app_id, $lang) { //{{{
	if (is_file(CACHE.DIRECTORY_SEPARATOR.$app_id) && is_readable(CACHE.DIRECTORY_SEPARATOR.$app_id)) {
		$app = unserialize(file_get_contents(CACHE.DIRECTORY_SEPARATOR.$app_id));
	} else {
		if (($data = simplexml_load_file(DATA)) !== false) {
			$app = build_app($data, $app_id);
			$data = null;
		} else {
			return false;
		};
	};
	$icon = ICONS_DIR_LIGHT.DIRECTORY_SEPARATOR.$app['icon'];
	$version = "<dt>".translate('iface', 'version', $lang).":</dt><dd>{$app['package']['version']} - ".translate('iface', 'added', $lang).": {$app['updated']}</dd>";
	$updated = "<dt>".translate('iface', 'updated', $lang).":</dt><dd>{$app['updated']}</dd>";
	$summary = "<dt>".translate('iface', 'summary', $lang).":</dt><dd>{$app['summary']}</dd>";
	$size = $app['package']['size'];
	if (($size / 1048572) > 1) {
		$size /= 1048572;
		$size = "<dt>".translate('iface', 'size', $lang).":</dt><dd>".round($size, 2)." MB</dd>";
	} else {
		$size /= 1024;
		$size = "<dt>".translate('iface', 'size', $lang).":</dt><dd>".round($size, 2)." kB</dd>";
	};
	echo "<li><fieldset id=\"".str_replace(array('.', ' '), '_', $app['id'])."\">
	<legend>{$app['name']}</legend>
	<img src=\"{$icon}\" alt=\"icone {$app['name']}\" title=\"icone {$app['name']}\" />
	<dl>
		{$size}
		{$version}
		{$updated}
		{$summary}
	</dl>
	<a href=\"{$app['package']['apkname']}\" title=\"".translate('iface', 'download', $lang)."\">".translate('iface', 'download', $lang)."</a>
	<a href=\"?getSheet={$app['id']}\" title=\"".translate('iface', 'sheet', $lang)."\">".translate('iface', 'sheet', $lang)."</a>
</fieldset></li>";
};
//}}}
function decore_applist($tampon, $lang) { //{{{
	echo "<a href=\"#menu\" title=\"".translate('iface', 'ret_menu', $lang)."\">".translate('iface', 'menu', $lang)."</a>";
	echo "<ul id=\"applist\">";
	foreach($tampon as $app) { decore_app_light($app, $lang); };
	echo "</ul>";
};
//}}}
function build_list($data, $params=null) { //{{{
	if (isset($params['categories'])) {
		unset($_SESSION['list']);
		$list = $params['categories'];
	} elseif (isset($params['licenses'])) {
		unset($_SESSION['list']);
		$list = $params['licenses'];
	} elseif (isset($params['search'])) {
		unset($_SESSION['list']);
		$list = $data;
	} else {
		if (isset($_SESSION['list'])) {
			$list = $_SESSION['list'];
		} else {
			$list = $data;
		};
	};
	$_SESSION['list'] = $list;
	return $list;
};//}}}
function build_cache_data($hash) { //{{{
	$dh = opendir(CACHE);
	while (false !== ($file = readdir($dh))) {
		if (is_file(CACHE.DIRECTORY_SEPARATOR.$file) && $file != '.htaccess') unlink(CACHE.DIRECTORY_SEPARATOR.$file);
	};
	closedir($dh);
	file_put_contents(MANIFEST, $hash);
	$repos = build_structure(simplexml_load_file(DATA));
	$cat = cache_categories($repos['list']);
	$rel = cache_relations($repos['list']);
	$lic = cache_licenses($repos['list']);
	return array('repos'=>$repos, "cat"=>$cat, 'rel'=>$rel, 'lic'=>$lic);
};
//}}}
function apply_filters($relations, $categories, $licenses) { //{{{
	if (!isset($_REQUEST['prop'])) return null;
	$property = $_REQUEST['prop'];
	if ($property == 'cat') { //{{{
		unset($_SESSION['lic']);
		if (!isset($_REQUEST['val'])) return null;
		$value = $_REQUEST['val'];
		if (isset($categories[$value])) {
			$_SESSION['cat'] = $value;
			return array('categories'=>$relations[$value]);
		} else {
			unset($_SESSION['cat']);
			return null;
		}; //}}}
	} elseif ($property == 'lic') { //{{{
		unset($_SESSION['cat']);
		if (!isset($_REQUEST['val'])) return null;
		$value = $_REQUEST['val'];
		if (isset($licenses[$value])) {
			$_SESSION['lic'] = $value;
			return array('licenses'=>$licenses[$value]);
		} else {
			unset($_SESSION['lic']);
			return null;
		}; //}}}
	} elseif ($property == 'desc') { //{{{
		unset($_SESSION['cat']);
		unset($_SESSION['lic']);
		// search
		 // }}}
	} else {
		unset($_SESSION['cat']);
		unset($_SESSION['lic']);
		unset($_SESSION['list']);
		return null;
	};
};
//}}}
function build_tagcloud_categories($relations, $lang, $nbr_apps) { //{{{
	if (count($relations) > 0) {
		echo "<fieldset id=\"categories\"><legend>".translate('iface', 'categories', $lang).": <a href=\"#menu\" title=\"".translate('iface', 'ret_menu', $lang)."\">".translate('iface', 'menu', $lang)."</a></legend><ul>";
		$lab_all_cat = translate('iface', 'all_categories', $lang);
		if (!isset($_SESSION['cat'])) {
			echo "<li><b>{$lab_all_cat}<span> ({$nbr_apps})</span></b></li>";
		} else {
			echo "<li><a href=\"?prop=init\" title=\"".translate('iface', 'alt_cat_link', $lang).": {$lab_all_cat}\">{$lab_all_cat}</a><span> ({$nbr_apps})</span></li>";
		};
		reset($relations);
		while (false !== ($cat = current($relations))) {
			$name_cat = translate('cat', key($relations), $lang);
			echo "<li>";
			if (isset($_SESSION['cat']) && key($relations) == $_SESSION['cat']) {
				echo "<b>{$name_cat}<span> (".count($cat).")</span></b>";
			} else {
				echo "<a href=\"?prop=cat&amp;val=".urlencode(key($relations))."\" title=\"".translate('iface', 'alt_cat_link', $lang).": {$name_cat}\">{$name_cat}</a><span> (".count($cat).")</span>";
			};
			echo "</li>";
			next($relations);
		};
		echo "</ul></fieldset>";
	};
};	
//}}}
function build_tagcloud_licenses($licenses, $lang, $nbr_apps) { //{{{
	if (count($licenses) > 0) {
		echo "<fieldset id=\"licenses\"><legend>".translate('iface', 'license', $lang).": <a href=\"#menu\" title=\"".translate('iface', 'ret_menu', $lang)."\">".translate('iface', 'menu', $lang)."</a></legend><ul>";
		$lab_all_lic = translate('iface', 'all_licenses', $lang);
		if (!isset($_SESSION['lic'])) {
			echo "<li><b>{$lab_all_lic}<span> ({$nbr_apps})</span></b></li>";
		} else {
			echo "<li><a href=\"?prop=init\" title=\"".translate('iface', 'alt_lic_link', $lang).": {$lab_all_lic}\">{$lab_all_lic}</a><span> ({$nbr_apps})</span></li>";
		};
		reset($licenses);
		while (false !== ($lic = current($licenses))) {
			$name_lic = translate('lic', key($licenses), $lang);
			echo "<li>";
			if (isset($_SESSION['lic']) && key($licenses) == $_SESSION['lic']) {
				echo "<b>{$name_lic}<span> (".count($lic).")</span></b>";
			} else {
				echo "<a href=\"?prop=lic&amp;val=".urlencode(key($licenses))."\" title=\"".translate('iface', 'alt_lic_link', $lang).": {$name_lic}\">{$name_lic}</a><span> (".count($lic).")</span>";
			};
			echo "</li>";
			next($licenses);
		};
		echo "</ul></fieldset>";
	};
};	
//}}}
function build_menu($lang) { //{{{
	echo "<fieldset id=\"menu\"><legend>".translate('iface', 'menu', $lang)."</legend><ul>";
	echo "<li><a href=\"#categories\" title=\"".translate('iface', 'browse_cat', $lang)."\">".translate('iface', 'categories', $lang)."</a></li>";
	echo "<li><a href=\"#licenses\" title=\"".translate('iface', 'browse_lic', $lang)."\">".translate('iface', 'license', $lang)."</a></li>";
	echo "<li><a href=\"#applist\" title=\"".translate('iface', 'access_applist', $lang)."\">".translate('iface', 'applist', $lang)."</a></li>";
	echo "</ul></fieldset>";
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
		build_headers(translate('iface', 'error_label', $lang), translate('iface', 'error_message', $lang));
		build_footers();
		exit;
	} else {
		$repos = unserialize(file_get_contents(REPOS_FILE));
		$categories = (is_file(CAT_FILE)) ? unserialize(file_get_contents(CAT_FILE)) : cache_categories($repos['list']);
		$relations = (is_file(REL_FILE)) ? unserialize(file_get_contents(REL_FILE)) : cache_relations($repos['list']);
		$licenses = (is_file(LIC_FILE)) ? unserialize(file_get_contents(LIC_FILE)) : cache_licenses($repos['list']);
	};
} else {
	$hash = hash_file(HASH_ALGO, DATA);
	if ((is_file(MANIFEST) && $hash != file_get_contents(MANIFEST)) || !is_file(REPOS_FILE)) {
		$data = build_cache_data($hash);
		$repos = $data['repos'];
		$categories = $data['cat'];
		$relations = $data['rel'];
		$licenses = $data['lic'];
	} else {
		file_put_contents(MANIFEST, $hash);
		$repos = unserialize(file_get_contents(REPOS_FILE));
		$categories = (is_file(CAT_FILE)) ? unserialize(file_get_contents(CAT_FILE)) : cache_categories($repos['list']);
		$relations = (is_file(REL_FILE)) ? unserialize(file_get_contents(REL_FILE)) : cache_relations($repos['list']);
		$licenses = (is_file(LIC_FILE)) ? unserialize(file_get_contents(LIC_FILE)) : cache_licenses($repos['list']);
	};
};
//}}}
$liste = build_list($repos['list'], apply_filters($relations, $categories, $licenses));
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
if (isset($_REQUEST['getSheet'])) {
	$sheet = $_REQUEST['getSheet'];
	if (in_array($sheet, $repos['list'])) {
		decore_app($sheet, $lang);
	} else {
		echo "<fieldset><legend>".translate('iface', 'error_label', $lang)."</legend>";
		echo translate('iface', 'error_message', $lang)."</fieldset>";
	};
} else {
	build_menu($lang);
	build_tagcloud_categories($relations, $lang, $repos['nbr']);
	build_tagcloud_licenses($licenses, $lang, $repos['nbr']);
	build_pager($page, ceil(count($liste) / RECORDS_PER_PAGE), $lang);
	decore_applist($tampon, $lang);
	build_pager($page, ceil(count($liste) / RECORDS_PER_PAGE), $lang);
};
build_footers();
?>
