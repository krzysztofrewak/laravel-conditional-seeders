<?php

declare(strict_types=1);

namespace KrzysztofRewak\ConditionalSeeder;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use InvalidArgumentException;

/**
 * Class ConditionalSeeder
 * @package KrzysztofRewak\ConditionalSeeder
 */
class ConditionalSeeder extends Seeder
{
    /**
     * Seed the given connection from the given path.
     * @param array|string $class
     * @param bool $silent
     * @param mixed ...$parameters
     * @return $this
     */
    public function call($class, $silent = false, ...$parameters)
    {
        $classes = Arr::wrap($class);

        foreach ($classes as $class) {
            /** @var static $seeder */
            $seeder = $this->resolve($class);

            $name = get_class($seeder);

            if ($this->shouldBeWritten($silent)) {
                $this->command->getOutput()->writeln("<comment>Seeding:</comment> {$name}");
            }

            $startTime = microtime(true);

            try {
                $seeder->__invoke();
            } catch (SeederNotAuthorizedException $e) {
                if ($this->shouldBeWritten($silent)) {
                    $this->command->getOutput()->writeln("<info>Skipped:</info>  {$name}");
                }
                continue;
            }

            $runTime = number_format((microtime(true) - $startTime) * 1000, 2);

            if ($this->shouldBeWritten($silent)) {
                $this->command->getOutput()->writeln("<info>Seeded:</info>  {$name} ({$runTime}ms)");
            }
        }

        return $this;
    }

    /**
     * Run the database seeds.
     * @param mixed ...$parameters
     * @return mixed
     * @throws InvalidArgumentException
     * @throws SeederNotAuthorizedException
     */
    public function __invoke(...$parameters)
    {
        if (!method_exists($this, "run")) {
            throw new InvalidArgumentException("Method [run] missing from " . get_class($this));
        }

        if (!$this->authorize()) {
            throw new SeederNotAuthorizedException(get_class($this) . " was not authorized to run.");
        }

        return isset($this->container)
            ? $this->container->call([$this, "run"], $parameters)
            : $this->run(...$parameters);
    }

    /**
     * Authorizes call.
     * @return bool
     */
    protected function authorize(): bool
    {
        return true;
    }

    /**
     * Checks if anything should be written in the console output.
     * @param bool $silent
     * @return bool
     */
    protected function shouldBeWritten(bool $silent): bool
    {
        return $silent === false && isset($this->command);
    }
}
