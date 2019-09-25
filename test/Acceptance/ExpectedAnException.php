<?php
declare(strict_types=1);


namespace Test\Acceptance;

use RuntimeException;

class ExpectedAnException extends RuntimeException
{

    public function __construct()
    {
        parent::__construct('Expect an exception');
    }
}
