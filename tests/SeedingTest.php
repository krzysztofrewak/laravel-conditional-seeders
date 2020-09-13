<?php

declare(strict_types=1);

use KrzysztofRewak\ConditionalSeeder\ConditionalSeeder;
use KrzysztofRewak\ConditionalSeeder\SeederNotAuthorizedException;
use PHPUnit\Framework\TestCase;

/**
 * Class SeedingTest
 */
final class SeedingTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     * @throws SeederNotAuthorizedException
     */
    public function testConditionalSeederWithoutRunMethod(): void
    {
        $seeder = new class extends ConditionalSeeder {
        };

        $this->expectException(InvalidArgumentException::class);
        $seeder->__invoke();
    }

    /**
     * @throws InvalidArgumentException
     * @throws SeederNotAuthorizedException
     */
    public function testSimpleConditionalSeeder(): void
    {
        $seeder = new class extends ConditionalSeeder {
            public function run(): bool
            {
                return true;
            }
        };

        $seed = $seeder->__invoke();
        $this->assertTrue($seed);
    }

    /**
     * @throws InvalidArgumentException
     * @throws SeederNotAuthorizedException
     */
    public function testNotAuthorizedConditionalSeeder(): void
    {
        $seeder = new class extends ConditionalSeeder {
            public function run(): bool
            {
                return true;
            }

            protected function authorize(): bool
            {
                return false;
            }
        };

        $this->expectException(SeederNotAuthorizedException::class);
        $seeder->__invoke();
    }

    /**
     * @throws InvalidArgumentException
     * @throws SeederNotAuthorizedException
     */
    public function testConditionalSeederWithAuthorizeResultDependentFromExternalState(): void
    {
        $seeder = new class extends ConditionalSeeder {
            public function run(): bool
            {
                return true;
            }

            protected function authorize(): bool
            {
                return ExternalSource::$state;
            }
        };

        $seed = $seeder->__invoke();
        $this->assertTrue($seed);

        ExternalSource::$state = false;
        try {
            $seeder->__invoke();
        } catch (Throwable $exception) {
            $this->assertInstanceOf(SeederNotAuthorizedException::class, $exception);
        }

        ExternalSource::$state = true;
        $seed = $seeder->__invoke();
        $this->assertTrue($seed);
    }
}

final class ExternalSource
{
    public static $state = true;
}
