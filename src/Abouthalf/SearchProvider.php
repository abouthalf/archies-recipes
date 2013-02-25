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
 * Provider for querying Zend_Search_Lucene index
 */
class SearchProvider implements ServiceProviderInterface
{
	const SEARCH_PROVIDER = 'search';
	const SEARCH_INDEX = 'search.index';

	/**
	 * Register query service
	 *
	 * Load index from config parameters, query, return results
	 *
	 * @param Application $app An Application instance
	 * @throws \Exception
	 * @return \Zend_Search_Lucene_Search_QueryHit[]
	 */
	public function register(Application $app)
	{

		$app[self::SEARCH_PROVIDER] = $app->protect(function($query) use ($app) {
			$indexPath = $app[self::SEARCH_INDEX];
			if (!$indexPath)
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
				return array();
			}
		});
	}

	/**
	 * Bootstraps the application. Required by interface
	 */
	public function boot(Application $app) {}

	/**
	 * @param $path
	 * @return \Zend_Search_Lucene_Interface|null
	 */
	public static function getIndex($path)
	{
		try
		{
			return Search::open($path);
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
			$h['score'] = $hit->score;
			$h['type'] = $hit->getDocument()->getFieldValue('type');
			$results[] = $h;
		}
		return $results;
	}
}
