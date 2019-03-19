<?php declare(strict_types=1);

namespace PhpPupeeteer\Resources;

/**
 * Class Browser
 * @package PhpPupeeteer\Resources
 *
 * @method BrowserContext[] browserContexts()
 * @method BrowserContext createIncognitoBrowserContext()
 * @method BrowserContext defaultBrowserContext()
 * @method void disconnect()
 * @method void close()
 * @method Page[] pages()
 * @method Buffer|null process()
 * @method Target target()
 * @method Target[] targets()
 * @method string userAgent()
 * @method string version()
 * @method Target waitForTarget(JSHandle $callback, array $options = [])
 * @method string wsEndpoint()
 */
class Browser extends Buffer
{

	protected $userAgent = 'Mozilla/5.0 (Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3582.0 Safari/537.36';

	protected $blockedRequests = [];

	public function getUserAgent(): string
	{
		return $this->userAgent;
	}

	public function setUserAgent(string $userAgent): self
	{
		$this->userAgent = $userAgent;
		return $this;
	}

	public function newPage(): Page
	{
		/** @var Page $page */
		$page = parent::newPage();
		$page->setUserAgent($this->userAgent);

		//$logFunction = Js::createWithParameters(['consoleObj'])
		//	->body( "consoleObj => console.log(consoleObj.text())")
		//;
		//$page->on('console', $logFunction);

		return $page;
	}

}