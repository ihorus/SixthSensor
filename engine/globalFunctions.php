<?php
function autoPluralise ($singular, $plural, $count = 1) {
	// fantasticly clever function to return the correct plural of a word/count combo
	// Usage:	$singular	= single version of the word (e.g. 'Bus')
	//       	$plural 	= plural version of the word (e.g. 'Busses')
	//			$count		= the number you wish to work out the plural from (e.g. 2)
	// Return:	the singular or plural word, based on the count (e.g. 'Jobs')
	// Example:	autoPluralise("Bus", "Busses", 3)  -  would return "Busses"
	//			autoPluralise("Bus", "Busses", 1)  -  would return "Bus"

	return ($count == 1)? $singular : $plural;
} // END function autoPluralise

function fileInclude($file) {
	if (file_exists($file)) {
		include_once($file);
	} else {
		echo ("<img src=\"..\\images\\404.png\">");
		echo ("<br />");
		echo ("The file '" . $file . "' doesn't exist.");
	}
}

function paragraphTidyup($string = "") {
	// clean up the text to replace hard returns "\r" with proper HTML markup "<br /"
	// Usage:	$string	=	paragraph or string of text you wish to replace hardreturns with
	//						proper html markup. (e.g. 'Hello,\r World')
	// Return:	the same string, with <br /> tags inplace of hard returns AND URLs fully hyperlinked
	
	// terrible preg_replace pattern, I know - why don't you do better!
	$string = preg_replace('@((https|http|www)://([-\w\.]+)+(:\d+)?(/([\w/_\.\-\%\+]*(\?\S+)?)?)?)@', '<a href="$1">$1</a>', $string);
	
	//replace any hard returns with proper HTML <br /> tags
	$string = str_replace("\r", "<br />", $string);
	
	return $string;
} // END function paragraphTidyUp


function howLongAgo($strPastDate) {
	$diff = time() - ((int) $strPastDate);
	
	if ($diff < 0) {
		return FALSE;
	} else if ($diff < 60) {
		return ("just now");
	} else if ($diff < 3600) {
		// minutes ago
		$diff = round($diff / 60);
		if ($diff == 0) {
			$diff = 1;
		}
		$diff = $diff . (autoPluralise (" minute", " minutes", $diff)) . " ago";

		return ($diff);
	} else if ($diff < 86400) {
		// hours ago
		$diff = round($diff / 3600);
		if ($diff == 0) {
			$diff = 1;
		}
		$diff = $diff . (autoPluralise (" hour", " hours", $diff)) . " ago";

		return ($diff);
	} else if ($diff < 2592000) {
		// days ago
		$diff = round($diff / 86400);
		if ($diff == 0 | $diff == 1) {
			$diff = ("yesterday");
			return $diff;
		}
		$diff = $diff . (autoPluralise (" day", " days", $diff)) . " ago";
		return ($diff);
	} else if ($diff < 31536000) {
		//months ago
		$diff = round($diff / 2592000);
		$diff = $diff . (autoPluralise (" month", " months", $diff)) . " ago";
		return ($diff);
	} else {
		// years ago
		$diff = round($diff / 31536000);
		$diff = $diff . (autoPluralise (" year", " years", $diff)) . " ago";
		return ($diff);
	}

}

function optionDropdown($value, $display, $selected) {
 	$option  = ("<option ");
 	
 	// check if the selected option is an array
	if (is_array($selected)){
		// if so, we need to check EACH element against the $value
		foreach ($selected AS $select) {
			if ($select == $value) {
				$option .= (" selected ");
			}
		}
	} else {
		if ($selected == $value) {
			$option .= (" selected ");
		}
	}
	
	$option .= ("value=\"");
	$option .= ($value);
	$option .= ("\">");
	$option .= ($display);
	$option .= ("</option>");
	
	return $option;
}



function dateDisplay($strUnixTime, $age=false) {
	// check if the time element is '00:00' - meaning time isn't a consideration
	if (date('H:i', $strUnixTime) == "00:00") {
		// time element is '00:00' - so change the mask to not display it
		$strDateTime = date('l jS \of F Y', $strUnixTime);
	} else {
		// time element is specified - so use the date mask to display it
		$strDateTime = date('l jS \of F Y H:i', $strUnixTime);
	}
	
	// should we even show how old the date/time is?
	if ($age == true) {
		// check that we get a value back from howLongAgo, otherwise, the age isn't valid and shouldn't be displayed
		if (howLongAgo($strUnixTime)) {
			$strDateTime = $strDateTime . " <i>(" . howLongAgo($strUnixTime) . ")</i>";
		}
	}
	return $strDateTime;
} // END function dateDisplay()

function moneyDisplay($value = FALSE, $showSymbol = TRUE) {
	$currencySign = "&#163;";
	$value = round($value,2);
	
	$value = number_format($value, 2, '.', ',');
	
	if ($showSymbol == TRUE) {
		$value = $currencySign . $value;
	}
	
	return $value;
}

function printArray($array) {
	echo ("<pre>");
	print_r ($array);
	echo ("</pre>");
}

function makeBlock($type = NULL, $author = NULL, $meta = NULL, $content = NULL) {
	$output = "<div class=\"block\">";
		$output .= "<div class=\"meta\">";
			if ($type == "tag") {
				$editIcon  = "<a href=\"index.php?node=tagEdit.php&tagSerial=" . $author . "\">";
				$editIcon .= "<img src=\"images/wrench.png\" class=\"editIcon\" />";
				$editIcon .= "</a>";
			} else {
				$editIcon = "";
			}
			
			$output .= "<span class=\"" . $type . "\">" . $author . $editIcon . "</span>";
			$output .= "<span class=\"date\">" . $meta . "</span>";	
		$output .= "</div>";
		$output .= "<div class=\"content\">";
			$output .= $content;
		$output .= "</div>";
	$output .= "</div>";
	
	return $output;
}

function sanitise_array($array = NULL) {
	// function to take an array (or comma-seperated string) and make sure that the return is a clean
	// well formed array.
	
	// if the passed string isn't an array - build it into one
	if (!is_array($array)) {
		$output = explode(",", $array);
	}
	
	$output = array_filter($output);
	
	return $output;
}

function displayDateSlider() {
	$years[] = date('Y') - 1;
	$years[] = date('Y');
	
	$output  = "<fieldset>";
	$output .= "<select name=\"valueAA\" id=\"valueAA\">";
	
	// itterate though each element in the $years array
	//
	// this is the first slider control
	foreach ($years AS $key => $year) {
		$i = 1;
		
		$output .= "<optgroup label=\"" . $year . "\">";
		
		// build a slider with every month for this year
		do {
			// set the slider to the first month, in the first year (of the years array)
			if ($i == 1 && $key == 0) {
				$selcted = "selected=\"selected\"";
			} else {
				$selcted = "";
			}
			
			$output .= "<option value=\"" . getMonthString($i) . "\" " . $selcted . ">" . getMonthString($i) . "/" . $year . "</option>";
			$i++;
		} while ($i <= 12);
		
		$output .= "</optgroup>";
	}
	$output .= "</select>";
	
	// do the same as before, but this time, for the end slider
	$output .= "<select name=\"valueBB\" id=\"valueBB\">";
	foreach ($years AS $key => $year) {
		$i = 1;
		
		$output .= "<optgroup label=\"" . $year . "\">";
		
		do {
			// set the slider end date to this month (in the second year of the years array)
			// there is little point in setting it for the future!
			if ($i == date('m') && $key == 1) {

				$selcted = "selected=\"selected\"";
			} else {
				$selcted = "";
			}
			
			$output .= "<option value=\"" . $i . "\" " . $selcted . ">" . getMonthString($i) . "/" . $year . "</option>";
			$i++;
		} while ($i <= 12);
		
		$output .= "</optgroup>";
	}
	$output .= "</select>";
	$output .= "</fieldset>";
	
	return $output;
}

function getMonthString($n) {
	$timestamp = mktime(0, 0, 0, $n, 1, 2005);
	
	return date("M", $timestamp);
}

function cacheResult($cacheTitle = NULL, $cacheValue = NULL) {
	$_SESSION['cachedValues'][$cacheTitle] = $cacheValue;
}

function cacheCheck($cacheTitle = NULL) {
	if (isset($_SESSION['cachedValues'][$cacheTitle])) {
		$returnValue = TRUE;
	} else {
		$returnValue = FALSE;
	}
	
	return $returnValue;
}

function cacheReturn($cacheTitle = NULL) {
	return $_SESSION['cachedValues'][$cacheTitle];
}

function startOfTerm() {
	// calculate the scholastic year dates
	if (date('m') >= '8') {
		// we're past August 1st, so it's from this August, to next
		$dateFrom = date('Y') . "-08-01";
	} else {
		// we're not past August 1st, so it's from last August to next
		$dateFrom = date('Y')-1 . "-08-01";
	}
	
	return $dateFrom;
}

function endOfTerm() {
	// calculate the scholastic year dates
	if (date('m') >= '8') {
		// we're past August 1st, so it's from this August, to next
		$dateTo = date('Y')+1 . "-07-31";
	} else {
		// we're not past August 1st, so it's from last August to next
		$dateTo = date('Y') . "-07-31";
	}
	
	return $dateTo;
}

function scholasticYears() {
	// work out this year in YYYY format, remembering that the new intake happens in september (09 month)
	$thisYear = date('Y', strtotime(startOfTerm()));
	
	// build an array, working backwards, with the key being the KeyStage year
	// and the array value being the calendar year
	$yearsArray[14] = $thisYear -7;		// Year 14 (Sixth Form)
	$yearsArray[13] = $thisYear -6;		// Year 13 (Sixth Form)
	$yearsArray[12] = $thisYear -5;		// Year 12 (Sixth Form)
	$yearsArray[11] = $thisYear -4;		// Year 11
	$yearsArray[10] = $thisYear -3;		// Year 10
	$yearsArray[9] = $thisYear -2;		// Year 9
	$yearsArray[8] = $thisYear -1;		// Year 8
	$yearsArray[7] = $thisYear;			// Year 7
	
	return $yearsArray;
}

function sendMail($recipient, $subject, $message) {

	if (!isset($subject)) {
		$subject = ("Auto Generated E-Mail from " . SITE_NAME);
	}
	// Function to send a simple e-mail
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	
	$headers .= "To: " . $recipient . "\r\n";
	$headers .= "From: " . SITE_NAME . " <no-reply@wallingford.oxon.sch.uk>" . "\r\n";
	$headers .= "Reply-To: no-reply@wallingford.oxon.sch.uk" . "\r\n";
	
	// Additional headers
	// $headers .= 'Cc: birthdayarchive@example.com' . "\r\n";
	// $headers .= 'Bcc: birthdaycheck@example.com' . "\r\n";

	$message = ($message);
	
	if (isset($recipient)) {
		//echo("<p>Message send complete…</p>");
		//echo $recipient . $subject . $message;
		mail($recipient, $subject, $message, $headers);
	} else {
		//echo("<p>Message delivery failed...</p>");
		//echo $recipient . $subject . $message;
		return FALSE;
	}
}

function sendMailWithAttachment($filename = NULL, $path = NULL, $mailto, $from_mail, $from_name, $replyto, $subject, $message) {
	$file = $path.$filename;
	$file_size = filesize($file);
	$handle = fopen($file, "r");
	$content = fread($handle, $file_size);
	fclose($handle);
	$content = chunk_split(base64_encode($content));
	$uid = md5(uniqid(time()));
	$name = basename($file);
    $header = "From: ".$from_name." <".$from_mail.">\r\n";
    $header .= "Reply-To: ".$replyto."\r\n";
    $header .= "MIME-Version: 1.0\r\n";
    $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
    $header .= "This is a multi-part message in MIME format.\r\n";
    $header .= "--".$uid."\r\n";
    $header .= "Content-type:text/html; charset=iso-8859-1\r\n";
    $header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $header .= $message."\r\n\r\n";
    $header .= "--".$uid."\r\n";
    $header .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"; // use different content types here
    $header .= "Content-Transfer-Encoding: base64\r\n";
    $header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
    $header .= $content."\r\n\r\n";
    $header .= "--".$uid."--";
    if (mail($mailto, $subject, "", $header)) {
        echo "Sending e-mail to " . $mailto . " completed.";
    } else {
        echo "Sending e-mail to " . $mailto . " failed.";
    }
}
?>
