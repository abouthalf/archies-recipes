<?php

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Silex\Provider\TwigServiceProvider;
use Silex\Application;

date_default_timezone_set('America/Los_Angeles');

$app = new Silex\Application();

$app['debug'] = ($_SERVER['APPLICATION_ENV'] === 'development') ? true : false;

$app->register(new TwigServiceProvider(), array(
		'twig.path' => __DIR__ . '/../views',
		'twig.options' => array(
			'cache' => __DIR__ . '/../views/cache'
		)
	));

// globals and defaults
$app['siteName'] = 'Archie’s Recipe Book';
$app['homePage'] = 'index.html';
$app['defaultKeywords'] = "Great grandfather's recipe book archive";

/**
 * Page Converter callback - converts page name into SimpleXML object
 *
 * @param $page string
 * @throws Exception
 * @return SimpleXMLElement;
 */
$loadPageXML = function($page) use ($app)
{
	// get page context
	getMedia($page, $app);
	// get page content
	return getPageContent($page);
};

/**
 * @param string $page
 * @return SimpleXMLElement
 * @throws Exception
 */
function getPageContent($page)
{
	$f = __DIR__. '/../html/'.$page;
	$h = fopen($f,'r');
	if ($h === false) {
		throw new Exception('content file not found');
	}
	$txt = fread($h, filesize($f));
	fclose($h);

	// convert to object
	$dom = new DOMDocument();
	$dom->strictErrorChecking = false;
	@$dom->loadHTML($txt); // suppress errors
	$xml = simplexml_import_dom($dom);
	return $xml;
}

/**
 * Query media.xml for the page, determine next and previous files, determine image, tuck into $app
 *
 * @param string $pageName
 * @param Application $app
 */
function getMedia($pageName, $app)
{
	$xml = simplexml_load_file(__DIR__.'/../html/media.xml');
	/* @var $pages SimpleXMLElement */
	$pages = $xml->page;
	$len = $pages->count();
	for($i = 0; $i < $len; $i++)
	{
		$page = $pages[$i];
		if ($page->name == $pageName)
		{
			$image = $page->image;
			$next = '';
			$prev = '';
			if ($p = $pages[$i-1])
			{
				$prev = $p->name;
			}
			if ($n = $pages[$i+1])
			{
				$next = $n->name;
			}
			$app['image'] = $image;
			$app['next'] = $next;
			$app['prev'] = $prev;
			$app['current'] = $pageName;
			break;
		}

	}
}


/**
 * Attempt to extract data from meta tags, deposit into an array
 *
 * @param SimpleXMLElement $xml object representation of page
 * @return array
 */
function getMetaData(SimpleXMLElement $xml)
{
	try
	{
		$out = array();
		$metas = $xml->head->meta;
		foreach($metas as $meta)
		{
			$out[] = array('name' => $meta['name'],'content'=> $meta['content']);
		}
		return $out;
	}
	catch(Exception $e)
	{
		print_r($e);
		return array();
	}
}

/**
 * Blog controller
 */
$app->get('/blog/{year}/{month}/{day}/{id}', function(Application $app, Request $request, $year, $month, $day, $id)
{
	/** @var $twig Twig_Environment */
	$twig = $app['twig'];
	// what page are we looking at?
	$page = intval($request->get('page',1)) - 1;
	// get all blog files
	$path = __DIR__.'/../html/blog';
	$files = scandir($path,1);

	$action = 'home'; //default! show a list of 10 last posts
	$pattern = $allHtmlFiles = '/^.*\.html$/'; // all html files
	$date = sprintf('%s/%s/%s',$year,$month,$day); // archive date
	if ($id) {
		$action = 'post';
		$pattern = sprintf('/^(%s-%s-%s).*(%s)$/',$year,$month,$day,$id); // exact html file
	} else if ($day !== '00') {
		$action = 'day';
		$pattern = sprintf('/^(%s-%s-%s).*\.html/',$year,$month,$day); // all html files for a given day
	} else if ($month !== '00') {
		$action = 'month';
		$pattern = sprintf('/^(%s-%s).*\.html/',$year,$month); // all html files for a given month/year
		$date = sprintf('%s/%s',$year,$month);
	} else if ($year !== '0000') {
		$action = 'year';
		$pattern = sprintf('/^(%s).*\.html/',$year); // all html files for a given year
		$date = sprintf('%s',$year);
	}

	$matches = array_filter($files,function($f) use($pattern) {
			return preg_match($pattern,$f);
		});

	// you gots no matches, son. 404
	if (count($matches) === 0)
	{
		return $twig->render('404.twig',array());
	}

	// single post
	if (count($matches) === 1 && $action == 'post')
	{
		$post = getPageContent('blog/'.$matches[0]);
		$out = array(
			'year' => $year,
			'month' => $month,
			'day' => $day,
			'post' => $post,
			'meta' => getMetaData($post),
			'permalink' => $request->getUri()
		);
		return $twig->render('post.twig',$out);
	}

	// archive page
	if (count($matches) > 1 && $action != 'post')
	{
		$title = 'Archives for '.$date;
		// split into pages if needed
		$pages = array_chunk($matches,9);
		$totalPages = count($pages);
		$postsPerPage = $pages[$page];
		$posts = array();
		foreach($postsPerPage as $post)
		{
			$posts[] = getPageContent('blog/'.$post);
		}
		$next = $prev = null;
		$out = array(
			'meta' => array(
				array('name'=>'description','content'=>$title),
				array('name'=>'keywords','content'=>$app['defaultKeywords'])
			),
			'action' => $action,
			'posts' => $posts,
			'title' => $title
		);
		return $twig->render('archive.twig',$out);
	}





	$out = array('posts' => $posts, 'keywords'=>'foo');

	switch($action)
	{
		case 'post':
			break;
		case 'day':
		case 'month':
		case 'year':
			$out['title'] = 'archive';
			return $twig->render('archive.twig',$out);
			break;
		default:
			$matches = array_slice($files,0,9);
			break;
	}

})
->value('year','0000')->value('month','00')->value('day','00')->value('id','')
->assert('year','\d\d\d\d')->assert('month','\d\d')->assert('day','\d\d');

/**
 * Route trailing slash 'directory' routes to the blog controller with correct parameters
 */

/**
 * day archives
 */
$app->get('/blog/{year}/{month}/{day}/',function(Application $app, Request $request, $year, $month, $day)
{
	$path = sprintf('/blog/%s/%s/%s',$year, $month, $day);
	$subRequest = Request::create($path, 'GET');
	return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
});

/**
 * month archives
 */
$app->get('/blog/{year}/{month}/',function(Application $app, Request $request, $year, $month)
{
	$path = sprintf('/blog/%s/%s',$year, $month);
	$subRequest = Request::create($path, 'GET');
	return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
});

/**
 * year archives
 */
$app->get('/blog/{year}/',function(Application $app, Request $request, $year)
{
	$path = sprintf('/blog/%s',$year);
	$subRequest = Request::create($path, 'GET');
	return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
});

/**
 * List current blog entries
 */
$app->get('/blog/', function(Application $app, Request $request)
{
	$path = '/blog';
	$subRequest = Request::create($path, 'GET');
	return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
});

/**
 * RSS controller.
 * Get 10 most recent blog posts and format as RSS
 */
$app->get('/rss', function(Application $app, Request $request)
{

});

/**
 * main page controller, look up and load pageName from content directory
 *
 */
$app->get('/{page}', function(Application $app, Request $request, $page)
{
	// template output
	$out = array();
	/* @var $page SimpleXMLElement */
	$out = array(
		'next' => $app['next'],
		'prev' => $app['prev'],
		'current' => $app['current'],
		'image' => $app['image'],
		'meta' => getMetaData($page),
		'permalink' => $request->getUri(),
		'page' => $page,
		'isHome' => ($app['current'] == $app['homePage'])
	);

	/** @var $twig Twig_Environment */
	$twig = $app['twig'];
	return $twig->render('page.twig',$out);
})
->value('page',$app['homePage'])
->assert('page','^(?!blog)[\w/]+\.html$')
->convert('page',$loadPageXML);


/**
 * error controller
 */
$app->error(function (\Exception $e, $code) use ($app)
{
	// in development environment dump stack trace
	if ($app['debug']) {return;}

	// fetch 404
	/** @var $twig Twig_Environment */
	$twig = $app['twig'];
	return $twig->render('404.twig',array());
});

$app->run();
