<?php
	// This generates a list of <option>s displaying a folder tree, excluding (if set) the branch with id $this->exclude_branch
	
	$options = array();
	$options[] = '<option value="">Please select...</option>';
	$options[] = '<option value="0">/</option>';
	$options = array_merge($options, getOptionsBranch(0,1,$this->exclude_branch));

	function getOptionsBranch($branchid,$depth,$exclude){
		$db = Database::getInstance();
		
		$options = array();
		//$branch = $db->queryrow("SELECT name FROM folders WHERE id=".$branchid,'assoc');
		$children = $db->query("SELECT name, id FROM folders WHERE parent=".$branchid,'assoc');
		if($children){
			foreach($children as $child){
				if($child['id']!=$exclude){
					$options[] = '<option value="'.$child['id'].'">'.str_repeat('&nbsp;', $depth*3).'/'.$child['name'].'</option>';
					$options = array_merge($options, getOptionsBranch($child['id'],$depth+1,$exclude));
				}
			}
		}
		
		return $options;
	}
	
	echo implode('', $options);