<?php
/**
 *	vim: foldmarker{{{,}}}
 *
 *	@author A. Keledjian	<dervishe@yahoo.fr>
 *	@copyright Association Française des Petits Débrouillards
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
define('MEDIA_SITE', 'Media');
define('ICONS_DIR', 'icons-320');
define('ICONS_DIR_LIGHT', 'icons-240');
define('ICONS_DIR_ABSTRACT', 'icons-120');
define('QRCODES_DIR', 'qrcodes');
define('LANG', 'lang');
define('DICT', LANG.DIRECTORY_SEPARATOR.'dict');
define('FLAGS', LANG.DIRECTORY_SEPARATOR.'flag');
define('CACHE', ROOT.DIRECTORY_SEPARATOR.'cache');
define('APP_CACHE', CACHE.DIRECTORY_SEPARATOR.'app_files');
// FILES
define('CATEGORIES', ROOT.DIRECTORY_SEPARATOR.'categories.txt');
define('DATA', ROOT.DIRECTORY_SEPARATOR.'index.xml');
define('REPOS_FILE', CACHE.DIRECTORY_SEPARATOR.'repository');
define('CAT_FILE', CACHE.DIRECTORY_SEPARATOR.'categories'); // store categories as an array
define('REL_FILE', CACHE.DIRECTORY_SEPARATOR.'relations'); // store relations between categories and apps as an array
define('LIC_FILE', CACHE.DIRECTORY_SEPARATOR.'licenses'); // store used licenses as an array
define('LAST_FILE', CACHE.DIRECTORY_SEPARATOR.'last_apps'); // store last apps id as an array
define('WORD_FILE', CACHE.DIRECTORY_SEPARATOR.'words'); // store used words as an array
define('MANIFEST', CACHE.DIRECTORY_SEPARATOR.'Manifest'); // store index.xml hash
// PARAMETERS
define('HASH_ALGO', 'whirlpool');
define('USE_QRCODE', true);
define('NUMBER_LAST_APP', 4);
define('RECORDS_PER_PAGE', 3);
define('DEFAULT_LANG', 'fr');
define('LOCALIZATION', 'fr');
define('MSG_FOOTER', '(C) Association Française des Petits Débrouillards');
// ALLOWED VALUES
$formats = array('json'=>1, 'atom'=>2, 'rss'=>3);
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
	$application['tracker'] = (string) $app->tracker;
	$application['requirements'] = (string) $app->requirements;
	$application['donate'] = (string) $app->donate;
	$application['flattr'] = (string) $app->flattr;
	$application['bitcoin'] = (string) $app->bitcoin;
	$application['antifeatures'] = (string) $app->antifeatures;
	$package = array();
	$package['version'] = (string) $app->package->version;
	$package['apkname'] = (string) $app->package->apkname;
	$package['size'] = (int) $app->package->size;
	$package['permissions'] = (string) $app->package->permissions;
	$package['sdkver'] = (string) $app->package->sdkver;
	$application['package'] = $package;
	file_put_contents(APP_CACHE.DIRECTORY_SEPARATOR.$application['id'], serialize($application));
	return $application;
};//}}}
function build_lang_selector($lang_label, $lang) { //{{{
	$bloc = "<div id=\"lang\"><span>".translate('iface', 'language', $lang).": </span><ul>";
	if ($dh = opendir(LANG)) {
		while (false !== ($file = readdir($dh))) {
			if (is_file(LANG.DIRECTORY_SEPARATOR.$file)) {
				$file = substr($file, 0, 2);
				$bloc .= ($file != $lang_label) ? 
					"<li>
						<a href=\"?lang={$file}\" title=\"".translate('lang', $file, $lang)."\">
							<img alt=\"".translate('lang', $file, $lang)."\" src=\"".FLAGS.DIRECTORY_SEPARATOR.$file.".png\" />
						</a>
					</li>" : "<li><span	><img alt=\"".translate('lang', $file, $lang)."\" src=\"".FLAGS.DIRECTORY_SEPARATOR.$file.".png\" /></span></li>";
			};
		};
		closedir($dh);
	};
	$bloc .= '</ul></div>';
	return $bloc;
};//}}}
function build_pager($page_number, $max, $lang) { //{{{
	$bloc = "<div><span>".translate('iface', 'page', $lang).":</span><ul>";
	for ($i = 1; $i < $page_number; $i++) { 
		$bloc .= "
		<li>
			<a href=\"?page={$i}\" title=\"".translate('iface', 'go_to_page', $lang)." {$i}\">
				{$i}
			</a>
		</li>";
	};
	$bloc .= "<li><span>{$page_number}</span></li>";
	for ($i = $page_number + 1; $i <= $max; $i++) { 
		$bloc .= "
			<li>
				<a href=\"?page={$i}\" title=\"".translate('iface', 'go_to_page', $lang)." {$i}\">
				{$i}
				</a>
			</li>";
	};
	$bloc .= "</ul></div>";
	return $bloc;
};//}}}
function build_form_search($lang) { //{{{
	return "
<article id=\"search\">
	<header>
		<h2>".translate('iface', 'form_val', $lang)."</h2> 
	</header>
	<form method=\"POST\" action=\"?prop=search\">
		<label for=\"word_search\">".translate('iface', 'word_search', $lang)."</label>
		<input id=\"word_search\" type=\"search\" name=\"val\" title=\"".translate('iface', 'form_field', $lang)."\" />
		<input type=\"submit\" value=\"".translate('iface', 'form_val', $lang)."\" title=\"".translate('iface', 'form_val', $lang)."\" />
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
	$lst = cache_lastapps($repos['list']);
	return array('repos'=>$repos, 'cat'=>$cat, 'rel'=>$rel, 'lic'=>$lic, 'wrd'=>$words, 'lst'=>$lst);
};
//}}}
function build_tools($relations, $licenses, $lang, $nbr) { //{{{
	return "<aside id=\"tools\" role=\"search\">".
			build_form_search($lang).
			decore_categories($relations, $lang, $nbr).
			decore_licenses($licenses, $lang, $nbr).
			"</aside>";
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
	} elseif (count($repos) > 0) {		// Fallback: if DATA is not present, then we use app file stored in cache
		foreach ($repos as $app) {
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
	if (count($result) > 0) file_put_contents(LAST_FILE, serialize($result));
	return $result;
};
//}}}
function cache_words($repos) { //{{{	Fields to search: name, summary, description
	$wd = array();
	if (is_file(DATA) && is_readable(DATA) && ($data = simplexml_load_file(DATA)) !== false) {
		include_once(DICT.DIRECTORY_SEPARATOR.LOCALIZATION.".st.php"); // $stopwords loading
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
		include_once(DICT.DIRECTORY_SEPARATOR.LOCALIZATION.".st.php"); // $stopwords loading
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
function translate_perm($item) { //{{{
	global $lang;
	return (isset($lang['perms'][$item])) ? $lang['perms'][$item] : $item;
};
//}}}
function translate_feat($item) { //{{{
	global $lang;
	return (isset($lang['afeat'][$item])) ? $lang['afeat'][$item] : $item;
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
	if (USE_QRCODE) { //{{{
		include_once('phpqrcode/phpqrcode.php');
		$qrcode = QRCODES_DIR.DIRECTORY_SEPARATOR.$app['id'].".png";
		if (!is_file($qrcode)) {
			QRCode::png("https://{$_SERVER['SERVER_NAME']}/{$app['package']['apkname']}", $qrcode);
		};
		$dl_label = translate('iface', 'download', $lang);
		$tag_qrcode = "
		<aside title=\"{$dl_label}\">
			<img src=\"{$qrcode}\" alt=\"QR-Code {$app['name']}\" title=\"QR-Code: {$dl_label} {$app['name']}\" />
			<a href=\"{$app['package']['apkname']}\" title=\"{$dl_label} {$app['name']}\">{$dl_label}</a></aside>";
	} else {
		$tag_qrcode = "<aside><a title=\"{$dl_label} {$app['name']}\" href=\"{$app['package']['apkname']}\">{$dl_label}</a></aside>";
	};//}}}
	$icon = ICONS_DIR.DIRECTORY_SEPARATOR.$app['icon'];
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
		<span>{$app['package']['version']}</span> - <span>{$label}</span>: 
		<span>{$date}</span>
	</div>";//}}}
	$lic_label = translate('iface', 'license', $lang);
	$license = "<div title=\"{$lic_label}\"><span>{$lic_label}: </span><span>".translate('lic', $app['license'], $lang)."</span></div>";
	$sum_label = translate('iface', 'summary', $lang);
	$summary = "<span title=\"{$sum_label}\">{$app['summary']}</span>";
	$desc_label = translate('iface', 'desc', $lang);
	$desc = "<div title=\"{$desc_label}\" id=\"description\">{$app['desc']}</div>";
	$reqs_label = translate('iface', 'requirements', $lang);
	$requirements = (strlen($app['requirements']) > 0) ? 
		"<div title=\"{$reqs_label}\"><span>{$reqs_label}: </span><span>{$app['requirements']}</span></div>" : '';
	$size = $app['package']['size']; //{{{
	$size_label = translate('iface', 'size', $lang);
	if (($size / 1048572) > 1) {
		$size /= 1048572;
		$size = "<div title=\"{$size_label}\"><span>{$size_label}: </span><span>".round($size, 2)." MB</span></div>";
	} else {
		$size /= 1024;
		$size = "<div title=\"{$size_label}\"><span>{$size_label}: </span><span>".round($size, 2)." kB</span></div>";
	};//}}}
	$categories = $app['categories'];
	$cats = (strlen($categories) > 0 && $categories != 'None') ? "<ul><li>".implode('</li><li>', array_map('translate_cat', explode(',', $categories)))."</li></ul>" : '';
	$cats_span = translate('iface', 'categories', $lang);
	$categories = "<aside title=\"{$cats_span}\"><span>{$cats_span}: </span>{$cats}</aside>";
	$permissions = $app['package']['permissions'];
	$perms = (strlen($permissions) > 0) ? "<ul><li>".implode('</li><li>', array_map('translate_perm', explode(',', $permissions)))."</li></ul>" : '';
	$perms_span = translate('iface', 'permissions', $lang);
	$permissions = "<aside title=\"{$perms_span}\"><span>{$perms_span}: </span>{$perms}</aside>";
	$afeatures = $app['antifeatures'];
	$afeat = (strlen($afeatures) > 0) ? "<ul><li>".implode('</li><li>', array_map('translate_feat', explode(',', $afeatures)))."</li></ul>" : '';
	$afeat_span = translate('iface', 'antifeatures', $lang);
	$afeatures = "<aside id=\"antifeatures\" title=\"{$afeat_span}\"><span>{$afeat_span}: </span>{$afeat}</aside>";
	return "
<article id=\"appsheet\">
	<header>
		<h2>
			<img src=\"{$icon}\" alt=\"icone {$app['name']}\" />
			<span>{$app['name']}</span>
		</h2>
		{$summary}
	</header>
	{$tag_qrcode}
	{$size}
	{$version}
	{$license}
	{$desc}
	{$requirements}
	".(($cats != '') ? $categories : '')."
	".(($perms != '') ? $permissions : '')."
	".(($afeat != '') ? $afeatures : '')."
</article>";
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
	$icon = ICONS_DIR_ABSTRACT.DIRECTORY_SEPARATOR.$app['icon'];
	if ($app['updated'] == $app['added']) {
		$version = "
		<li>
			<span>".translate('iface', 'version', $lang).":</span>
			<span>{$app['package']['version']}</span> - <span>".
			translate('iface', 'added', $lang).":</span> 
			<span>{$app['added']}</span>
		</li>";
	} else {
		$version = "
		<li>
			<span>".translate('iface', 'version', $lang).":</span>
			<span>{$app['package']['version']}</span> - <span>".
			translate('iface', 'updated', $lang).":</span> 
			<span>{$app['updated']}</span>
		</li>";
	};
	$sum_label = translate('iface', 'summary', $lang);
	$summary = "<span id=\"desc_{$app['name']}\" title=\"{$sum_label}\">{$app['summary']}</span>";
	$size = $app['package']['size'];
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
		<a href=\"{$app['package']['apkname']}\" title=\"".
		translate('iface', 'download', $lang).
		": {$app['name']}\" aria-describedby=\"desc_{$app['name']}\">".
		translate('iface', 'download', $lang).
		"</a>
		<a href=\"?getSheet={$app['id']}\" title=\"".
		translate('iface', 'sheet', $lang).
		": {$app['name']}\" aria-describedby=\"desc_{$app['name']}\">".
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
	$icon = ICONS_DIR_ABSTRACT.DIRECTORY_SEPARATOR.$app['icon'];
	$license = "
	<div title=\"".translate('iface', 'license', $lang)."\">
		<span>".translate('iface', 'license', $lang).": </span>
		<span>".translate('lic', $app['license'], $lang)."</span>
	</div>";
	return "
	<div id=\"last_".str_replace(array('.', ' '), '_', $app['id'])."\">
		<img src=\"{$icon}\" alt=\"icone {$app['name']}\" />
		<div>
			<a href=\"?getSheet={$app['id']}\" title=\"".
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
			<form method=\"POST\" action=\"index.php\">
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
			<form method=\"POST\" action=\"index.php\">
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
function decore_headers($title, $lang_label, $lang, $description=null) { //{{{
	$bloc = "<header role=\"banner\">
			<div>
				<img src=\"".MEDIA_SITE."/images/logo.png\" alt=\"logo: {$title}\" />
				<h1>{$title}</h1>
			</div>
			<div>$description</div>";
	$bloc .= build_lang_selector($lang_label, $lang);
	$bloc .= "</header>";
	return $bloc;
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
		unset($_SESSION['list']);
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
		unset($_SESSION['list']);
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
	$wrd_ids = (isset($_REQUEST['wrd'])) ? $_REQUEST['wrd'] : array();
	if (!is_array($wrd_ids)) $wrd_ids = array($wrd_ids);
	if (count($wrd_ids) > 0) { //{{{
		$_SESSION['words'] = $wrd_ids;
		$flag |= true;
		$candidates['words'] = array();
		foreach ($wrd_ids as $key) {
			if (isset($licenses[$key])) $candidates['words'] = array_merge($candidates['words'], $words[$key]);
		};
		$candidates['words'] = array_unique($candidates['words']);
	} else {
		$candidates['words'] = $repos;
	}; //}}}
	//}}}
	if ($flag) {
		$list = array_intersect($candidates['categories'], $candidates['licenses'], $candidates['words']);
		$_SESSION['list'] = $list;
		unset($_SESSION['page']);
		return $list;
	} elseif (isset($_SESSION['list'])) {
		return $_SESSION['list'];
	} else {
		return $repos;
	};
};
//}}}
//}}}
//{{{Select lang
if (isset($_GET['lang'])) {
	$lang_label = filter_var($_GET['lang'], FILTER_VALIDATE_REGEXP, array('options'=>array('regexp'=>'/^[a-z]{2}$/')));
	if ($lang_label === false || !is_file(LANG.DIRECTORY_SEPARATOR."{$lang_label}.php")) $lang_label = DEFAULT_LANG;
	$_SESSION['lang'] = $lang_label;
} elseif (isset($_SESSION['lang'])) {
	$lang_label = (is_file(LANG.DIRECTORY_SEPARATOR."{$_SESSION['lang']}.php")) ? $_SESSION['lang'] : DEFAULT_LANG;
} else {
	$lang_label = DEFAULT_LANG;
	$_SESSION['lang'] = $lang_label;
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
		$words = (is_file(WORD_FILE)) ? unserialize(file_get_contents(WORD_FILE)) : cache_words($repos['list']);
		$last_apps = (is_file(LAST_FILE)) ? unserialize(file_get_contents(LAST_FILE)) : cache_lastapps($repos['list']);
	};
} else {
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
		$last_apps = (is_file(LAST_FILE)) ? unserialize(file_get_contents(LAST_FILE)) : cache_lastapps($repos['list']);
	};
};
//}}}
$list = apply_filters($relations, $licenses, $words, $repos['list']);
$nbr_app = count($list);
//{{{Select page
if (isset($_GET['page'])) {
	$page = filter_var($_GET['page'], FILTER_VALIDATE_INT);
	if ($page === false || ((int) $page - 1) * RECORDS_PER_PAGE > $nbr_app) $page = 1;
	$_SESSION['page'] = $page;
} elseif (isset($_SESSION['page']) && !isset($_REQUEST['getSheet'])) {
	$page = $_SESSION['page'];
	unset($_SESSION['page']);
} else {
	$page = 1;
};
$tampon = array_slice($list, ($page - 1) * RECORDS_PER_PAGE, RECORDS_PER_PAGE);
//}}}
if (!isset($_REQUEST['format']) || !isset($formats[$_REQUEST['format']])) {	// HTML case
	//{{{ Building content
	$footer = "<footer role=\"contentinfo\"><span>".MSG_FOOTER."</span></footer>";
	$favicon = (is_file(MEDIA_SITE.'/images/favicon.ico') && 
			is_readable(MEDIA_SITE.'/images/favicon.ico')) ? 
			"<link type=\"image/png\" rel=\"icon\" href=\"".MEDIA_SITE.'/images/favicon.ico'."\" />" : '';
	$headers = decore_headers($repos['name'], $lang_label, $lang, $repos['desc']);
	$tools = build_tools($relations, $licenses, $lang, $repos['nbr']);
	$applist = decore_applist($tampon, $lang, $nbr_app, $page);
	$lastapp = decore_lastapplist($last_apps, $lang);
	if (isset($_REQUEST['getSheet'])) { //{{{ 
		$sheet = $_REQUEST['getSheet'];
		if (in_array($sheet, $repos['list'])) {
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
		<link type=\"text/css\" rel=\"stylesheet\" href=\"".MEDIA_SITE."/css/default.css\" />
	</head>
	<body>
		{$headers}
		<main role=\"main\">
			{$menu}
			{$tools}
			{$main}
			{$lastapp}
		</main>
		{$footer}
	</body>
</html>
";
} elseif ($_REQUEST['format'] == 'json') {
} elseif ($_REQUEST['format'] == 'atom') {
};
?>
