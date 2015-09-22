<?php

/**
 * @param views/flexibox/whatson_list.php
 * @param $CalendarData
 * @param $MaxEvents
 * @param $PadEvents
 * @param $ReturnUri
 * @param $CalendarUri
 */

$occurrences = &$CalendarData->GetCalendarOccurrences();
$relevent_occurrences = array();
foreach ($occurrences as &$occurrence) {
	$pass = false;
	foreach ($occurrence->Event->Organisations as &$organisation) {
		if ($organisation['confirmed'] &&
			$organisation['org']->YorkerOrganisationId == $CalendarId)
		{
			$pass = true;
			break;
		}
	}
	if (!$pass) {
		foreach ($occurrence->Event->Subscribers as &$subscriber) {
			if ($subscriber['confirmed'] &&
				$subscriber['org']->YorkerOrganisationId == $CalendarId)
			{
				$pass = true;
				break;
			}
		}
	}
	if ($pass) {
		$relevent_occurrences[] = &$occurrence;
	}
}

switch ($size) {
	case '1/2':
		$box_size = 'Box12';
		if (!isset($box_width)) {
			$box_width = '50%';
		}
		break;
	case '1/3':
		$box_size = 'Box13';
		if (!isset($box_width)) {
			$box_width = '';
		}
		break;
	case '2/3':
		$box_size = 'Box23';
		if (!isset($box_width)) {
			$box_width = '50%';
		}
		break;
	default:
		$box_size = '';
		if (!isset($box_width)) {
			$box_width = '';
		}
}
?>

<div class="ArticleListBox FlexiBox<?php if (!empty($box_size)) { echo(' ' . $box_size); } if (!empty($last)) { echo(' FlexiBoxLast'); } ?>"<?php if (!empty($position)) { echo(' style="float:' . $position . ';clear:' . $position . ';"'); } ?>>
	<div class="<?php echo(empty($title_image) ? 'ArticleListTitle' : 'ArticleListTitleImg'); ?>">
<?php if (null !== $CalendarUri) { ?>
		<a href="<?php echo(xml_escape($CalendarUri)) ?>">
			<img title="show calendar" alt="show calendar" src="<?php echo(site_url('images/icons/calendar.png')); ?>" />
		</a>
<?php } ?>
<?php if (!empty($title_link)) { ?>
		<a href="<?php echo($title_link); ?>">
<?php } ?>
<?php if (!empty($title_image)) { ?>
			<img src="<?php echo($title_image); ?>" alt="<?php echo($title); ?>" title="<?php echo($title); ?>" />
<?php } else { ?>
			<?php echo($title); ?>
<?php } ?>
<?php if (!empty($title_link)) { ?>
		</a>
<?php } ?>
<?php if (null !== $ReturnUri) { ?>
		<a style="float:right" href="<?php echo(xml_escape($ReturnUri)) ?>">return</a>
<?php } ?>
	</div>
	<?php
	$num_events = 0;
	if (count($relevent_occurrences)) {
		foreach ($relevent_occurrences as &$occurrence) {
			++$num_events;
			if ($num_events > $MaxEvents) {
				break;
			}
			?><div<?php if (!empty($box_width)) echo(' style="float:left;width:' . $box_width . ';"'); ?>>
				<a href="<?php echo(xml_escape('/calendar/src/'.$occurrence->Event->Source->GetSourceId().'/event/'.$occurrence->Event->SourceEventId.'/occ/'.$occurrence->SourceOccurrenceId.'/info/default/default'.$this->uri->uri_string())); ?>">
					<?php echo(xml_escape($occurrence->Event->Name)); ?>
				</a>
				<div class="Date"><?php echo(xml_escape($occurrence->StartTime->Format('l, jS F Y'.($occurrence->TimeAssociated ? ', %T' : '')))); ?></div>
				<div class="clear"></div>
			</div><?php
		}
	}
	while ($num_events < $PadEvents) {
		?><div<?php if (!empty($box_width)) echo(' style="float:left;width:' . $box_width . ';"'); ?>><?php
			?><p><?php
			if (!$num_events) {
				// no occurrences
				?>no events<?php
			}
			else {
				?>&nbsp;<?php
			}
			?></p><?php
		?></div><?php
		++$num_events;
	}
	?>
</div>
