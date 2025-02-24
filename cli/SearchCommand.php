<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputInterface, InputOption};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wearesho\RiskTools\Blacklist\{Service, Category, Search, Exception};

class SearchCommand extends Command
{
    protected static $defaultName = 'search';
    protected static $defaultDescription = 'Search in blacklist by phone or IPN';

    public function __construct(
        private readonly Service $service,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'phone',
                'p',
                InputOption::VALUE_REQUIRED,
                'Phone number to search'
            )
            ->addOption(
                'ipn',
                'i',
                InputOption::VALUE_REQUIRED,
                'IPN to search'
            )
            ->addOption(
                'category',
                'c',
                InputOption::VALUE_REQUIRED,
                'Category to filter by: ' . implode(', ', array_column(Category::cases(), 'value'))
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $phone = $input->getOption('phone');
        $ipn = $input->getOption('ipn');

        if (!$phone && !$ipn) {
            $io->error('Either phone or IPN must be provided');
            return Command::FAILURE;
        }

        try {
            $categoryOption = $input->getOption('category');
            $category = ($categoryOption !== null)
                ? [Category::from($input->getOption('category'))]
                : [];

            $request = match (true) {
                ($phone && $ipn) => Search\Request::byPhoneOrIpn($phone, $ipn, ...$category),
                (bool)$phone => Search\Request::byPhone($phone, ...$category),
                (bool)$ipn => Search\Request::byIpn($ipn, ...$category),
            };

            $response = $this->service->search($request);

            if (empty($response->records())) {
                $io->info('No records found');
                return Command::SUCCESS;
            }

            $records = [];
            foreach ($response->records() as $record) {
                $records[] = [
                    'Phone' => $record->phone() ?? '-',
                    'IPN' => $record->ipn() ?? '-',
                    'Category' => $record->category()->value,
                    'Partner ID' => $record->partnerId() ?? '-',
                    'Added At' => $record->addedAt()?->format('Y-m-d H:i:s') ?? '-',
                ];
            }

            $io->table(
                ['Phone', 'IPN', 'Category', 'Partner ID', 'Added At'],
                $records
            );

            return Command::SUCCESS;
        } catch (Exception $e) {
            $io->error([
                'Search failed',
                $e->getMessage()
            ]);

            if ($output->isVerbose()) {
                $io->error($e->getTraceAsString());
            }

            return Command::FAILURE;
        }
    }
}
