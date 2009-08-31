<?php
/**
* FController - File & Folder controller
*/
class FController extends BaseController{
	
	private $filePath;
	private $fs;
	
	public function indexAction(){
		$this->filePath = $this->getFilePath();
		$this->fs = new Filesystem();
		
		switch($this->registry->request_method){
			case 'post':
				return $this->indexPost();
				break;
			case 'put':
				return $this->indexPut();
				break;
			case 'delete':
				return $this->indexDelete();
				break;
			case 'get':
			default:
				return $this->indexGet();
				break;

		}
	}
	
	private function indexGet(){
		$this->view->assign('pagetitle',$this->filePath);
		
		if(empty($this->filePath)){
			// If it's empty then we should show the root folder
			$this->view->assign('foldername','Home');
			$this->view->assign('folderenclosingfolder',null);
			
			$this->view->assign('newfolderurl',WEBROOT.'/f');
			$this->view->assign('uploadfileurl',WEBROOT.'/f');
			
			$this->view->assign('foldercontents',Folder::getRootContents());
			
			return $this->view->render('folder');
		}
		else if($this->fs->isFolder($this->filePath)){
			//fetch all the info we might need about this file in any context and slap it in the view.
			//filename, web path, size, created, modified, permissions, visibility
			$folder = $this->fs->getFolder($this->filePath);
			$this->view->assign('folderid',$folder->getId());
			$this->view->assign('foldername',$folder->getName());
			$this->view->assign('foldercreated',$folder->getCreated());
			$this->view->assign('foldermodified',$folder->getModified());
			$this->view->assign('folderpermissions',$folder->getPermissions());
			$this->view->assign('foldervisibility',$folder->getVisibility());
			$this->view->assign('folderdeletelink',$folder->getFilesystemUrl().'?_method=delete');
			$this->view->assign('foldereditlink',$folder->getFilesystemUrl().'?_editmode=1');
			$this->view->assign('foldereditmode',$this->registry->var->get('_editmode','0'));
			$this->view->assign('folderenclosingfolder',$folder->getEnclosingFolder());
			
			$this->view->assign('folderurl',$folder->getFileSystemUrl());
			$this->view->assign('newfolderurl',$folder->getFileSystemUrl());
			$this->view->assign('uploadfileurl',$folder->getFileSystemUrl());
			$this->view->assign('updateurl',$folder->getFilesystemUrl().'?_method=put');
			
			$this->view->assign('foldercontents',$folder->getContents());
			
			return $this->view->render('folder');
		}
		else if($this->fs->isFile($this->filePath)){
			//fetch all the info we might need about this file in any context and slap it in the view.
			//filename, web path, size, created, modified, permissions, visibility
			$file = $this->fs->getFile($this->filePath);
			$this->view->assign('fileid',$file->getId());
			$this->view->assign('filename',$file->getFilename());
			$this->view->assign('filepath',$file->getPath());
			$this->view->assign('filesize',$file->getAutoSize());
			$this->view->assign('filecreated',$file->getCreated());
			$this->view->assign('filemodified',$file->getModified());
			$this->view->assign('filepermissions',$file->getPermissions());
			$this->view->assign('filevisibility',$file->getVisibility());
			$simpletype = $file->getSimpleType();
			$this->view->assign('filetype',$simpletype);
			if($file->isPreviewable()){
				$this->view->assign('fileiconurl',$file->getPreview());
			}
			else if(file_exists(FSROOT.'/static/img/document-icons/'.$simpletype.'.png')){
				$this->view->assign('fileiconurl',WEBROOT.'/static/img/document-icons/'.$simpletype.'.png');
			}
			else{
				$this->view->assign('fileiconurl',WEBROOT.'/static/img/document-icons/unknown.png');
			}
			$this->view->assign('filedownloadlink',$file->getDownloadUrl());
			$this->view->assign('filedeletelink',$file->getFilesystemUrl().'?_method=delete');
			$this->view->assign('fileeditlink',$file->getFilesystemUrl().'?_editmode=1');
			$this->view->assign('fileeditmode',$this->registry->var->get('_editmode','0'));
			$this->view->assign('fileenclosingfolder',$file->getEnclosingFolder());
			
			$this->view->assign('fileurl',$file->getFileSystemUrl());
			$this->view->assign('updateurl',$file->getFilesystemUrl().'?_method=put');
			
			return $this->view->render('file');
		}
		else{
			throw new Exception('File path not recognised');
		}
	}
	
	private function indexPost(){
		//if we're posting to a page within /f/ then we're either creating a new folder or uploading a file.
		//since we're creating new stuff (post), better check that the address doesn't point to old stuff
		if(!$this->fs->isFile($this->filePath) && !$this->fs->isFolder($this->filePath)){
			//That's fine, it's a new item. Gotta decide on whether it's a new file or a new folder.
			//We can tell that by looking at the post vars
			if($this->registry->var->get('folder','')!=''){
				//new folder.
				$f = new Folder($this->filePath);
				$f->create();
				return $this->view->render('postfolder');
			}
			else if(isset($_FILES['file'])){
				//new file
				$f = new File($this->filePath);
				$f->create($_FILES['file']);
				return $this->view->render('postfile');
			}
			else{
				return $this->view->render('error500');
			}
		}
		else if($this->fs->isFolder($this->filePath)){
			// if it's a folder that's fine too, but we have to treat it a bit differently.
			// We can take posts to an existing folder because that's where the HTML form will post to.
			// Because of this we assume the user is in a browser in this case.
			// In this case the folder name must be specified in the post var 'folder' or the file with the name 'file'.
			// If neither is specified it will fail because we don't know what to create in the folder!
			if($this->registry->var->get('folder','')!=''){
				//new folder.
				$newPath = $this->filePath.'/'.$this->registry->var->get('folder');
				$f = new Folder($newPath);
				$f->create();
				
				// return $this->redirect(WEBROOT.'/f/'.$newPath); // redirects to the new folder
				return $this->redirect(WEBROOT.'/f/'.$this->filePath); //redirects to the current folder (to run the get function)
			}
			else if(isset($_FILES['file'])){
				//new file
				$newPath = $this->filePath.'/'.$_FILES['file']['name'];
				$f = new File($newPath);
				$f->create($_FILES['file']);
				
				// return $this->redirect(WEBROOT.'/f/'.$newPath); // redirects to the new folder
				return $this->redirect(WEBROOT.'/f/'.$this->filePath); //redirects to the current folder (to run the get function)
			}
			else{
				return $this->view->render('error500');
			}
			return $this->view->render('error500');
		}
		else{
			return $this->view->render('error500');
		}
	}
	
	private function indexPut(){
		if($this->fs->isFile($this->filePath)){
		}
		else if($this->fs->isFolder($this->filePath)){
			$folder = new Folder($this->filePath);
			//set the properties as per the values sent in the header
			$errors = array();
			if($this->registry->var->get('name')){
				try{ $folder->setName($this->registry->var->get('name')); }
				catch(Exception $e){ $errors[] = $e; }
			}
			if($this->registry->var->get('visibility')){
				try{ $folder->setVisibility($this->registry->var->get('visibility')); }
				catch(Exception $e){ $errors[] = $e; }
			}
			if($this->registry->var->get('parent')){
				try{ $folder->setParent($this->registry->var->get('parent')); }
				catch(Exception $e){ $errors[] = $e; }
			}
			
			if(!count($errors)){
				if(!$folder->update()){
					return $this->view->render('error500');
				}
			}
			else{
				$this->view->assign('edit_form_errors',$errors);
			}
		}
		else{
			return HTML::error404('Path does not exist to update');
		}
		
		//if it's an html view (ie in the browser) we want to redirect to the enclosing folder
		if(empty($this->registry->format) || $this->registry->format=='html'){
			$parentPath = explode('/', $this->filePath);
			array_pop($parentPath);
			if(isset($file)){
				return $this->redirect($file->getFilesystemUrl());
			}
			else if(isset($folder)){
				return $this->redirect($folder->getFilesystemUrl());
			}
		}
		else{ //otherwise, show a nice update success page.
			return $this->view->render('update');
		}
	}
	
	private function indexDelete(){
		if($this->fs->isFile($this->filePath)){
			if($this->fs->getFile($this->filePath)->delete()){
				$this->view->assign('status',1);
				$this->view->assign('message','File deleted successfully');
			}
			else{
				$this->view->assign('status',0);
				$this->view->assign('message','File was not deleted');
			}
		}
		else if($this->fs->isFolder($this->filePath)){
			if($this->fs->getFolder($this->filePath)->delete()){
				$this->view->assign('status',1);
				$this->view->assign('message','Folder deleted successfully');
			}
			else{
				$this->view->assign('status',0);
				$this->view->assign('message','Folder was not deleted');
			}
		}
		else{
			$this->view->assign('status',0);
			$this->view->assign('message','Path does not exist to delete');
			HTML::error404('Path does not exist to delete');
		}
		
		//if it's an html view (ie in the browser) we want to redirect to the enclosing folder
		if(empty($this->registry->format) || $this->registry->format=='html'){
			$parentPath = explode('/', $this->filePath);
			array_pop($parentPath);
			return $this->redirect(WEBROOT.'/f/'.implode('/', $parentPath));
		}
		else{ //otherwise, show a nice delete success page.
			return $this->view->render('delete');
		}
	}
	
	private function getFilePath(){
		$path = $this->registry->path;
		$path = array_slice($path, 1, count($path)-1, true);
		
		return rtrim(implode('/', $path), '/');
	}
}
