<h2><? $this->render_partial('crumbtrail',array('dest_folder'=>$this->fileenclosingfolder)); ?> <?= $this->filename ?></h2>

<a href="<?= $this->filedownloadlink ?>"><img src="<?= $this->fileiconurl ?>" alt="<?= $this->filetype ?> file" /></a>

<h3>File details</h3>
<? if($this->fileeditmode==='1'): ?>
	<form action="<?= $this->updateurl ?>" method="post" id="edit_form">
		<ul class="details">
			<li><label for="edit_name">Name</label> <input type="text" class="text" name="name" id="edit_name" value="<?= $this->filename ?>" /></li>
			<li><span>Size:</span> <?= $this->filesize ?></li>
			<li><span>Created:</span> <?= $this->filecreated ?></li>
			<li><span>Modified:</span> <?= $this->filemodified ?></li>
			<li><label for="edit_visibility">Visbility</label> <select name="visibility" id="edit_visibility">
				<option value="public"<?= (($this->filevisibility=='public')?' selected="selected"':'') ?>>Public</option>
				<option value="private"<?= (($this->filevisibility=='private')?' selected="selected"':'') ?>>Private</option>
			</select></li>
			<li><label for="edit_parent">Move to</label> <select name="parent" id="edit_parent">
				<? $this->render_partial('foldertreeoptions',array('type'=>'folder','exclude_branch'=>$this->folderid)) ?>
			</select></li>
			<li><label for="edit_replace">Replace</label> <input type="file" class="file" name="file" id="edit_replace" /></li>
			<input type="submit" class="submit button" name="edit_submit" id="edit_submit" value="Update" />
		</ul>
	</form>
<? else: ?>
	<ul class="details">
		<li><span>Name:</span> <?= $this->filename ?></li>
		<li><span>Size:</span> <?= $this->filesize ?></li>
		<li><span>Created:</span> <?= $this->filecreated ?></li>
		<li><span>Modified:</span> <?= $this->filemodified ?></li>
		<li><span>Visbility:</span> <?= $this->filevisibility ?></li>
	</ul>
<? endif; ?>

<div class="actions">
	<p><a href="<?= $this->filedownloadlink ?>" class="download">Download</a>
	<? if($this->fileeditmode==='1'): ?>
		<a href="<?= $this->fileurl ?>" class="edit">Cancel Edit</a>
	<? else: ?>
		<a href="<?= $this->fileeditlink ?>" class="edit">Edit / Move</a>
	<? endif; ?>
	<a href="<?= $this->filedeletelink ?>" class="delete">Delete</a></p>
</div>