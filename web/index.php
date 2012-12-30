<?php

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Silex\Provider\TwigServiceProvider;
use Silex\Application;

$app = new Silex\Application();

$app['debug'] = ($_SERVER['APPLICATION_ENV'] === 'development') ? true : false;

// globals and defaults
$app['siteName'] = 'Archie’s Recipe Book';
$app['homePage'] = 'index.html';
$app['defaultKeywords'] = "Great grandfather's recipe book archive";
$app['postsPerPage'] = 10;
$app['timezone'] = 'America/Los_Angeles';
$app['baseUrl'] = 'http://'.$_SERVER['SERVER_NAME'];

/**
 * Register providers
 */

/**
 * Twig template provider
 */
$app->register(new TwigServiceProvider(), array(
		'twig.path' => __DIR__ . '/../views',
		'twig.options' => array(
			'cache' => __DIR__ . '/../views/cache',
			'strict_variables' => false,
			'charset' => 'UTF-8'
			//'auto_reload' => true
		)
	));


// set timezone
date_default_timezone_set($app['timezone']);


/**
 * Callbacks and helper functions
 */

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
	$h = @fopen($f,'rb');
	if ($h === false) {
		throw new Exception('content file not found');
	}
	$txt = fread($h, filesize($f));
	fclose($h);
	$xml = simplexml_load_string($txt);
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
	$app['current'] = $pageName;
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
				if (file_exists(__DIR__.'/../html/'.$p->name))
				{
					$prev = $p->name;
				}
			}
			if ($n = $pages[$i+1])
			{
				if (file_exists(__DIR__.'/../html/'.$n->name))
				{
					$next = $n->name;
				}
			}
			$app['image'] = $image;
			$app['next'] = $next;
			$app['prev'] = $prev;
			return;
		}
	}
	$app['image'] = '';
	$app['next'] = '';
	$app['prev'] = '';
}


/**
 * Attempt to extract data from meta tags, deposit into an array
 *
 * @param SimpleXMLElement $xml object representation of page
 * @return array
 */
function getMetaData(SimpleXMLElement $xml = null)
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
		return array();
	}
}

/**
 * Construct a blog entry permalink from the file name
 *
 * Expects a blog entry to be named in the following format:
 * yyyy-mm-dd name-of-post.html
 * or
 * yyyy-mm-ddThh-mm-ss name-of-post.html
 *
 * @param string $name
 * @return string
 */
function buildBlogUrlFromFileName($name)
{
	preg_match('/^(\d\d\d\d)-(\d\d)-(\d\d).* (.*\.html)$/',$name,$matches);
	return sprintf('/blog/%s/%s/%s/%s',$matches[1],$matches[2],$matches[3],$matches[4]);
}


/**
 * Return RSS formatted date constructed from timestamp in blog file name
 *
 * @param string $name
 * @param Application $app
 * @return string formatted date string
 */
function buildPubDateFromFileName($name,$app)
{
	$datePattern = '/^(\d\d\d\d)-(\d\d)-(\d\d) (.*)\.html$/';
	$dateTimePattern = '/^(\d\d\d\d)-(\d\d)-(\d\d)T(\d\d)-(\d\d)-(\d\d) (.*).html$/';

	$tz = new DateTimeZone($app['timezone']);
	$gmt = new DateTimeZone('GMT');
	$stamp = '%s-%s-%s %s:%s:%s';
	$year = date('Y');
	$month = date('m');
	$day = date('d');
	$hour = '12'; // noon is a good default hour
	$minute = '00';
	$second = '00';
	if (preg_match($datePattern,$name,$matches))
	{
		$year = $matches[1];
		$month = $matches[2];
		$day = $matches[3];
	}
	elseif(preg_match($dateTimePattern,$page,$matches))
	{
		$year = $matches[1];
		$month = $matches[2];
		$day = $matches[3];
		$hour = $matches[4];
		$minute = $matches[5];
		$second = $matches[6];
	}

	$date = new DateTime(
		sprintf($stamp,$year,$month,$day,$hour,$minute,$second),
		$tz
	);

	// set to GMT timezone
	$date->setTimezone($gmt);

	return $date->format(DATE_RSS);
}

/**
 * Controllers
 *
 */


//<editor-fold desc="Blog controllers">
/**
 * Blog controller
 */
$app->get('/blog/{year}/{month}/{day}/{id}', function(Application $app, Request $request, $year, $month, $day, $id)
{
	/** @var $twig Twig_Environment */
	$twig = $app['twig'];
	// what page are we looking at?

	// get all blog files
	$path = __DIR__.'/../html/blog';
	$files = scandir($path,1);

	$action = 'home'; //default! show a list of 10 last posts
	$pattern = $allHtmlFiles = '/^.*\.html$/'; // all html files
	$date = '';
	$breadcrumb = array();
	if ($id) {
		$action = 'post';
		$pattern = sprintf('/^(%s-%s-%s).*(%s)$/',$year,$month,$day,$id); // exact html file
		$date = sprintf('%s/%s/%s',$year,$month,$day); // archive date
	} else if ($day !== '00') {
		$action = 'day';
		$pattern = sprintf('/^(%s-%s-%s).*\.html/',$year,$month,$day); // all html files for a given day
		$breadcrumb = array($year,$month,$day);
		$date = sprintf('%s/%s/%s',$year,$month,$day); // archive date
	} else if ($month !== '00') {
		$action = 'month';
		$pattern = sprintf('/^(%s-%s).*\.html/',$year,$month); // all html files for a given month/year
		$date = sprintf('%s/%s',$year,$month);
		$breadcrumb = array($year,$month);
	} else if ($year !== '0000') {
		$action = 'year';
		$pattern = sprintf('/^(%s).*\.html/',$year); // all html files for a given year
		$date = sprintf('%s',$year);
		$breadcrumb = array($year);
	}

	$matches = array_values(array_filter($files,function($f) use($pattern) {
			return preg_match($pattern,$f);
		}));

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
	if (count($matches) > 0 && $action != 'post')
	{
		// paginate - pages are 1-based
		$page = intval($request->get('page',1));
		$next = $prev = null;
		$pages = array_chunk($matches,$app['postsPerPage']);
		$totalPages = count($pages);
		if ($page > $totalPages) {$page = $totalPages;}
		if ($page < $totalPages) {$next = $page + 1;}
		if ($page > 0) {$prev = $page - 1;}
		if ($page < 0) {$page = 1;}

		$postsPerPage = $pages[$page - 1];
		$posts = array();
		foreach($postsPerPage as $post)
		{
			$xml = getPageContent('blog/'.$post);
			$xml->addChild('permalink',buildBlogUrlFromFileName($post)); // hacky! slap a permalink into the xml object
			$posts[] = $xml;
		}

		$out = array(
			'meta' => array(
				array('name'=>'keywords','content'=>$app['defaultKeywords'])
			),
			'action' => $action,
			'date' => $date,
			'posts' => $posts,
			'breadcrumb' => $breadcrumb,
			'next' => $next,
			'prev' => $prev
		);
		return $twig->render('archive.twig',$out);
	}

	// if all else fails, 404
	return $twig->render('404.twig',array());
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
//</editor-fold>


//<editor-fold desc="RSS Controller">
/**
 * RSS controller.
 * Get recent blog posts and format as RSS
 */
$app->get('/rss', function(Application $app, Request $request)
{
	// feed publication date is now
	$gmt = new DateTimeZone('GMT');
	$now = new DateTime('now',$gmt);
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->setIndent(true);
	$xml->setIndentString("\t");
	$xml->startDocument('1.0', 'UTF-8');

	$xml->startElement('rss');
	$xml->writeAttribute('version', '2.0');
	$xml->writeAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
	$xml->writeAttribute('xmlns:content','http://purl.org/rss/1.0/modules/content/');

	$xml->startElement("channel");
	$xml->writeElement('title', $app['siteName']);

	$xml->startElementNs('atom','link',null);
	$xml->writeAttribute('href',$app['baseUrl'].'/rss');
	$xml->writeAttribute('rel','self');
	$xml->endElement(); // atom:link

	$xml->writeElement('description', $app['defaultKeywords']);
	$xml->writeElement('link','http://'.$_SERVER['SERVER_NAME']);
	$xml->writeElement('pubDate', $now->format(DATE_RSS));

	// get files
	$path = __DIR__.'/../html/blog';
	$files = scandir($path,1);

	// ignore dot-files and not-html-files
	$files = array_values(array_filter($files,function($f) {
			return preg_match('/^.*\.html$/',$f);
		}));

	// build items
	for($i = 0; $i < 10; $i++)
	{
		$file = $files[0];
		$post = getPageContent('blog/'.$file);
		$body = $post->body->asXML();
		$body = str_replace('<body>','',$body);
		$body = str_replace('</body>','',$body);
		$description = join(' ',explode(' ',strip_tags($body),50));

		$url = 'http://'.$_SERVER['SERVER_NAME'] . buildBlogUrlFromFileName($file);
		$xml->startElement('item');
		$xml->writeElement('title',$post->head->title);
		$xml->writeElement('link',$url);
		$xml->startElement('description');
			$xml->writeCdata($description);
		$xml->endElement();//description
		$xml->writeElement('pubDate',buildPubDateFromFileName($file,$app));
		$xml->startElementNs('content','encoded',null);
			$xml->writeCdata($body);
		$xml->endElement();//content:encoded

		$xml->endElement();// item
	}

	$xml->endElement(); //channel
	$xml->endElement(); //rss

	return new Response($xml->outputMemory(),200,array('Content-Type'=>'text/xml'));
});

/**
 * redirect trailing slash
 */
$app->get('/rss/',function(Application $app, Request $req) {
	return $app->redirect('/rss');
});

//</editor-fold>

/**
 * main page controller, look up and load pageName from content directory
 *
 */
$app->get('/{page}', function(Application $app, Request $request, $page)
{
	// template output
	/* @var $page SimpleXMLElement */
	$out = array(
		'next' => $app['next'],
		'prev' => $app['prev'],
		'current' => $app['current'],
		'image' => $app['image'],
		'meta' => getMetaData($page),
		'permalink' => $request->getUri(),
		'page' => $page,
		'title' => $page->head->title,
		'body' => $page->body->asXML(),
		'isHome' => ($app['current'] == $app['homePage'])
	);

	$request->getBaseUrl();
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
