<?php declare(strict_types=1);

namespace PhpPupeeteer\Resources;

/**
 * Class Keyboard
 * @package Scrappper\Resources
 *
 * @method void down(string $key, array $options = [])
 * @method void press(string $key, array $options = [])
 * @method void sendCharacter(string $char)
 * @method void type(string $text, array $options = [])
 * @method void up(string $key)
 */
class Keyboard extends Buffer {}
