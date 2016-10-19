<?php
/*
 * This file is part of the Money package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
class DemoTest extends TestCase
{
    public function testFailure()
    {
        $this->assertEquals(1, 0);
    }

}