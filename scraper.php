<?php

// includes Simple HTML DOM Parser
include 'simple_html_dom.php';

// includes generic functions
include '_functions_scraper.php';

// D7 Base url
$base_url_d7 = "https://d7-url.com";

// D8 Base url
$base_url_d8 = "http://d8-url.com";

// Get all the url that needs to need to scraped
$relative_url = _return_url_relative_path();

// Input to the scraper to scrape only certain tags.
$_scrape_certain_tags = _return_scrape_certain_tags();

// Displaying messaging
print_r('The total url to be processed is : ' . count($relative_url));
echo "\n";

// Loop through the relative paths
foreach ($relative_url as $key => $url_path) {
  $d7_get_path = $base_url_d7 . $url_path;
  $d8_get_path = $base_url_d8 . $url_path;
  
  //Create a DOM object & load the html for d7
  $d7_get_xpath = _get_html_dom_object($d7_get_path);
  
  //Create a DOM object & load the html for d8
  $d8_get_xpath = _get_html_dom_object($d8_get_path);
  // check whether both url is has html
  if (!isset($d7_get_xpath) || !isset($d8_get_xpath)) {
    $final_result[$key] = array(
      'address' => $url_path,
      'status' => 'Url is not processed!'
    );
  }
  else {
    $status_message = _get_scraper_for_tags($_scrape_certain_tags, $d7_get_xpath, $d8_get_xpath);
    if (!empty($status_message)) {
      $final_result[$key] = array(
        'address' => $url_path,
        'status' => $status_message
      );
    }
  }
  
  print_r('Processed items : ' . $key);
  echo "\n";
}

// Import the scraper message into csv file
_import_scraper_data_into_csv($final_result);

print_r('Scraper for the url is processed. Please check the scraperresult.csv for the details.');
echo "\n";
