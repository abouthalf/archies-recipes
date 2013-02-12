<?php

require_once __DIR__.'/../vendor/autoload.php';

define('SEARCH_INDEX',__DIR__.'/../data/search_index');
define('HTML_PAGES',__DIR__.'/../html');
define('HTML_BLOGS',__DIR__.'/../html/blog');

use Zend_Search_Lucene as Lucene;
use Zend_Search_Lucene_Document_Html as HtmlDoc;
use Zend_Search_Lucene_Field as Field;

// remove old index
$d = opendir(SEARCH_INDEX);
if ($d)
{
	while($d && ($file = readdir($d)) !== false)
	{
		if (strpos($file,'.') !== 0)
		{
			unlink(SEARCH_INDEX.'/'.$file);
		}

	}
	closedir($d);
	rmdir(SEARCH_INDEX);
}

// create the index
$idx = Lucene::create(SEARCH_INDEX);

// add static HTML
$d = opendir(HTML_PAGES);
while($d && ($file = readdir($d)) !== false)
{
	if (!is_dir($file) && (strpos($file,'.') !== 0) && strpos($file,'.html'))
	{
		echo 'Indexing: '.$file.PHP_EOL;
		$path = '/'.$file;
		$doc = HtmlDoc::loadHTMLFile(HTML_PAGES.$path);
		$doc->addField(Field::unIndexed('url',$path));
		$doc->addField(Field::unIndexed('path',HTML_PAGES.$path));
		$doc->addField(Field::unIndexed('type','page'));
		$idx->addDocument($doc);
	}
}
closedir($d);

$d = opendir(HTML_BLOGS);
while($d && ($file = readdir($d)) !== false)
{
	if (!is_dir($file) && (strpos($file,'.') !== 0))
	{
		echo 'Indexing: '.$file.PHP_EOL;
		$path = '/'.$file;
		preg_match('/^(\d\d\d\d)-(\d\d)-(\d\d).* (.*\.html)$/',$file,$matches);
		$url = sprintf('/blog/%s/%s/%s/%s',$matches[1],$matches[2],$matches[3],$matches[4]);
		$doc = HtmlDoc::loadHTMLFile(HTML_BLOGS.$path);
		$doc->addField(Field::unIndexed('url',$url));
		$doc->addField(Field::unIndexed('path',HTML_BLOGS.$path));
		$doc->addField(Field::unIndexed('type','blog'));
		$idx->addDocument($doc);
	}
}
closedir($d);