<?php

namespace Abouthalf;

use Silex\ServiceProviderInterface;
use Silex\Application;
use Zend_Search_Lucene as Search;
use Zend_Search_Exception as SearchException;
use Zend_Search_Lucene_Search_QueryParser as Parser;
use Zend_Search_Lucene_Document_Html as HtmlDoc;
use Exception;

/**
 * Created by JetBrains PhpStorm.
 * User: device55
 * Date: 2/11/13
 * Time: 6:40 PM
 * To change this template use File | Settings | File Templates.
 */
class SearchProvider implements ServiceProviderInterface
{
	const SEARCH_PROVIDER = 'search';

	/**
	 * Registers services on the given app.
	 *
	 * This method should only be used to configure services and parameters.
	 * It should not get services.
	 *
	 * @param Application $app An Application instance
	 * @throws \Exception
	 * @return \Zend_Search_Lucene_Search_QueryHit[]|null
	 */
	public function register(Application $app)
	{

		$app[self::SEARCH_PROVIDER] = $app->protect(function($query) use ($app) {
			$indexPath = $app['search.index'];
			$contentPath = $app['search.content'];
			if (!$indexPath || !$contentPath)
			{
				throw new Exception(__CLASS__. ' requires a valid search.index path');
			}

			$index = SearchProvider::getIndex($indexPath);
			if ($index)
			{
				return SearchProvider::query($index, $query);
			}
			else
			{
				return null;
			}
		});
	}

	/**
	 * Bootstraps the application.
	 *
	 * This method is called after all services are registers
	 * and should be used for "dynamic" configuration (whenever
	 * a service must be requested).
	 */
	public function boot(Application $app)
	{
	}

	/**
	 * @param $index
	 * @return \Zend_Search_Lucene_Interface|null
	 */
	public static function getIndex($index)
	{
		try
		{
			return Search::open($index);
		}
		catch(SearchException $e)
		{
			error_log($e->getMessage());
			return null;
		}
	}

	/**
	 * Find, format, return results
	 *
	 * @param \Zend_Search_Lucene_Interface $index
	 * @param string $query
	 * @return array
	 */
	public static function query(\Zend_Search_Lucene_Interface $index, $query)
	{
		$q = Parser::parse($query);
		/* @var $hits \Zend_Search_Lucene_Search_QueryHit[] */
		$hits = $index->find($q,'type',SORT_REGULAR,SORT_DESC);
		$results = array();
		foreach($hits as $hit)
		{
			$h = array();
			$h['title'] = $hit->getDocument()->getFieldValue('title');
			$h['url'] = $hit->getDocument()->getFieldValue('url');
			$path = $hit->getDocument()->getFieldValue('path');
			$h['score'] = $hit->score;
			$h['type'] = $hit->getDocument()->getFieldValue('type');
//			$doc = new \DOMDocument();
//			$doc->substituteEntities = true;
//			$doc->strictErrorChecking = false;
//			$doc->loadHTML($q->highlightMatches(file_get_contents($path)));
//			$xml = simplexml_import_dom($doc);
//			$h['highlights'] = $xml->body->asXML();
			$results[] = $h;
		}
		return $results;
	}

	public static function formatResults(array $hits)
	{

	}

}
