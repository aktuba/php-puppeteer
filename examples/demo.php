<?php declare(strict_types=1);

require __DIR__.'/../../../vendor/autoload.php';

$phpPuppeteer = new \PhpPupeeteer\PhpPupeeteer;
try {
	$browser = $phpPuppeteer->getBrowser();
	$page = $browser->newPage()
		->disableRequestsByTypes([
			'image',
			'media',
			'font',
			'texttrack',
			'object',
			'beacon',
			'csp_report',
			'imageset',
		])
		->disableRequestsByDomains([
			'google-analytics.com',
			'googletagservices.com',
			'doubleclick.net',
			'google.com',
			'google.ru',
			'yandex.ru',
			'facebook.net',
			'facebook.com',
			'twitter.com',
			'tns-counter.ru',
			'vk.com',
			'yadro.ru',
		])
	;
	$page->gotoWithWait("https://yandex.ru/");
	$page->screenshot(['path' => 'yandex.png']);
} catch (\Throwable $throwable) {
	echo '[!] ERROR: '.$throwable->getMessage().' in '.$throwable->getFile().':'.$throwable->getLine().PHP_EOL;
}
