<?php

/**
 * Custom function that returns url relative path for scraping tags.
 */
function _return_url_relative_path() {
  $row = 0;
  $url_relative_path = array();
  if (($handle = fopen("url_relative_path.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
      $url_relative_path[$row] = $data[0];
      $row++;
    }
    fclose($handle);
  }
  return $url_relative_path;
}

/**
 * Custom function that returns flag to the scraper to scrape certain tags.
 */
function _return_scrape_certain_tags() {
  return array (
    'heading' => array(
      'flag' => 1,
      'list' => array(
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
      ),
    ),
    'meta_og' => array(
      'flag' => 0,
      'list' => array(
        'og:title'
      ),
    ),
  );
}

/**
 * Custom function return HTML Response
 */
function _get_html_response($path) {
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => $path,
    CURLOPT_USERAGENT => 'Scarper Html',
    CURLOPT_TIMEOUT => 600,
  ));
  
  // Send the request & save response to $resp
  $response = curl_exec($curl);
  
  // Error handling on Curl
  if ($response === FALSE) {
    $response_data = array();
  }
  
  // Close request to clear up some resources
  curl_close($curl);
  
  return $response;
}

/**
 * Custom function get DOM Object
 */
function _get_html_dom_object($path) {
  $get_html = _get_html_response($path);
  $get_doc = new \DOMDocument();
  @$get_doc->loadHTML($get_html);
  $get_doc->saveHTML();
  $xpath = new \DOMXpath($get_doc);
  return $xpath;
}

/**
 * Custom function to get scrape
 */

function _get_scraper_for_tags($_scrape_certain_tags, $d7_get_xpath, $d8_get_xpath) {
  $status_array = [];
  $status = '';
  foreach ($_scrape_certain_tags  as $certain_key => $certain_tag) {
    switch ($certain_key) {
      case 'heading':
        if ($certain_tag['flag']) {
          foreach ($certain_tag['list'] as $tags) {
            $d7array = $d8array = [];
            $d7_get_result = $d7_get_xpath->query('//' . $tags);
            foreach ($d7_get_result as $t_v) {
              $d7array[] = $t_v->nodeValue;
            }
          
            $d8_get_result = $d8_get_xpath->query('//' . $tags);
            foreach ($d8_get_result as $t_v) {
              $d8array[] = $t_v->nodeValue;
            }
            $results_diff = array_diff($d7array, $d8array);
            if (!empty($results_diff)) {
              $status_array[$tags] = $results_diff;
            }
          }
        }
        break;
      case 'meta_og':
        if ($certain_tag['flag']) {
          foreach ($certain_tag['list'] as $tags) {
            $d7_get_result[] = $d7_get_xpath->query('//meta[@property="' . $tags . '"]/@content');
            $d8_get_result[] = $d8_get_xpath->query('//' . $tags);
            $results_diff = array_diff($d7_get_result, $d8_get_result);
            if (!empty($results_diff)) {
              $status_array[$tags] = $results_diff;
            }
          }
        }
        break;
    }
  }
  if (!empty($status_array)) {
    foreach ($status_array as $key => $value) {
      $status = $status . ' --- ' . $key . ': '  . implode(' /// ', $value);
    }
  }
  
  return $status;
}

/**
 * Custom function to import scraper data
 */
function _import_scraper_data_into_csv($final_result) {
  // Open the file "scraperresult.csv" for writing
  $file = fopen('scraperresult.csv', 'w');
  
  // save the column headers
  fputcsv($file, array('Address', 'Scraper message'));
  foreach ($final_result as $key => $row) {
    fputcsv($file, $row);
  }
  
  // Close the file
  fclose($file);
}