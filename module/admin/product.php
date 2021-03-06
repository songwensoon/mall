<?php
/**
 * @copyright   2008-2012 简好技术 <http://www.phpshe.com>
 * @creatdate   2012-0501 koyshe <koyshe@gmail.com>
 */
$menumark = 'product';
pe_lead('hook/category.hook.php');
$category_treelist = category_treelist();
$cache_rule = cache::get('rule');
switch ($act) {
	//#####################@ 商品增加 @#####################//
	case 'add':
		if (isset($_p_pesubmit)) {
			if ($_FILES['product_logo']['size']) {
				pe_lead('include/class/upload.class.php');
				$upload = new upload($_FILES['product_logo']);
				$_p_info['product_logo'] = $upload->filehost;
			}
			$_p_info['product_atime'] = $_p_info['product_atime'] ? strtotime($_p_info['product_atime']) : time();

			if(!empty($_p_rule_id)){
				$_p_info['rule_id'] = implode(",",$_p_rule_id);
				$prorule = array();
				foreach($_POST as $key=>$value){
					if(in_array($key,$_p_rule_id)){
						$value = array_filter($value);
						if(!empty($value)){
							foreach($value as $k=>$v){
								$prorule[$k][] = $v;
							}
						}
					}
				}
			}
			if ($product_id = $db->pe_insert('product', pe_dbhold($_p_info, array('product_text')))) {
				if(!empty($prorule)){
					$prorule_id = array();
					foreach($prorule as $kk=>$vv){
						$prorule_id = implode(",",$vv);
						$product_money = $_p_product_money[$kk];
						$product_num = $_p_product_num[$kk];
						$data = array(
						'prorule_id'=>$prorule_id,
						'product_money'=>$product_money,
						'product_num' => '',
						'product_id' => $product_id
						);
						$db->pe_insert('prorule',$data);
					}
				}
				pe_success('商品发布成功!', 'admin.php?mod=product&state=1');
			} else {
				pe_error('商品发布失败...');
			}
		}
		$rule_list = $db->pe_selectall('rule', '', '*', array());
		foreach ($rule_list as $k => $v) {
			$rule_list[$k]['ruledata_list'] = $db->pe_selectall('ruledata', array('rule_id'=>$v['rule_id'],'order by'=>'`ruledata_order` Asc'));
		}
		$seo = pe_seo($menutitle='发布商品', '', '', 'admin');
		include(pe_tpl('product_add.html'));
	break;
	//#####################@ 商品修改 @#####################//
	case 'edit':
		$product_id = intval($_g_id);
		if (isset($_p_pesubmit)) {
			if ($_FILES['product_logo']['size']) {
				pe_lead('include/class/upload.class.php');
				$upload = new upload($_FILES['product_logo']);
				$_p_info['product_logo'] = $upload->filehost;
			}
			$_p_info['product_atime'] = $_p_info['product_atime'] ? strtotime($_p_info['product_atime']) : time();
			if(!empty($_p_rule_id)){
				$_p_info['rule_id'] = implode(",",$_p_rule_id);
				$prorule = array();
				foreach($_POST as $key=>$value){
					if(in_array($key,$_p_rule_id)){
						$value = array_filter($value);
						if(!empty($value)){
							foreach($value as $k=>$v){
								$prorule[$k][] = $v;
							}
						}
					}
				}
			}
			if ($db->pe_update('product', array('product_id'=>$product_id), pe_dbhold($_p_info, array('product_text')))) {
				$db->pe_delete('prorule', array('product_id'=>$product_id));
				if(!empty($prorule)){
					$prorule_id = array();
					foreach($prorule as $kk=>$vv){
						$prorule_id = implode(",",$vv);
						$product_money = $_p_product_money[$kk];
						$product_num = $_p_product_num[$kk];
						$data = array(
						'prorule_id'=>$prorule_id,
						'product_money'=>$product_money,
						'product_num' => $product_num,
						'product_id' => $product_id
						);
						$db->pe_insert('prorule',$data);
					}
				}

				pe_success('商品修改成功!', $_g_fromto);
			}
			else {
				pe_error('商品修改失败!' );
			}
		}
		$info = $db->pe_select('product', array('product_id'=>$product_id));
		$info['rule_id'] = explode(",",$info['rule_id']);
		$rule_list = $db->pe_selectall('rule', '', '*', array());
		$prorule_list = $db->pe_selectall('prorule',array('product_id'=>$product_id), '*', array());
		
		foreach ($rule_list as $k => $v) {
			$rule_list[$k]['ruledata_list'] = $db->pe_selectall('ruledata', array('rule_id'=>$v['rule_id'],'order by'=>'`ruledata_order` Asc'));
		}
		$seo = pe_seo($menutitle='修改商品', '', '', 'admin');
		include(pe_tpl('product_add.html'));
	break;
	//#####################@ 商品删除 @#####################//
	case 'del':
		if ($db->pe_delete('product', array('product_id'=>is_array($_p_product_id) ? $_p_product_id : $_g_id))) {
			//删除商品相关表
			$db->pe_delete('collect', array('product_id'=>is_array($_p_product_id) ? $_p_product_id : $_g_id));	
			$db->pe_delete('comment', array('product_id'=>is_array($_p_product_id) ? $_p_product_id : $_g_id));
			$db->pe_delete('ask', array('product_id'=>is_array($_p_product_id) ? $_p_product_id : $_g_id));
			$db->pe_delete('prorule', array('product_id'=>is_array($_p_product_id) ? $_p_product_id : $_g_id));
			pe_success('商品删除成功!');
		}
		else {
			pe_error('商品删除失败...');
		}
	break;
	//#####################@ 商品上下架 @#####################//
	case 'state':
		foreach ($_p_product_id as $v) {
			$result = $db->pe_update('product', array('product_id'=>$v), array('product_state'=>$_g_state));
		}
		if ($result) {
			pe_success("操作成功!");
		}
		else {
			pe_error("操作失败...");
		}
	break;
	//#####################@ 商品上下架 @#####################//
	case 'tuijian':
		foreach ($_p_product_id as $v) {
			$result = $db->pe_update('product', array('product_id'=>$v), array('product_istuijian'=>$_g_tuijian));
		}
		if ($result) {
			pe_success("操作成功!");
		}
		else {
			pe_error("操作失败...");
		}
	break;
	//#####################@ 商品列表 @#####################//
	default :
		$cache_category = cache::get('category');
		$orderby_arr['clicknum|desc'] = '浏览量(多到少)';
		$orderby_arr['clicknum|asc'] = '浏览量(少到多)';
		$orderby_arr['sellnum|desc'] = '销售量(多到少)';
		$orderby_arr['sellnum|asc'] = '销售量(少到多)';
		$orderby_arr['num|desc'] = '库存量(多到少)';
		$orderby_arr['num|asc'] = '库存量(少到多)';
		$orderby_arr['collectnum|desc'] = '收藏数(多到少)';
		$orderby_arr['collectnum|asc'] = '收藏数(少到多)';
		$orderby_arr['asknum|desc'] = '咨询数(多到少)';
		$orderby_arr['asknum|asc'] = '咨询数(少到多)';
		$orderby_arr['commentnum|desc'] = '评价数(多到少)';
		$orderby_arr['commentnum|asc'] = '评价数(少到多)';
		$filter_arr = array('istuijian|1'=>'推荐商品', 'wlmoney|0'=>'包邮商品', 'num|0'=>'售空商品');

		$_g_name && $sqlwhere .= " and `product_name` like '%{$_g_name}%'";
		$_g_state && $sqlwhere .= " and `product_state` = '{$_g_state}'";
		$_g_category_id && $sqlwhere .= is_array($category_cidarr = category_cidarr($_g_category_id)) ? " and `category_id` in('".implode("','", $category_cidarr)."')" : " and `category_id` = '{$_g_category_id}'";
		if ($_g_filter) {
			$filter = explode('|', $_g_filter);
			$sqlwhere .= " and `product_{$filter[0]}` = {$filter[1]}";
		}
		$sqlwhere .= ' order by';
		if ($_g_orderby) {
			$orderby = explode('|', $_g_orderby);
			$sqlwhere .= " `product_{$orderby[0]}` {$orderby[1]},";
		}
		$sqlwhere .= " `product_id` desc";
		$info_list = $db->pe_selectall('product', $sqlwhere, '*', array(20, $_g_page));

		$seo = pe_seo($menutitle='商品列表', '', '', 'admin');
		include(pe_tpl('product_list.html'));
	break;
}
?>
