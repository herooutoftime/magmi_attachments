<?php
class AttachmentsProcessor extends Magmi_ItemProcessor
{

	protected $_attcol = array();
	protected $_item = array();
	protected $_pid = '';
	protected $_settings = array();
	protected $_magedir = '';
	protected $_dirhandler;

	const DS = DIRECTORY_SEPARATOR;

	public function getPluginInfo()
	{
		return array(
			'name' => 'Attachments Importer',
			'author' => 'Andreas Bilz,herooutoftime',
			'version' => '0.0.1'
		);
	}

	public function processItemAfterId(&$item, $params = null)
	{
		$this->_item = $item;
		$this->_item['product_id'] = $params["product_id"];
		$this->_settings = array(
			'att_tbl' => $this->tablename($this->getParam("ATTACH:table", "uni_fileuploader")),
			'att_col' => $this->getParam("ATTACH:column_name", "attachment"),
			'att_src_dir' => $this->getParam('ATTACH:source_dir', 'media/wawi/files/'),
			'att_dest_dir' => $this->getParam('ATTACH:destination_dir', 'custom/attachments/'),
			'att_dest_org' => $this->getParam('ATTACH:destination_org', 'sku'),
			'att_file_op' => $this->getParam('ATTACH:file_operation', 'copy'),
			'att_file_exclude' => $this->getParam('ATTACH:file_exclusion', 'jpg,png,gif'),
			'att_file_include' => $this->getParam('ATTACH:file_inclusion', 'doc,docx,pdf,xls,xlsx'),
		);
		$attachments = json_decode($item[$this->_settings['att_col']]);

		$cols = array(
			'title',
			'uploaded_file',
			'file_content',
			'product_ids',
			'file_status',
			'content_disp',
			'sort_order',
			'update_time'
		);
		$sql = "INSERT INTO {$this->_settings['att_tbl']} (" . implode(', ', $cols) . ") VALUES(:" . implode(',:', $cols) . ")";
		foreach ($attachments as $index => $attachment) {
			$file_dest_path = $this->prepareFilepath(/*$this->_item, */$attachment);
			$att_id = $this->get($file_dest_path);
			// Do nothing if this attachment already exists!
			/**
			 * @todo
			 * Handle updates better:
			 * * Set property `mode` for _item to `update`
			 * * Prevent INSERT
			 * * BUT check if file was modified => timestamp
			 * * Update file
			 */
			if($att_id)
				continue;

			$data = array(
				'title' => $attachment->title,
				'uploaded_file' => $file_dest_path,
				'file_content' => '',
				'product_ids' => $this->_item['product_id'],
				'file_status' => 1,
				'content_disp' => 1,
				'sort_order' => $index,
				'update_time' => date("Y-m-d H:i:s"),
			);
			if(!$this->handleFile($file_dest_path, $attachment)) {

			} else {
				var_dump($data);
				var_dump($this->insert($sql, $data));
			}
		}
		return true;
	}

	public function get($file_path)
	{
		$product = $this->_item;
		$sql = "SELECT fileuploader_id FROM {$this->_settings['att_tbl']} WHERE uploaded_file = '$file_path' AND FIND_IN_SET('{$this->_item['product_id']}', CAST(product_ids as char)) > 0";
		$result = $this->testexists($sql, null, 'fileuploader_id');
		if($result)
			return $this->selectone($sql, null, 'fileuploader_id');
		return false;
	}

	public function prepareFilepath(/*$product, */$attachment)
	{
		$product = $this->_item;
		$organisation = $this->_settings['att_dest_org'];
		$fi = pathinfo($attachment->file);
		if(empty($organisation) || !$organisation || $organisation == '')
			return $fi['basename'];
		switch($organisation) {
			default:
			case "sku":
			case "product_id":
				$dir[] = $product[$organisation];
				break;
			case "date":
				$dir = array(
					'year' => date('Y'),
					'month' => date('m'),
				);
				break;
		}
		return preg_replace('#/+#', '/', $this->_settings['att_dest_dir'] . self::DS . implode('/', $dir) . self::DS . $fi['basename']);
	}

	public function handleFile($file_dest_path, $attachment)
	{
		// Source & Destination folders exactly match there's nothing else to do
		if(str_replace($this->_magedir, '', $this->_settings['att_src_dir']) === $this->_settings['att_dest_dir'])
			return true;

		$src_abs = false;
		$fi = pathinfo($file_path);
		var_dump($fi);
		$dest_dir = $this->slashes('media' . self::DS . $fi['dirname']);
		$this->_dirhandler->mkdir($dest_dir, Magmi_Config::getInstance()->getDirMask(), true);

		$root = $this->slashes($this->_magedir . self::DS . $this->_settings['att_src_dir']);
		if($this->_settings['att_src_dir'][0] === '/') {
			$src_abs = true;
			$root = $this->_settings['att_src_dir'];
		}
		$src_path = $this->slashes($root . self::DS . $attachment->file);
		$dest_path = $this->slashes('media' . self::DS . $file_dest_path);

		if($this->_settings['att_file_op'] == 'copy')
			$result = $this->_dirhandler->copy($src_path, $dest_path);
		if($this->_settings['att_file_op'] == 'move') {
			$result['copy'] = $this->_dirhandler->copy($src_path, $dest_path);
			$result['unlink'] = $this->_dirhandler->unlink($src_path);
		}
		return $result;
	}

	public function slashes($path)
	{
		return preg_replace('#/+#', '/', $path);
	}

	public function processColumnList(&$cols, $params = null)
	{
		$pattern = $this->getParam("ATTACH:column_name", "attachment");
		foreach ($cols as $col) {
			if (preg_match("|{$pattern}|", $col, $matches)) {
				$tpinf = array("name" => $matches[1], "id" => null);
				$this->_attcol[$col] = $tpinf;
			}
		}
		return true;
	}

	public function initialize($params)
	{
		$this->_magedir = Magmi_Config::getInstance()->getMagentoDir();
		$this->_dirhandler = MagentoDirHandlerFactory::getInstance()->getHandler($this->_magedir);
	}
}