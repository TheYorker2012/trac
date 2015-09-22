<?php

/**
 * @file views/whatson/office/index.php
 * @author James Hogan <james_hogan@theyorker.co.uk>
 *
 * @param $Calendars
 */

?><div class="BlueBox"><?php
	?><h2>what's on calendars</h2><?php

	?><a href="<?php echo(xml_escape(site_url('office/whatson/add'))); ?>">create new what's on calendar</a><?php

	?><ul style="width:40%"><?php
	$id = 0;
	foreach ($Calendars as $calendar) {
		++$id;
		?><li><?php
			?><a href="<?php echo(xml_escape(site_url('office/whatson/calendar/'.$calendar['shortname']))); ?>"><?php
				echo(xml_escape($calendar['name']));
			?></a><?php
			$links = array();
			if ($id > 1) {
				$links[site_url('office/whatson/calendar/'.$calendar['shortname'].'/moveup?ret='.urlencode($this->uri->uri_string()))] = 'up';
			}
			if ($id < count($Calendars)) {
				$links[site_url('office/whatson/calendar/'.$calendar['shortname'].'/movedown?ret='.urlencode($this->uri->uri_string()))] = 'down';
			}
			$links[site_url('office/whatson/calendar/'.$calendar['shortname'].'/manage')] = 'manage';
			$linkHtml = array();
			foreach ($links as $uri => $label) {
				$linkHtml[] = '<a href="'.xml_escape($uri).'">'.xml_escape($label).'</a>';
			}
			?><span style="float:right"><?php
			echo(' ('.implode(', ', $linkHtml).')');
			?></span><?php
		?></li><?php
	}
	?></ul><?php
?></div><?php

?>
