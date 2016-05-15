<?php 

/**
* Builds and manipulates an events calendar
*
*/
class Calendar extends DB_Connect
{
	
	/**
	* the date from which the calendar should be built
	* Stored in YYYY-MM-DD HH:MM:SS format
	* @var string the date to use for the calendar
	*/

	private $_useDate;
	/**
	* The months for which the calendar is being built
	*
	* @var int the month being used
	*/
	
	private $_m;
	
	/**
	* The year from which the months start day is selected
	*
	* @var int the year being used
	*/
	
	private $_y;

	/**
	* The number of days in the month being used
	*
	* @var int the number of the days in the month
	*/

	private $_daysInMonth;

	/**
	* The index of the day of the week the month starts on (0-6)
	* 
	* @var int the day of the week the month starts on 
	*/

	private $_startDay;

	/*
	* Creates a database object and stores relevant data
	*/

	public function __construct($dbo=NULL, $useDate=NULL)
	{
		/*
		* Call the parent constructor to check for 
		* a database object
		*/

		parent::__construct($dbo);

		/*
		* Gather snd store data relevant to the month
		*/

		if (isset($useDate)) 
		{
			$this->_useDate = $useDate;
		}
		else 
		{
			$this->_useDate = date('Y-m-d H:i:s');
		}

		/*
		* Convert to a time stamp then determine the month 
		*and year to use when building the calendar
		*
		*/ 
		$ts = strtotime($this->_useDate);
		$this->_m = (int)date('m', $ts);
		$this->_y = (int)date('Y', $ts);

		/*
		* Determine how many months are there in the month
		*/

		$this->_daysInMonth = cal_days_in_month(
			CAL_GREGORIAN, 
			$this->_m,
			$this->_y
			);

		/*
		*Determine what weekday the months starts on
		*/

		$ts = mktime(0, 0, 0, $this->_m, 1, $this->_y);
		$this->_startDay = (int)date('w', $ts);

	}

	/**
	* Displays a given event`s information
	* @param int $id the event ID
	* @return string basic markup to display event info
	*/

	public function displayEvent($id)
	{
		/*
		* Make sure an ID was passed
		*/
		if (empty($id)) 
		{
			return NULL;
		}
		/*
		* Make sure the ID is an integer
		*/

		$id = preg_replace('/[^0-9]/', '', $id);

		/*
		* Load the event data from the DB
		*/

		$event = $this->_loadEventById($id);

		/*
		* Generate strings for the date, start, and end time
		*/

		$ts = strtotime($event->start);
		$date = date('F d, Y', $ts);
		$start = date('g:ia', $ts);
		$end = date('g:ia', strtotime($event->end));

		/*
		* Load admin options if the user is logged in
		*/

		$admin = $this->_adminEntryOptions($id);

		/*
		* Generate and return the markup
		*/

		return "<h2>$event->title</h2>"
		."<p class=\"dates\">$date, $start&mdash;$end</p>"
		."<p>$event->description</p>$admin";


	}//end of displayEventbyID

	/**
	* Generates a form to edit or create events
	* @return string HTML markup for the editing form
	*/

	public function displayForm()
	{
		/*
		* Check if an ID was passed
		*/

		if (isset($_POST['event_id'])) 
		{
			$id = (int)$_POST['event_id'];
		//force integer type to sanitize data
		}
		else
		{
			$id = NULL;
		}

		

		/*
		* Otherwise load the associated event
		*/

		if (!empty($id)) 
		{
			$event = $this->_loadEventById($id);

		/*
		* If mo object is returned, return NULL
		*/

		if (!is_object($event)) 
		{
			return NULL;
		}
		$submit = "Edit this event";
		}
		else
		{
			/* 
		* instantiate the headline/submit button text
		*/
		$submit = 'Create new event';

		/*
		* If no ID is passed, start with an empty event object.
		*/

		$event = new Event($id);		
		}

		/*
		* Build the Markup
		*/

		return <<<FORM_MARKUP

		<form action="assets/inc/process.inc.php" method="post">
		<fieldset>
		<legend>$submit</legend>
		<label for="event_title">Event Title</label>
		<input type="text" name="event_title" id="event_title" value="$event->title">
		<label for="event_start">Start Time</label>
		<input type="text" name="event_start" id="event_start" value="$event->start">
		<label for="event_end">End Time</label>
		<input type="text" name="event_end" id="event_end" value="$event->end">
		<label for="event_description">Event Description</label>
		<textarea name="event_description" id="event_description">$event->description</textarea>
		<input type="hidden" name="event_id" value="$event->id">
		<input type="hidden" name="token" value="$_SESSION[token]">
		<input type="hidden" name="action" value="event_edit">
		<input type="submit" name="event_submit" value="$submit">
		or <a href="./">cancel</a>
		</fieldset>
		</form>

FORM_MARKUP;
//form_markup should be at the start of the line
	}//end of displayForm

	/**
	* validates the form and saves/edits the event
	* 
	* @return mixed TRUE on success, an error message on failure
	*/

	public function processForm()

{

	/*
	* Exit if the action isn`t set properly
	*/

	if($_POST['action'] != 'event_edit')
	{
		return "The method processForm was accessed incorrectly!";
	}

	/*
	* escape data from the form
	*/

	$title = htmlentities($_POST['event_title'], ENT_QUOTES);
	$desc = htmlentities($_POST['event_description'], ENT_QUOTES);
	$start = htmlentities($_POST['event_start'], ENT_QUOTES);
	$end = htmlentities($_POST['event_end'], ENT_QUOTES);

	/*
	* If the start or end dates aren`t in a valid 
	*/
	if (!$this->_validDate($start)||!$this->_validDate($end))
	{
		return "Invalid date format! Use YYYY-MM-DD HH:MM:SS";
	}


	/*
	* if no event id passed, create a new event
	*/

	if (empty($_POST['event_id'])) 
	{
	$sql = "INSERT INTO `events`
			(`event_title`, `event_desc`, `event_start`, `event_end`)
			VALUES 
			(:title, :description, :start, :end)";
	}
/*
* update the event if it`s being edited
*/

else
{
	/*
	* cast the event ID as an integer for security
	*/

	$id = (int)$_POST['event_id'];
	$sql = "UPDATE `events`
			SET 
			`event_title` 	= :title,
			`event_desc`	= :description,
			`event_start`	= :start,
			`event_end`		= :end
			WHERE `event_id`= $id";
}

/*
* Execute the create or edit query after binding the data
*/
try
{
	$stmt = $this->db->prepare($sql);
	$stmt->bindParam(":title", $title, PDO::PARAM_STR);
	$stmt->bindParam(":description", $desc, PDO::PARAM_STR);
	$stmt->bindParam(":start", $start, PDO::PARAM_STR);
	$stmt->bindParam(":end", $end, PDO::PARAM_STR);
	$stmt->execute();
	$stmt->closeCursor();
	
	/*
	* returns the id of the event
	*/
	return $this->db->lastInsertId();
}
catch ( Exception $e)
{
	return $e->getMessage();
}
}//end of processForm()

/**
* confirm that the event should be deleted and does so
* 
* Upon clicking the button to delete an event, this
* generates a confirmation box. If the user confirms,
* this deletes the evebt from the database and sends the 
* user back out to the main calendar view. If the user
* decides not to delete the event, they`re sent back to 
* the main calendar view without deleting anything.
* 
* @param int $id the event ID
* @return mixed the form if confirming, voud or error if deleting
*/

public function confirmDelete($id)
{
	/*
	* Make sure an ID was passed
	*/

	if (empty($id)) { return NULL;}

	/*
	* Make sure the ID is an integer
	*/

	$id = preg_replace('/[^0-9]/', '', $id);

	/*
	* if the confirnation form was submitted and the form
	* has a valid token, check the form submission
	*/

	if (isset($_POST['confirm_delete']) && $_POST['token']==$_SESSION['token']) 
	{
	/*
	* If the deletion is confirmed, remove the event 
	* from the database
	*/

	if ($_POST['confirm_delete']=="Yes, Delete It") 
	{
	$sql = "DELETE FROM `events`
			WHERE `event_id`=:id
			LIMIT 1";
		try 
	{
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		$stmt->execute();
		$stmt->closeCursor();
		header("Location:./");
		return;
	}
	catch (Exception $e)
	{
		return $e->getMessage();
	}
	}
/*
* If not confirmed, sends the user to the main page
*/
else
{
	header("Location: ./");
	return;
}
}
/*
* If the confirmation form hasn`t been submitted, display it
*/

$event = $this->_loadEventById($id);

/*
* If no object is returned, return to the main view
*/
if (!is_object($event)) { header("Location: ./");}

return <<<CONFIRM_DELETE
<form action="confirmdelete.php" method="post">
<h2>
Are you sure you want to delete "$event->title"?
</h2>
<p>
There is <strong>no undo</strong> if you continue.
</p>
<p>
<input type="submit" name="confirm_delete" value="Yes, Delete It">
<input type="submit" name="confirm_delete" value="Nope! Just Kidding!">
<input type="hidden" name="event_id" value="$event->id">
<input type="hidden" name="token" value="$_SESSION[token]">
</p>
</form>
CONFIRM_DELETE;

}//end of confirmDelete()

/**
* validates a date string
*$param string $date the date string to validate
*@return bool TRUE on success, FALSE on failure
*/
private function _validDate($date)
{
	/*
	*Define a regex pattern to check the date format
	*/
	$pattern = "/^(\d{4}(-\d{2}){2} (\d{2}:){2}(\d{2}))$/";

	/*
	* if a match is found, return TRUE, FALSE otherwise=
	*/
	return preg_match($pattern, $date)==1 ? TRUE:FALSE;
}

	/**
	* Loads event(s) info into an array
	* @param int $id an optional event ID to filter results
	* @return array an array of events from the database
	*/

	private function _loadEventData($id=NULL)
	{
		$sql = "SELECT * FROM events";

		/*
		* if an event ID is supplied, add a WHERE clause
		* so only that event is returned
				*/

		if (!empty($id)) 
		{
			$sql .= " WHERE event_id=:id LIMIT 1"; 
		}
		/*
		*otherwise, load all events for the month in use
				*/
		else
		{
			/*
			*find the first and the last days of the month
					*/

			$start_ts = mktime(0, 0, 0, $this->_m, 1, $this->_y);
			$end_ts = mktime(23, 59, 59, $this->_m+1, 0, $this->_y);
			$start_date = date('Y-m-d H:i:s', $start_ts);
			$end_date = date('Y-m-d H:i:s', $end_ts);

			/*
			* Filter events only those happening in the
			* currently selected month
					*/

			$sql .= " WHERE `event_start` BETWEEN '2016-01-01' AND '2016-01-30' ORDER BY `event_start`";
		}

		try 
		{
			$stmt = $this->db->prepare($sql);

			/*
			* Bind the parameter if an ID was passed
				*/

			if(!empty($id))
			{
				$stmt ->bindParam(":id", $id, PDO::PARAM_INT);
			}

			$stmt->execute();
			$results= $stmt->fetchALL(PDO::FETCH_ASSOC);
			$stmt->closeCursor();

			return $results;

		}

		catch (Exception $e)
		{
			die($e->getMessage());
		}
	}//end of loadEventData

	/*
	* Loads all events for the month into an array
	*
	* @return array events info
	*/

	public function _createEventObj()
	{
		/*
		* Loads the events array
		*/

		$arr=$this->_loadEventData();

		/*
		* Create new array, then organise the events
		* by the day of the month on which they occr
		*/

		$events = array();
		foreach ($arr as $event) 
		{
			$day = date('j', strtotime($event['event_start']));
			try
			{
				$events[$day][] = new Event($event);
			}
			catch(Exception $e)
			{
				die($e->getMessage());
			}
		}
		return $events;


	}// end of _createEventObj()

	/**
	* Returns a single event object
	* @param int $d an event ID
	*
	* @return object the event object
	* 
	*/
	private function _loadEventById($id)
	{
		/*
		* If no id is passed, return NULL
		*/

		if (empty($id)) 
		{
			return NULL;
		}

		/*
		* Load the event info array
		*/
		$event = $this->_loadEventData($id);

		/*
		* Return an event object
		*/
		if (isset($event[0])) 
		{
			return new Event($event[0]);
		}
		else 
		{
			return NULL;
		}
	}//end of _loadEventById

	/**
	*generates markup to display administrative links
	*
	* @return string markup to display the administrative links
	*/
private function _adminGeneralOptions()
{

/*
* If the user s logged in
*/
if (isset($_SESSION['user'])) {
	
/*
* display admin controls
*/
 
 return <<<ADMIN_OPTIONS
 <a href="admin.php" class="admin">+Add a New Event</a>
 <form action="assets/inc/process.inc.php" method="post">
 <div>
 <input type="submit" value="Log Out" class="logout"/>
 <input type="hidden" name="token" value="$_SESSION[token]">
 <input type="hidden" name="action" value="user_logout">
 </div>
 </form>
ADMIN_OPTIONS;
}
else
{
	return <<<ADMIN_OPTIONS
 <a href='login.php'>Log In</a>
ADMIN_OPTIONS;
}

}//end of _adminGeneralOptions()


	/**
	*generates edit and delete options for a given ID
	*
	* @param int $id the event ID to generate options for
	* @return string the markup for the edit/delete options
	*/

private function _adminEntryOptions($id)
{
	if (isset($_SESSION['user'])) {
return <<<ADMIN_OPTIONS
<div class="admin-options">
<form action="admin.php" method="post">

<input type="submit" name="edit_event" value="Edit This Event">
<br>
<input type="hidden" name="event_id" value="$id">
</form>
<form action="confirmdelete.php" method="post">

<input type="submit" name="delete_event" value="Delete This Event">
<input type="hidden" name="event_id" value="$id">


</form>
</div>
<!--end .admin-options-->
ADMIN_OPTIONS;
}
else
{
	return NULL;
}
}//end of _adminEntryOptions($id)

	/**
	* returns  HTML markup to display the ca;endar and events
	* using the info stored in class properties,
	* the events for the given month are loaded, the calendar is
	* generated, and the whole thing is returned as valid markup
	*
	*@return string the calendar HTML markup
	*/

	public function buildCalendar()
	{
		/*
		*determine the calendar month and create an array of
		* weekday abbreviations to label the calendar columns
		*/

		$cal_month = date('F Y', strtotime($this->_useDate));
		$cal_id 	= date('Y-m', strtotime($this->_useDate));
		//define('WEEKDAYS', array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'));
		$weekdays = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');

		/*
		* Add a header to calendar markup
		*/
		$html = "<h2 id=\"month-$cal_id\">$cal_month</h2>";

		for($d=0, $labels=NULL; $d<7; ++$d)
		{
			$labels .= "<li>".$weekdays[$d]."</li>";
		}

		$html .= '<ul class="weekdays">'.$labels."</ul>";

		/*
		* Load events data
		*/

		$events = $this->_createEventObj();
		

		/* 
		* Create the calendar markup
		*/

		$html .= "<ul>"; //start a new unordered list

		for ($i=1, $c=1, $t=date('j'), $m=date('m'), $y = date('Y'); $c<=$this->_daysInMonth; ++$i) 
		{ 
			/*
			* apply  a 'fill' class to the boxes occuring
			* before the first of the month
		*/
			$class = $i<=$this->_startDay ? "fill" : NULL;


			/*
			* Add a "today" class if the current date matches
			* the current date
		*/

			if ($c==$t && $m==$this->_m && $y == $this->_y) 
			{
				$class = "today";
			}

			/*
			* build the opening and closing list item tags
		*/

			$ls = sprintf('<li class="%s">', $class);
			$le = "</li>";

			/*
			* add the day of the month to identify the calendar box
		*/
			$event_info = NULL;//clear the variable
			

			if ($this->_startDay<$i && $this->_daysInMonth>=$c) 
			{
				/*
				* Format events data
			*/
				if(isset($events[$c]))
				{
					foreach ($events[$c] as $event) 
					{
						$link = '<a href="view.php?event_id='.$event->id.'">'.$event->title.'</a>';
						$event_info .= "$link";
					}
				}
				$date = sprintf("<strong>%02d</strong>", $c++);
			}
			else {$date = "&nbsp;";}

			/*
			* if the current day is a  Saturday, wrap to the next row
		*/

			$wrap = $i!=0 && $i%7==0 ? "</ul><ul>" : NULL;

			/*
			* Assemble the pieces into a finished item
		*/

			$html .= $ls . $date .$event_info.$le . $wrap;

			/*
			* Add a filter to finish out the last week
		*/
		}

		while ($i%7!=1) 
		{
			$html .= '<li class="fill">&nbsp;</li>';
			$i++;
		}

		/*
		* Close the final unordered list
		*/

		$html .= "</ul>\n\n";
/*
* if logged in, display the admin options
*/

$admin = $this->_adminGeneralOptions();

		
		/*
		* return the markup for output
		*/

		return $html . $admin;


	}//end of buildCalendar()
	



}