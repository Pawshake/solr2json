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
     * @dataProvider provideSolrData
     */
    public function shouldTransform($solrData)
    {
        $result = $this->transformer->transform((array) $solrData);
        $this->assertMatchesSnapshot($result);
    }

    public function provideSolrData()
    {
        $solrDataSets = json_decode(
            file_get_contents('./tests/Transformer/solr_data.json')
        );

        return array_map(
            function ($solrDataSet) {
                return [$solrDataSet];
            },
            $solrDataSets
        );
    }

    protected function setUp()
    {
        parent::setUp();
        $this->transformer = new Transformer();
    }
}
