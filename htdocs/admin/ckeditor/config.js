/*
Copyright (c) 2003-2009, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	
	config.language = 'ru';
	config.removePlugins = 'save,contextmenu,pastetext';
	config.filebrowserUploadUrl = '/admin/fm/upload_file';
	config.disableNativeSpellChecker = false;
};
