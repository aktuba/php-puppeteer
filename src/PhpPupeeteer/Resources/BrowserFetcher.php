<?php declare(strict_types=1);

namespace PhpPupeeteer\Resources;

/**
 * Class BrowserFetcher
 * @package PhpPupeeteer\Resources
 *
 * @method bool canDownload(string $revision)
 * @method array download(string $revision, JSHandle $progressCallback)
 * @method array localRevisions()
 * @method string platform()
 * @method void remove(string $revision)
 * @method array revisionInfo(string $revision)
 */
class BrowserFetcher extends Buffer {}
