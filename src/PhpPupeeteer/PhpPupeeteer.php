<?php declare(strict_types=1);

namespace PhpPupeeteer;

use Psr\Log\LoggerInterface;
use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\AbstractEntryPoint;
use Symfony\Component\Process\Process;
use vierbergenlars\SemVer\{version, expression, SemVerException};
use PhpPupeeteer\Resources\{Browser, BrowserFetcher, Page};

/**
 * Class PhpPupeeteer
 *
 * @method Browser launch(array $options = [])
 * @method BrowserFetcher createBrowserFetcher(array $options = [])
 * @method array defaultArgs(array $options = [])
 * @method string executablePath()
 */
class PhpPupeeteer extends Puppeteer
{

	protected $options = [
		'read_timeout' => 30,

		// Logs the output of Browser's console methods (console.log, console.debug, etc...) to the PHP logger
		'log_browser_console' => false,
	];

	protected $viewport = [
		'width' => 1920,
		'height' => 1080,
		'deviceScaleFactor' => 1,
		'isMobile' => false,
		'hasTouch' => false,
		'isLandscape' => false,
	];

	protected $ignoreHTTPSErrors = false;

	protected $headless = true;

	protected $timeout = 30000;

	protected $dumpio = false;

	protected $browsers = [];

	public function __construct(array $options = [])
	{
		$userOptions = $this->prepareOptions($options);

		if (array_key_exists('logger', $userOptions) && $userOptions['logger'] instanceof LoggerInterface)
		{
			$this->checkPuppeteerVersion($userOptions['executable_path'] ?? 'node', $userOptions['logger']);
		}

		$reflector = new \ReflectionClass(parent::class);
		$dir = dirname($reflector->getFileName());
		AbstractEntryPoint::__construct(
			$dir.'/PuppeteerConnectionDelegate.js',
			new ProcessDelegate,
			$this->options,
			$userOptions
		);
	}

	public function getBrowser(array $options = []): Browser
	{
		$options = array_replace_recursive([
			'ignoreHTTPSErrors' => $this->isIgnoreHTTPSErrors(),
			'headless' => $this->isHeadless(),
			'defaultViewport' => $this->getViewport(),
			'timeout' => $this->getTimeout(),
			'idle_timeout' => 0,
			'read_timeout' => 240,
		], $options);

		$key = md5(json_encode($options));
		if (!array_key_exists($key, $this->browsers)) {
			$this->browsers[$key] = $this->launch($options);
		}
		return $this->browsers[$key];
	}

	public function isIgnoreHTTPSErrors(): bool
	{
		return $this->ignoreHTTPSErrors;
	}

	public function setIgnoreHTTPSErrors(bool $ignore): PhpPupeeteer
	{
		$this->ignoreHTTPSErrors = $ignore;
		return $this;
	}

	public function isHeadless(): bool
	{
		return $this->headless;
	}

	public function setHeadless(bool $headless): PhpPupeeteer
	{
		$this->headless = $headless;
		return $this;
	}

	public function getTimeout(): int
	{
		return $this->timeout;
	}

	public function setTimeout(int $timeoutInMs): PhpPupeeteer
	{
		$this->timeout = $timeoutInMs;
		return $this;
	}

	public function isDumpio(): bool
	{
		return $this->dumpio;
	}

	public function setDumpio(bool $dumpio): PhpPupeeteer
	{
		$this->dumpio = $dumpio;
		return $this;
	}

	public function getViewport(): array
	{
		return $this->viewport;
	}

	public function setViewport(
		int $width,
		int $height,
		int $deviceScaleFactor = 1,
		bool $isMobile = false,
		bool $hasTouch = false,
		bool $isLandscape =  false
	): PhpPupeeteer
	{
		$this->viewport = [
			'width' => $width,
			'height' => $height,
			'deviceScaleFactor' => $deviceScaleFactor,
			'isMobile' => $isMobile,
			'hasTouch' => $hasTouch,
			'isLandscape' => $isLandscape,
		];
		return $this;
	}

	protected function prepareOptions(array $options)
	{
		$result = array_replace_recursive([
			'args' => [
				'--headless' => false,
				'--proxy-server' => null,
				'--window-size' => '1920x1080',
				'--no-sandbox' => true,
				'--disable-setuid-sandbox' => true,
				'--disable-dev-shm-usage' => true,
				'--disable-accelerated-2d-canvas' => true,
				'--disable-gpu' => true,
				'--allow-file-access-from-files' => true,
				'--start-maximized' => true,
				'--0' => true, // disable timing information for chrome://profiler
			],
		], $options);

		if (array_key_exists('args', $result))
		{
			if (
				!array_key_exists('--proxy-server', $result['args']) ||
				!is_string($result['args']['--proxy-server']) ||
				mb_strlen($result['args']['--proxy-server'], 'UTF-8') <= 0
			) {
				unset($result['args']['--proxy-server']);
			}
		}

		return $result;
	}

	private function checkPuppeteerVersion(string $nodePath, LoggerInterface $logger): void {
		$currentVersion = $this->currentPuppeteerVersion($nodePath);
		$acceptedVersions = $this->acceptedPuppeteerVersion();

		try {
			$semver = new version($currentVersion);
			$expression = new expression($acceptedVersions);

			if (!$semver->satisfies($expression)) {
				$logger->warning(
					"The installed version of Puppeteer (v$currentVersion) doesn't match the requirements"
					." ($acceptedVersions), you may encounter issues."
				);
			}
		} catch (SemVerException $exception) {
			$logger->warning("Puppeteer doesn't seem to be installed.");
		}
	}

	private function currentPuppeteerVersion(string $nodePath): ?string {
		$process = new Process([$nodePath, __DIR__.'/get-puppeteer-version.js']);
		$process->mustRun();

		return json_decode($process->getOutput());
	}

	private function acceptedPuppeteerVersion(): string {
		$npmManifestPath = __DIR__.'/../package.json';
		$npmManifest = json_decode(file_get_contents($npmManifestPath));

		return $npmManifest->dependencies->puppeteer;
	}

}
