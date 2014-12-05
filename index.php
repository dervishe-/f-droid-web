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
define('TEMPLATE_DIR', ROOT.DIRECTORY_SEPARATOR.'templates');
define('DEFAULT_THEME', 'default');
define('THEME', 'default');
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
define('FEED_AUTHOR', "Your feed's author");
define('NUMBER_LAST_APP', 10);
define('RECORDS_PER_PAGE', 12);
define('NUMBER_PAGES', 9);		// Fixe the number of appearing page numbers in the pager
define('DEFAULT_LANG', 'fr');	// Fixe the localization of the UI
define('LOCALIZATION', 'en');	// Fixe the localization of the search (mainly related to the languages in which the apps are describes)
define('MSG_FOOTER', "Your footer's text");//}}}
// ALLOWED VALUES
$formats = array('json' => 1, 'atom' => 2);
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
		QRCode::png(repo_qr_url($repos), REPOS_QRCODE);
	};
	return $repos;
};//}}}
function repo_qr_url($repos) {//{{{
	return "{$repos['url']}?fingerprint=".hash(HASH_REPOS_PUBKEY, hex2bin($repos['pubkey']));
}//}}}
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
	$bloc = '';
	if ($dh = opendir(LANG)) {
		while (false !== ($dir = readdir($dh))) {
			$rep_lang = LANG.DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR;
			if (is_file($rep_lang.'lang.php')) {
				$placeholders = array(
					'Lang:Id' => $dir,
					'Lang:IconPath' => $rep_lang.'flag.png',
					'Lang:IsSelected' => $dir == $lang_label,
					'Lang:IsNotSelected' => $dir != $lang_label,
					'Lang:Name' => translate('lang', $dir, $lang),
				);
				$bloc .= parse_template('main_headers_language', $placeholders);
			};
		};
		closedir($dh);
	};
	return $bloc;
};//}}}
function build_pager($current_page, $number_page, $lang) { //{{{
	$bloc = '';
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

	$render_pager_item = function($page_num, $selected = false, $spacer = false) use ($lang) {
		return parse_template(
			'applist_pageritem',
			array(
				'Pager:Link' => '?page=' . $page_num,
				'Pager:Number' => $page_num,
				'Text:GoToPage' => translate('iface', 'go_to_page', $lang),
				'Pager:IsSelected' => $selected ? '1' : '0',
				'Pager:IsSpacer' => $spacer ? '1' : '0',
				'Pager:IsButton' => !$selected && !$spacer,
			)
		);
	};

	if ($current_page > floor($nb / 2) + 1) {
		$bloc .= $render_pager_item(1);
	};
	if ($page_init > 2) $bloc .= $render_pager_item(0, false, true);
	for ($i = $page_init; $i < $current_page; $i++) { 
		$bloc .= $render_pager_item($i);;
	};
	$bloc .= $render_pager_item($current_page, true);
	for ($i = $current_page + 1; $i <= $page_end; $i++) { 
		$bloc .= $render_pager_item($i);
	};
	if ($page_end < $number_page - 1) $bloc .= $render_pager_item(0, false, true);
	if ($current_page < $number_page - ceil($nb / 2)) {
		$bloc .= $render_pager_item($number_page);
	};
	return $bloc;
};//}}}
function build_form_search($lang, $value='') { //{{{
	return parse_template(
		'search_keyword',
		array(
			'Text:FormValue' => translate('iface', 'form_val', $lang),
			'Text:KeywordSearch' => translate('iface', 'word_search', $lang),
			'Text:FormField' => translate('iface', 'form_field', $lang),
			'Search:Keyword:Link' => '?search',
			'Search:Keyword:Value' => $value, // TODO: Escape this value when neccessary - i.e. when it contains double quotes.
		)
	);
};//}}}
function build_reset($lang, $value='') { //{{{
	return parse_template(
		'search_reset',
		array(
			'Text:Reset' => translate('iface', 'reset', $lang),
			'Text:FormReset' => translate('iface', 'form_reset', $lang),
			'Reset:Link' => '?reset',
		)
	);
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
function build_atom($repos, $list, $url=null) { //{{{
	$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != '') ? 'https://' : 'http://';
	$icon = "{$_SERVER['SERVER_NAME']}/Media/images/{$repos['icon']}";
	$date = date('c', $repos['timestamp']);
	$feed = "";
	$i = 0;
	if (is_file(DATA) && is_readable(DATA) && ($_xml = simplexml_load_file(DATA)) !== false) {
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
	} else {
		foreach ($list as $app) {
			if (is_file(APP_CACHE.DIRECTORY_SEPARATOR.$app) && is_readable(APP_CACHE.DIRECTORY_SEPARATOR.$app)) {
				$app = unserialize(file_get_contents(APP_CACHE.DIRECTORY_SEPARATOR.$app));
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
		};
	};
	$url = (is_null($url)) ? '/'.FEED_NAME : $url;
	$url = "{$scheme}{$_SERVER['SERVER_NAME']}".$url;
	$bloc = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>
<feed xmlns=\"http://www.w3.org/2005/Atom\">
	<title>{$repos['name']}</title>
	<subtitle>{$repos['desc']}</subtitle>
	<link rel=\"self\" href=\"{$url}\" />
	<updated>{$date}</updated>
	<id>{$scheme}{$_SERVER['SERVER_NAME']}/</id>
	<logo>{$icon}</logo>
	<author>
		<name>".FEED_AUTHOR."</name>
	</author>
	{$feed}
</feed>";
	return $bloc;
};
//}}}
function build_list($relations, $licenses, $words, $list) { //{{{
	if (isset($_REQUEST['reset'])) {
		unset($_SESSION['licenses']);
		unset($_SESSION['categories']);
		unset($_SESSION['words']);
		unset($_SESSION['list']);
		unset($_SESSION['page']);
	} elseif (isset($_REQUEST['search'])) {
		unset($_SESSION['list']);
		unset($_SESSION['page']);
		$list = apply_filters($relations, $licenses, $words, $list);
	} elseif (isset($_SESSION['list'])) {
		$list = $_SESSION['list'];
	};
	return $list;
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
			if (is_file(APP_CACHE.DIRECTORY_SEPARATOR.$app) && is_readable(APP_CACHE.DIRECTORY_SEPARATOR.$app)) {
				$app = unserialize(file_get_contents(APP_CACHE.DIRECTORY_SEPARATOR.$app));
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
		while (current($last)) {
			$result = array_merge($result, current($last));
			next($last);
		};
		$result = array_slice($result, 0, NUMBER_LAST_APP);
	};
	if (USE_FEEDS) file_put_contents(FEED_NAME, build_atom($repos, $result));
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
function load_template($template) { //{{{
	$file = TEMPLATE_DIR.DIRECTORY_SEPARATOR.THEME.DIRECTORY_SEPARATOR.$template.".tpl";
	if (!file_exists($file)) {
		$file = TEMPLATE_DIR.DIRECTORY_SEPARATOR.DEFAULT_THEME.DIRECTORY_SEPARATOR.$template.".tpl";
		if (!file_exists($file)) {
			return "<div>Could not find template \"$theme/$template\" (or \"default/$template\")</div>";
		}
	}
	return file_get_contents($file);
};
//}}}
/**
 * Either removes the entire <if>*</if> block if trim((string)$value) evaluates to false, or
 * removes the <if> and </if> tags leaving the content otherwise.
 * TODO: Don;t do the preg_match_all for each placeholder.
 *  - Rather, do it once capturing the value of placeholder="(.*)" as well.
 */
function parse_conditional_placeholder($template, $placeholder, $value) { //{{{
	$pattern = '/<if placeholder="' . $placeholder . '">(.*)<\/if>/Usm';
	$matches = array();
	if (preg_match_all($pattern, $template, $matches)) {
		for ($i = 0; $i < count($matches[0]); $i ++) {
			$to_replace = $matches[0][$i];
			$to_replace_with = $value ? $matches[1][$i] : '';
			$template = str_replace($to_replace, $to_replace_with, $template);
		}
	}
	return $template;
}
//}}}
function parse_template($template, $placeholders) { //{{{
	$template = load_template($template);
	foreach($placeholders as $key => $value) {
		$template = parse_conditional_placeholder($template, $key, $value);
		$template = str_replace("[$key]", $value, $template);
	}
	return $template;
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
	$qr_image_path = QRCODES_DIR.DIRECTORY_SEPARATOR.$app['id'].".png";
	if (USE_QRCODE) { //{{{
		include_once('phpqrcode/phpqrcode.php');
		if (!is_file($qr_image_path)) {
			QRCode::png("{$scheme}{$_SERVER['SERVER_NAME']}/{$app['packages'][0]['apkname']}", $qr_image_path);
		};
	};//}}}
	$translate_cat = translator('cat', $lang);
	$categories_list = '';
	if (strlen($app['categories']) > 0 && $app['categories'] != 'None') {
		foreach(explode(',', $app['categories']) as $category) {
			$categories_list .= parse_template(
				'app_details_category',
				array(
					'Category:Id' => $category,
					'Category:Name' => $translate_cat($category),
				)
			);
		}
	}

	$process_permissions_template = function($package) use ($lang) {
		$translate_perm = translator('perms', $lang);
		$permissions_list = '';
		if (strlen($package['permissions']) > 0) {
			foreach(explode(',', $package['permissions']) as $permission) {
				$permissions_list .= parse_template(
					'app_details_permission',
					array(
						'Text:Permissions' => translate('iface', 'permissions', $lang),
						'Package:Version' => $package['version'],
						'Permission:Id' => $permission,
						'Permission:Name' => $translate_perm($permission),
						'Permission:Link:Description' => 'http://developer.android.com/reference/android/Manifest.permission.html#' . $permission,
					)
				);
			}
		}
		return $permissions_list;
	};

	$permissions_list = $process_permissions_template($app['packages'][0]);

	$social_msg = "{$app['name']}: {$app['summary']}";

	$antifeatures_list = '';
	if (strlen($app['antifeatures']) > 0) {
		foreach(explode(',', $app['antifeatures']) as $antifeature) {
			$antifeatures_list .= parse_template(
				'app_details_antifeature',
				array('Antifeature:Name' => $antifeature)
			);
		}
	}

	$old_versions_list = '';
	if (count($app['packages']) > 1) {
		for ($i = 1; $i < count($app['packages']); $i++) {
			$pkg = $app['packages'][$i];

			$placeholders = array_merge(
				app_package_placeholders($pkg),
				array(
					'Text:Hash' => translate('iface', 'hash', $lang),
					'Text:Permissions' => translate('iface', 'permissions', $lang),
					"Text:Size" => translate('iface', 'size', $lang),
					'Text:SdkVersion' => translate('iface', 'sdkver', $lang),
					'Text:Download' => translate('iface', 'download', $lang),
					'Text:Version' => translate('iface', 'version', $lang),
					"Text:DateAdded" => translate('iface', 'added', $lang),

					'Subtemplate:Permissions' => $process_permissions_template($pkg),
				)
			);

			$old_versions_list .= parse_template('app_details_package', $placeholders);
		}
	};

	$params = array(

		"Config:UseSocial" => USE_SOCIAL,
		'Config:UseQrCode' => USE_QRCODE,

		"Social:Message" => $social_msg,
		"Social:Message:UrlEncoded" => urlencode($social_msg),
		"Social:Url" => "{$scheme}{$_SERVER['SERVER_NAME']}/?sheet={$app['id']}",
		"Social:Icon:Diaspora" => SOCIAL_DIR.'Diaspora.ico',
		"Social:Icon:Facebook" => SOCIAL_DIR.'Facebook.ico',
		"Social:Icon:GooglePlus" => SOCIAL_DIR.'GooglePlus.ico',
		"Social:Icon:Twitter" => SOCIAL_DIR.'Twitter.ico',

		'QrCode:ImagePath' => $qr_image_path,

		'Subtemplate:Antifeatures' => $antifeatures_list,
		'Subtemplate:Permissions' => $permissions_list,
		'Subtemplate:Categories' => $categories_list,
		'Subtemplate:Versions' => $old_versions_list,

	);

	$all_params = array_merge($params, app_placeholders($app, $lang));

	return parse_template("app_details", $all_params);

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
function app_package_placeholders($package, $prefix = '') {//{{{

	return array(
		$prefix . "Package:Version" => $package['version'],
		$prefix . "Package:SdkVersion" => $package['sdkver'],
		$prefix . "Package:Name" => $package['apkname'],
		$prefix . "Package:SizeBytes" => $package['size'],
		$prefix . "Package:SizeReadable" => util_readable_size($package['size']),
		$prefix . "Package:Hash:Type" => $package['hash']['type'],
		$prefix . "Package:Hash:Value" => $package['hash']['value'],
		$prefix . "Package:DateAdded" => $package['added'],
	);

};//}}}
function util_readable_size($size_bytes) {//{{{
	return $size_bytes / 1048572 > 1
		? round(($size_bytes / 1048572), 2) . " MB"
		: round(($size_bytes / 1024), 2) . " kB";
};//}}}
function app_placeholders($app, $lang) {//{{{

	$app_params = array(

		"App:Id" => $app['id'],
		"App:Id:Safe" => str_replace(array('.', ' '), '_', $app['id']),
		"App:Name" => $app['name'],
		"App:License" => translate('lic', $app['license'], $lang),
		"App:Icon" => ICONS_DIR_ABSTRACT.DIRECTORY_SEPARATOR.$app['icon'],
		"App:Summary"  => $app['summary'],
		"App:IconPath" => ICONS_DIR.DIRECTORY_SEPARATOR.$app['icon'],
		"App:DateUpdated" => $app['updated'],
		"App:DateAdded" => $app['added'],
		"App:Date" => $app['updated'] != $app['added'] ? $app['updated'] : $app['added'],
		"App:Requirements" => $app['requirements'],
		"App:Description" => $app['desc'],
		"App:AntiFeaturesCommaSeparated" => $app['antifeatures'],
		"App:Link" => '?sheet=' . $app['id'],

		"App:Link:Website" => $app['web'],
		"App:Link:IssueTracker" => $app['tracker'],
		"App:Link:SourceCode" => $app['source'],

		"App:Donate:HasDonationOptions" => $app['donate'] || $app['flattr'] || $app['bitcoin'],
		"App:Donate:Link" => $app['donate'],
		"App:Donate:FlattrLink" => $app['flattr'] ? FLATTR_SCHEME.$app['flattr'] : '',
		"App:Donate:BitcoinAddress" => $app['bitcoin'],

	);

	$text_params = array(

		"Text:Back" => translate('iface', 'back', $lang),
		"Text:Summary" => translate('iface', 'summary', $lang),
		"Text:Version" => translate('iface', 'version', $lang),
		"Text:PastVersions" => translate('iface', 'past_versions', $lang),
		"Text:DateUpdated" => translate('iface', 'updated', $lang),
		"Text:DateAdded" => translate('iface', 'added', $lang),
		"Text:Date" => $app['updated'] != $app['added'] ? translate('iface', 'updated', $lang) : translate('iface', 'added', $lang),
		"Text:License" => translate('iface', 'license', $lang),
		"Text:Size" => translate('iface', 'size', $lang),
		"Text:Requirements" => translate('iface', 'requirements', $lang),
		"Text:Description" => translate('iface', 'desc', $lang),
		"Text:Donate" => translate('iface', 'donate', $lang),
		"Text:Flattr" => translate('iface', 'flattr', $lang),
		"Text:Bitcoin" => translate('iface', 'bitcoin', $lang),
		"Text:SdkVersion" => translate('iface', 'sdkver', $lang),
		"Text:Website" => translate('iface', 'web', $lang),
		"Text:IssueTracker" => translate('iface', 'tracker', $lang),
		"Text:SourceCode" => translate('iface', 'sources', $lang),
		"Text:AntiFeatures" => translate('iface', 'antifeatures', $lang),
		"Text:Share" => translate('iface', 'share', $lang),
		'Text:Download' => translate('iface', 'download', $lang),
		'Text:Permissions' => translate('iface', 'permissions', $lang),
		'Text:Categories' => translate('iface', 'categories', $lang),
		"Text:Sheet" => translate('iface', 'sheet', $lang),
		"Text:Hash" => translate('iface', 'hash', $lang),

	);

	return array_merge(
		$app_params,
		$text_params,
		app_package_placeholders($app['packages'][0], "App:")
	);

}
//}}}
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

	return parse_template(
		'applist_app',
		app_placeholders($app, $lang)
	);
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

	return parse_template("latestapps_app", app_placeholders($app, $lang));

};
//}}}
function decore_applist($buffer, $lang, $nbr_app, $page) { //{{{
	$app_list = '';
	foreach($buffer as $app) {
		$app_list .= decore_app_light($app, $lang);
	}

	return parse_template(
		'applist',
		array(
			'Text:AppList' => translate('iface', 'applist', $lang),
			'Text:NumResults' => translate('iface', 'nbr_result', $lang),
			'Text:NoResults' => translate('iface', 'no_result', $lang),
			'Text:Page' => translate('iface', 'page', $lang),

			'AppList:NumResults' => $nbr_app,
			'AppList:HasNoResults' => $nbr_app <= 0,
			'AppList:HasResults' => $nbr_app > 0,

			'Subtemplate:AppItems' => $app_list,
			'Subtemplate:PagerItems' => build_pager($page, ceil($nbr_app / RECORDS_PER_PAGE), $lang),
		)
	);
};
//}}}
function decore_lastapplist($list, $lang) { //{{{
	$app_list = '';
	if (count($list) > 0) {
		foreach($list as $app) {
			$app_list .= decore_app_abstract($app, $lang);
		}
	}

	return parse_template(
		'latestapps',
		array(
			'Text:LatestAppList' => translate('iface', 'lastapplist', $lang),
			'Text:NoApps' => translate('iface', 'no_apps', $lang),
			'AppList:HasNoResults' => count($list) <= 0,
			'AppList:HasResults' => count($list) > 0,
			'Subtemplate:AppItems' => $app_list,
		)
	);
};
//}}}
function decore_categories($relations, $lang, $nbr_apps) { //{{{
	if (!is_array($relations) || count($relations) <= 0) {
		return '';
	}

	$recorded_cats = (isset($_SESSION['categories'])) ? array_flip($_SESSION['categories']) : array();
	$i = 1;
	$category_list = array();
	foreach ($relations as $category => $apps) {
		$category_list[$category] = parse_template(
			'search_categories_category',
			array(
				'Text:AltCategoryLink' => translate('iface', 'alt_cat_link', $lang),
				'Category:Index' => $i++,
				'Category:Id' => $category,
				'Category:Name' => translate('cat', $category, $lang),
				'Category:AppCount' => count($apps),
				'Category:Selected' => isset($recorded_cats[$category]) ? '1' : '0',
			)
		);
	};
	ksort($category_list);
	$category_list_template = implode('', $category_list);

	return parse_template(
		'search_categories',
		array(
			'Text:FormValue' => translate('iface', 'form_val', $lang),
			'Text:Categories' => translate('iface', 'categories', $lang),
			'Text:CategoriesList' => translate('iface', 'categories_list', $lang),
			'Text:AllCategoriesLink' => translate('iface', 'alt_cat_link', $lang),
			'Text:AllCategoriesLabel' => translate('iface', 'all_categories', $lang),

			'Search:Categories:TotalAppCount' => $nbr_apps,
			'Search:Categories:AllCategoriesSelected' => !isset($_SESSION['categories']) ? '1' : '0',
			'Search:Categories:Link' => 'index.php?search',

			'Subtemplate:CategoryItems' => $category_list_template,
		)
	);
};	
//}}}
function decore_licenses($licenses, $lang, $nbr_apps) { //{{{
	if (!is_array($licenses) || count($licenses) <= 0) {
		return '';
	}

	$license_list = array();
	$recorded_lics = (isset($_SESSION['licenses'])) ? array_flip($_SESSION['licenses']) : array();
	$i = 1;
	foreach ($licenses as $license => $apps) {
		$name_lic = translate('lic', $license, $lang);
		$license_list[$name_lic] = parse_template(
			'search_licenses_license',
			array(
				'Text:AltLicenseLink' => translate('iface', 'alt_lic_link', $lang),

				'License:Name' => $name_lic,
				'License:Id' => $license,
				'License:Index' => $i++,
				'License:AppCount' => count($apps),
				'License:Selected' => isset($recorded_lics[$license]) ? '1' : '0',
			)
		);
	};

	ksort($license_list);
	$license_list_template = implode('', $license_list);

	return parse_template(
		'search_licenses',
		array(
			'Text:License' => translate('iface', 'license', $lang),
			'Text:LicenseList' => translate('iface', 'license_list', $lang),
			'Text:AllLicenses' => translate('iface', 'all_licenses', $lang),
			'Text:AltLicenseLink' => translate('iface', 'alt_lic_link', $lang),
			'Text:FormValue' => translate('iface', 'form_val', $lang),

			'Search:Licenses:Link' => 'index.php?search',
			'Search:Licenses:TotalAppCount' => $nbr_apps,
			'Search:Licenses:AllLicensesSelected' => !isset($_SESSION['licenses']) ? '1' : '0',

			'Subtemplate:LicenseItems' => $license_list_template,
		)
	);
};	
//}}}
function decore_headers($repos, $lang_label, $lang) { //{{{
	$placeholders = array(
		'Config:UseQrCodes' => USE_QRCODE,

		'Text:LastModified' => translate('iface', 'last_modified', $lang),
		'Text:Language' => translate('iface', 'language', $lang),
		'Text:RepoQrCode' => translate('iface', 'qrcode_repo', $lang),

		'Repo:Name' => $repos['name'],
		'Repo:Url' => repo_qr_url($repos),
		'Repo:Link:Home' => 'index.php',
		'Repo:Description' => $repos['desc'],
		'Repo:LastModified' => date('Y-m-d', $repos['timestamp']),
		'Repo:IconPath' => 'Media/images/' . $repos['icon'],
		'Repo:QrCodePath' => REPOS_QRCODE,

		'Subtemplate:LangSelector' => build_lang_selector($lang_label, $lang),
	);
	return parse_template('main_headers', $placeholders);
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
function select_page($nbr_app) { //{{{
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
	return $page;
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
		$warning = translate('iface', 'warning_msg', $lang);
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
if (!isset($_REQUEST['format']) || !isset($formats[$_REQUEST['format']])) {//{{{ HTML case
	$list = build_list($relations, $licenses, $words, $repos['list']);
	$nbr_app = count($list);
	$page = select_page($nbr_app);
	$buffer = array_slice($list, ($page - 1) * RECORDS_PER_PAGE, RECORDS_PER_PAGE);
	//{{{ Building content
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
	//}}}

	$placeholders = array(
		'Config:UseFeeds' => USE_FEEDS,

		'Text:Warning' => translate('iface', 'warning_label', $lang),
		'Text:Menu' => translate('iface', 'menu', $lang),
		'Text:Search' => translate('iface', 'form_val', $lang),

		'Text:Nav:AccessFormVal' => translate('iface', 'access_form_val', $lang),
		'Text:Nav:FormVal' => translate('iface', 'form_val', $lang),
		'Text:Nav:BrowseCategories' => translate('iface', 'browse_cat', $lang),
		'Text:Nav:Categories' => translate('iface', 'categories', $lang),
		'Text:Nav:BrowseLicenses' => translate('iface', 'browse_lic', $lang),
		'Text:Nav:Licenses' => translate('iface', 'license', $lang),
		'Text:Nav:AccessLastAppList' => translate('iface', 'access_lastapplist', $lang),
		'Text:Nav:LastAppList' => translate('iface', 'lastapplist', $lang),

		'Text:Nav:AccessMenu' => $label_access_menu,
		'Text:Nav:Menu' => $label_menu,

		'Lang:Current' => $lang_label,

		'Page:Favicon' => (is_file('favicon.ico') && is_readable('favicon.ico')) ? 'favicon.ico' : '',
		'Page:Feed:Link' => FEED_NAME,
		'Page:Feed:Name' => $repos['name'] . ': ' . translate('iface', 'lastapplist', $lang),
		'Page:Nav:AnchorMenu' => $anchor_menu,
		'Page:WarningMessage' => $warning,
		'Page:FooterText' => MSG_FOOTER,

		'Repo:Name' => $repos['name'],

		'Subtemplate:Headers' => decore_headers($repos, $lang_label, $lang),
		'Subtemplate:MainContent' => $main,
		'Subtemplate:LastApp' => $lastapp,
		'Subtemplate:Tools:KeywordSearch' => build_form_search($lang, (isset($_SESSION['words'])) ? implode('+', $_SESSION['words']) : ''),
		'Subtemplate:Tools:Categories' => decore_categories($relations, $lang, $repos['nbr']),
		'Subtemplate:Tools:Reset' => build_reset($lang),
		'Subtemplate:Tools:Licenses' => decore_licenses($licenses, $lang, $repos['nbr']),
	);

	echo parse_template('main', $placeholders);

} elseif ($_REQUEST['format'] == 'json') {//{{{
	$list = build_list($relations, $licenses, $words, $repos['list']);
	$nbr_app = count($list);
	$page = select_page($nbr_app);
	$buffer = array_slice($list, ($page - 1) * RECORDS_PER_PAGE, RECORDS_PER_PAGE);
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
	};//}}}
} elseif ($_REQUEST['format'] == 'atom') { //{{{
	unset($_SESSION['licenses']);
	unset($_SESSION['categories']);
	unset($_SESSION['words']);
	unset($_SESSION['list']);
	unset($_SESSION['page']);
	$request = explode('?', $_SERVER['REQUEST_URI']);
	$request_tab = explode('&', $request[1]);
	$req_tab = array();
	foreach ($request_tab as $req) {
		if (stripos($req, 'format') === false && 
			stripos($req, 'page') === false && 
			stripos($req, 'reset') === false) 
					$req_tab[] = strtolower($req);
	};
	if (count($req_tab) > 0) {
		header('Content-type: application/atom+xml');
		sort($req_tab);
		$new_query = implode('&', $req_tab);
		$hash_req = hash(HASH_ALGO, $new_query);
		if (is_file(CACHE.DIRECTORY_SEPARATOR.$hash_req) && is_readable(CACHE.DIRECTORY_SEPARATOR.$hash_req)) {
			echo file_get_contents(CACHE.DIRECTORY_SEPARATOR.$hash_req);
		} else {
			$list = build_list($relations, $licenses, $words, $repos['list']);
			$url = str_replace(array('[', ']'), array('%5B', '%5D'), htmlentities($_SERVER['REQUEST_URI']));
			$feed = build_atom($repos, $list, $url);
			file_put_contents(CACHE.DIRECTORY_SEPARATOR.$hash_req, $feed);
			echo $feed;
		};
	};
};//}}}
