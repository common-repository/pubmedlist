<?php

/*
Plugin Name: Pubmed
Plugin URI: http://wordpress.org/extend/plugins/pubmedlist/
Description: Given a Pubmed search term this plugin lists the resulting papers.
Author: Elfar Torarinsson
Version: 1.0
License: GPL
Author URI: http://bio-geeks.com
*/ 

/*
USAGE: Use following code with a PHP-Plugin for WordPress:
Example: <?php PubmedList(); ?>
------------------------------------------------------
*/

/*
Copyright 2008 Elfar Torarinsson  (email : elfar [a t ] bio-geeks DOT com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


if (!class_exists("Pubmed")) {
  class Pubmed {
    var $adminOptionsName = "PubmedAdminOptions";
    
    function Pubmed() {
    }
    
    function init() {
      $this->getAdminOptions();
    }
    
    function getAdminOptions() {
      $PubmedAdminOptions = array('header' => '',
				       'searchterm' => '');
      
      $devOptions = get_option($this->adminOptionsName);
      
      if (!empty($devOptions)) {
	foreach ($devOptions as $key => $option)
	  $PubmedAdminOptions[$key] = $option;
      }
      
      update_option($this->adminOptionsName, $PubmedAdminOptions);
      
      return $PubmedAdminOptions;
    }
    
    function PubmedSearch() {
      echo '<link type="text/css" rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/pubmed-list/css/pubmed.css" />' . "\n";
      $devOptions = get_option($this->adminOptionsName);
      $searchterm = $devOptions['searchterm'];
      $header = $devOptions['header'];

      if($searchterm == ""){
	echo "<b>Please specify a search term in the settings</b>\n";
      }else{

	//Add the search term to the URL
	$url = 'http://www.ncbi.nlm.nih.gov/sites/entrez?Db=pubmed&Cmd=search&dispmax=200&Term='.$searchterm;
	
	//Read the search result into the $html variable
	$html = implode('', file($url));
	//Add the ncbi server prefix to the local links
	$html = str_replace('href="/pubmed/','target="_blank" href="http://www.ncbi.nlm.nih.gov/pubmed/',$html);
	$html = str_replace('href="/sites/','target="_blank" href="http://www.ncbi.nlm.nih.gov/sites/',$html);
	//Split the $html into the different papers, the first and last element will have to be corrected
	$papers = preg_split('/<div class="rprtnum".*?<\/div>/',$html);
	
	//Remove the end on the last element so it only holds that last paper
	$tmp = split('<div class="title_and_pager bottom"',end($papers));
	array_pop ($papers);
	array_push($papers,$tmp[0]);
	$first = 0;
	$antal = sizeof($papers) - 1;
	
	if($header == ""){
	  echo "<br /><h3>There are " . $antal . " published papers</h3><br />&nbsp;<br />\n";
	}else{
	  echo "<br /><h3>" . $header ."</h3><br />&nbsp;<br />\n";
	}
	//Add some divs that were missing since you regard the first element
	echo '<div class="pubmed"><div class="rprt">'."\n";
	foreach ($papers as &$p){
	  //Don't print the first element since that is everything before the first paper
	  if($first){
	    echo $p;
	    echo "\n";
	  }else{
	    $first = 1;
	  }
	}
      }
    }
    
    function printAdminPage() {
      $devOptions = $this->getAdminOptions();
      
      if (isset($_POST['update_PubmedSettings'])) {
	if (isset($_POST['pubmedHeader'])) {
	  $devOptions['header'] = $_POST['pubmedHeader'];
	}
	if (isset($_POST['pubmedSearchterm'])) {
	  $devOptions['searchterm'] = $_POST['pubmedSearchterm'];
	}

	update_option($this->adminOptionsName, $devOptions);
	
	?>
		      <div class="updated"><p><strong><?php _e("Settings Updated.", "PubmedList");?></strong></p></div>
			<?php
			}  ?>
		    <div class=wrap>
		       <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
<h2><?php _e("Pubmed Settings","PubmedList"); ?></h2>
<h3><?php _e("Search term for you pubmed query","PubmedList"); ?></h3>
<p><?php _e("Here you can put whatever you would search pubmed for, f.ex. the following will search all publications by four different authors since year 1998: (Lindow+M[Author]+OR+Torarinsson+E[Author]+OR+Lindgreen+S[Author]+OR+Marstrand+T[Author])+AND+(1998%2F01%2F01[PDAT]+%3A+3000[PDAT])","PubmedList"); ?>
<p><label for="pubmedSearchterm"><input type="text" id="pubmedSearchterm" name="pubmedSearchterm" size="90" value="<?php if ($devOptions['searchterm'] != "") { echo $devOptions['searchterm']; } ?>"></label></p>
<br />
<h3><?php _e("Header for your output","PubmedList"); ?></h3>
<p><?php _e("Customize the header for your publication listing. The default is: There are XX published papers.","PubmedList"); ?></p>
<p>
<p><label for="pubmedHeader"><input type="text" id="pubmedHeader" name="pubmedHeader" size="50" value="<?php if ($devOptions['header'] != "") { echo $devOptions['header']; } ?>"></label></p>
</p>

<div class="submit">
<input type="submit" name="update_PubmedSettings" value="<?php _e('Update Settings', 'PubmedList'); ?>" /></div>
</form>
 </div>
		<?php
				      }//End function printAdminPage()
    
  }
 }

// End of plugin class code

// Prepare plugin start
if (class_exists("Pubmed")) {
  $o_Pubmed = new Pubmed();
 }

//Initialize the admin panel

if (!function_exists("PubmedList")) {
  function PubmedList() {
    global $o_Pubmed;
    if (!isset($o_Pubmed)) {
      return;
    }
    $o_Pubmed->PubmedSearch();
  }
 }

if (!function_exists("Pubmed_ap")) {
  function Pubmed_ap() {
    global $o_Pubmed;
    if (!isset($o_Pubmed)) {
      return;
    }
    if (function_exists('add_options_page')) {
      add_options_page('Pubmed List Plugin', 'PubmedList', 9, basename(__FILE__), array(&$o_Pubmed, 'printAdminPage'));
    }
  }
}

if (isset($o_Pubmed)) {
  //Actions
  add_action('admin_menu', 'Pubmed_ap');
  add_action('activate_pubmed/pubmed-list.php',  array(&$o_Pubmed, 'init'));
}


?>