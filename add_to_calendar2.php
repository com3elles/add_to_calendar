<?php
/**
 * @package         FLEXIcontent
 * @version         3.2
 *
 * @author          Emmanuel Danan, Georgios Papadakis, Yannick Berges, others, see contributor page
 * @link            https://flexicontent.org
 * @copyright       Copyright © 2017, FLEXIcontent team, All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
JLoader::register('FCField', JPATH_ADMINISTRATOR . '/components/com_flexicontent/helpers/fcfield/parentfield.php');

class plgFlexicontent_fieldsAdd_to_calendar extends FCField
{
	static $field_types = null;
	var $task_callable = null;  // Field's methods allowed to be called via AJAX

	// ***
	// *** CONSTRUCTOR
	// ***

	function __construct( &$subject, $params )
	{
		parent::__construct( $subject, $params );
	}



	// ***
	// *** DISPLAY methods, item form & frontend views
	// ***

	// Method to create field's HTML display for item form
	function onDisplayField(&$field, &$item)
	{
		if ( !in_array($field->field_type, static::$field_types) ) return;

		$field->label = JText::_($field->label);
		$use_ingroup = $field->parameters->get('use_ingroup', 0);
		if (!isset($field->formhidden_grp)) $field->formhidden_grp = $field->formhidden;
		if ($use_ingroup) $field->formhidden = 3;
		if ($use_ingroup && empty($field->ingroup)) return;

		// Initialize framework objects and other variables
		$document = JFactory::getDocument();
		$cparams  = JComponentHelper::getParams( 'com_flexicontent' );

		$tooltip_class = 'hasTooltip';
		$add_on_class    = $cparams->get('bootstrap_ver', 2)==2  ?  'add-on' : 'input-group-addon';
		$input_grp_class = $cparams->get('bootstrap_ver', 2)==2  ?  'input-append input-prepend' : 'input-group';
		$form_font_icons = $cparams->get('form_font_icons', 1);
		$font_icon_class = $form_font_icons ? ' fcfont-icon' : '';

		// Set field and item objects
		$this->setField($field);
		$this->setItem($item);

		// Get field values
		//$values = $values ? $values : $field->value;
		$values = $this->parseValues($field->value);

		$field->value = array();
				$field->value[0]['title_event'] = '';
				$field->value[0]['start_date_event'] = '';
				$field->value[0]['end_date_event'] = '';
				$field->value[0]['description_event'] = '';
				$field->value[0]['location event'] = '';
				$values = $field->value;
			$this->values = & $values;
		// Some parameter shortcuts
		$add_to_calendar_edit_mode = $field->parameters->get('add_to_calendar_edit_mode', 1);//1 display button, 0 set to default, -1 nothing

		// JS safe Field name
		//$field_name_js = str_replace('-', '_', $field->name);

		$js = '';
		$css = '';

		$classes  = 'fcfield_textval' . $required_class;

		/**
		 * Create field's HTML display for item form
		 */

		$field->html = array();
		// Do not convert the array to string if field is in a group
		if ($use_ingroup);

		// Handle single values
		else
		{
			$field->html = '<div class="fcfieldval_container valuebox fcfieldval_container_'.$field->id.'">Nothing maybe infos event or button for display default, yes no</div>';
		}
	}

	// ***
	// *** METHODS HANDLING before & after saving / deleting field events
	// ***

	// Method to handle field's values before they are saved into the DB
	function onBeforeSaveField( &$field, &$post, &$file, &$item )
	{
		if ( !in_array($field->field_type, static::$field_types) ) return;
		$use_ingroup = $field->parameters->get('use_ingroup', 0);
		if ( !is_array($post) && !strlen($post) && !$use_ingroup ) return;

		// Check if field has posted data
		if ( empty($post) || !is_array($post)) return;


	$newpost = array();
			$new=0;
	foreach ($post as $n => $v)
	{
		if (empty($v)) continue;

		// validate data or empty/set default values
		$newpost[$new] = array();
		$newpost[$new]['title_event'] = $addr;
		$newpost[$new]['start_date_event'] = $Sdate;
		$newpost[$new]['end_date_event'] = $Edate;
		$newpost[$new]['description_event'] = $desc;
		$newpost[$new]['location_event'] = $location;

		$new++;
	}
	$post = $newpost;

		// Serialize multi-property data before storing them into the DB, also map some properties as fields
	$props_to_fields = array('title_event', 'start_date_event', 'end_date_event', 'description_event', 'location event');
	$_fields = array();
	$byIds = FlexicontentFields::indexFieldsByIds($item->fields, $item);
	//var_dump ($byIds, 'fieldid');
	foreach($post as $i => $v)
	{
		foreach($props_to_fields as $propname)
		{
			$to_fieldid = $field->parameters->get('field_'.$propname);
				//var_dump ($to_fieldid, 'fieldid');
			if ( $to_fieldid && isset($byIds[$to_fieldid]) )
			{
				$to_fieldname = $byIds[$to_fieldid]->name;
				$item->calculated_fieldvalues[$to_fieldname][$i] = $v[$propname];
			}
		}
		$post[$i] = serialize($v);
	}
	//var_dump ($post, 'post');
	}

	// Method to take any actions/cleanups needed after field's values are saved into the DB
function onAfterSaveField( &$field, &$post, &$file, &$item ) {
}


// Method called just before the item is deleted to remove custom item data related to the field
function onBeforeDeleteField(&$field, &$item) {
}


	// Method to create field's HTML display for frontend views
	function onDisplayFieldValue(&$field, $item, $values=null, $prop='display')
	{
		if ( !in_array($field->field_type, static::$field_types) ) return;
		$field->label = JText::_($field->label);

		// Set field and item objects
		$this->setField($field);
		$this->setItem($item);

		// Use the custom field values, if these were provided
		$values = $values !== null ? $values : $this->field->value;

		$title_event = ''; //field set for title
		$start_date_event = '';//JHtml::date($datefield, 'Y-m-d H:i:s');
		$end_date_event = '';//field date set for date need to check format
		$tzname = JFactory::getUser()->getParam('timezone'); // need to checkk convertion code
		$id_event = $item_id; // return uniq id for apple device
		$description_event = 'test';// field set for description event
		$location_event = 'test'; //field set adress => only text
		$duration_event = 'test'; // set dureation for yahoo event

		function GetGoogleURL()
		{
		// generate google url calendar
			$urlGoogle = 'https://calendar.google.com/calendar/render?action=TEMPLATE';
    	$dateTimeFormat = $link->allDay ? 'Ymd' : "Ymd\THis";
    	$urlGoogle .= '&text='.urlencode($title_event);
    	$urlGoogle .= '&dates='.$start_date_event.'/'.$end_date_event;
    	$urlGoogle .= '&ctz='.$tzname;
        if ($link->description) {
            $url .= '&details='.urlencode($description_event);
        }
        if ($link->address) {
            $url .= '&location='.urlencode($location_event);
        }
      $urlGoogle .= '&sprop=&sprop=name:';
      return $urlGoogle;
		}
		function GetIcsURL()
		{
		// generate ics url for apple device
		   $url = array(
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'BEGIN:VEVENT',
        'UID:'.$id_event,
        'SUMMARY:'.$title_event,
        );
        if ($link->allDay) {
            $dateTimeFormat = 'Ymd';
            $url[] = 'DTSTART:'.$start_date_event;
            $url[] = 'DURATION:P1D';
        } else {
            $dateTimeFormat = "e:Ymd\THis";
            $url[] = 'DTSTART;TZID='.$start_date_event;
            $url[] = 'DTEND;TZID='.$end_date_event;
        }
        if ($link->description) {
            $url[] = 'DESCRIPTION:'.$description_event;
        }
        if ($link->address) {
            $url[] = 'LOCATION:'.$location_event;
        }
        $url[] = 'END:VEVENT';
        $url[] = 'END:VCALENDAR';
        $redirectLink = implode('%0d%0a', $url);
        $urlIcs = 'data:text/calendar;charset=utf8,'.$redirectLink;
		return $urlIcs;
	}


	function GetLiveURL()
	{
		// generate live url calendar
		$urlLive  = 'https://bay02.calendar.live.com/calendar/calendar.aspx?rru=addevent';
		$urlLive .= '&dtstart=' .$start_date_event;
		$urlLive .= '&dtend='.$end_date_event;
		$urlLive .= '&summary='.$title_event;
		$urlLive .= '&location='.$location_event;
		$urlLive .=	'&description='.$description_event;
		return $urlLive;
	}
	function GetYahooURL()
	{
		// generate yahoo url calendar
		$urlYahoo  = 'http://calendar.yahoo.com/?v=60&view=d&type=20';
		$urlYahoo .= '&title='.$title_event;
		$urlYahoo .= '&st='.$start_date_event;
		$urlYahoo .= '&dur='.$duration_event;
		$urlYahoo .= '&desc='.$description_event;
		$urlYahoo .= '&in_loc='.$location_event;
		return $urlYahoo;
    }

		// Parse field values
		$this->values = $this->parseValues($values);


		// Get layout name
		$viewlayout = $field->parameters->get('viewlayout', '');
		$viewlayout = $viewlayout && $viewlayout != 'value' ? 'value_'.$viewlayout : 'value';

		// Create field's display
		$this->displayFieldValue($prop, $viewlayout);
	}
}
