<h2><? $this->render_partial('crumbtrail',array('dest_folder'=>$this->folderenclosingfolder)); ?> <?= $this->foldername ?></h2>

<h3>Folder Details</h3>

<? if($this->foldereditmode==='1'): ?>
	<form action="<?= $this->updateurl ?>" method="post" id="edit_form">
		<ul class="details">
			<li><label for="edit_name">Name</label> <input type="text" class="text" name="name" id="edit_name" value="<?= $this->foldername ?>" /></li>
			<li><span>Created:</span> <?= $this->foldercreated ?></li>
			<li><span>Modified:</span> <?= $this->foldermodified ?></li>
			<li><label for="edit_visibility">Visbility</label> <select name="visibility" id="edit_visibility">
				<option value="public"<?= (($this->foldervisibility=='public')?' selected="selected"':'') ?>>Public</option>
				<option value="private"<?= (($this->foldervisibility=='private')?' selected="selected"':'') ?>>Private</option>
			</select></li>
			<li><label for="edit_parent">Move to</label> <select name="parent" id="edit_parent">
				<? $this->render_partial('foldertreeoptions',array('type'=>'folder','exclude_branch'=>$this->folderid)) ?>
			</select></li>
		</ul>
		<input type="submit" class="submit button" name="edit_submit" id="edit_submit" value="Update" />
	</form>
<? else: ?>
	<ul class="details">
		<li><span>Name:</span> <?= $this->foldername ?></li>
		<li><span>Created:</span> <?= $this->foldercreated ?></li>
		<li><span>Modified:</span> <?= $this->foldermodified ?></li>
		<li><span>Visbility:</span> <?= $this->foldervisibility ?></li>
	</ul>
<? endif; ?>

<div class="actions">
	<p>
	<? if($this->foldereditmode==='1'): ?>
		<a href="<?= $this->folderurl ?>" class="edit">Cancel Edit</a>
	<? else: ?>
		<a href="<?= $this->foldereditlink ?>" class="edit">Edit / Move</a>
	<? endif; ?>
		<a href="<?= $this->folderdeletelink ?>" class="delete">Delete</a>
	</p>
	<form action="<?= $this->newfolderurl ?>" method="post">
		<h4>New folder</h4>
		<div class="formline">
			<label for="folder">Folder name</label>
			<input type="text" class="text" name="folder" id="folder" value="" />
			<input type="submit" class="submit button" name="newfolder_submit" id="newfolder_submit" value="Create" />
		</div>
	</form>
	<form action="<?= $this->uploadfileurl ?>" method="post" enctype="multipart/form-data">
		<h4>Upload file</h4>
		<div class="formline">
			<label for="file">File</label>
			<input type="file" class="file" name="file" id="file" value="" />
			<input type="submit" class="submit button" name="uploadfile_submit" id="uploadfile_submit" value="Upload" />
		</div>
	</form>
</div>

<h3>Folder contents</h3>
<? if(count($this->foldercontents)): ?>
<ul>	
	<? foreach($this->foldercontents as $item){ ?>
	<li><a href="<?= $item->getFilesystemUrl() ?>"><?= $item->getName() ?></a></li>
	<? } ?>
</ul>
<? else: ?>
<p>Folder is empty</p>
<? endif; ?>