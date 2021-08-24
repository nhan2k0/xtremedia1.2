<?php
if (!defined('IN_MEDIA')) die("Hacking attempt");
$m_per_page = m_get_config('media_per_page');


$fields = "m_id, m_title, m_singer, m_type, m_viewed, m_downloaded, IF(m_lyric = '' OR m_lyric IS NULL,0,1) m_lyric";
$q = '';
if ($value[0] == 'List') {
	if (!$value[2]) $value[2] = 1;
	$page = $value[2];
	$limit = ($page-1)*$m_per_page;
	
	$check = $mysql->fetch_array($mysql->query("SELECT sub_id FROM ".$tb_prefix."cat WHERE cat_id = '$value[1]'"));
	if (!is_null($check['sub_id']) && $check['sub_id'] != 0) {
		$q = "SELECT ".$fields." FROM ".$tb_prefix."data WHERE m_cat = '$value[1]' ORDER BY m_id DESC LIMIT ".$limit.",$m_per_page";
		$tt = m_get_tt("m_cat = '".$value[1]."'");
	}
	else {
		$list_q = $mysql->query("SELECT cat_id FROM ".$tb_prefix."cat WHERE sub_id = '$value[1]'");
		$in_sql = '';
		while ($list_r = $mysql->fetch_array($list_q)) $in_sql .= "'".$list_r['cat_id']."',";
		$in_sql = substr($in_sql,0,-1);
		if (!$in_sql) $in_sql = -1;
		$q = "SELECT ".$fields." FROM ".$tb_prefix."data WHERE m_cat IN ($in_sql) ORDER BY m_id DESC LIMIT ".$limit.",$m_per_page";
		$tt = m_get_tt("m_cat IN (".$in_sql.")");
	}
}
elseif (in_array($value[0],array('Top_Download','Top_Play'))) {
	if (!$value[1]) $value[1] = 1;
	$page = $value[1];
	$limit = ($page-1)*$m_per_page;
	
	if ($value[0] == 'Top_Download') $order = 'm_downloaded';
	elseif ($value[0] == 'Top_Play') $order = 'm_viewed';
	
	$q = "SELECT ".$fields." FROM ".$tb_prefix."data ORDER BY ".$order." DESC LIMIT ".$limit.",$m_per_page";
	$tt = m_get_tt();
}
elseif ($value[0] == 'Home') {
	if (!$value[1]) $value[1] = 1;
	$page = $value[1];
	$limit = ($page-1)*$m_per_page;
	
	$q = "SELECT ".$fields." FROM ".$tb_prefix."data ORDER BY m_id DESC LIMIT ".$limit.",$m_per_page";
	$tt = m_get_tt();
}
if ($q) $q = $mysql->query($q);
if ($mysql->num_rows($q)) {
	if ($value[0] == 'List') {
		$cat_tit = $mysql->fetch_array($mysql->query("SELECT cat_name FROM ".$tb_prefix."cat WHERE cat_id = '$value[1]'"));
		$cat_tit = $cat_tit['cat_name'];
	}
	elseif ($value[0] == 'Home') $cat_tit = 'Danh sách ca khúc';
	
	$main = $tpl->get_tpl('list');
	$t['row'] = $tpl->get_block_from_str($main,'list_row',1);
	
	$html = '';
	while ($r = $mysql->fetch_array($q)) {
		static $i = 0;
		$class = (fmod($i,2) == 0)?'m_list':'m_list_2';
		$m_id = $r['m_id'];
		//$m_title = $r['m_title'];
		//$m_viewed = $r['m_viewed'];
		//$m_downloaded = $r['m_downloaded'];
		//$m_singer = $r['m_singer'];
		
		$lyric = ($r['m_lyric'])?"<img src='{TPL_LINK}/img/media/ok.gif'>":'';
		
		$singer = m_get_data('SINGER',$r['m_singer']);
		switch ($r['m_type']) {
			case 1 : $media_type = 'music'; break;
			case 2 : $media_type = 'flash'; break;
			case 3 : $media_type = 'movie'; break;
		}
		$media_type = "<img src='{TPL_LINK}/img/media/type/$media_type.gif'>";
		$html .= $tpl->assign_vars($t['row'],
			array(
				'song.CLASS' => $class,
				'song.TYPE' => $media_type,
				'song.ID' => $r['m_id'],
				'song.URL' => '#Play,'.$r['m_id'],
				'song.TITLE' => $r['m_title'],
				'song.VIEWED' => $r['m_viewed'],
				'song.DOWNLOADED' => $r['m_downloaded'],
				'song.LYRIC' => $lyric,
				'singer.NAME' => $singer,
				'singer.URL' => '#Singer,'.$r['m_singer'],
			)
		);
		$i++;
	}
	$class = (fmod($i,2) == 0)?'m_list':'m_list_2';
	$main = $tpl->assign_vars($main,
		array(
			'CLASS' => $class,
			'CAT_TITLE' => $cat_tit,
			'TOTAL'	=> $tt,
			'VIEW_PAGES' => m_viewpages($tt,$m_per_page,$page),
		)
	);
	
	$main = $tpl->assign_blocks_content($main,array(
			'list'	=>	$html,
		)
	);
	
	$tpl->parse_tpl($main);
}
else echo "<center><b>Không có dữ liệu trong mục này.</b></center>";

?>