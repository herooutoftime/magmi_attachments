<ul class="formline">
	<li class="label">Attachments Table</li>
	<li class="value">
		<input type="text" name="ATTACH:table"
	                         value="<?php echo $this->getParam("ATTACH:table", "uni_fileuploader")?>"></input>
	</li>
</ul>
<ul class="formline">
	<li class="label">Attachments Column Value (DB or CSV)</li>
	<li class="value"><input type="text" name="ATTACH:column_name"
	                         value="<?php echo $this->getParam("ATTACH:column_name", "attachment")?>"></input></li>
</ul>

<ul class="formline">
	<li class="label">Attachments Source Directory</li>
	<li class="value"><input type="text" name="ATTACH:source_dir"
	                         value="<?php echo $this->getParam("ATTACH:source_dir", "wawi/files/")?>"></input>
	</li>
	<li class="fieldinfo">Relative to Magento's media folder: no leading `/`, Absolute: leading `/`</li>
</ul>

<ul class="formline">
	<li class="label">Attachments Destination Directory</li>
	<li class="value">
		<input type="text" name="ATTACH:destination_dir"
	                         value="<?php echo $this->getParam("ATTACH:destination_dir", "custom/attachments/")?>"></input>
	</li>
	<li class="fieldinfo">Relative to Magento's media folder</li>
</ul>

<ul class="formline">
	<li class="label">Organize destination directory</li>
	<li class="value">
		<select name="ATTACH:destination_org" id="">
			<option value="sku" selected>By SKU</option>
			<option value="id">By ID</option>
			<option value="date">By date</option>
			<option value="">None</option>
		</select>
	</li>
	<li class="fieldinfo">Either MOVE or COPY original files to destination</li>
</ul>

<ul class="formline">
	<li class="label">Share attachments (experimental)</li>
	<li class="value">
		<select name="ATTACH:file_operation" id="">
			<option value="1" selected>Yes</option>
			<option value="0">No</option>
		</select>
	</li>
	<li class="fieldinfo">Share attachments with multiple products</li>
</ul>

<ul class="formline">
	<li class="label">Attachments File Operation</li>
	<li class="value">
		<select name="ATTACH:file_operation" id="">
			<option value="copy" selected>Copy</option>
			<option value="move">Move</option>
		</select>
	</li>
	<li class="fieldinfo">Either MOVE or COPY original files to destination</li>
</ul>