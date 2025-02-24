<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist;

class Service
{
    public function __construct(
        private readonly Client $client,
        private readonly Search\Factory $searchFactory,
    ) {
    }

    public function search(Search\Request $request): Search\Response
    {
        return $this->searchFactory->createResponse(
            $this->client->request(Endpoint::Search, $request->toArray())
        );
    }
}
