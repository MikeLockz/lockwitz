<?php
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Google Calendar</title>
		<meta name="generator" content="Studio 3 http://aptana.com/">
		<meta name="author" content="Mikey">
		<!-- Date: 2011-05-17 -->
		
<script type="text/javascript" src="http://www.google.com/jsapi?key=ABQIAAAAPL06g_qDj4UyIsn7PbS_6BSjzEEM70ACYB5n49SV0ixGGjPbmRSsmjs8pdiWNa9moQf_qtvVnTq4dg
"></script>
<script type="text/javascript">
<!--
/* Loads the Google data JavaScript client library */
google.load("gdata", "2.x");

function init() {
  // init the Google data JS client library with an error handler
  google.gdata.client.init(handleGDError);
  // load the code.google.com developer calendar
  loadDeveloperCalendar();
}

/**
 * Loads the Google Developers Event Calendar
 */
function loadDeveloperCalendar() {
  loadCalendarByAddress('hbseb4720e9lrmvisom9e8t7fs@group.calendar.google.com');
}

/**
 * Adds a leading zero to a single-digit number.  Used for displaying dates.
 */
function padNumber(num) {
  if (num <= 9) {
    return num;
  }
  return num;
}

/**
 * Determines the full calendarUrl based upon the calendarAddress
 * argument and calls loadCalendar with the calendarUrl value.
 *
 * @param {string} calendarAddress is the email-style address for the calendar
 */ 
function loadCalendarByAddress(calendarAddress) {
  var calendarUrl = 'https://www.google.com/calendar/feeds/' +
                    calendarAddress + 
                    '/public/full';
  loadCalendar(calendarUrl);
}

/**
 * Uses Google data JS client library to retrieve a calendar feed from the specified
 * URL.  The feed is controlled by several query parameters and a callback 
 * function is called to process the feed results.
 *
 * @param {string} calendarUrl is the URL for a public calendar feed
 */  
function loadCalendar(calendarUrl) {
  var service = new google.gdata.calendar.CalendarService('gdata-js-client-samples-simple');
  var query = new google.gdata.calendar.CalendarEventQuery(calendarUrl);
  query.setOrderBy('starttime');
  query.setSortOrder('ascending');
  query.setFutureEvents(true);
  query.setSingleEvents(true);
  query.setMaxResults(10);

  service.getEventsFeed(query, listEvents, handleGDError);
}

/**
 * Callback function for the Google data JS client library to call when an error
 * occurs during the retrieval of the feed.  Details available depend partly
 * on the web browser, but this shows a few basic examples. In the case of
 * a privileged environment using ClientLogin authentication, there may also
 * be an e.type attribute in some cases.
 *
 * @param {Error} e is an instance of an Error 
 */
function handleGDError(e) {
  document.getElementById('jsSourceFinal').setAttribute('style', 
      'display:none');
  if (e instanceof Error) {
    /* alert with the error line number, file and message */
    alert('Error at line ' + e.lineNumber +
          ' in ' + e.fileName + '\n' +
          'Message: ' + e.message);
    /* if available, output HTTP error code and status text */
    if (e.cause) {
      var status = e.cause.status;
      var statusText = e.cause.statusText;
      alert('Root cause: HTTP error ' + status + ' with status text of: ' + 
            statusText);
    }
  } else {
    alert(e.toString());
  }
}

/**
 * Callback function for the Google data JS client library to call with a feed 
 * of events retrieved.
 *
 * Creates an unordered list of events in a human-readable form.  This list of
 * events is added into a div called 'events'.  The title for the calendar is
 * placed in a div called 'calendarTitle'
 *
 * @param {json} feedRoot is the root of the feed, containing all entries 
 */ 
function listEvents(feedRoot) {
  var entries = feedRoot.feed.getEntries();
  var eventDiv = document.getElementById('events');
  if (eventDiv.childNodes.length > 0) {
    eventDiv.removeChild(eventDiv.childNodes[0]);
  }
  /* create a new unordered list */
  var ul = document.createElement('ul');
  /* set the calendarTitle div with the name of the calendar */
  document.getElementById('calendarTitle').innerHTML = "Calendar: " + feedRoot.feed.title.$t;
  /* loop through each event in the feed */
  var len = entries.length;
  for (var i = 0; i < len; i++) {
    var entry = entries[i];
    var title = entry.getTitle().getText();
    var locations = entry.getLocations();
	var location = locations[0].getValueString()
	var startDateTime = null;
    var startJSDate = null;
	var times = entry.getTimes();
    if (times.length > 0) {
      startDateTime = times[0].getStartTime();
      startJSDate = startDateTime.getDate();
    }
    var entryLinkHref = null;
    if (entry.getHtmlLink() != null) {
      entryLinkHref = entry.getHtmlLink().getHref();
    }
    var dateString = (startJSDate.getMonth() + 1) + "/" + startJSDate.getDate();
    if (!startDateTime.isDateOnly()) {
      dateString += " " + startJSDate.getHours() + ":" + 
          padNumber(startJSDate.getMinutes());
    }
    var li = document.createElement('li');

    /* if we have a link to the event, create an 'a' element */
    if (entryLinkHref != null) {
      entryLink = document.createElement('a');
      entryLink.setAttribute('href', entryLinkHref);
      entryLink.appendChild(document.createTextNode(title));
      li.appendChild(entryLink);
      li.appendChild(document.createTextNode(' - ' + location + ' - ' + dateString));
    } else {
      //li.appendChild(document.createTextNode(title + ' - ' + dateString));
	  li.appendChild(document.createTextNode(title + ' - ' + location + ' - ' + dateString));
    }	    

    /* append the list item onto the unordered list */
    ul.appendChild(li);
  }
  eventDiv.appendChild(ul);
}

google.setOnLoadCallback(init);
//-->
</script>
<div id="calendarTitle"></div>
<div id="events"></div>
  
<script type="text/javascript">
loadCalendar('https://www.google.com/calendar/feeds/hbseb4720e9lrmvisom9e8t7fs@group.calendar.google.com/public/full');
</script>
		
		
	</head>
	<body>
		<div id="events"><p></p></div> 
	</body>
</html>