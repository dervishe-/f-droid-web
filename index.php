<?php
/**
 *	vim: foldmarker{{{,}}}
 *
 *	@author A. Keledjian	<dervishe@yahoo.fr>
 *	@copyright Association Française des Petits Débrouillards
 *	@license http://opensource.org/licenses/LGPL-3.0 GNU Public License
 *	@version 0.1
 *
 *	Lightweight website for f-droid server
 *
 */
session_start();
//{{{ Configuration
//{{{ DIRECTORIES
define('ROOT', dirname(__FILE__));
define('SOCIAL_DIR', 'Media/images/social_icons/');
define('ICONS_DIR', 'icons-320');
define('ICONS_DIR_ABSTRACT', 'icons-160');
define('QRCODES_DIR', 'Media/images/qrcodes');
define('LANG', 'lang');
define('CACHE', ROOT.DIRECTORY_SEPARATOR.'cache');
define('APP_CACHE', CACHE.DIRECTORY_SEPARATOR.'app_files');//}}}
//{{{ FILES
define('CATEGORIES', ROOT.DIRECTORY_SEPARATOR.'categories.txt');
define('DATA', ROOT.DIRECTORY_SEPARATOR.'index.xml');
define('REPOS_FILE', CACHE.DIRECTORY_SEPARATOR.'repository');
define('CAT_FILE', CACHE.DIRECTORY_SEPARATOR.'categories'); // store categories as an array
define('REL_FILE', CACHE.DIRECTORY_SEPARATOR.'relations'); // store relations between categories and apps as an array
define('LIC_FILE', CACHE.DIRECTORY_SEPARATOR.'licenses'); // store used licenses as an array
define('LAST_FILE', CACHE.DIRECTORY_SEPARATOR.'last_apps'); // store last apps id as an array
define('WORD_FILE', CACHE.DIRECTORY_SEPARATOR.'words'); // store used words as an array
define('MANIFEST', CACHE.DIRECTORY_SEPARATOR.'Manifest'); // store index.xml hash
define('FEED_NAME', "last_app.atom");
define('REPOS_QRCODE', "Media/images/repos_qrcode.png"); //}}}
//{{{ PARAMETERS
define('FLATTR_SCHEME', 'https://flattr.com/thing/');
define('HASH_ALGO', 'whirlpool');
define('HASH_REPOS_PUBKEY', 'sha256');
define('USE_QRCODE', true);
define('USE_FEEDS', true);
define('USE_SOCIAL', true);
define('FEED_AUTHOR', "Your feed's author here");
define('NUMBER_LAST_APP', 10);
define('RECORDS_PER_PAGE', 12);
define('NUMBER_PAGES', 9);		// Fixe the number of appearing page numbers in the pager
define('DEFAULT_LANG', 'fr');	// Fixe the localization of the UI
define('LOCALIZATION', 'en');	// Fixe the localization of the search (mainly related to the languages in which the apps are describes)
define('MSG_FOOTER', "Your footer's message here");//}}}
// ALLOWED VALUES
$formats = array('json' => 1);
//}}}
//{{{ Library
function build_structure($_xml) { //{{{
	$repos = array();
	$repos['name'] = (string) $_xml->repo['name'];
	$repos['icon'] = (string) $_xml->repo['icon'];
	$repos['desc'] = (string) $_xml->repo->description;
	$repos['url'] = (string) $_xml->repo['url'];
	$repos['pubkey'] = (string) $_xml->repo['pubkey'];
	$repos['timestamp'] = (string) $_xml->repo['timestamp'];
	$repos['list'] = array();
	foreach($_xml->application as $app) { $repos['list'][] = (string) $app->id; };
	$repos['nbr'] = count($repos['list']);
	file_put_contents(REPOS_FILE, serialize($repos));
	if (USE_QRCODE) {
		include_once('phpqrcode/phpqrcode.php');
		$url = "{$repos['url']}?fingerprint=".hash(HASH_REPOS_PUBKEY, hex2bin($repos['pubkey']));
		QRCode::png($url, REPOS_QRCODE);
	};
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
	$application['tracker'] = (string) $app->tracker;
	$application['requirements'] = (string) $app->requirements;
	$application['donate'] = (string) $app->donate;
	$application['flattr'] = (string) $app->flattr;
	$application['bitcoin'] = (string) $app->bitcoin;
	$application['antifeatures'] = (string) $app->antifeatures;
	$packages = array();
	foreach ($app->package as $pkg) {
		$package = array();
		$package['version'] = (string) $pkg->version;
		$package['apkname'] = (string) $pkg->apkname;
		$package['size'] = (int) $pkg->size;
		$package['added'] = (string) $pkg->added;
		$package['permissions'] = (string) $pkg->permissions;
		$package['sdkver'] = (string) $pkg->sdkver;
		$package['hash'] = array('type'=> (string) $pkg->hash['type'], 'value' => (string) $pkg->hash);
		$packages[] = $package;
	};
	usort($packages, 'sort_package');
	$application['packages'] = $packages;
	file_put_contents(APP_CACHE.DIRECTORY_SEPARATOR.$application['id'], serialize($application));
	return $application;
};//}}}
function sort_package($pkg1, $pkg2) { //{{{
	if ($pkg1['version'] == $pkg2['version']) return 0;
	if ($pkg1['version'] < $pkg2['version']) return 1;
	return -1;
};//}}}
function build_lang_selector($lang_label, $lang) { //{{{
	$bloc = "<div id=\"lang\"><span>".translate('iface', 'language', $lang).": </span><ul>";
	if ($dh = opendir(LANG)) {
		while (false !== ($dir = readdir($dh))) {
			$rep_lang = LANG.DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR;
			if (is_file($rep_lang.'lang.php')) {
				$bloc .= ($dir != $lang_label) ? 
					"<li>
						<a href=\"?lang={$dir}\" title=\"".translate('lang', $dir, $lang)."\">
							<img alt=\"".translate('lang', $dir, $lang)."\" src=\"{$rep_lang}flag.png\" />
						</a>
					</li>" : "<li><span	><img alt=\"".translate('lang', $dir, $lang)."\" src=\"{$rep_lang}flag.png\" /></span></li>";
			};
		};
		closedir($dh);
	};
	$bloc .= '</ul></div>';
	return $bloc;
};//}}}
function build_pager($current_page, $number_page, $lang) { //{{{
	$bloc = "<div><span>".translate('iface', 'page', $lang).":</span><ul>";
	$nb = NUMBER_PAGES - 1;
	if ($number_page <= NUMBER_PAGES) {
		$page_init = 1;
	} elseif ($current_page >= $number_page - floor($nb / 2)) {
		$page_init = $number_page - $nb;
	} else {
		$page_init = max(array($current_page - floor($nb / 2), 1));
	};
	if ($number_page <= NUMBER_PAGES) {
		$page_end = $number_page;
	} elseif ($current_page <= floor($nb / 2)) {
		$page_end = NUMBER_PAGES;
	} else {
		$page_end = min(array($current_page + floor($nb / 2) + ($nb % 2), $number_page));
	};
	if ($current_page > floor($nb / 2) + 1) {
		$bloc .= "
		<li>
			<a href=\"?page=1\" title=\"".translate('iface', 'go_to_page', $lang)." 1\">
				1
			</a>
		</li>";
	};
	if ($page_init > 2) $bloc .= "<li> .. </li>";
	for ($i = $page_init; $i < $current_page; $i++) { 
		$bloc .= "
		<li>
			<a href=\"?page={$i}\" title=\"".translate('iface', 'go_to_page', $lang)." {$i}\">
				{$i}
			</a>
		</li>";
	};
	$bloc .= "<li><span>{$current_page}</span></li>";
	for ($i = $current_page + 1; $i <= $page_end; $i++) { 
		$bloc .= "
			<li>
				<a href=\"?page={$i}\" title=\"".translate('iface', 'go_to_page', $lang)." {$i}\">
				{$i}
				</a>
			</li>";
	};
	if ($page_end < $number_page - 1) $bloc .= "<li> .. </li>";
	if ($current_page < $number_page - ceil($nb / 2)) {
		$bloc .= "
		<li>
			<a href=\"?page={$number_page}\" title=\"".translate('iface', 'go_to_page', $lang)." {$number_page}\">
				{$number_page}
			</a>
		</li>";
	};
	$bloc .= "</ul></div>";
	return $bloc;
};//}}}
function build_form_search($lang, $value='') { //{{{
	return "
<article id=\"search\">
	<header>
		<h2>".translate('iface', 'form_val', $lang)."</h2> 
	</header>
	<form method=\"POST\" action=\"?search\">
		<label for=\"word_search\">".translate('iface', 'word_search', $lang)."</label>
		<input id=\"word_search\" type=\"search\" name=\"val\" title=\"".translate('iface', 'form_field', $lang)."\" value=\"{$value}\" />
		<input type=\"submit\" value=\"".translate('iface', 'form_val', $lang)."\" title=\"".translate('iface', 'form_val', $lang)."\" />
	</form>
	</article>";
};//}}}
function build_reset($lang, $value='') { //{{{
	return "
<article id=\"reset\">
	<header>
		<h2>".translate('iface', 'reset', $lang)."</h2> 
	</header>
	<form method=\"POST\" action=\"?reset\">
		<input type=\"submit\" value=\"".translate('iface', 'reset', $lang)."\" title=\"".translate('iface', 'form_reset', $lang)."\" />
	</form>
	</article>";
};//}}}
function build_cache_data($hash) { //{{{
	$dh = opendir(CACHE);
	while (false !== ($file = readdir($dh))) {
		if (is_file(APP_CACHE.DIRECTORY_SEPARATOR.$file) && $file != '.htaccess')
			unlink(APP_CACHE.DIRECTORY_SEPARATOR.$file);
	};
	closedir($dh);
	file_put_contents(MANIFEST, $hash);
	$_xml = simplexml_load_file(DATA);
	$repos = build_structure($_xml);
	$words = cache_words($repos['list']);
	$cat = cache_categories($repos['list']);
	$rel = cache_relations($repos['list']);
	$lic = cache_licenses($repos['list']);
	$lst = cache_lastapps($repos);
	return array('repos'=>$repos, 'cat'=>$cat, 'rel'=>$rel, 'lic'=>$lic, 'wrd'=>$words, 'lst'=>$lst);
};
//}}}
function build_tools($relations, $licenses, $lang, $nbr) { //{{{
	$value = (isset($_SESSION['words'])) ? implode('+', $_SESSION['words']) : '';
	return "<aside id=\"tools\" role=\"search\">".
			build_reset($lang).
			build_form_search($lang, $value).
			decore_categories($relations, $lang, $nbr).
			decore_licenses($licenses, $lang, $nbr).
			"</aside>";
};
//}}}
function build_atom($repos, $list) { //{{{
	$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != '') ? 'https://' : 'http://';
	$icon = "{$_SERVER['SERVER_NAME']}/Media/images/{$repos['icon']}";
	$date = date('c', $repos['timestamp']);
	$feed = "";
	$i = 0;
	$_xml = simplexml_load_file(DATA);
	foreach ($list as $app) {
		if (!is_file(APP_CACHE.DIRECTORY_SEPARATOR.$app) || !is_readable(APP_CACHE.DIRECTORY_SEPARATOR.$app)) {
			$app = build_app($_xml, $app);
		} else {
			$app = unserialize(file_get_contents(APP_CACHE.DIRECTORY_SEPARATOR.$app));
		};
		$time_comp = explode('-', $app['updated']);
		$date_app = date('c', mktime(0, $i, 0, $time_comp[1], $time_comp[2], $time_comp[0]));
		$i++;
		$feed .= "
<entry>
	<title>{$app['name']}</title>
	<link href=\"{$scheme}{$_SERVER['SERVER_NAME']}/?sheet={$app['id']}\" />
	<id>{$scheme}{$_SERVER['SERVER_NAME']}/{$app['packages'][0]['apkname']}</id>
	<updated>{$date_app}</updated>
	<summary>{$app['summary']}</summary>
</entry>";
	};
	$bloc = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>
<feed xmlns=\"http://www.w3.org/2005/Atom\">
	<title>{$repos['name']}</title>
	<subtitle>{$repos['desc']}</subtitle>
	<link rel=\"self\" href=\"{$scheme}{$_SERVER['SERVER_NAME']}/".FEED_NAME."\" />
	<updated>{$date}</updated>
	<id>{$scheme}{$_SERVER['SERVER_NAME']}/</id>
	<logo>{$icon}</logo>
	<author>
		<name>".FEED_AUTHOR."</name>
	</author>
	{$feed}
</feed>";
	file_put_contents(FEED_NAME, $bloc);
};
//}}}
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
function cache_lastapps($repos) { //{{{
	$last = array();
	if (is_file(DATA) && is_readable(DATA) && ($data = simplexml_load_file(DATA)) !== false) {
		foreach ($data->application as $app) {
			$idx = (string) $app->lastupdated;
			if (!isset($last[$idx])) $last[$idx] = array();
			$last[$idx][] = (string) $app->id;
		};
	} elseif (count($repos['list']) > 0) {		// Fallback: if DATA is not present, then we use app file stored in cache
		foreach ($repos['list'] as $app) {
			if (is_file(CACHE.DIRECTORY_SEPARATOR.$app) && is_readable(CACHE.DIRECTORY_SEPARATOR.$app)) {
				$app = unserialize(file_get_contents($app));
				$idx = $app['updated'];
				if (!isset($last[$idx])) $last[$idx] = array();
				$last[$idx][] = $app['id'];
			};
		};
	};
	$result = array();
	if (count($last) > 0) {
		krsort($last);
		reset($last);
		while (count($result) <= 10 && current($last)) {
			$result = array_merge($result, current($last));
			next($last);
		};
		$result = array_slice($result, 0, NUMBER_LAST_APP);
	};
	if (USE_FEEDS) build_atom($repos, $result);
	if (count($result) > 0) file_put_contents(LAST_FILE, serialize($result));
	return $result;
};
//}}}
function cache_words($repos) { //{{{	Fields to search: name, summary, description
	$wd = array();
	if (is_file(DATA) && is_readable(DATA) && ($data = simplexml_load_file(DATA)) !== false) {
		include_once(LANG.DIRECTORY_SEPARATOR.LOCALIZATION.DIRECTORY_SEPARATOR."stopwords.php"); // $stopwords loading
		foreach ($data->application as $app) {
			$name = (string) $app->name;
			$summary = (string) $app->summary;
			$desc = (string) $app->desc;
			$dico = sanitize($name.' '.$summary.' '.$desc, $stopwords);
			foreach ($dico as $word) {
				if (!isset($wd[$word])) $wd[$word] = array();
				$wd[$word][] = (string) $app->id;
			};
		};
	} elseif (count($repos) > 0) {		// Fallback: if DATA is not present, then we use app file stored in cache
		include_once(LANG.DIRECTORY_SEPARATOR.LOCALIZATION."stopwords.php"); // $stopwords loading
		foreach ($repos as $app) {
			if (is_file(CACHE.DIRECTORY_SEPARATOR.$app) && is_readable(CACHE.DIRECTORY_SEPARATOR.$app)) {
				$app = unserialize(file_get_contents($app));
				$name = $app['name'];
				$summary = $app['summary'];
				$desc = $app['desc'];
				$dico = sanitize($name.' '.$summary.' '.$desc, $stopwords);
				foreach ($dico as $word) {
					if (!isset($wd[$word])) $wd[$word] = array();
					$wd[$word][] = (string) $app->id;
				};
			};
		};
	};
	if (count($wd) > 0) file_put_contents(WORD_FILE, serialize($wd));
	return $wd;
};
//}}}
function translate($section, $item, $lang) { //{{{
	return (isset($lang[$section][$item])) ? $lang[$section][$item] : $item;
};
//}}}
function translator($section, $lang) { //{{{
	return function($item) use($lang, $section) {
		return translate($section, $item, $lang);
	};
};
//}}}
function decore_app($app_id, $lang) { //{{{
	if (is_file(APP_CACHE.DIRECTORY_SEPARATOR.$app_id) && is_readable(APP_CACHE.DIRECTORY_SEPARATOR.$app_id)) { //{{{
		$app = unserialize(file_get_contents(APP_CACHE.DIRECTORY_SEPARATOR.$app_id));
	} else {
		if (($data = simplexml_load_file(DATA)) !== false) {
			$app = build_app($data, $app_id);
			$data = null;
		} else {
			return false;
		};
	};//}}}
	$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != '') ? 'https://' : 'http://';
	if (USE_QRCODE) { //{{{
		include_once('phpqrcode/phpqrcode.php');
		$qrcode = QRCODES_DIR.DIRECTORY_SEPARATOR.$app['id'].".png";
		if (!is_file($qrcode)) {
			QRCode::png("{$scheme}{$_SERVER['SERVER_NAME']}/{$app['packages'][0]['apkname']}", $qrcode);
		};
		$dl_label = translate('iface', 'download', $lang);
		$tag_qrcode = "
		<aside id=\"download\">
			<img src=\"{$qrcode}\" alt=\"QR-Code {$app['name']}\" title=\"QR-Code: {$dl_label} {$app['name']}\" />
			<a href=\"{$app['packages'][0]['apkname']}\" title=\"{$dl_label} {$app['name']}\">{$dl_label}</a></aside>";
	} else {
		$tag_qrcode = "<aside id=\"download\"><a title=\"{$dl_label} {$app['name']}\" href=\"{$app['packages'][0]['apkname']}\">{$dl_label}</a></aside>";
	};//}}}
	$icon = ICONS_DIR."/{$app['icon']}";
	if ($app['updated'] != $app['added']) { //{{{
		$label = translate('iface', 'updated', $lang);
		$date = $app['updated'];
	} else {
		$label = translate('iface', 'added', $lang);
		$date = $app['added'];
	};
	$vers_label = translate('iface', 'version', $lang);
	$version = "
	<div title=\"{$vers_label}\">
		<span>{$vers_label}: </span>
		<span>{$app['packages'][0]['version']}</span> - <span>{$label}</span>: 
		<span>{$date}</span>
	</div>";//}}}
	$lic_label = translate('iface', 'license', $lang);
	$license = "<div title=\"{$lic_label}\"><span>{$lic_label}: </span><span>".translate('lic', $app['license'], $lang)."</span></div>";
	$sum_label = translate('iface', 'summary', $lang);
	$summary = "<div title=\"{$sum_label}\">{$app['summary']}</div>";
	$desc_label = translate('iface', 'desc', $lang);
	$desc = "<div title=\"{$desc_label}\" id=\"description\">{$app['desc']}</div>";
	$hash_label = translate('iface', 'hash', $lang);
	$hash = "<div title=\"{$hash_label}\" id=\"hash\">
		<span>{$hash_label} [{$app['packages'][0]['hash']['type']}]: </span>
		<span>{$app['packages'][0]['hash']['value']}</span>
	</div>";
	$reqs_label = translate('iface', 'requirements', $lang);
	$requirements = (strlen($app['requirements']) > 0) ? 
		"<div title=\"{$reqs_label}\"><span>{$reqs_label}: </span><span>{$app['requirements']}</span></div>" : '';
	$size = $app['packages'][0]['size']; //{{{
	$size_label = translate('iface', 'size', $lang);
	if (($size / 1048572) > 1) {
		$size /= 1048572;
		$size = "<div title=\"{$size_label}\"><span>{$size_label}: </span><span>".round($size, 2)." MB</span></div>";
	} else {
		$size /= 1024;
		$size = "<div title=\"{$size_label}\"><span>{$size_label}: </span><span>".round($size, 2)." kB</span></div>";
	};//}}}
	$categories = $app['categories']; //{{{
	$translate_cat = translator('cat', $lang);
	$cats = (strlen($categories) > 0 && $categories != 'None') ? "<ul><li>".implode('</li><li>', array_map($translate_cat, explode(',', $categories)))."</li></ul>" : '';
	$cats_span = translate('iface', 'categories', $lang);
	$categories = "<aside id=\"used_categories\" title=\"{$cats_span}\"><span>{$cats_span}: </span>{$cats}</aside>";//}}}
	$permissions = $app['packages'][0]['permissions']; //{{{
	$translate_perm = translator('perms', $lang);
	$perms_span = translate('iface', 'permissions', $lang);
	$perms = '';
	if (strlen($permissions) > 0) {
		$permissions = explode(',', $permissions);
		reset($permissions);
		while ($perm = current($permissions)) {
			$perms .= "
		<li>
			<a title=\"{$perms_span}: {$perm}\" href=\"http://developer.android.com/reference/android/Manifest.permission.html#".$perm."\">".
			$translate_perm($perm).
			"</a></li>";
			next($permissions);
		};
		$perms = "<ul>{$perms}</ul>";
		$permissions = "<aside id=\"perms\" title=\"{$perms_span}\"><span>{$perms_span}: </span>{$perms}</aside>";
	} else {
		$permissions = '';//}}}
	};
	$afeatures = $app['antifeatures']; //{{{
	$translate_feat = translator('afeat', $lang);
	$afeat = (strlen($afeatures) > 0) ? "<ul><li>".implode('</li><li>', array_map($translate_feat, explode(',', $afeatures)))."</li></ul>" : '';
	$afeat_span = translate('iface', 'antifeatures', $lang);
	$afeatures = "<aside id=\"antifeatures\" title=\"{$afeat_span}\"><span>{$afeat_span}: </span>{$afeat}</aside>";//}}}
	$donate = ""; //{{{
	$don_label = translate('iface', 'donate', $lang);
	$flattr_label = translate('iface', 'flattr', $lang);
	$bc_label = translate('iface', 'bitcoin', $lang);
	if ($app['donate'] != '') 
		$donate .= "<a title=\"{$don_label}\" href=\"{$app['donate']}\">{$don_label}</a>";
	if ($app['flattr'] != '') 
		$donate .= "<a title=\"{$don_label}: {$flattr_label}\" href=\"".FLATTR_SCHEME."{$app['flattr']}\">{$flattr_label}</a>";
	if ($app['bitcoin'] != '') 
		$donate .= "<div title=\"{$don_label}: {$bc_label}\"><span>{$bc_label}</span><span>{$app['bitcoin']}</span></div>";
	if ($donate != '') $donate = "<aside id=\"donate_app\">{$donate}</aside>"; //}}}
	$dev_span = translate('iface', 'devfeatures', $lang);
	$sdk_span = translate('iface', 'sdkver', $lang);
	$dev_body = "<div><span>{$sdk_span}: </span><span>v{$app['packages'][0]['sdkver']}</span></div>";
	if ($app['web'] != '') $dev_body .= "<a href=\"{$app['web']}\">".translate('iface', 'web', $lang)."</a>";
	if ($app['tracker'] != '') $dev_body .= "<a href=\"{$app['tracker']}\">".translate('iface', 'tracker', $lang)."</a>";
	if ($app['source'] != '') $dev_body .= "<a href=\"{$app['source']}\">".translate('iface', 'sources', $lang)."</a>";
	$blockdev = "<aside id=\"block_dev\">{$dev_body}</aside>";
	if (USE_SOCIAL) { //{{{
		$social_msg = urlencode("{$app['name']}: {$app['summary']}");
		$social_alt = translate('iface', 'share', $lang);
		$social_url = "{$scheme}{$_SERVER['SERVER_NAME']}/?sheet={$app['id']}";
		$social = "
	<aside id=\"social_links\">
		<a title=\"{$social_alt} Diaspora\" href=\"http://sharetodiaspora.github.io/?title={$social_msg}&amp;url={$social_url}\">
			<img alt=\"{$social_alt} Diaspora\" src=\"".SOCIAL_DIR."Diaspora.ico\" />
		</a>
		<a title=\"{$social_alt} Facebook\" href=\"https://www.facebook.com/sharer.php?u={$social_msg}&amp;t={$social_url}\">
			<img alt=\"{$social_alt} Facebook\" src=\"".SOCIAL_DIR."Facebook.ico\" />
		</a>
		<a title=\"{$social_alt} Google+\" href=\"https://plus.google.com/share?url={$social_url}\">
			<img alt=\"{$social_alt} Google+\" src=\"".SOCIAL_DIR."GooglePlus.ico\" />
		</a>
		<a title=\"{$social_alt} Twitter\" href=\"https://twitter.com/intent/tweet?text={$social_msg}&amp;url={$social_url}\">
			<img alt=\"{$social_alt} Twitter\" src=\"".SOCIAL_DIR."Twitter.ico\" />
		</a>
		</aside>";
	} else {
		$social = '';
	};//}}}
	$nb_ver = count($app['packages']);
	$oldversion = '';
	if ($nb_ver > 1) {
		$oldlist = '';
		reset($app['packages']);
		next($app['packages']);
		while ($pkg = current($app['packages'])) {
			$size_pkg = $pkg['size']; //{{{
			if (($size_pkg / 1048572) > 1) {
				$size_pkg /= 1048572;
				$size_pkg = "<div title=\"{$size_label}\"><span>{$size_label}: </span><span>".round($size_pkg, 2)." MB</span></div>";
			} else {
				$size_pkg /= 1024;
				$size_pkg = "<div title=\"{$size_label}\"><span>{$size_label}: </span><span>".round($size_pkg, 2)." kB</span></div>";
			};//}}}
			$perm_pkg = $pkg['permissions']; //{{{
			$perms_sdk = '';
			if (strlen($perm_pkg) > 0) {
				$perm_pkg = explode(',', $perm_pkg);
				reset($perm_pkg);
				while ($perm = current($perm_pkg)) {
					$perms_sdk .= "
					<li>
						<a aria-describedby=\"perms_{$pkg['version']}\" title=\"{$perms_span}: {$perm}\" href=\"http://developer.android.com/reference/android/Manifest.permission.html#".$perm."\">".
						$translate_perm($perm).
						"</a></li>";
					next($perm_pkg);
				};
				$perms_sdk = "<ul>{$perms_sdk}</ul>";
				$perm_pkg = "<div id=\"perms_{$pkg['version']}\" title=\"{$perms_span}\"><span>{$perms_span}: </span>{$perms_sdk}</div>";
			} else {
				$perm_pkg ='';
			};//}}}
			$label = translate('iface', 'added', $lang);
			$date_pkg = $pkg['added'];
			$tag_dl_pkg = "<a title=\"{$dl_label} {$app['name']}\" href=\"{$pkg['apkname']}\" aria-describedby=\"{$pkg['version']}\">{$dl_label}</a>";
			$version_pkg = "
			<div title=\"{$vers_label}\">
				<span>{$vers_label}: </span>
				<span>{$pkg['version']}</span> - <span>{$label}</span>: 
				<span>{$date_pkg}</span>
			</div>";
			$hash_pkg = "
			<div title=\"{$hash_label} {$pkg['apkname']}\">
				<span>{$hash_label} [{$pkg['hash']['type']}]: </span>
				<span>{$pkg['hash']['value']}</span>
			</div>";
			$sdk_pkg = "<div><span>{$sdk_span}: </span><span>v{$pkg['sdkver']}</span></div>";
			$oldlist .= "
				<div id=\"{$pkg['version']}\">
					{$tag_dl_pkg}
					{$version_pkg}
					{$sdk_pkg}
					{$size_pkg}
					{$hash_pkg}
					{$perm_pkg}
				</div>";
			next($app['packages']);
		};
		$oldversion = "
		<aside id=\"oldversions\">
			{$oldlist}
		</aside>
		";
	};
	return "
<article id=\"appsheet\">
	<header>
		<h2>
			<img src=\"{$icon}\" alt=\"icone {$app['name']}\" />
			<span>{$app['name']}</span>
			<a href=\"index.php\">".translate('iface', 'back', $lang)."</a>
		</h2>
		{$summary}
		{$social}
	</header>
	<div id=\"details\">
	{$tag_qrcode}
	{$size}
	{$version}
	{$license}
	{$requirements}
	</div>
	{$desc}
	<div id=\"misc\">
	".(($cats != '') ? $categories : '')."
	".(($perms != '') ? $permissions : '')."
	</div>
	".(($afeat != '') ? $afeatures : '')."
	{$hash}
	<div>
	{$donate}
	{$blockdev}
	{$oldversion}
	</div>
</article>";
};
//}}}
function decore_app_json($app_id, $light=false) { //{{{
	if (is_file(APP_CACHE.DIRECTORY_SEPARATOR.$app_id) && is_readable(APP_CACHE.DIRECTORY_SEPARATOR.$app_id)) {
		$app = unserialize(file_get_contents(APP_CACHE.DIRECTORY_SEPARATOR.$app_id));
	} else {
		if (($data = simplexml_load_file(DATA)) !== false) {
			$app = build_app($data, $app_id);
			$data = null;
		} else {
			return false;
		};
	};
	if ($light) {
		$buffer = array();
		$buffer['id'] = $app['id'];
		$buffer['name'] = $app['name'];
		$buffer['summary'] = $app['summary'];
		$buffer['version'] = $app['packages'][0]['version'];
		$buffer['size'] = $app['packages'][0]['size'];
		$buffer['updated'] = $app['updated'];
		$app = $buffer;
	};
	return $app;
}//}}}
function decore_app_light($app_id, $lang) { //{{{
	if (is_file(APP_CACHE.DIRECTORY_SEPARATOR.$app_id) && is_readable(APP_CACHE.DIRECTORY_SEPARATOR.$app_id)) {
		$app = unserialize(file_get_contents(APP_CACHE.DIRECTORY_SEPARATOR.$app_id));
	} else {
		if (($data = simplexml_load_file(DATA)) !== false) {
			$app = build_app($data, $app_id);
			$data = null;
		} else {
			return false;
		};
	};
	$icon = ICONS_DIR_ABSTRACT.DIRECTORY_SEPARATOR.$app['icon'];
	if ($app['updated'] == $app['added']) {
		$version = "
		<li>
			<span>".translate('iface', 'version', $lang).":</span>
			<span>{$app['packages'][0]['version']}</span> - <span>".
			translate('iface', 'added', $lang).":</span> 
			<span>{$app['added']}</span>
		</li>";
	} else {
		$version = "
		<li>
			<span>".translate('iface', 'version', $lang).":</span>
			<span>{$app['packages'][0]['version']}</span> - <span>".
			translate('iface', 'updated', $lang).":</span> 
			<span>{$app['updated']}</span>
		</li>";
	};
	$sum_label = translate('iface', 'summary', $lang);
	$summary = "<span id=\"desc_{$app['id']}\" title=\"{$sum_label}\">{$app['summary']}</span>";
	$size = $app['packages'][0]['size'];
	if (($size / 1048572) > 1) {
		$size /= 1048572;
		$size = "<li><span>".translate('iface', 'size', $lang).":</span><span>".round($size, 2)." MB</span></li>";
	} else {
		$size /= 1024;
		$size = "<li><span>".translate('iface', 'size', $lang).":</span><span>".round($size, 2)." kB</span></li>";
	};
	$block = "
<article id=\"".str_replace(array('.', ' ', '_'), '-', $app['id'])."\">
	<header>
		<h3>
			<img src=\"{$icon}\" alt=\"icone {$app['name']}\" />
			<span>{$app['name']}</span>
		</h3>
		{$summary}
	</header>
	<div>
		<a href=\"{$app['packages'][0]['apkname']}\" title=\"".
		translate('iface', 'download', $lang).
		": {$app['name']}\" aria-describedby=\"desc_{$app['id']}\">".
		translate('iface', 'download', $lang).
		"</a>
		<a href=\"?sheet={$app['id']}\" title=\"".
		translate('iface', 'sheet', $lang).
		": {$app['name']}\" aria-describedby=\"desc_{$app['id']}\">".
		translate('iface', 'sheet', $lang).
		"</a>
	</div>
	<ul>
	{$size}
	{$version}
	</ul>
</article>";
	return $block;
};
//}}}
function decore_app_abstract($app_id, $lang) { //{{{
	if (is_file(APP_CACHE.DIRECTORY_SEPARATOR.$app_id) && is_readable(APP_CACHE.DIRECTORY_SEPARATOR.$app_id)) {
		$app = unserialize(file_get_contents(APP_CACHE.DIRECTORY_SEPARATOR.$app_id));
	} else {
		if (($data = simplexml_load_file(DATA)) !== false) {
			$app = build_app($data, $app_id);
			$data = null;
		} else {
			return false;
		};
	};
	$icon = ICONS_DIR_ABSTRACT.DIRECTORY_SEPARATOR.$app['icon'];
	$license = "
	<div title=\"".translate('iface', 'license', $lang).": ".translate('lic', $app['license'], $lang)."\">
		<span>".translate('iface', 'license', $lang).": </span>
		<span>".translate('lic', $app['license'], $lang)."</span>
	</div>";
	return "
	<div id=\"last_".str_replace(array('.', ' '), '_', $app['id'])."\">
		<img src=\"{$icon}\" alt=\"icone {$app['name']}\" />
		<div>
			<a aria-describedby=\"lastapplist\" href=\"?sheet={$app['id']}\" title=\"".
			translate('iface', 'sheet', $lang).": {$app['name']}\">{$app['name']}</a>
			{$license}
		</div>
	</div>";
};
//}}}
function decore_applist($tampon, $lang, $nbr_app, $page) { //{{{
	$pager = build_pager($page, ceil($nbr_app / RECORDS_PER_PAGE), $lang);
	$block = "
<section id=\"applist\">
	<header>
		<h2>".translate('iface', 'applist', $lang).": 
			<span title=\"".translate('iface', 'nbr_result', $lang).
			": {$nbr_app}\">({$nbr_app})</span>
		</h2>
	</header>";
	if ($nbr_app > 0) {
		foreach($tampon as $app) { $block .= decore_app_light($app, $lang); };
		$block .= "<footer>";
		$block .= build_pager($page, ceil($nbr_app / RECORDS_PER_PAGE), $lang);
		$block .= "</footer>";
	} else {
		$block .= '<p>'.translate('iface', 'no_result', $lang).'</p>';
	};
	$block .= "
</section>
";
	return $block;
};
//}}}
function decore_lastapplist($list, $lang) { //{{{
	$content = '';
	if (count($list) > 0) {
		foreach($list as $app) { $content .= decore_app_abstract($app, $lang); };
	} else {
		$content .= '<p>'.translate('iface', 'no_apps', $lang).'</p>';
	};
	return "
<aside id=\"lastapplist\" role=\"complementary\">
	<header>
		<h2>".translate('iface', 'lastapplist', $lang)."</h2>
	</header>
	{$content}
</aside>
";
};
//}}}
function decore_categories($relations, $lang, $nbr_apps) { //{{{
	$bloc = "";
	if (count($relations) > 0) {
		$bloc .= "
		<article id=\"categories\">
			<header>
				<h2>".translate('iface', 'categories', $lang)."</h2>
			</header>
			<form method=\"POST\" action=\"index.php?search\">
			<fieldset>
				<legend>".translate('iface', 'categories_list', $lang)."</legend>
				<ul>";
		$lab_all_cat = translate('iface', 'all_categories', $lang);
		$flagCheck = (!isset($_SESSION['categories'])) ? "checked=\"checked\"" : '';
		$bloc .= "
		<li>
			<input type=\"checkbox\" id=\"all_cat\" name=\"cat[]\" value=\"all\" {$flagCheck} title=\"".
				translate('iface', 'alt_cat_link', $lang).": {$lab_all_cat}\" />
			<label for=\"all_cat\">{$lab_all_cat} ({$nbr_apps})</label>
		</li>";
		$tab_relations = array();
		$recorded_cats = (isset($_SESSION['categories'])) ? array_flip($_SESSION['categories']) : array();
		reset($relations);
		$i = 0;
		while (false !== ($cat = current($relations))) {
			$name_cat = translate('cat', key($relations), $lang);
			$flagCheck = (isset($recorded_cats[key($relations)])) ? "checked=\"checked\"" : '';
			$str = "
			<li>
				<input type=\"checkbox\" id=\"cat_{$i}\" name=\"cat[]\" value=\"".
					key($relations)."\" {$flagCheck} title=\"".
					translate('iface', 'alt_cat_link', $lang).": {$name_cat}\" />
				<label for=\"cat_{$i}\">{$name_cat} (".count($cat).")</label>
			</li>
			";
			$tab_relations[$name_cat] = $str;
			$i++;
			next($relations);
		};
		ksort($tab_relations);
		$bloc .= implode('', $tab_relations);
		$bloc .= "
		<li>
			<input type=\"submit\" value=\"".translate('iface', 'form_val', $lang).
				"\" title=\"".translate('iface', 'form_val', $lang).
				": ".translate('iface', 'categories', $lang).
				"\" name=\"".translate('iface', 'categories', $lang)."\" />
		</li>
		</ul></fieldset></form></article>";
		return $bloc;
	};
};	
//}}}
function decore_licenses($licenses, $lang, $nbr_apps) { //{{{
	$bloc = "";
	if (count($licenses) > 0) {
		$bloc .= "
		<article id=\"licenses\">
			<header>
				<h2>".translate('iface', 'license', $lang)."</h2>
			</header>
			<form method=\"POST\" action=\"index.php?search\">
			<fieldset>
				<legend>".translate('iface', 'license_list', $lang)."</legend>
		<ul>";
		$lab_all_lic = translate('iface', 'all_licenses', $lang);
		$flagCheck = (!isset($_SESSION['licenses'])) ? "checked=\"checked\"" : '';
		$bloc .= "
		<li>
			<input type=\"checkbox\" id=\"all_lic\" name=\"lic[]\" value=\"all\" {$flagCheck} title=\"".translate('iface', 'alt_lic_link', $lang).": {$lab_all_lic}\" />
			<label for=\"all_lic\">{$lab_all_lic} ({$nbr_apps})</label>
		</li>";
		$tab_licenses = array();
		$recorded_lics = (isset($_SESSION['licenses'])) ? array_flip($_SESSION['licenses']) : array();
		reset($licenses);
		$i = 0;
		while (false !== ($lic = current($licenses))) {
			$name_lic = translate('lic', key($licenses), $lang);
			$flagCheck = (isset($recorded_lics[key($licenses)])) ? "checked=\"checked\"" : '';
			$str = "
			<li>
				<input type=\"checkbox\" id=\"lic_{$i}\" name=\"lic[]\" value=\"".key($licenses)."\" {$flagCheck} title=\"".translate('iface', 'alt_lic_link', $lang).": {$name_lic}\" />
				<label for=\"lic_{$i}\">{$name_lic} (".count($lic).")</label>
			</li>
			";
			$tab_licenses[$name_lic] = $str;
			$i++;
			next($licenses);
		};
		ksort($tab_licenses);
		$bloc .= implode('', $tab_licenses);
		$bloc .= "
		<li>
			<input type=\"submit\" value=\"".
				translate('iface', 'form_val', $lang).
				"\" title=\"".translate('iface', 'form_val', $lang).
				": ".translate('iface', 'license', $lang).
				"\" name=\"".translate('iface', 'license', $lang)."\" />
		</li></ul></fieldset></form></article>";
		return $bloc;
	};
};	
//}}}
function decore_headers($repos, $lang_label, $lang) { //{{{
	$tag_qrcode = 
			(USE_QRCODE) ? "<img title=\"".translate('iface', 'qrcode_repo', $lang).
			"\" src=\"".REPOS_QRCODE."\" alt=\"qrcode: {$repos['name']}\" />" : '';
	$bloc = "<header role=\"banner\">
			<div>
				<img src=\"Media/images/{$repos['icon']}\" alt=\"logo: {$repos['name']}\" />
				<h1>{$repos['name']}</h1>
				{$tag_qrcode}
			</div>
			<div>{$repos['desc']}</div>";
	$bloc .= "<div><span>".translate('iface', 'last_modified', $lang).": </span><span>".date('Y-m-d', $repos['timestamp'])."</span></div>";
	$bloc .= build_lang_selector($lang_label, $lang);
	$bloc .= "</header>";
	return $bloc;
};//}}}
function sanitize($text, $stopwords) { //{{{
	$pattern_markup = "/<\/?[^>]*>/";
	$quote_list = "\"“”’«";
	$pattern_split = "/[{$quote_list}{}\[\]{}\(\)+=_*<>!?.,;:\/\s]+/";
	$pattern_surround = "/[{$quote_list}']?([^{$quote_list}']*)[{$quote_list}']?/";
	$buffer = preg_split($pattern_split, preg_replace($pattern_markup, ' ', trim(strtolower($text))));
	$result = array();
	foreach ($buffer as $key) {
		$tmp = preg_replace($pattern_surround, '\1', $key);
		if (strlen($tmp) > 2) $result[] = $tmp;
	};
	return array_unique(array_diff($result, $stopwords));
};//}}}
function sanitize_entry($words) { //{{{
	$buffer = array();
	foreach ($words as $item) $buffer[] = htmlentities($item);
	return $buffer;
};//}}}
function apply_filters($relations, $licenses, $words, $repos) { //{{{
	$flag = false;
	$candidates = array();
	//{{{ Categories
	$cat_ids = (isset($_REQUEST['cat'])) ? $_REQUEST['cat'] : 
			((isset($_SESSION['categories'])) ? $_SESSION['categories'] : array());
	if (!is_array($cat_ids)) $cat_ids = array($cat_ids);
	if (in_array('all', $cat_ids)) {
		$cat_ids = array();
		unset($_SESSION['categories']);
	};
	if (count($cat_ids) > 0) { //{{{
		$_SESSION['categories'] = $cat_ids;
		$flag |= true;
		$candidates['categories'] = array();
		foreach ($cat_ids as $key) {
			if (isset($relations[$key])) $candidates['categories'] = array_merge($candidates['categories'], $relations[$key]);
		};
		$candidates['categories'] = array_unique($candidates['categories']);
	} else {
		$candidates['categories'] = $repos;
	}; //}}}
	//}}}
	// {{{ Licenses
	$lic_ids = (isset($_REQUEST['lic'])) ? $_REQUEST['lic'] : 
			((isset($_SESSION['licenses'])) ? $_SESSION['licenses'] : array());
	if (!is_array($lic_ids)) $lic_ids = array($lic_ids);
	if (in_array('all', $lic_ids)) {
		$lic_ids = array();
		unset($_SESSION['licenses']);
	};
	if (count($lic_ids) > 0) { //{{{
		$_SESSION['licenses'] = $lic_ids;
		$flag |= true;
		$candidates['licenses'] = array();
		foreach ($lic_ids as $key) {
			if (isset($licenses[$key])) $candidates['licenses'] = array_merge($candidates['licenses'], $licenses[$key]);
		};
		$candidates['licenses'] = array_unique($candidates['licenses']);
	} else {
		$candidates['licenses'] = $repos;
	}; //}}}
	//}}}
	//{{{ Words
	$wordstofind = (isset($_REQUEST['val'])) ? $_REQUEST['val'] : 
			((isset($_SESSION['words'])) ? $_SESSION['words'] : array());
	if (!is_array($wordstofind)) $wordstofind = explode('+', $wordstofind);
	if (in_array('', $wordstofind)) {
		$wordstofind = array();
		unset($_SESSION['words']);
	};
	$candidates['words'] = $repos;
	if (count($wordstofind) > 0) { //{{{
		$wordstofind = sanitize_entry($wordstofind);
		$_SESSION['words'] = $wordstofind;
		$flag |= true;
		$registered_words = array_keys($words);
		foreach ($wordstofind as $tosearch) {
			$buffer = array();
			foreach($registered_words as $key) {
				if (strpos(trim(strtolower($key)), trim(strtolower($tosearch))) !== false) {
					$buffer = array_merge($buffer, $words[$key]);
				};
			};
			$candidates['words'] = array_intersect($candidates['words'], $buffer);
		};
		$candidates['words'] = array_unique($candidates['words']);
	} else {
		$candidates['words'] = $repos;
	}; //}}}
	//}}}
	if ($flag) {
		$list = array_intersect($candidates['categories'], $candidates['licenses'], $candidates['words']);
		$_SESSION['list'] = $list;
		return $list;
	} else {
		return $repos;
	};
};
//}}}
//}}}
//{{{Select lang
if (isset($_REQUEST['lang'])) {
	$lang_label = filter_var($_REQUEST['lang'], FILTER_VALIDATE_REGEXP, array('options'=>array('regexp'=>'/^[a-z]{2}$/')));
	if ($lang_label === false || !is_file(LANG.DIRECTORY_SEPARATOR.$lang_label.DIRECTORY_SEPARATOR."lang.php")) $lang_label = DEFAULT_LANG;
	$_SESSION['lang'] = $lang_label;
} elseif (isset($_SESSION['lang'])) {
	$lang_label = (is_file(LANG.DIRECTORY_SEPARATOR.$_SESSION['lang'].DIRECTORY_SEPARATOR."lang.php")) ? $_SESSION['lang'] : DEFAULT_LANG;
} else {
	$lang_label = DEFAULT_LANG;
	$_SESSION['lang'] = $lang_label;
};
include_once("lang/{$lang_label}".DIRECTORY_SEPARATOR."lang.php");
//}}}
//{{{ Retrieve data from cache
libxml_use_internal_errors(true);
if (!is_file(DATA) || !is_readable(DATA) || simplexml_load_file(DATA) === false) {
	if (!is_file(REPOS_FILE)) {//{{{
		$favicon = (is_file('favicon.ico') && is_readable('favicon.ico')) ? 
			"<link rel=\"icon\" type=\"image/x-icon\" href=\"favicon.ico\" />" : '';
		$headers = "<header role=\"banner\">
			<div>
				<h1>".translate('iface', 'error_label', $lang)."</h1>
			</div>";
		$headers .= build_lang_selector($lang_label, $lang);
		$headers .= "</header>";
		$main = "<article><h2>".translate('iface', 'error_label', $lang)."</h2><div id=\"warning\">".translate('iface', 'error_message', $lang)."<div></article>";
		$footer = "<footer role=\"contentinfo\"><span>".MSG_FOOTER."</span></footer>";
		$menu = "
<nav id=\"menu\" role=\"navigation\">
	<h2>".translate('iface', 'menu', $lang).":</h2>
	<ul>
		<li><a href=\"#warning\" title=\"".translate('iface', 'access_error_mess', $lang)."\">".translate('iface', 'error_label', $lang)."</a></li>
	</ul>
</nav>
";
		echo "
<!DOCTYPE html>
<html lang=\"{$_SESSION['lang']}\">
	<head>
		<meta charset=\"UTF-8\">
		<title>".translate('iface', 'error_label', $lang)."</title>
		{$favicon}
		<link type=\"text/css\" rel=\"stylesheet\" href=\"Media/css/default.css\" />
	</head>
	<body>
		{$headers}
		<main role=\"main\">
			{$menu}
			{$main}
		</main>
		{$footer}
	</body>
</html>
";
		exit;//}}}
	} else {
		$warning = "<div id=\"warning\" title=\"".translate('iface', 'warning_label', $lang)."\">".translate('iface', 'warning_msg', $lang)."</div>";
		$repos = unserialize(file_get_contents(REPOS_FILE));
		$categories = (is_file(CAT_FILE)) ? unserialize(file_get_contents(CAT_FILE)) : cache_categories($repos['list']);
		$relations = (is_file(REL_FILE)) ? unserialize(file_get_contents(REL_FILE)) : cache_relations($repos['list']);
		$licenses = (is_file(LIC_FILE)) ? unserialize(file_get_contents(LIC_FILE)) : cache_licenses($repos['list']);
		$words = (is_file(WORD_FILE)) ? unserialize(file_get_contents(WORD_FILE)) : cache_words($repos['list']);
		$last_apps = (is_file(LAST_FILE)) ? unserialize(file_get_contents(LAST_FILE)) : cache_lastapps($repos);
		// reducing the list to the apps only present in the cache
		$buffered_list = array();
		if (is_dir(APP_CACHE)) {
			$dh = opendir(APP_CACHE);
			while (false !== ($file = readdir($dh))) {
				if (is_file(APP_CACHE.DIRECTORY_SEPARATOR.$file) && 
					is_readable(APP_CACHE.DIRECTORY_SEPARATOR.$file))
						$buffered_list[] = $file;
			};
			closedir($dh);
		};
		$repos['list'] = $buffered_list;
	};
} else {
	$warning = '';
	$hash = hash_file(HASH_ALGO, DATA);
	if ((is_file(MANIFEST) && $hash != file_get_contents(MANIFEST)) || !is_file(REPOS_FILE)) {
		$data = build_cache_data($hash);
		$repos = $data['repos'];
		$categories = $data['cat'];
		$relations = $data['rel'];
		$licenses = $data['lic'];
		$words = $data['wrd'];
		$last_apps = $data['lst'];
	} else {
		file_put_contents(MANIFEST, $hash);
		$repos = unserialize(file_get_contents(REPOS_FILE));
		$categories = (is_file(CAT_FILE)) ? unserialize(file_get_contents(CAT_FILE)) : cache_categories($repos['list']);
		$relations = (is_file(REL_FILE)) ? unserialize(file_get_contents(REL_FILE)) : cache_relations($repos['list']);
		$licenses = (is_file(LIC_FILE)) ? unserialize(file_get_contents(LIC_FILE)) : cache_licenses($repos['list']);
		$words = (is_file(WORD_FILE)) ? unserialize(file_get_contents(WORD_FILE)) : cache_words($repos['list']);
		$last_apps = (is_file(LAST_FILE)) ? unserialize(file_get_contents(LAST_FILE)) : cache_lastapps($repos);
	};
};
//}}}
if (isset($_REQUEST['reset'])) {
	unset($_SESSION['licenses']);
	unset($_SESSION['categories']);
	unset($_SESSION['words']);
	unset($_SESSION['list']);
	unset($_SESSION['page']);
	$list = $repos['list'];
} elseif (isset($_REQUEST['search'])) {
	unset($_SESSION['list']);
	unset($_SESSION['page']);
	$list = apply_filters($relations, $licenses, $words, $repos['list']);
} elseif (isset($_SESSION['list'])) {
	$list = $_SESSION['list'];
} else {
	$list = $repos['list'];
};
$nbr_app = count($list);
//{{{Select page
if (isset($_REQUEST['page'])) {
	$page = filter_var($_REQUEST['page'], FILTER_VALIDATE_INT);
	if ($page === false || ((int) $page - 1) * RECORDS_PER_PAGE > $nbr_app || $page < 1) $page = 1;
	$_SESSION['page'] = $page;
} elseif (isset($_SESSION['page'])) {
	$page = $_SESSION['page'];
} else {
	unset($_SESSION['page']);
	$page = 1;
};
$buffer = array_slice($list, ($page - 1) * RECORDS_PER_PAGE, RECORDS_PER_PAGE);
//}}}
if (!isset($_REQUEST['format']) || !isset($formats[$_REQUEST['format']])) {	// HTML case
	//{{{ Building content
	$footer = "<footer role=\"contentinfo\"><span>".MSG_FOOTER."</span></footer>";
	$favicon = (is_file('favicon.ico') && is_readable('favicon.ico')) ? 
			"<link rel=\"icon\" type=\"image/x-icon\" href=\"favicon.ico\" />" : '';
	$headers = decore_headers($repos, $lang_label, $lang);
	$tools = build_tools($relations, $licenses, $lang, $repos['nbr']);
	$applist = decore_applist($buffer, $lang, $nbr_app, $page);
	$lastapp = decore_lastapplist($last_apps, $lang);
	if (!isset($_REQUEST['lang'])) unset($_SESSION['sheet']);
	if (isset($_REQUEST['sheet']) || isset($_SESSION['sheet'])) { //{{{ 
		$sheet = (isset($_REQUEST['sheet'])) ? $_REQUEST['sheet'] : $_SESSION['sheet'];
		if (in_array($sheet, $repos['list'])) {
			$_SESSION['sheet'] = $sheet;
			$main = decore_app($sheet, $lang);
		} else {
			$main = "
			<fieldset>
				<legend>".translate('iface', 'error_label', $lang)."</legend>".
				translate('iface', 'error_message', $lang).
			"</fieldset>";
		};
		$anchor_menu = "#appsheet";
		$label_access_menu = translate('iface', 'access_appsheet', $lang);
		$label_menu = translate('iface', 'sheet', $lang);
	} else {
		$anchor_menu = "#applist";
		$label_access_menu = translate('iface', 'access_applist', $lang);
		$label_menu = translate('iface', 'applist', $lang);
		$main = $applist;
	}; //}}}
	$tagFeed = '';
	if (USE_FEEDS) {
		$feed_title = "";
		$tagfeed = "<link rel=\"alternate\" type=\"application/atom+xml\" title=\"{$feed_title}\" href=\"".FEED_NAME."\" />";
	};
	$menu = "
<nav id=\"menu\" role=\"navigation\">
	<h2>".translate('iface', 'menu', $lang).":</h2>
	<ul>
		<li><a href=\"#search\" title=\"".translate('iface', 'access_form_val', $lang)."\">".translate('iface', 'form_val', $lang)."</a></li>
		<li><a href=\"#categories\" title=\"".translate('iface', 'browse_cat', $lang)."\">".translate('iface', 'categories', $lang)."</a></li>
		<li><a href=\"#licenses\" title=\"".translate('iface', 'browse_lic', $lang)."\">".translate('iface', 'license', $lang)."</a></li>
		<li><a href=\"{$anchor_menu}\" title=\"{$label_access_menu}\">{$label_menu}</a></li>
		<li><a href=\"#lastapplist\" title=\"".translate('iface', 'access_lastapplist', $lang)."\">".translate('iface', 'lastapplist', $lang)."</a></li>
	</ul>
</nav>
";
	//}}}
	echo "
<!DOCTYPE html>
<html lang=\"{$_SESSION['lang']}\">
	<head>
		<meta charset=\"UTF-8\">
		<title>{$repos['name']}</title>
		{$favicon}
		{$tagfeed}
		<link type=\"text/css\" rel=\"stylesheet\" href=\"Media/css/default.css\" />
	</head>
	<body>
		{$headers}
		<main role=\"main\">
			{$warning}
			{$menu}
			{$main}
			{$tools}
			{$lastapp}
		</main>
		{$footer}
	</body>
</html>
";
} elseif ($_REQUEST['format'] == 'json') {
	if (isset($_REQUEST['categories'])) {
		echo json_encode(array_keys($categories));
	} elseif (isset($_REQUEST['licenses'])) {
		echo json_encode(array_keys($licenses));
	} elseif (isset($_REQUEST['sheet'])) {
		echo json_encode(decore_app_json($_REQUEST['sheet']));
	} else {
		$result = array('total' => count($list), 'list' => array());
		if (isset($_REQUEST['all'])) {
			$buffer = $list;
		} else {
			$result['page'] = $page;
		};
		foreach($buffer as $app) {
			$result['list'][] = decore_app_json($app, true);
		};
		echo json_encode($result);
	};
};
?>
