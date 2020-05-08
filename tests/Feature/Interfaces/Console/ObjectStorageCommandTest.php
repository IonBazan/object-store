<?php

declare(strict_types=1);

namespace App\Tests\Feature\Interfaces\Console;

use App\Interfaces\Console\GetCommand;
use App\Interfaces\Console\StoreCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ObjectStorageCommandTest extends KernelTestCase
{
    protected StoreCommand $storeCommand;
    protected GetCommand $getCommand;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $this->storeCommand = $application->find('app:object-store:store');
        $this->getCommand = $application->find('app:object-store:get');
    }

    public function testCreatesAndReadsData(): void
    {
        $key = 'test-key';
        $value = 'my-value';

        $storeTester = new CommandTester($this->storeCommand);
        $this->assertSame(0, $storeTester->execute(['key' => $key, 'value' => json_encode($value)]));
        $storeOutput = $storeTester->getDisplay();
        $this->assertStringContainsString('test-key', $storeOutput);
        $this->assertStringContainsString('Key', $storeOutput);
        $this->assertStringContainsString('Value stored successfully', $storeOutput);
        $this->assertStringContainsString('Timestamp', $storeOutput);
        $this->assertStringContainsString('Current timestamp', $storeOutput);

        $getTester = new CommandTester($this->getCommand);
        $this->assertSame(0, $getTester->execute(['key' => $key]));
        $getOutput = $getTester->getDisplay();
        $this->assertStringContainsString('Object store', $getOutput);
        $this->assertStringContainsString('Key', $getOutput);
        $this->assertStringContainsString('test-key', $getOutput);
        $this->assertStringContainsString(json_encode($value), $getOutput);
        $this->assertStringContainsString('Timestamp', $getOutput);
        $this->assertStringContainsString('Current timestamp', $getOutput);

        $this->assertSame(1, $getTester->execute(['key' => $key, '-t' => 0]));
        $getOutput = $getTester->getDisplay();
        $this->assertStringContainsString('[ERROR] Object not found', $getOutput);
    }
}
