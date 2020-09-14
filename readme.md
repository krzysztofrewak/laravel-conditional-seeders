## About
Relatively often I needed to put some logic into seeders and run them accordingly to some conditions (eg. I didn't want to duplicate or overwrite some data, I just wanted to run it once, I just wanted to run it only on certain environment, etc.). Putting these conditions into `run()` method was making these seeders less readable and putting them into `DatabaseSeeder` could be dangerous enough (because someone could just ran it from the console).

I added a method `authorize()` to an abstract `Seeder`. Method is returning `true` by default, so it should be completely backward compatible with existing seeders. Conditionally disabling any seeder from running is extremely easy:

```php
class DisabledTestSeeder extends Seeder
{
    public function run(): void
    {
        // (...)
    }

    protected function authorize(): bool
    {
        return false;
    }
}
```

Of course, it can look like this (to ensure that it will fire only when users table would be empty):


```php
protected function authorize(): bool
{
    return User::count() === 0;
}
```

## Installation
Include this package with Composer:
```shell script
composer require krzysztofrewak/laravel-conditional-seeders ^1.0
```

Now you can switch base class of your seeders in `database/seeders` directory to `\KrzysztofRewak\ConditionalSeeder\ConditionalSeeder`. By overwriting `authorize()` method you can conditionally switch on/off seeder in question.

Please remember to change base class of `DatabaseSeeder` to `\KrzysztofRewak\ConditionalSeeder\ConditionalSeeder` too. Without it, it won't work properly.
