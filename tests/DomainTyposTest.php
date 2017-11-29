<?php

use PHPUnit\Framework\TestCase;

final class DomainTyposTest extends TestCase
{
    public function testNotSame()
    {
        $this->assertEquals(
            true, DomainTypos::notSame([1, 2])
        );
        $this->assertEquals(
            false, DomainTypos::notSame([1, 1])
        );
    }

    public function testEndsWith()
    {
        $this->assertEquals(
            true, DomainTypos::endsWith('foobar', 'bar')
        );
        $this->assertEquals(
            false, DomainTypos::endsWith('foobaz', 'bar')
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

    public function testDomainIndex()
    {
        $index = DomainTypos::domainIndex(['gmail.com', 'foo.co.uk'],
                                          ['.com', '.co.uk']);
        $this->assertEquals(
            ['.com' => ['gmail.com'],
             '.co.uk' => ['foo.co.uk']],
            $index
        );
    }

    public function testIsTypo()
    {
        $domains = ['gmail.com', 'bar.co.uk'];
        $tlds = ['.com'];
        $index = DomainTypos::domainIndex($domains, ['.com', '.co.uk']);

        // not a typo
        $this->assertEquals(
            false, DomainTypos::isTypo('foo@gmail.com', 1, $index, $tlds)
        );

        // one character off (threshold=1)
        $this->assertEquals(
            true, DomainTypos::isTypo('foo@gnail.com', 1, $index, $tlds)
        );

        // different lengths (should not match)
        $this->assertEquals(
            false, DomainTypos::isTypo('foo@gmailz.com', 1, $index, $tlds)
        );

        // shouldn't match because tlds don't match
        $this->assertEquals(
            false, DomainTypos::isTypo('foo@gmail.com', 1, $index, ['.co.uk'])
        );

        // shouldn't match because tlds don't match
        $this->assertEquals(
            false, DomainTypos::isTypo('fox@bar.co.uk', 1, $index, $tlds)
        );

        // should match because tlds match and one character off
        $this->assertEquals(
            true, DomainTypos::isTypo('foo@baz.co.uk', 1, $index, ['.com', '.co.uk'])
        );

        // not a typo
        $this->assertEquals(
            false, DomainTypos::isTypo('foo@bar.co.uk', 1, $index, ['.com', '.co.uk'])
        );

        // shouldn't match because tlds don't match
        $this->assertEquals(
            false, DomainTypos::isTypo('foo@gmail.cox', 1, $index, $tlds)
        );

        // two characters off (threshold=1)
        $this->assertEquals(
            false, DomainTypos::isTypo('foo@ganil.com', 1, $index, $tlds)
        );

        // two characters off (threshold=2)
        $this->assertEquals(
            true, DomainTypos::isTypo('foo@ganil.com', 2, $index, $tlds)
        );

        $domains = ['gmail.com', 'yahoo.com'];
        $index = DomainTypos::domainIndex($domains, $tlds);

        // two domains to scan

        // not a typo
        $this->assertEquals(
            false, DomainTypos::isTypo('foo@yahoo.com', 1, $index, $tlds)
        );

        // two chars diff (threshold=1)
        $this->assertEquals(
            false, DomainTypos::isTypo('foo@ayhoo.com', 1, $index, $tlds)
        );

        // two chars off (threshold=2)
        $this->assertEquals(
            true, DomainTypos::isTypo('foo@ayhoo.com', 2, $index, $tlds)
        );

        $domains = ['gmail.com', 'ymail.com'];
        $index = DomainTypos::domainIndex($domains, $tlds);

        // gmail vs ymail test (both valid)
        $this->assertEquals(
            false, DomainTypos::isTypo('foo@ymail.com', 1, $index, $tlds)
        );

        // check a failed index
        $this->assertEquals(
            false, DomainTypos::isTypo('foo@gmail.com', 1, [], $tlds)
        );
    }
}
?>
