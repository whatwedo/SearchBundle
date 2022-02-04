<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests\App\Formatter;

use whatwedo\CoreBundle\Formatter\AbstractFormatter;

class DummyFormatter extends AbstractFormatter
{
    public function getString($value): string
    {
        return 'dummy';
    }
}
