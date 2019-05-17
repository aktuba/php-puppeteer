<?php declare(strict_types=1);

namespace PhpPupeeteer\Resources;

use PhpPupeeteer\Data\Js;
use PhpPupeeteer\Traits\{
	AliasesSelectionMethods,
	AliasesEvaluationMethods,
	Throttler
};
use Nesk\Rialto\Exceptions\Node;
use PhpPupeeteer\Exception\InternalError;

/**
 * Class Page
 * @package PhpPupeeteer\Resources
 *
 * @property-read Accessibility accessibility
 * @property-read Coverage coverage
 * @property-read Keyboard keyboard
 * @property-read Mouse mouse
 * @property-read TouchScreen touchscreen
 * @property-read Tracing tracing
 * @property-read Page tryCatch
 *
 * @method void bringToFront()
 * @method Browser browser()
 * @method BrowserContext browserContext()
 * @method string content()
 * @method array cookies(...$urls)
 * @method Coverage coverage()
 * @method void deleteCookie(...$cookies)
 * @method void evaluateOnNewDocument(Js $pageFunction, ...$args)
 * @method void exposeFunction(string $name, Js $puppeteerFunction)
 * @method void focus(string $selector)
 * @method Frame[] frames()
 * @method void hover(string $selector)
 * @method bool isClosed()
 * @method Frame mainFrame()
 * @method array metrics()
 * @method Buffer pdf(array $options = [])
 * @method JSHandle queryObjects(JSHandle $prototypeHandle)
 * @method Response reload(array $options = [])
 * @method Buffer|string screenshot(array $options)
 * @method array select(string $selector, ...$values)
 * @method void setBypassCSP(bool $enabled)
 * @method void setCacheEnabled(bool $enabled)
 * @method void setContent(string $content, array $options = [])
 * @method void setCookie(...$cookies)
 * @method void setDefaultNavigationTimeout(int $timeoutInMilliseconds)
 * @method void setDefaultTimeout(int $timeoutInMilliseconds)
 * @method void setExtraHTTPHeaders(array $headers)
 * @method void setJavaScriptEnabled(bool $enabled)
 * @method void setOfflineMode(bool $enabled)
 * @method void setRequestInterception(bool $value)
 * @method void setUserAgent(string $userAgent)
 * @method void setViewport(array $options)
 * @method Target target()
 * @method string title()
 * @method void type(string $selector, string $text, array $options = [])
 * @method string url()
 * @method array viewport()
 * @method Worker[] workers()
 * @method Page on(string $event, Js $callback)
 * @method ElementHandle addScriptTag(array $options)
 * @method ElementHandle addStyleTag(array $options)
 * @method JSHandle waitFor($selectorOrFunctionOrTimeout, array $options = [], ...$args)
 * @method Request waitForRequest($urlOrPredicate, array $options = [])
 * @method Response waitForResponse($urlOrPredicate, array $options = [])
 * @method ElementHandle waitForXPath($xpath, array $options = [])
 * @method evaluate(Js $function)
 */
class Page extends Buffer
{

	use
		AliasesSelectionMethods,
		AliasesEvaluationMethods,
		Throttler
	;

	protected $blockedRequests = [];

	/** @var bool */
	protected $initBlockRequest = false;

	public function enableRequestsByTypes(array $types): self
	{
		return $this->removeBlockingRule('type', $types);
	}

	public function disableRequestsByTypes(array $types): self
	{
		return $this->addBlockingRule('type', $types);
	}

	public function enableRequestsByUrls(array $urls): self
	{
		return $this->removeBlockingRule('url', $urls);
	}

	public function disableRequestsByUrls(array $urls): self
	{
		return $this->addBlockingRule('url', $urls);
	}

	public function enableRequestsByDomains(array $domains): self
	{
		return $this->removeBlockingRule('domain', $domains);
	}

	public function disableRequestsByDomains(array $domains): self
	{
		return $this->addBlockingRule('domain', $domains);
	}

	public function enableRequestsByRegex(array $regex): self
	{
		return $this->removeBlockingRule('regex', $regex);
	}

	public function disableRequestsByRegex(array $regex): self
	{
		return $this->addBlockingRule('regex', $regex);
	}

	public function enableRequests(string $type, array $rules): self
	{
		return $this->addBlockingRule($type, $rules);
	}

	public function authenticate(string $username, string $password)
	{
		return parent::authenticate([
			'username' => $username,
			'password' => $password,
		]);
	}

	public function goto(string $url, array $options = []): ?Response
	{
		if (!$this->initBlockRequest) {
			$this->setBlockedRequests();
		}
		return parent::goto($url, $options);
	}

	public function gotoWithWait(string $url, array $options = [])
	{
		return $this->goto($url, array_merge([$options, [
			'waitUntil' => 'networkidle0',
		]]));
	}

	public function waitForUrlRequest(string $url, array $options = []): Request
	{
		$function = Js::createWithParameters(['response'])
			->scope(['ulr' => $url])
			->body('return url === response.url();')
		;

		return $this->waitForRequest($function, $options);
	}

	public function waitForUrlResponse(string $url, array $options = []): Response
	{
		$function = Js::createWithParameters(['response'])
			->scope(['ulr' => $url])
			->body('return url === response.url();')
		;

		return $this->waitForResponse($function, $options);
	}

	public function waitForRegexUrlResponse(string $regexUrl, array $options = []): Response
	{
		$function = Js::createWithParameters(['response'])
			->body("
                let regex = {$regexUrl};
                return response.url().match(regex);
            ")
		;
		return $this->waitForResponse($function, $options);
	}

	public function setGeolocation(float $latitude, float $longitude, int $accuracy = 0)
	{
		return parent::setGeolocation([
			'latitude' => $latitude,
			'longitude' => $longitude,
			'accuracy' => $accuracy,
		]);
	}

	public function close($runBeforeUnload = false): void
	{
		parent::close([
			'runBeforeUnload' => $runBeforeUnload,
		]);
	}

	protected function setBlockedRequests(): void
	{
		if ($this->initBlockRequest) {
			throw new InternalError('Request is already handled!');
		}

		$consts = [];
		$codes = [];
		foreach ($this->blockedRequests as $type => $rules) {
			switch ($type) {
				case 'type':
					$rules = implode(', ', array_map(function($rule){
						return "'{$rule}'";
					}, $rules));
					$consts[] = "blockedTypes = [{$rules}]";
					$codes[] = "blockedTypes.indexOf(request.resourceType()) !== -1";
					break;
				case 'domain':
					$rules = implode('|', array_map(function($rule){
						return preg_quote($rule, '/');
					}, $rules));
					$regex = "new RegExp(/^(?:http(?:s)?:)?\/\/.*?({$rules})(\/|$)/, 'g')";
					$consts[] = "blockedDomains = {$regex}";
					$codes[] = "request.url().match(blockedDomains)";
					break;
				case 'url':
					$rules = implode('|', array_map(function($rule){
						return preg_quote($rule, '/');
					}, $rules));
					$regex = "new RegExp(/^({$rules})(\/|$)/, 'g')";
					$consts[] = "blockedUrls = {$regex}";
					$codes[] = "request.url().match(blockedUrls)";
					break;
				case 'regex':
					$regex = implode('|', array_map(function($rule){
						$rule = preg_quote($rule, '/');
						return "new RegExp(/^($rule)(\/|$)/, 'g')";
					}, $rules));
					$consts[] = "blockedRegex = [{$regex}]";
					$codes[] = "request.url().match(blockedRegex)";
					break;
			}
		}

		if (count($consts) > 0 && count($codes) > 0) {
			$this->initBlockRequest = true;
			$this->setRequestInterception(true);

			try {
				$consts = implode(", ", $consts);
				$codes = implode(" || ", $codes);
				$pageFunction = Js::createWithParameters(['request'])
					->body("
						const {$consts};
						if ({$codes}){
							request.abort();
						} else {
							request.continue();
						}
					")
				;

				$this->tryCatch->on('request', $pageFunction);
			} catch (Node\Exception $exception) {
				print_r($exception->getMessage());
			}
		}
	}

	protected function addBlockingRule(string $type, $rules): self
	{
		if (!is_array($rules)) {
			$rules = [$rules];
		}
		$rules = array_merge($this->blockedRequests[$type] ?? [], $rules);
		$this->blockedRequests[$type] = array_keys(array_flip($rules));
		return $this;
	}

	protected function removeBlockingRule(string $type, $rules): self
	{
		if (array_key_exists($type, $this->blockedRequests)) {
			if (!is_array($rules)) {
				$rules = [$rules];
			}
			$array = array_flip($this->blockedRequests[$type]);
			foreach ($rules as $rule) {
				if (array_key_exists($rule, $array)) {
					unset($array[$rule]);
				}
			}
			$this->blockedRequests[$type] = array_keys($array);
		}
		return $this;
	}

	/**
	 * @param array $options
	 * @return Response|null
	 */
	public function goBack(array $options = [])
    {
		$this->addAction()->waitForAPM();
		return parent::goBack($options);
	}

	/**
	 * @param array $options
	 * @return Response|null
	 */
	public function goForward(array $options = [])
    {
		$this->addAction()->waitForAPM();
		return parent::goForward($options);
	}

	/**
	 * @param string $selector
	 * @param array $options
	 * @return void
	 */
	public function click(string $selector, array $options = []):void
    {
		$this->addAction()->waitForAPM();
		parent::click($selector, $options);
	}

	/**
	 * @param string $selector
	 * @param array $options
	 * @return void
	 */
	public function tap(string $selector, array $options = []):void
    {
		$this->addAction()->waitForAPM();
		parent::tap($selector, $options);
	}

}
