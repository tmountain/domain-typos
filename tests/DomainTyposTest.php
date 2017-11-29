<?php

use PHPUnit\Framework\TestCase;

final class DomainTyposTest extends TestCase
{
    public function testEndsWith()
    {
        $this->assertEquals(
            true, DomainTypos::endsWith('foobar', 'bar')
        );
        $this->assertEquals(
            false, DomainTypos::endsWith('foobaz', 'bar')
        );
    }

    public function testRepeat()
    {
        $this->assertEquals(
            ['foo', 'foo', 'foo'], DomainTypos::repeat('foo', 3)
        );

        $this->assertEquals(
            [], DomainTypos::repeat('foo', 0)
        );

        $this->assertEquals(
            [], DomainTypos::repeat('foo', -1)
        );
    }

    public function testMatchTLD()
    {
        $tlds = ['.com', '.co.uk'];

        $this->assertEquals(
            '.com', DomainTypos::matchTLD('gmail.com', $tlds)
        );

        $this->assertEquals(
            '.co.uk', DomainTypos::matchTLD('foo.co.uk', $tlds)
        );

        $this->assertEquals(
            '', DomainTypos::matchTLD('foo.org', $tlds)
        );
    }

    public function testExtractHost()
    {
        $this->assertEquals(
            'foo', DomainTypos::extractHost('foo.com', '.com')
        );

        $this->assertEquals(
            'foo', DomainTypos::extractHost('foo.co.uk', '.co.uk')
        );

        $this->assertEquals(
            '', DomainTypos::extractHost('foo.com', '.co.uk')
        );
    }

    public function testHammingDistance()
    {
        $this->assertEquals(
            0, DomainTypos::hammingDistance('foo.com', 'foo.com')
        );
        $this->assertEquals(
            1, DomainTypos::hammingDistance('fox.com', 'foo.com')
        );
        $this->assertEquals(
            2, DomainTypos::hammingDistance('box.com', 'foo.com')
        );
        $this->assertEquals(
            -1, DomainTypos::hammingDistance('box.com', 'fo.com')
        );
    }

    public function testDomain()
    {
        $this->assertEquals(
            'example.com', DomainTypos::domain('foo@example.com')
        );
        $this->assertEquals(
            '', DomainTypos::domain('foo')
        );
        $this->assertEquals(
            '', DomainTypos::domain('')
        );
    }

    public function testIsTypo()
    {
        $domains = ['gmail.com', 'bar.co.uk'];
        $tlds = ['.com'];

        // not a typo
        $this->assertEquals(
            false, DomainTypos::isTypo('foo@gmail.com', 1, $domains, $tlds)
        );

        // one character off (threshold=1)
        $this->assertEquals(
            true, DomainTypos::isTypo('foo@gnail.com', 1, $domains, $tlds)
        );

        // different lengths (should not match)
        $this->assertEquals(
            false, DomainTypos::isTypo('foo@gmailz.com', 1, $domains, $tlds)
        );

        // shouldn't match because tlds don't match
        $this->assertEquals(
            false, DomainTypos::isTypo('foo@gmail.com', 1, $domains, ['.co.uk'])
        );

        // shouldn't match because tlds don't match
        $this->assertEquals(
            false, DomainTypos::isTypo('fox@bar.co.uk', 1, $domains, $tlds)
        );

        // should match because tlds match and one character off
        $this->assertEquals(
            true, DomainTypos::isTypo('foo@baz.co.uk', 1, $domains, ['.com', '.co.uk'])
        );

        // not a typo
        $this->assertEquals(
            false, DomainTypos::isTypo('foo@bar.co.uk', 1, $domains, ['.com', '.co.uk'])
        );

        // shouldn't match because tlds don't match
        $this->assertEquals(
            false, DomainTypos::isTypo('foo@gmail.cox', 1, $domains, $tlds)
        );

        // two characters off (threshold=1)
        $this->assertEquals(
            false, DomainTypos::isTypo('foo@ganil.com', 1, $domains, $tlds)
        );

        // two characters off (threshold=2)
        $this->assertEquals(
            true, DomainTypos::isTypo('foo@ganil.com', 2, $domains, $tlds)
        );

        $domains = ['gmail.com', 'yahoo.com'];
        // two domains to scan

        // not a typo
        $this->assertEquals(
            false, DomainTypos::isTypo('foo@yahoo.com', 1, $domains, $tlds)
        );

        // two chars diff (threshold=1)
        $this->assertEquals(
            false, DomainTypos::isTypo('foo@ayhoo.com', 1, $domains, $tlds)
        );

        // two chars off (threshold=2)
        $this->assertEquals(
            true, DomainTypos::isTypo('foo@ayhoo.com', 2, $domains, $tlds)
        );

        // gmail vs ymail test (both valid)
        $this->assertEquals(
            false, DomainTypos::isTypo('foo@ymail.com', 1, ['gmail.com', 'ymail.com'], $tlds)
        );
    }
}
?>
