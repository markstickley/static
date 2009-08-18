<?php
/**
* FController - File & Folder controller
*/
class FController extends BaseController{
	
	public function indexAction(){
		$filePath = $this->getFilePath();
		$fs = new Filesystem();
		
		$this->view->assign('pagetitle',$filePath);
		
		if($fs->isFile($filePath)){
			//fetch all the info we might need about this file in any context and slap it in the view.
			//filename, web path, size, created, modified, permissions, visibility
			$file = $fs->getFile($filePath);
			$this->view->assign('filename',$file->getFilename());
			$this->view->assign('filepath',$file->getPath());
			$this->view->assign('filesize',$file->getSize());
			$this->view->assign('filecreated',$file->getCreated());
			$this->view->assign('filemodified',$file->getModified());
			$this->view->assign('filepermissions',$file->getPermissions());
			$this->view->assign('filevisibility',$file->getVisibility());
			return $this->view->render('file');
		}
		else if($fs->isFolder($filePath)){
			return $this->view->render('folder');
		}
		else{
			throw new Exception('File path not recognised');
		}
	}
	
	private function getFilePath(){
		$path = $this->registry->path;
		$path = array_slice($path, 1, count($path)-1, true);
		
		return rtrim(implode('/', $path), '/');
	}
}
