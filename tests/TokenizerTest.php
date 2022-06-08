<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests;

use PHPUnit\Framework\TestCase;
use whatwedo\SearchBundle\Tokenizer\StandardTokenizer;

class TokenizerTest extends TestCase
{
    public function testLowerCaseFilter()
    {
        $tokeizer = new StandardTokenizer();

        self::assertSame([
            'DATA1',
            'DaTa2',
        ], $tokeizer->tokenize('DATA1 DaTa2'));
    }
}
