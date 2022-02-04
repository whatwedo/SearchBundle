<?php

namespace whatwedo\SearchBundle\Tests\App\Formatter;

use whatwedo\CoreBundle\Formatter\AbstractFormatter;

class DummyFormatter extends AbstractFormatter
{

    public function getString($value): string
    {
        return 'dummy';
    }

}
