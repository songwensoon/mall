<?php
/**
 * @copyright   2008-2012 简好技术 <http://www.phpshe.com>
 * @creatdate   2012-0501 koyshe <koyshe@gmail.com>
 */
$menumark = 'rule';
pe_lead('hook/cache.hook.php');
pe_lead('include/class/upload.class.php');
switch ($act) {
	//#####################@ 增加分类 @#####################//
	case 'add':
		if (isset($_p_pesubmit)) {
			if ($rule_id = $db->pe_insert('rule', $_p_info)) {
				if (!empty($_p_name)) {
					$i=0;
					foreach($_p_name as $key=>$value){
						$data = array(
						'ruledata_name'=>$value,
						'rule_id'=>$rule_id,
						'ruledata_order'=>$i
						);
						$i++;
						$ruledata_id = $db->pe_insert('ruledata', $data);
					}
				}
				cache_write('rule');
				pe_success('规格增加成功!', 'admin.php?mod=rule');
			}
			else {
				pe_error('规格增加失败!');
			}
		}
		$seo = pe_seo($menutitle='增加规格', '', '', 'admin');
		include(pe_tpl('rule_add.html'));
	break;
	//#####################@ 修改分类 @#####################//
	case 'edit':
		$rule_id = intval($_g_id);
		if (isset($_p_pesubmit)) {
			$new_count = count($_p_isnew);
			$num = count($_p_name);
			if ($db->pe_update('rule', array('rule_id'=>$rule_id), $_p_info)) {
				if (!empty($_p_name)) {
					$i=1;
					foreach($_p_name as $key=>$value){
						$data = array(
						'ruledata_name'=>$value,
						'ruledata_order'=>$i
						);
						
						if($num-$i>=$new_count){
							$db->pe_update('ruledata', array('ruledata_id'=>$key), $data);
						}else{
							$data = array(
							'ruledata_name'=>$value,
							'rule_id'=>$rule_id,
							'ruledata_order'=>$i
							);
							$ruledata_id = $db->pe_insert('ruledata', $data);
						}
						$i++;
					}
				}
				cache_write('rule');
				pe_success('规格修改成功!', 'admin.php?mod=rule');
			}
			else {
				pe_error('规格修改失败...');
			}
		}
		$info = $db->pe_select('rule', array('rule_id'=>$rule_id));
		$infotdata = $db->pe_selectall('ruledata',array('rule_id'=>$rule_id,'order by'=>'`ruledata_order` Asc'));
		$seo = pe_seo($menutitle='修改分类', '', '', 'admin');
		include(pe_tpl('rule_add.html'));
	break;
	//#####################@ 分类排序 @#####################//
	case 'order':
		foreach ($_p_category_order as $k=>$v) {
			$result = $db->pe_update('category', array('category_id'=>$k), array('category_order'=>$v));
		}
		if ($result) {
			cache_write('category');
			pe_success('分类排序成功!');
		}
		else {
			pe_error('分类排序失败...');
		}
	break;
	//#####################@ 分类删除 @#####################//
	case 'del':
		if ($db->pe_delete('rule', array('rule_id'=>is_array($_p_rule_id) ? $_p_rule_id : $_g_id))) {
			$db->pe_delete('ruledata', array('rule_id'=>is_array($_p_rule_id) ? $_p_rule_id : $_g_id));
			cache_write('rule');
			pe_success('规格删除成功!');
		}
		else {
			pe_error('规格删除失败...');
		}
	break;
	//#####################@ 规格属性删除 @#####################//
	case 'del_data':
		if ($db->pe_delete('ruledata', array('ruledata_id'=>$_g_id))) {
			echo json_encode(array('result'=>true,'show'=>'规格删除成功!'));
		} else {
			echo json_encode(array('result'=>false,'show'=>'规格删除失败...'));
		}
	break;
	//#####################@ 商品规格列表 @#####################//
	default :
		$info_list = $db->pe_selectall('rule', '', '*', array());
		foreach ($info_list as $k => $v) {
			$info_list[$k]['rule_list'] = $db->pe_selectall('ruledata', array('rule_id'=>$v['rule_id'],'order by'=>'`ruledata_order` Asc'));
		}
		$seo = pe_seo($menutitle='商品规格', '', '', 'admin');
		include(pe_tpl('rule_list.html'));
	break;
}
?>
