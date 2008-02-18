<?php

require_once(dirname(__FILE__).'/package.php');

$transform = "simpletest.org.xslt";
$source_path = "../../docs/source/";
$destination_path = "../../docs/simpletest.org/";

$languages = array("en/", "fr/", "../../");

foreach ($languages as $language) {
	$dir = opendir($source_path.$language);
	while (($file = readdir($dir)) !== false) {
		if (is_file($source_path.$language.$file) and preg_match("/\.xml$/", $file)) {
			$source = simplexml_load_file($source_path.$language.$file, "SimpleTestXMLElement");
			$destination = $source->destination("map.xml");
			
			if (!empty($destination)) {
				$page = file_get_contents('template.html');

				$page = str_replace('KEYWORDS', $source->keywords(), $page);
				$page = str_replace('TITLE', $source->title(), $page);
				$page = str_replace('CONTENT', $source->content(), $page);
				$page = str_replace('INTERNAL', $source->internal(), $page);
				$page = str_replace('EXTERNAL', $source->external(), $page);
				$page = preg_replace("/\"([a-z_]*)\.php\"/", "\"\\1.html\"", $page);
				
				$links = $source->links("map.xml");
				foreach ($links as $category => $link) {
					$page = str_replace("LINKS_".strtoupper($category), $link, $page);
				}
				
				$destination_dir = dirname($destination_path.$destination);
				if (!is_dir($destination_dir)) {
					mkdir($destination_dir);
				}

				$ok = file_put_contents($destination_path.$destination, $page);
				touch($destination_path.$destination, filemtime($source_path.$language.$file));

				if ($ok) {
					$result = "OK";
				} else {
					$result = "KO";
				}
				$sections = $source->xpath('//section');
				if (count($sections) > 0) {
					$result .= " <span style='color : green'>avec sections</span>";
				} else {
					$result .= " <span style='color : red'>sans sections</span>";
				}
				
				echo $destination_path.$destination." : ".$result."<br />";
			}
		}
	}
	closedir($dir);
}
?>