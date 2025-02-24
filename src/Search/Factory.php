<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist\Search;

use Wearesho\RiskTools\Blacklist\Category;
use Wearesho\RiskTools\Blacklist\ResponseException;
use DateTimeImmutable;

class Factory
{
    /**
     * @throws ResponseException
     */
    public function createResponse(array $data): Response
    {
        if (!array_key_exists('records', $data)) {
            throw new ResponseException('Missing required key: records');
        }
        if (!is_array($data['records'])) {
            throw new ResponseException('Records must be an array');
        }
        if (!array_key_exists('found', $data)) {
            throw new ResponseException('Missing required key: found');
        }
        if (!is_int($data['found'])) {
            throw new ResponseException('Found must be an integer');
        }
        if (!array_key_exists('partners', $data)) {
            throw new ResponseException('Missing required key: partners');
        }
        if (!is_array($data['partners'])) {
            throw new ResponseException('Partners must be an array');
        }
        if (!array_key_exists('categories', $data)) {
            throw new ResponseException('Missing required key: categories');
        }
        if (!is_array($data['categories'])) {
            throw new ResponseException('Categories must be an array');
        }

        return new Response(
            records: array_map(
                fn(array $record): Record => $this->createRecord($record),
                $data['records']
            ),
            found: $data['found'],
            partners: $data['partners'],
            categories: $data['categories'],
        );
    }

    /**
     * @throws ResponseException
     */
    public function createRecord(array $data): Record
    {
        if (!array_key_exists('category', $data)) {
            throw new ResponseException('Missing required key: category');
        }
        if (!is_string($data['category'])) {
            throw new ResponseException('Category must be a string');
        }

        try {
            $category = Category::from($data['category']);
        } catch (\ValueError $e) {
            throw new ResponseException(
                "Invalid category value: {$data['category']}"
            );
        }

        if (!array_key_exists('added_at', $data)) {
            throw new ResponseException('Missing required key: added_at');
        }
        if (!is_string($data['added_at'])) {
            throw new ResponseException('Added at must be a string');
        }
        if (!array_key_exists('partner_id', $data)) {
            throw new ResponseException('Missing required key: partner_id');
        }
        if (!is_string($data['partner_id'])) {
            throw new ResponseException('Partner ID must be a string');
        }

        try {
            return new Record(
                ipn: $data['ipn'] ?? null,
                phone: $data['phone'] ?? null,
                category: $category,
                addedAt: new DateTimeImmutable($data['added_at']),
                partnerId: $data['partner_id'],
            );
        } catch (\Throwable $e) {
            throw new ResponseException(
                "Failed to create Record: " . $e->getMessage(),
                code: $e->getCode(),
                previous: $e
            );
        }
    }
}
