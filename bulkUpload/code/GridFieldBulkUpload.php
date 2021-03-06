<?php
/**
 * GridField component for uploading images in bulk
 *
 * @author colymba
 * @package GridFieldBulkEditingTools
 * @subpackage BulkUpload
 */
class GridFieldBulkUpload implements GridField_HTMLProvider, GridField_URLHandler
{		
	/**
	 * component configuration
	 * 
	 * 'fileRelationName' => field name of the $has_one File/Image relation
	 * @var array 
	 */
	protected $config = array(
    'fileRelationName'  => null
	);


	/**
	 * UploadField configuration.
	 * These options are passed on directly to the UploadField
	 * via {@link UploadField::setConfig()} api
	 *
	 * Defaults are:	 * 
	 * 'sequentialUploads' => false : process uploads 1 after the other rather than all at once
	 * 'canAttachExisting' => true : displays "From files" button in the UploadField
	 * 'canPreviewFolder'  => true : displays the upload location in the UploadField
	 * 
	 * @var array 
	 */
	protected $ufConfig = array(
		'sequentialUploads' => false,
    'canAttachExisting' => true,
    'canPreviewFolder'  => true
	);


	/**
	 * UploadField setup function calls.
	 * List of setup functions to call on {@link UploadField} with the value to pass
	 * 
	 * e.g. array('setFolderName' => 'bulkUpload') will result in:
	 * $uploadField->setFolderName('bulkUpload')
	 * 
	 * @var array 
	 */
	protected $ufSetup = array(
    'setFolderName' => 'bulkUpload'
	);


	/**
	 * UploadField Validator setup function calls.
	 * List of setup functions to call on {@link Upload::validator} with the value to pass
	 * 
	 * e.g. array('setAllowedMaxFileSize' => 10) will result in:
	 * $uploadField->getValidator()->setAllowedMaxFileSize(10)
	 * 
	 * @var array 
	 */
	protected $ufValidatorSetup = array(
    'setAllowedMaxFileSize' => null
	);


	/**
	 * Component constructor
	 * 
	 * @param string $fileRelationName
	 */
	public function __construct($fileRelationName = null)
	{		
		if ( $fileRelationName != null ) $this->setConfig ( 'fileRelationName', $fileRelationName );
	}
	


	/* **********************************************************************
	 * Components settings and custom methodes
	 * */
	
	/**
	 * Set a component configuration parameter
	 * 
	 * @param string $reference
	 * @param mixed $value 
	 */
	function setConfig ( $reference, $value )
	{
		if ( in_array($reference, array('folderName', 'maxFileSize', 'sequentialUploads', 'canAttachExisting', 'canPreviewFolder')) )
		{
			Deprecation::notice('2.1.0', "GridFieldBulkUpload 'setConfig()' doesn't support '$reference' anymore. Please use 'setUfConfig()', 'setUfSetup()' or 'setUfValidatorSetup()' instead.");

			if ( $reference === 'folderName' )
			{
				$this->setUfSetup('setFolderName', $value);
			}
			else if ( $reference === 'maxFileSize' )
			{
				$this->setUfValidatorSetup('setAllowedMaxFileSize', $value);
			}
			else{
				$this->setUfConfig($reference, $value);
			}
		}
		else if (!key_exists($reference, $this->config) ) {
			user_error("Unknown option reference: $reference", E_USER_ERROR);
		}

		$this->config[$reference] = $value;
		return $this;
	}


	/**
	 * Set an UploadField configuration parameter
	 * 
	 * @param string $reference
	 * @param mixed $value 
	 */
	function setUfConfig ( $reference, $value )
	{
		$this->ufConfig[$reference] = $value;		
		return $this;
	}


	/**
	 * Set an UploadField setup function call
	 * 
	 * @param string $function
	 * @param mixed $param 
	 */
	function setUfSetup ( $function, $param )
	{
		$this->ufSetup[$function] = $param;
		return $this;
	}


	/**
	 * Set an UploadField Validator setup function call
	 * 
	 * @param string $function
	 * @param mixed $param 
	 */
	function setUfValidatorSetup ( $function, $param )
	{
		$this->ufValidatorSetup[$function] = $param;
		return $this;
	}
	

	/**
	 * Returns one $config reference or the full $config
	 * 
	 * @param string $reference $congif parameter to return
	 * @return mixed 
	 */
	function getConfig ( $reference = false )
	{
		if ( $reference ) return $this->config[$reference];
		else return $this->config;
	}
	

	/**
	 * Returns one $ufConfig reference or the full config.
	 * 
	 * @param string $reference $ufConfig parameter to return
	 * @return mixed 
	 */
	function getUfConfig ( $reference = false )
	{
		if ( $reference ) return $this->ufConfig[$reference];
		else return $this->ufConfig;
	}
	

	/**
	 * Returns one $ufSetup reference or the full config.
	 * 
	 * @param string $reference $ufSetup parameter to return
	 * @return mixed 
	 */
	function getUfSetup ( $reference = false )
	{
		if ( $reference ) return $this->ufSetup[$reference];
		else return $this->ufSetup;
	}
	

	/**
	 * Returns one $ufValidatorSetup reference or the full config.
	 * 
	 * @param string $reference $ufValidatorSetup parameter to return
	 * @return mixed 
	 */
	function getUfValidatorSetup ( $reference = false )
	{
		if ( $reference ) return $this->ufValidatorSetup[$reference];
		else return $this->ufValidatorSetup;
	}


	/**
	 * Get the first has_one Image/File relation from the GridField managed DataObject
	 * i.e. 'MyImage' => 'Image' will return 'MyImage'
	 * 
	 * @return string Name of the $has_one relation
	 */
	public function getDefaultFileRelationName($gridField)
	{
		$recordClass = $gridField->list->dataClass;
		$hasOneFields = Config::inst()->get($recordClass, 'has_one', Config::INHERITED);
		
		$imageField = null;
		foreach( $hasOneFields as $field => $type )
		{
			if( $type === 'Image' ||  $type === 'File' || is_subclass_of($type, 'File') )
			{
				$imageField = $field;
				break;
			}
		}
		
		return $imageField;
	}	


	/**
	 * Returns the name of the Image/File field name from the managed record
	 * Either as set in the component config or the default one
	 * 
	 * @return string 
	 */	
	public function getFileRelationName($gridField)
	{
		$configFileRelationName = $this->getConfig('fileRelationName');
		return $configFileRelationName ? $configFileRelationName : $this->getDefaultFileRelationName($gridField);
	}


	/**
	 * Return the ClassName of the fileRelation
	 * i.e. 'MyImage' => 'Image' will return 'Image'
	 * i.e. 'MyImage' => 'File' will return 'File'
	 *
	 * @return string file relation className
	 */
	public function getFileRelationClassName($gridField)
	{
		$recordClass  = $gridField->list->dataClass;
		$hasOneFields = Config::inst()->get($recordClass, 'has_one', Config::INHERITED);

		$fieldName = $this->getFileRelationName($gridField);
		if($fieldName)
		{
			return $hasOneFields[$fieldName];
		}
		else{
			return 'File';
		}		
	}
	

	/**
	 * Returned a configured UploadField instance
	 * embedded in the gridfield heard
	 * @param  GridField $gridField Current GridField
	 * @return UploadField          Configured UploadField instance
	 */
	public function bulkUploadField($gridField)
	{
		$fileRelationName = $this->getFileRelationName($gridField);
		$uploadField = UploadField::create($fileRelationName, '')
			->setForm($gridField->getForm())

			->setConfig('previewMaxWidth', 20)
			->setConfig('previewMaxHeight', 20)
			->setConfig('changeDetection', false)
			
			->setRecord(DataObject::create()) // avoid UploadField to get auto-config from the Page (e.g fix allowedMaxFileNumber)

			->setTemplate('GridFieldBulkUploadField')
			->setDownloadTemplateName('colymba-bulkuploaddownloadtemplate')
			
			->setConfig('url', $gridField->Link('bulkupload/upload'))
			->setConfig('urlSelectDialog', $gridField->Link('bulkupload/select'))
			->setConfig('urlAttach', $gridField->Link('bulkupload/attach'))
			->setConfig('urlFileExists', $gridField->Link('bulkupload/fileexists'))
			;

    //set UploadField config
    foreach ($this->ufConfig as $key => $val)
    {
    	$uploadField->setConfig($key, $val);
    }

    //UploadField setup
    foreach ($this->ufSetup as $fn => $param)
    {
    	$uploadField->{$fn}($param);
    }

    //UploadField Validator setup
    foreach ($this->ufValidatorSetup as $fn => $param)
    {
    	$uploadField->getValidator()->{$fn}($param);
    }
		
		return $uploadField;
	}



	/* **********************************************************************
	 * GridField_HTMLProvider
	 * */
	
	/**
	 * HTML to be embedded into the GridField
	 * 
	 * @param GridField $gridField
	 * @return array 
	 */
	public function getHTMLFragments($gridField)
	{				
		// permission check
		if( !singleton($gridField->getModelClass())->canEdit() )
		{
			return array();
		}

		// check BulkManager exists
		$bulkManager = $gridField->getConfig()->getComponentsByType('GridFieldBulkManager');

		// upload management buttons
		$finishButton = FormAction::create('Finish', _t('GRIDFIELD_BULK_UPLOAD.FINISH_BTN_LABEL', 'Finish'))
			->addExtraClass('bulkUploadFinishButton')
			->setAttribute('data-icon', 'accept')
			->setUseButtonTag(true);

		$clearErrorButton = FormAction::create('ClearError', _t('GRIDFIELD_BULK_UPLOAD.CLEAR_ERROR_BTN_LABEL', 'Clear errors'))
			->addExtraClass('bulkUploadClearErrorButton')
			->setAttribute('data-icon', 'arrow-circle-double')
			->setUseButtonTag(true);

		if ( count($bulkManager) )
		{
			$cancelButton = FormAction::create('Cancel', _t('GRIDFIELD_BULK_UPLOAD.CANCEL_BTN_LABEL', 'Cancel'))
				->addExtraClass('bulkUploadCancelButton ss-ui-action-destructive')
				->setAttribute('data-icon', 'decline')
				->setAttribute('data-url', $gridField->Link('bulkupload/cancel'))
				->setUseButtonTag(true);			

			$bulkManager_config = $bulkManager->first()->getConfig();
			$bulkManager_actions = $bulkManager_config['actions'];
			if(array_key_exists('bulkedit' , $bulkManager_actions)){
				$editAllButton = FormAction::create('EditAll', _t('GRIDFIELD_BULK_UPLOAD.EDIT_ALL_BTN_LABEL', 'Edit all'))
                                        ->addExtraClass('bulkUploadEditButton')
                                        ->setAttribute('data-icon', 'pencil')
                                        ->setAttribute('data-url', $gridField->Link('bulkupload/edit'))
                                        ->setUseButtonTag(true);
			}else{
				$editAllButton = '';
			}
		}
		else{
			$cancelButton = '';
			$editAllButton = '';
		}

		// get uploadField + inject extra buttons
		$uploadField = $this->bulkUploadField($gridField);
    $uploadField->FinishButton     = $finishButton;
    $uploadField->CancelButton     = $cancelButton;
    $uploadField->EditAllButton    = $editAllButton;
    $uploadField->ClearErrorButton = $clearErrorButton;

		$data = ArrayData::create(array(
      'Colspan'     => count($gridField->getColumns()),
      'UploadField' => $uploadField->Field() // call ->Field() to get requirements in right order
		));

		Requirements::css(BULKEDITTOOLS_UPLOAD_PATH . '/css/GridFieldBulkUpload.css');
		Requirements::javascript(BULKEDITTOOLS_UPLOAD_PATH . '/javascript/GridFieldBulkUpload.js');
		Requirements::javascript(BULKEDITTOOLS_UPLOAD_PATH . '/javascript/GridFieldBulkUpload_downloadtemplate.js');
		Requirements::add_i18n_javascript(BULKEDITTOOLS_PATH . '/lang/js');
		
		return array(
			'header' => $data->renderWith('GridFieldBulkUpload')
		);
	}



	/* **********************************************************************
	 * GridField_URLHandler
	 * */
	
	/**
	 * Component URL handlers
	 * 
	 * @param GridField $gridField
	 * @return array 
	 */
	public function getURLHandlers($gridField) {
		return array(
			'bulkupload' => 'handleBulkUpload'
		);
	}
	

	/**
	 * Pass control over to the RequestHandler
	 * 
	 * @param GridField $gridField
	 * @param SS_HTTPRequest $request
	 * @return mixed 
	 */
	public function handleBulkUpload($gridField, $request)
	{				
    $controller = $gridField->getForm()->Controller();
    $handler    = new GridFieldBulkUpload_Request($gridField, $this, $controller);
		
		return $handler->handleRequest($request, DataModel::inst());		
	}
}
