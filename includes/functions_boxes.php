<?php
if (!defined('IN_MEDIA')) die("Hacking attempt");
function box_main_menu($file_tpl = 'main_menu') {
	global $tpl;
	return $tpl->get_box($file_tpl);
}

function box_gift($file_tpl = 'gift') {
	global $tpl;
	return $tpl->get_box($file_tpl);
}

function box_user_menu($file_tpl_1 = 'user_logged',$file_tpl_2 = 'user_guest') {
	global $mysql, $isLoggedIn, $tpl;
	if ($isLoggedIn) {
		$html = $tpl->get_box($file_tpl_1);
		$html = $tpl->assign_vars($html,
			array(
				'user.NAME'	=>	m_get_data('USER',$_SESSION['user_id']),
			)
		);
	}
	else {
		$html = $tpl->get_box($file_tpl_2);
	}
	return $html;
}

function box_category_menu($file_tpl = 'category_menu') {
	global $mysql,$tb_prefix,$tpl;
	$main = $tpl->get_box($file_tpl);
	
	$t['parent'] = $tpl->get_block_from_str($main,'cat_list.parent',1);
	$t['sub'] = $tpl->get_block_from_str($main,'cat_list.sub',1);
	
	$q = $mysql->query("SELECT cat_id, cat_name FROM ".$tb_prefix."cat WHERE sub_id IS NULL OR sub_id = 0 ORDER BY cat_order ASC");
	while ($r = $mysql->fetch_array($q)) {
		$html .= $tpl->assign_vars($t['parent'],
			array(
				'cat_parent.URL' => '#List,'.$r['cat_id'],
				'cat_parent.NAME' => $r['cat_name'],
			)
		);
		$q2 = $mysql->query("SELECT cat_id, cat_name FROM ".$tb_prefix."cat WHERE sub_id = '".$r['cat_id']."' ORDER BY cat_order ASC");
		while ($r2 = $mysql->fetch_array($q2)) {
			$html .= $tpl->assign_vars($t['sub'],
				array(
					'cat_sub.URL' => '#List,'.$r2['cat_id'],
					'cat_sub.NAME' => $r2['cat_name'],
				)
			);
		}
	}
	$html = $tpl->assign_blocks_content($main,array(
		'cat_list'	=>	$html
		)
	);
	return $html;
}

function box_album($type = 'New', $number = 10, $apr = 1, $file_tpl = 'new_album') {
	global $mysql,$tb_prefix,$tpl;
	if ($type == 'New') {
		$result = $mysql->query("SELECT album_id, album_name, album_singer, album_img FROM ".$tb_prefix."album ORDER BY album_id DESC LIMIT $number");
		$block = 'new_album';
	}
	$main = $tpl->get_box($file_tpl);
	
	$t['link'] = $tpl->get_block_from_str($main,$block.'.row',1);
	$t['begin_tag'] = $tpl->get_block_from_str($main,$block.'.begin_tag',1);
	$t['end_tag'] = $tpl->get_block_from_str($main,$block.'.end_tag',1);
	
	if (!$mysql->num_rows($result)) $html = "Chưa có";
	$i = 0;
	while ($r = $mysql->fetch_array($result)) {
		$album_img = m_get_img('Album',$r['album_img']);
		if ($t['begin_tag'] && fmod($i,$apr) == 0) $html .= $t['begin_tag'];
		$html .= $tpl->assign_vars($t['link'],
			array(
				'album.URL'		=>	'#Album,'.$r['album_id'],
				'album.NAME'	=>	$r['album_name'],
				'album.IMG'		=>	$album_img,
				'singer.URL'	=>	'#Singer,'.$r['album_singer'],
				'singer.NAME'	=>	m_get_data('SINGER',$r['album_singer']),
			)
		);
		if ($t['end_tag'] && fmod($i,$apr) == $apr - 1) $html .= $t['end_tag'];
		$i++;
	}
	if ($t['end_tag'] && fmod($i,$apr) != $apr - 1) $html .= $t['end_tag'];
	
	$html = $tpl->assign_blocks_content($main,array(
		'new_album'	=>	$html
		)
	);
	return $html;
}

function box_stats($file_tpl = 'stats') {
	global $mysql,$tb_prefix,$tpl;
	$html = $tpl->get_box($file_tpl);
	$r = $mysql->fetch_array($mysql->query("SELECT SUM(m.m_viewed) views, COUNT(m.m_id) songs, SUM(m.m_downloaded) downloads FROM ".$tb_prefix."data m"));
	extract($r);
	$r = $mysql->fetch_array($mysql->query("SELECT COUNT(singer_id) singers FROM ".$tb_prefix."singer"));
	extract($r);
	$r = $mysql->fetch_array($mysql->query("SELECT COUNT(user_id) users FROM ".$tb_prefix."user"));
	extract($r);
	$r = $mysql->fetch_array($mysql->query("SELECT COUNT(album_id) albums FROM ".$tb_prefix."album"));
	extract($r);
	$html = $tpl->assign_vars($html,
		array(
			'stat.SINGERS'	=>	$singers,
			'stat.SONGS'	=>	$songs,
			'stat.ALBUMS'	=>	$albums,
			'stat.USERS'	=>	$users,
			'stat.VIEWS'	=>	max(0,$views),
			'stat.DOWNLOADS'	=>	max(0,$downloads),
			'stat.COUNTER'	=>	m_counter(),
		)
	);
	return $html;
}

function box_tpl_list($file_tpl = 'tpl_list') {
	global $mysql,$tpl,$tb_prefix;
	$list = "<select name=tpl_name>";
	$q = $mysql->query("SELECT * FROM ".$tb_prefix."tpl ORDER BY 'tpl_order' ASC");
	while ($r = $mysql->fetch_array($q)) {
		$list .= "<option value='".$r['tpl_sname']."'".(($_SESSION['current_tpl']==$r['tpl_sname'])?' selected':'').">".$r['tpl_fname']."</option>";
	}
	$list .= "</select>";
	$html = $tpl->get_box($file_tpl);
	$html = $tpl->assign_vars($html,
		array(
			'TPL_LIST' => $list,
		)
	);
	return $html;
}

function box_announcement($file_tpl = 'announcement') {
	global $mysql, $tpl;
	$html = $tpl->get_box($file_tpl);
	$contents = stripslashes(m_get_config('announcement'));
	$contents = m_text_tidy($contents);
	if (!$contents) return '';
	$html = $tpl->assign_vars($html,
		array(
			'ANNOUNCEMENT'	=>	$contents,
		)
	);
	return $html;
}

function box_singer_list($type, $file_tpl) {
	global $mysql,$tb_prefix,$tpl;
	$q = $mysql->query("SELECT * FROM ".$tb_prefix."singer WHERE singer_type = '".$type."' ORDER BY singer_name ASC");
	switch ($type) {
		case 1 :
			if (!$file_tpl) $file_tpl = 'singer_vn';
			$block = 'vnsinger';
			$unknownID = -1;
		break;
		case 2 :
			if (!$file_tpl) $file_tpl = 'singer_fr';
			$block = 'frsinger';
			$unknownID = -2;
		break;
	}
	
	$main = $tpl->get_box($file_tpl);
	$t['link'] = $tpl->get_block_from_str($main,$block.'.row',1);
	
	$html = $tpl->assign_vars($t['link'],
		array(
			'singer.NAME' => 'Chưa biết',
			'singer.URL'	=>	'#Singer,'.$unknownID,
		)
	);
	while ($r = $mysql->fetch_array($q)) {
		$html .= $tpl->assign_vars($t['link'],
			array(
				'singer.NAME' => $r['singer_name'],
				'singer.URL'	=>	'#Singer,'.$r['singer_id'],
			)
		);
	}
	$html = $tpl->assign_blocks_content($main,array(
			$block	=>	$html
		)
	);
	return $html;
}

function box_top_media($type,$number = 10) {
	global $mysql,$tb_prefix,$tpl;
	if ($type == 'Download_Month') {
		$result = $mysql->query("SELECT m_id, m_title FROM ".$tb_prefix."data WHERE m_downloaded_month > 0 ORDER BY m_downloaded DESC LIMIT ".$number);
		$block = 'top_download';
	}
	elseif ($type == 'Download') {
		$result = $mysql->query("SELECT m_id, m_title FROM ".$tb_prefix."data WHERE m_downloaded > 0 ORDER BY m_downloaded DESC LIMIT ".$number);
		$block = 'top_download';
	}
	elseif ($type == 'Play_Month') {
		$result = $mysql->query("SELECT m_id, m_title FROM ".$tb_prefix."data WHERE m_viewed_month > 0 ORDER BY m_viewed DESC LIMIT ".$number);
		$block = 'top_play';
	}
	elseif ($type == 'Play') {
		$result = $mysql->query("SELECT m_id, m_title FROM ".$tb_prefix."data WHERE m_viewed > 0 ORDER BY m_viewed DESC LIMIT ".$number);
		$block = 'top_play';
	}
	elseif ($type == 'Newest') {
		$result = $mysql->query("SELECT m_id, m_title FROM ".$tb_prefix."data ORDER BY m_id DESC LIMIT ".$number);
		$block = 'top_newest';
	}
	$main = $tpl->get_box($block);
	$t['link'] = $tpl->get_block_from_str($main,$block.'.row',1);
	$n = 0;
	if (!$mysql->num_rows($result)) $html = "Chưa có";
	else
		while ($r = $mysql->fetch_array($result)) {
			$n++;
			$html .= $tpl->assign_vars($t['link'],
				array(
					'song.ID' => $r['m_id'],
					'song.TITLE' => $r['m_title'],
					'song.URL'	=>	'#Play,'.$r['m_id'],
					'song.NUMBER'	=>	sprintf('%0'.strlen($number).'d',$n),
				)
			);
		}
	$main = $tpl->assign_vars($main,
		array(
			'top.MONTH'	=> m_get_config('current_month'),
		)
	);
	$main = $tpl->assign_blocks_content($main,array(
		$block	=>	$html
		)
	);
	return $main;
}

function box_playlist($reload = false, $file_tpl = 'playlist') {
	global $mysql, $tpl, $isLoggedIn, $tb_prefix, $add_id, $remove_id;
	$html = $tpl->get_box($file_tpl);
	if ($isLoggedIn) {
		$t['row'] = $tpl->get_block_from_str($html,'playlist.row',1);
		$content = '';
		$playlist_id = m_get_data('USER',$_SESSION['user_id'],'user_playlist_id');
		$playlist = m_get_data('PLAYLIST',$playlist_id);
		$playlist = trim($playlist,',');
		if ($playlist) {
			$q = $mysql->query("SELECT m_id, m_title FROM ".$tb_prefix."data WHERE m_id IN (".$playlist.")");
			while ($r = $mysql->fetch_array($q)) {
				$id = $r['m_id'];
				$title = $r['m_title'];
				$content .= $tpl->assign_vars($t['row'],
					array(
						'song.ID'	=>	$id,
						'song.URL'	=>	'#Play,'.$id,
						'song.TITLE'	=>	$title,
					)
				);
			}
		}
		else {
			$content = "Playlist rỗng";
		}
		if ($reload) {
			return $content;
		}
		else {
			$html = $tpl->unset_block($html,array('guest_block'));
			
			$html = $tpl->assign_blocks_content($html,
				array(
					'playlist'	=>	$content,
				)
			);
		}
	}
	else {
		$html = $tpl->unset_block($html,array('user_block'));
	}
	return $html;
}

function box_ads($file_tpl = 'ads') {
	global $mysql,$tb_prefix,$tpl;
	$result = $mysql->query("SELECT * FROM ".$tb_prefix."ads ORDER BY ads_count DESC");
	$main = $tpl->get_box($file_tpl);
	$t['ads'] = $tpl->get_block_from_str($main,'ads.row',1);
	
	if (!$mysql->num_rows($result)) $html = "Chưa có";
	while ($r = $mysql->fetch_array($result)) {
		$html .= $tpl->assign_vars($t['ads'],
			array(
				'ads.IMG'	=>	$r['ads_img'],
				'ads.URL'	=>	$r['ads_url'],
				'ads.WEB'	=>	$r['ads_web'],
			)
		);
	}
	
	$html = $tpl->assign_blocks_content($main,array(
		'ads'	=>	$html
		)
	);
	
	return $html;
}
?>