<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist;

class Service
{
    public function __construct(
        private readonly Client $client,
        private readonly Search\Factory $searchFactory,
        private readonly Update\Factory $updateFactory,
    ) {
    }

    /**
     * @throws Exception
     */
    public function search(Search\Request $request): Search\Response
    {
        return $this->searchFactory->createResponse(
            $this->client->request(Endpoint::Search, $request->toArray())
        );
    }


    /**
     * @param Update\Record[] $records
     * @throws Exception
     */
    public function update(array $records): Update\Response
    {
        return $this->updateFactory->createResponse(
            $this->client->request(
                Endpoint::Update,
                ['records' => array_map(fn(Update\Record $r) => $r->toArray(), $records)]
            )
        );
    }
}
