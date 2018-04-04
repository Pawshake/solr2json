<?php

namespace Pawshake\Solr2json\Transformer;

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class TransformerTest extends TestCase
{
    use MatchesSnapshots;

    /**
     * @var Transformer
     */
    private $transformer;

    /**
     * @test
     */
    public function shouldTransform()
    {
        $result = $this->transformer->transform([]);
        $this->assertMatchesSnapshot($result); 
    }

    protected function setUp()
    {
        parent::setUp();
        $this->transformer = new Transformer();
    }
}
