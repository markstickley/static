<?php
	$breadcrumb = '';
	$enclosingfolder = $this->dest_folder;
	if(!is_null($enclosingfolder)){
		while($enclosingfolder){
			$breadcrumb = '<a href="'.$enclosingfolder->getFilesystemUrl().'">'.$enclosingfolder->getName().'</a> / '.$breadcrumb;
			$enclosingfolder = $enclosingfolder->getEnclosingFolder();
		}
		echo '<a href="'.WEBROOT.'/f/">Home</a> / '.$breadcrumb;
	}