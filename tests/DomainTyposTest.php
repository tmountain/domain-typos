<?php

use PHPUnit\Framework\TestCase;

final class DomainTyposTest extends TestCase
{
    public function testHammingDistance()
    {
        $this->assertEquals(
            DomainTypos::hammingDistance('foo.com', 'foo.com'), 0
        );
        $this->assertEquals(
            DomainTypos::hammingDistance('fox.com', 'foo.com'), 1
        );
        $this->assertEquals(
            DomainTypos::hammingDistance('box.com', 'foo.com'), 2
        );
        $this->assertEquals(
            DomainTypos::hammingDistance('box.com', 'fo.com'), -1
        );
    }

    public function testDomain()
    {
        $this->assertEquals(
            DomainTypos::domain('foo@example.com'), 'example.com'
        );
        $this->assertEquals(
            DomainTypos::domain('foo'), ''
        );
        $this->assertEquals(
            DomainTypos::domain(''), ''
        );
    }

    public function testIsTypo()
    {
        $domains = ['gmail.com'];

        // not a typo
        $this->assertEquals(
            DomainTypos::isTypo('foo@gmail.com', 1, $domains), false
        );

        // one character off (default threshold)
        $this->assertEquals(
            DomainTypos::isTypo('foo@gnail.com', 1, $domains), true
        );

        // different lengths (should not match)
        $this->assertEquals(
            DomainTypos::isTypo('foo@gmailz.com', 1, $domains), false
        );

        // two characters off (default threshold)
        $this->assertEquals(
            DomainTypos::isTypo('foo@ganil.com', 1, $domains), false
        );

        // two characters off (threshold=2)
        $this->assertEquals(
            DomainTypos::isTypo('foo@ganil.com', 2, $domains), true
        );

        $domains = ['gmail.com', 'yahoo.com'];
        // two domains to scan

        // not a typo
        $this->assertEquals(
            DomainTypos::isTypo('foo@yahoo.com', 1, $domains), false
        );

        // two chars diff (default threshold)
        $this->assertEquals(
            DomainTypos::isTypo('foo@ayhoo.com', 1, $domains), false
        );

        // two chars off (threshold=2)
        $this->assertEquals(
            DomainTypos::isTypo('foo@ayhoo.com', 2, $domains), true
        );
    }
}
?>
