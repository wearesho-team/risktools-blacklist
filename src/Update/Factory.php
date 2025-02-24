<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist\Update;

use DateTimeImmutable;
use Wearesho\RiskTools\Blacklist\Category;
use Wearesho\RiskTools\Blacklist\ResponseException;

class Factory
{
    /**
     * @throws ResponseException
     */
    public function createResponse(array $data): Response
    {
        if (!array_key_exists('errors', $data)) {
            return new Response();
        }

        if (!is_array($data['errors'])) {
            throw new ResponseException('Errors must be an array');
        }

        $errors = [];
        foreach ($data['errors'] as $error) {
            if (!is_array($error)) {
                throw new ResponseException('Each error must be an array');
            }

            if (!array_key_exists('item', $error) || !is_array($error['item'])) {
                throw new ResponseException('Error item must be an array');
            }

            if (!array_key_exists('errors', $error) || !is_array($error['errors'])) {
                throw new ResponseException('Error errors must be an array');
            }

            $record = $this->createRecordFromErrorItem($error['item']);

            $errors[] = new Error($record, $error['errors']);
        }

        return new Response($errors);
    }

    private function createRecordFromErrorItem(array $item): Record
    {
        $category = null;
        if (isset($item['category'])) {
            try {
                $category = Category::from($item['category']);
            } catch (\ValueError) {
                // Игнорируем неверную категорию, так как это может быть частью ошибки
            }
        }

        $addedAt = null;
        if (isset($item['added_at'])) {
            try {
                $addedAt = new DateTimeImmutable($item['added_at']);
            } catch (\Exception) {
                // Игнорируем неверную дату, так как это может быть частью ошибки
            }
        }

        if (isset($item['phone'], $item['ipn'])) {
            return Record::withPhoneAndIpn(
                $item['phone'],
                $item['ipn'],
                $category ?? Category::OTHER,
                $addedAt
            );
        }

        if (isset($item['phone'])) {
            return Record::withPhone(
                $item['phone'],
                $category ?? Category::OTHER,
                $addedAt
            );
        }

        if (isset($item['ipn'])) {
            return Record::withIpn(
                $item['ipn'],
                $category ?? Category::OTHER,
                $addedAt
            );
        }

        // Если нет ни телефона, ни ИПН, создаем запись с минимальными данными
        return Record::withPhone(
            'invalid',
            $category ?? Category::OTHER,
            $addedAt
        );
    }
}
