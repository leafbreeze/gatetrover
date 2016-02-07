<?php
# ********************************************************** 
# Project: Fierce Flagg
# Description: PHP Website Scraping
# Author: Kyle M. Bondo
# Version: 0.15
# Published: February 04, 2016
# ********************************************************** 

# Include the library
include('simple_html_dom.php'); 

// start calculating page load time
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;

# **********************************************************
# PHP Simple HTML DOM Parser
# **********************************************************
function dlPage($href) {
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, FALSE);
	curl_setopt($curl, CURLOPT_URL, $href);
	curl_setopt($curl, CURLOPT_REFERER, $href);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.4 (KHTML, like Gecko) Chrome/5.0.375.125 Safari/533.4");
	$str = curl_exec($curl);
	curl_close($curl);

	// Create a DOM object
	$dom = new simple_html_dom();
	// Load HTML from a string
	$dom->load($str);

	return $dom;
}

# **********************************************************
# DEFINE CONSTANTS
# **********************************************************
define('ROOT_URL', 'http://www.milehighcomics.com');
define('CGI_URL', 'http://www.milehighcomics.com/cgi-bin/backissue.cgi?');
define('IMAGE_URL', 'http://image2.milehighcomics.com/istore/thumbnails/');
define('ACTION', 'list'); 
define('TITLE', 35490175336);
define('PUBLISHER', 'MV');
define('INSTOCK', 0);

# KNOWN CONSTANTS
define('START_NUMBER', 1);
define('ROWS_PER_PAGE', 20);


# DEV STATE CONSTANTS
define('APP_STATE', 0); // 0 = DEV, 1 = TEST


if (APP_STATE == 0) {
	# Target URL = G.I.Joe (Page 1) - START
	$headerUrl = 'http://www.milehighcomics.com/cgi-bin/backissue.cgi?action=list&title=35490175336&snumber=1';
} else if (APP_STATE == 1) {
	# Test URL = Avengers (Page 1) - START TEST
	$headerUrl = 'http://www.milehighcomics.com/cgi-bin/backissue.cgi?action=list&title=06386089740&snumber=1';
}

# Each page is an incriment of 20 (snumber)
# G.I.Joe by Marvel Comics shows approx. 356 database records, creating 19 dynamic links/pages
# G.I.Joe by Marvel Comics with INSTOCK=1 shows approx. 285 database records, creating 15 dynamic links/pages

# Find all the records available

	# find the snumber in the CGI URL string
	# Initial snumber = 1
	# Rows per page = 20
	# Page shows 20 items starting with #1
	# Last snumber = 361
	# 361 / 20 = 18.05
	# Real Last snumber = 361 + 20 = 381
	# 381 / 20 = 19.05
	# Equation: [(last snumber) + ((Rows per page) - 1)] / Rows per page = Total Pages Available


	# Now point the code towards the target URL
	# Target URL = G.I.Joe (Page 1) - START
// $headerUrl = 'http://www.milehighcomics.com/cgi-bin/backissue.cgi?action=list&title=35490175336&snumber=1';
	# Test URL = Avengers (Page 1) - START TEST
	// $headerUrl = 'http://www.milehighcomics.com/cgi-bin/backissue.cgi?action=list&title=06386089740&snumber=1';	





# **********************************************************
# BUILD HEADER
# **********************************************************
# You need the initial url to set up the page
# It only needs to run once to build the headers

$data = dlPage($headerUrl);

# find publisher name - unique row
foreach($data->find('td[colspan="5"]') as $bookPublisher) {
	$bookPublisher = substr($bookPublisher, ($pos = strpos($bookPublisher, ':')) !== false ? $pos + 1:0);
}

# find book title - unique row
foreach($data->find('td[colspan="4"]') as $bookTitle) {
	$bookTitle = substr($bookTitle, ($pos = strpos($bookTitle, ':')) !== false ? $pos + 1:0);
}

# count link block to determine number of total pages per title
// DOMXpath for finding the link block: $x('//p//font[@size="2"]')
$pageTotal = 0;
foreach($data->find('p font[size="2"]') as $linkBlock) {
	# tear apart the link blcok to find how many pieces you have
	if ($pageTotal == 0) {
		$pageCount = explode('[' , $linkBlock);
		$pageTotal = count($pageCount); // 19
		$pageTotal = $pageTotal - 1;
	}
}
// echo '<h3>linkBlock: ' . $linkBlock . '</h3>';
// echo '<h3>pageTotal: ' . $pageTotal . '</h3>';


# find book headers
$fieldSeperator = ",";
foreach($data->find('tr[align="CENTER"] td') as $e) {
	$bookFields = $e->innertext;
	if ($bookFields != "") {
		$bookHeaders .= $bookFields . $fieldSeperator;
	}
}

# find the eight (8) headers and create HTML
if ($bookHeaders != "") {
	list($dictBookGraphic, $dictBookIssue, $dictBookFair, $dictBookGood, $dictBookVeryGood, $dictBookFine, $dictBookVeryFine, $dictBookNearMint) = explode(",", $bookHeaders);
	$dictBookHeaders .= '<tr><th>' . $dictBookGraphic . '</th><th>' . $dictBookIssue . '</th><th>' . $dictBookFair . '</th><th>' . $dictBookGood . '</th><th>' . $dictBookVeryGood . '</th><th>' . $dictBookFine . '</th><th>' . $dictBookVeryFine . '</th><th>' . $dictBookNearMint . '</th></tr>';
}

	
# start frontpage HTML
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Mile High Comics Scraper</title>
    <link href="main.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <div class="wrapper">
    	<h1>Project FIERCE FLAGG v0.15</h1>
        <h2>Publisher: <?php echo $bookPublisher ?><br />
        Title: <?php echo $bookTitle ?><br />
        Total Number of Pages: <?php echo $pageTotal ?></h2>
    </div>

<table cellspacing='0'> <!-- cellspacing='0' is important, must stay -->

    <!-- Table Header -->
    <thead>
        <?php echo $dictBookHeaders; ?>
    </thead>
    <!-- Table Header -->

<?
	
$data->clear();
unset($data);

?>
    <!-- table body -->
    <tbody>
<?
		
# **********************************************************
# BUILD BODY
# **********************************************************
# Now run the page builder without the need for all the headers

# body build vars
// $startNumber = 1;
$landryList = 1; //Show Total Number of Target Pages on one screen (1 to 19 for Marvel's G.I.Joe)
$builderUrl = '';
$imageTotal = 0;
$rowCount = 0;
$cellCount = 0;

	for ($s = 0; $s < $landryList; $s++) {
	
		$builderUrl = CGI_URL . 'action=' . ACTION . '&title=' . TITLE . '&snumber=' . START_NUMBER . '&instock=' . INSTOCK;
	
		## CGI URL GET Request Analysis ##
		# ACTION = GET form field that request which HTML template the database will use to display records (e.g. list)
		# SNUMBER = Starting database query record ID and used to create dynamic HTML links (e.g. snumber = snumber + 20 per link)
		# PUBLISHER = Two-Letter ISO code to identify the publisher database row/table (e.g. MV = Marvel)
		# INSTOCK = Binary flag that filters query results by show-all-stock (0) or instock-only (1) 
		# - Impacts snumber dynamic link creation
		
		# FOUND MONSTERS: 
		# Dynamic Links include the ISSUE text in the metadata
		# This causes word-wrapping and long links that are difficult to read
		# Need a better way to filter results and link to other lists that does not include metadata artifacts
		
		# create an object using our requested url
		$data = dlPage($builderUrl);
	
		# find out how many td tags there are with align="CENTER"
		# SAMPLE OUTPUT: 1 Page => 20 Rows * 8 Cols = 160 Cells
		# DOMXPATH: ALL TD WITH CENTER EX = $x('//tr//td[@align="CENTER"]')

		$cellCounter = 0;		
		foreach($data->find('tr td[align="CENTER"]') as $td) {
			$cellCounter = $cellCounter + 1; // counting from 1 to 160
			$cellCount = $cellCount + 1;
			// echo $cellCount;
			if ($cellCounter % 8 == 0) {
				$rowCount = $rowCount + 1;
			}
		}

		$imageCount = 0;
		$imageArray = array();
		$imageParts = "";
		$imageSource = "";
		
		# find the image filename (covers are serialized)
		# NOTE: All cells with good date have the td CENTER tag
		
		# DOMXPATH: ALL TD WITH IMAGES = $x('//tr//td[@align="CENTER"]//img')
		# This gives me ONLY the images
		
		foreach($data->find('tr td[align="CENTER"] img') as $art) {
			// $imageArray[] = $art->src; 
			// echo '<p>src: [' . $imageCount . '] ' . $art->src . '</p>';
			
			# METADATA: Image value contains metadata containing 3 to 6 values (so far)
			# EX: http://image2.milehighcomics.com/istore/thumbnails/35490175336.1.GIF
			$imageSource = $art->src;
			$imageParts = substr($imageSource, ($pos = strpos($imageSource, '/')) !== false ? $pos + 2:0);
			list($domain, $istore, $thumbnails, $imageurl) = explode("/", $imageParts);
			# covers are the only images on the page that have the $imageurl var populated
			
			# FOUND MONSTERS: 
			# - CGI script uses dot syntax to create a unique image filename
			# TODO: The period in the Metadata value "JR." creates a (Image value + 1) case value 
			# - This results in the creation of a blank field
			# SEO: Need to find a way to remove the metadata from the image value			

			# tear apart the image filename to find how many pieces you have
			$bookMeta = explode('.' , $imageurl);
			$num_tags = count($bookMeta);
			
			# switch based on the number of pieces
			switch($num_tags)
			{
				case ('3'):
					list($serialNumber, $issueNumber, $fileExt) = explode(".", $imageurl);
					// $bookMeta = '<p>Serial: ' . $serialNumber . '<br />Issue: ' . $issueNumber . '<br />Ext: ' .  $fileExt . '</p>';	
					$bookMeta = '<p>&nbsp;</p>';			
					// echo 'value equals 3 - Cover: ' . $imageurl . '<br />';
					break;
				case ('4'):
					list($serialNumber, $issueNumber, $issueType1, $fileExt) = explode(".", $imageurl);										
					// $bookMeta = '<p>Serial: ' . $serialNumber . '<br />Issue: ' . $issueNumber . '<br />Type1: ' . $issueType1  . '<br />Ext: ' .  $fileExt . '</p>';
					$bookMeta = '<p>&nbsp;</p>';							
					// echo 'value equals 4 - Newsstand or Penny: ' . $issueType1 . '<br />';								
					break;
				case ('5'):
					list($serialNumber, $issueNumber, $issueType1, $issueType2, $fileExt) = explode(".", $imageurl);										
					// $bookMeta = '<p>Serial: ' . $serialNumber . '<br />Issue: ' . $issueNumber . '<br />Type1: ' . $issueType1 . '<br />Type2: ' . $issueType2  . '<br />Ext: ' .  $fileExt . '</p>';
					$bookMeta = '<p>&nbsp;</p>';								
					// echo 'value equals 5 - 2nd Print, Toy Insert: ' . $issueType1 . ' ' . $issueType2 . '<br />';								
					break;				
				case ('6'):	
					list($serialNumber, $issueNumber, $issueType1, $issueType2, $issueType3, $issue) = explode(".", $imageurl);					
					// $bookMeta = '<p>Serial: ' . $serialNumber . '<br />Issue: ' . $issueNumber . '<br />Type1: ' . $issueType1 . '<br />Type2: ' . $issueType2 . '<br />Type3: ' . $issueType3  . '<br />Ext: ' .  $fileExt . '</p>';
					$bookMeta = '<p>&nbsp;</p>';								
					// echo 'value equals 6 - 75 Cent CV: ' . $issueType1 . ' ' . $issueType2 . ' ' . $issueType3 . '<br />';													
					break;
				default:
					// echo "value equals NA - Not a Cover<br />";
					break;					
			}
			
			# covers are the only images on the page that have the $imageurl var populated		
			# if $imageurl is not blank, then it must be a cover
			if ($imageurl != "") {
				$imageArray[] = '<p>' . $imageurl . '<br />' . $bookMeta . '<br /><a href="' . $imageSource . '" target="_blank">' . $imageSource . '</a></p>';
			}
			# end var not blank check
			
			$imageCount = $imageCount + 1; // 14
			
			if ($imageTotal == 0) {
				$imageTotal = 1;
			} else {
				$imageTotal = $imageTotal + 1;
			}
		}
		
		$linkCount = 0;
		$linkSource = array();
		foreach($data->find('tr td[align="CENTER"] a') as $hyperlinks) { 
			$linkSource[] = $hyperlinks->href; 
			// echo '<p>href: [' . $linkCount . '] ' . $hyperlinks->href . '</p>';
			$linkCount = $linkCount + 1;
		}
		
# **********************************************************
# BUILD LIST
# **********************************************************
		# now that we know how many cells, we can pull every 8 cells to get the book cover cell
		# Cover Row: Image, Medium-Size Cover, Jumbo-Size Cover, Bibliography, Page 1, Page 2, Page 3 
		# Issue Row: Issue Number, Metadata
		# Price/Inventory Rows: Quality, In-Stock Price (Color Coded), Out-of-Stock Price (In parentheses)
		# Price Color Codes: 
		# - Prior Sale = Blue (#00ffff)
		# - Temp Genre Sale = Green (#00ee00)
		# - Discounted Excess = Red (#ee0000)
		# - Genre Sale = Yellow (#ffff33)
				
		$coverRow = 0;
		$cellShown = "";
		$bookData = "";
		$bookBuild = "";
		$imageShown = 0;
		$serialShown = 0;
		$issueShown = 0;
		
		for ($i = 0; $i < $cellCounter; $i++) {
			switch($coverRow % 8) {
				case ('0'):
					#coverRow
					$cellShown = $data->find('tr td[align="CENTER"]', $coverRow)->innertext;
					$cellBlank = $data->find('tr td[align="CENTER"]', $coverRow)->plaintext;
					
					# TODO: Move EXPLORE to here
					# - Work through each piece of the exploded string and discover what is there and what is not
					# - Should be a better way to find BLANKS
					
					if ($cellBlank != '') {
						if(strpos($cellBlank, 'Medium-Size') !== false) {
							
							// list($theImage) = explode(" ", $cellBlank);
							list($openRef, $coverLink, $imgRef, $imgSource, $widthRef, $imgWidth, $heightRef, $imgHeight, $alignRef, 
							$imgAlign, $jumboRef, $jumboLink, $bibRef, $bibLink, $closeRef) = explode('"', $cellShown);
							// $bookBuild .= '<tr><td class="coverRow">' . $cellShown . '<br />imageArray: ' . $imageArray[$imageShown] . '</td>';
							$bookBuild .= '<td class="coverRow"><p><a href="' . $coverLink . '">
							<img src="' . $imgSource . '" width="' . $imgWidth . '" height="' . $imgHeight . '" align="' . $imgAlign . '"></a></p>
							<p><a href="' . $coverLink . '">Medium-Size Cover</a></p>';
							if ($jumboLink != "") {
								$bookBuild .= '<p><a href="' . $jumboLink . '">Jumbo-Size Cover</a></p>';
							}
							if ($bibLink != "") {
								$bookBuild .= '<p><a href="' . $bibLink . '">Bibliography</a></p>';
							}
							$bookBuild .= '</td>';
							$imageShown = $imageShown + 1;							

						} else {
							// $issueShown = $cellShown[$cellCount];
							$bookBuild .= '<td class="issueRow">' . $cellShown . '</td>';
						}
					} 
					else {
						$bookBuild .= '<tr><td class="coverRow"><span style="text-decoration-color:#ff000;">BLANK!</span></td>';
					}
					$coverRow = $coverRow + 1;
					break;
				case ('1'):
					#issueRow
					$cellShown = $data->find('tr td[align="CENTER"]', $coverRow)->innertext;
					$cellBlank = $data->find('tr td[align="CENTER"]', $coverRow)->plaintext;
					if ($cellShown != '') {
						if (strpos($cellBlank, ' ') !== false) {

							list($theIssue, $theMeta1, $theMeta2, $theMeta3) = explode(" ", $cellBlank);
							
							// $bookBuild .= '<td class="issueRow"><p><b>FOO:</b> ' . $cellShown . '</p></td>';
							
							$bookBuild .= '<td class="issueRow"><p>Issue: <b>#' . $theIssue . '</b></p>';
							if ($theMeta1 != "") {
								$bookBuild .= '<p>Meta1: ' .  $theMeta1 . '</p>';	
							}
							if ($theMeta2 != "") {
								$bookBuild .= '<p>Meta2: ' .  $theMeta2 . '</p>';	
							}
							if ($theMeta3 != "") {
								$bookBuild .= '<p>Meta3: ' .  $theMeta3 . '</p>';	
							}														
							$bookBuild .= '</td>';

						} else {
							// $issueShown = $cellShown[$cellCount];
							$bookBuild .= '<td class="issueRow">' . $cellShown . '</td>';
						}
					} 
					else {
						$bookBuild .= '<td class="issueRow"><span style="text-decoration-color:#ff000;">BLANK!</span></td>';
					}
					$coverRow = $coverRow + 1;
					break;
				case ('2'):
					#fairRow
					$cellShown = $data->find('tr td[align="CENTER"]', $coverRow)->innertext;
					if ($cellShown != '') {
						
						list($fairCon, $fairPrice) = explode('<br/><br/>', $cellShown);
						// $bookBuild .= '<td class="fairRow">' . $cellShown . '</td>';
						$bookBuild .= '<td class="fairRow"><b>' . $fairCon . '</b><br /><i>' . $fairPrice . '</i></td>';
					} 
					else {
						$bookBuild .= '<td class="fairRow"><span style="text-decoration-color:#ff000;">BLANK!</span></td>';
					}
					$coverRow = $coverRow + 1;
					break;				
				case ('3'):
					#goodRow
					$cellShown = $data->find('tr td[align="CENTER"]', $coverRow)->innertext;
					if ($cellShown != '') {
						
						list($goodCon, $goodPrice) = explode('<br/><br/>', $cellShown);
						// $bookBuild .= '<td class="goodRow">' . $cellShown . '</td>';
						$bookBuild .= '<td class="fairRow"><b>' . $goodCon . '</b><br /><i>' . $goodPrice . '</i></td>';
					} 
					else {
						$bookBuild .= '<td class="goodRow"><span style="text-decoration-color:#ff000;">BLANK!</span></td>';
					}
					$coverRow = $coverRow + 1;
					break;
				case ('4'):
					#verygoodRow
					$cellShown = $data->find('tr td[align="CENTER"]', $coverRow)->innertext;
					if ($cellShown != '') {
						
						list($veryGoodCon, $veryGoodPrice) = explode('<br/><br/>', $cellShown);
						// $bookBuild .= '<td class="verygoodRow">' . $cellShown . '</td>';
						$bookBuild .= '<td class="fairRow"><b>' . $veryGoodCon . '</b><br /><i>' . $veryGoodPrice . '</i></td>';
					} 
					else {
						$bookBuild .= '<td class="verygoodRow"><span style="text-decoration-color:#ff000;">BLANK!</span></td>';
					}
					$coverRow = $coverRow + 1;
					break;
				case ('5'):
					#fineRow
					$cellShown = $data->find('tr td[align="CENTER"]', $coverRow)->innertext;
					if ($cellShown != '') {
						
						list($fineCon, $finePrice) = explode('<br/><br/>', $cellShown);
						// $bookBuild .= '<td class="fineRow">' . $cellShown . '</td>';
						$bookBuild .= '<td class="fairRow"><b>' . $fineCon . '</b><br /><i>' . $finePrice . '</i></td>';
					} 
					else {
						$bookBuild .= '<td class="fineRow"><span style="text-decoration-color:#ff000;">BLANK!</span></td>';
					}
					$coverRow = $coverRow + 1;
					break;
				case ('6'):
					#veryfineRow
					$cellShown = $data->find('tr td[align="CENTER"]', $coverRow)->innertext;
					if ($cellShown != '') {
						
						list($veryFineCon, $veryFinePrice) = explode('<br/><br/>', $cellShown);
						// $bookBuild .= '<td class="veryfineRow">' . $cellShown . '</td>';
						$bookBuild .= '<td class="fairRow"><b>' . $veryFineCon . '</b><br /><i>' . $veryFinePrice . '</i></td>';
					} 
					else {
						$bookBuild .= '<td class="veryfineRow"><span style="text-decoration-color:#ff000;">BLANK!</span></td>';
					}
					$coverRow = $coverRow + 1;
					break;				
				case ('7'):
					#nearmintRow
					$cellShown = $data->find('tr td[align="CENTER"]', $coverRow)->innertext;
					if ($cellShown != '') {
						
						list($nearMintCon, $nearMintPrice) = explode('<br/><br/>', $cellShown);
						// $bookBuild .= '<td class="nearmintRow">' . $cellShown . '</td></tr>';
						$bookBuild .= '<td class="fairRow"><b>' . $nearMintCon . '</b><br /><i>' . $nearMintPrice . '</i></td></tr>';
					} 
					else {
						$bookBuild .= '<td class="nearmintRow"><span style="text-decoration-color:#ff000;">BLANK!</span></td></tr>';
					}
					$coverRow = $coverRow + 1;
					break;			
				default:
					#not a row we want
					break;			
			}
		
		}
		# end td cell crawl case

        echo $bookBuild;
		
		# incriment snumber
		$startNumber = $startNumber + 20;
	}
	# end body build
?>

    </tbody>
    <!-- end table body -->
</table>
<!-- end table -->

<?
# stop calculating page load time - show results in seconds
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$totalTime = round(($finish - $start), 4);
?>

<div class="wrapper">
	<h2>Cell Count: <?php echo $cellCount ?> (Number of Cells Shown)</h2>
    <h2>Row Count: <?php echo $rowCount ?> (Number of Rows Shown)</h2>
    <h2>Image Count: <?php echo $imageTotal ?> (Number of Cover Images Shown)</h2>
    <h2>Total Execution Time: <?php echo $totalTime ?> (Seconds)</h2>
</div>

</body>
</html>