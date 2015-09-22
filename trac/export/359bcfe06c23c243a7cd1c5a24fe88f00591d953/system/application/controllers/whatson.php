<?php

/**
 * @file controllers/whatson.php
 * @author James Hogan <james_hogan@theyorker.co.uk>
 */

/// What's on main controller.
class Whatson extends Controller
{
	private $mainSource = null;

	function __construct()
	{
		parent::Controller();
	}

	function _remap($shortname = null, $op = null)
	{
		if (!CheckPermissions('public')) return;
		$this->main_frame->SetData('menu_tab', "what's on");

		$this->load->model('calendar/whatson_model');
		if (null === $shortname || $shortname == 'all') {
			$calendars = $this->whatson_model->GetAllCalendarsInfo();
		}
		else {
			$calendar = $this->whatson_model->GetCalendarInfoByShortName($shortname);
			if (null === $calendar) {
				$this->messages->AddMessage('error', xml_escape("No what's on calendar found with the short name '$shortname'"));
				redirect('whatson');
			}
			else {
				$calendars = array($calendar);
			}
		}
		switch ($op) {
			case null:
				break;
			case 'calendar':
				$args = func_get_args();
				array_shift($args);
				array_shift($args);
				return $this->_Calendar($calendars, $args);
			default:
				show_404();
				break;
		}

		$this->_setupMyCalendar();
		$this->mainSource->SetRange(time(), strtotime('6month'));

		$boxes = array();
		$first_size = '1';
		if (null === $shortname || 'all' == $shortname) {
			if ($this->user_auth->isLoggedIn) {
				$first_size = '1/2';
				$boxes[] = array(
					'type'          =>  'whatson_list',
					'title'         =>  'my personal calendar',
					'title_link'    =>  site_url('calendar/view/range'),
					'size'          =>  $first_size,
					'box_width'     =>  (null !== $shortname ? '33%' : ''),
					'last'			=>	false,
					'CalendarId'    =>  $this->user_auth->entityId,
					'MaxEvents'     =>  0,
					'PadEvents'     =>  0,
					'ReturnUri'     =>  null,
					'CalendarUri'   =>  site_url('calendar'),
				);
			}
			$boxes[] = array(
				'type'          =>  'whatson_list',
				'title'         =>  'all upcoming events',
				'title_link'    =>  site_url('whatson/all'),
				'size'          =>  $first_size,
				'box_width'     =>  (null !== $shortname ? '33%' : ''),
				'last'			=>	true,
				'CalendarId'    =>  null,
				'MaxEvents'     =>  0,
				'PadEvents'     =>  0,
				'ReturnUri'     =>  (null !== $shortname ? site_url('whatson') : null),
				'CalendarUri'   =>  site_url('whatson/all/calendar'),
			);
		}
		$fraction = (null !== $shortname ? 1 : 3);
		foreach ($calendars as $id => $info) {
			$boxes[] = array(
				'type'			=>	'whatson_list',
				'title'			=>	'upcoming '.$info['name'],
				'title_link'	=>	site_url('whatson/'.$info['shortname']),
				'size'			=>	(null !== $shortname ? '1' : '1/3'),
				'box_width'     =>  (null !== $shortname ? '33%' : ''),
				'last'			=>	($id % $fraction) == ($fraction-1),
				'CalendarId'    =>  $info['calendar_id'],
				'MaxEvents'     =>  (null !== $shortname ? 100 : 3),
				'PadEvents'     =>  (null !== $shortname ? 1 : 3),
				'ReturnUri'     =>  (null !== $shortname ? site_url('whatson') : null),
				'CalendarUri'   =>  site_url('whatson/'.$info['shortname'].'/calendar'),
			);
			$this->mainSource->GetSource(0)->IncludeStream($info['calendar_id'], TRUE);
		}

		// Get the events
		$calendar_data = new CalendarData();
		$this->messages->AddMessages($this->mainSource->FetchEvents($calendar_data));
		
		$data = array(
			'boxes'	=>	$boxes,
			'CalendarData' => &$calendar_data,
		);

		$this->pages_model->SetPageCode('whatson_index');
		$this->main_frame->SetContentSimple('flexibox/layout', $data);
		$this->main_frame->IncludeCss('stylesheets/home.css');
		$this->main_frame->Load();
	}

	/// Show calendar of some selection of calendars
	function _Calendar($calendars, $args)
	{
		// Show management calendar
		$this->load->model('subcontrollers/calendar_subcontroller');
		
		if (count($calendars) == 1) {
			$name = $calendars[0]['name'];
			$shortname = $calendars[0]['shortname'];
		}
		else {
			$name = "all";
			$shortname = "all";
		}
		$this->calendar_subcontroller->_AddCustomTab('back','Back to '.$name, site_url('whatson/'.$shortname));
		$permissions = array();
		$this->calendar_subcontroller->_AddPermission($permissions);
		
		$sources = & $this->calendar_subcontroller->GetSources();
		$sources->DisableGroup('inactive');
		// restrict to this organisation
		$streams = array();
		foreach ($calendars as $calendar) {
			$streams[(int)$calendar['calendar_id']] = array(
				'subscribed' => null,//$calendar['subscribed'],
				'name' => $calendar['name'],
				'short_name' => $calendar['shortname'],
			);
		}
		$this->calendar_subcontroller->UseStreams($streams);
		
		$this->calendar_subcontroller->_map($args);
	}

	/// Setup the main source.
	function _SetupMyCalendar()
	{
		if (null === $this->mainSource) {
			$this->load->library('calendar_backend');
			$this->load->library('calendar_source_my_calendar');
			
			$this->mainSource = new CalendarSourceMyCalendar();

			$this->mainSource->DisableGroup('subscribed');
			$this->mainSource->DisableGroup('owned');
			$this->mainSource->DisableGroup('private');
			$this->mainSource->EnableGroup('active');
			$this->mainSource->DisableGroup('inactive');
			$this->mainSource->EnableGroup('hide');
			$this->mainSource->EnableGroup('show');
			$this->mainSource->EnableGroup('rsvp');
		}
		return $this->mainSource;
	}
}

?>
