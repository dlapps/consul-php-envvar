# Consul PHP Environment Variables

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/5335a8c3-5f98-4ba4-87c4-542bc910dca4/mini.png)](https://insight.sensiolabs.com/projects/5335a8c3-5f98-4ba4-87c4-542bc910dca4)
[![Total Downloads][ico-downloads]][link-downloads]

The library enables developers to retrieve missing environment variables from a Consul KV store and make them available in a running PHP process, and also in a Symfony 3.2+ container. 

The package supports PSR-4 autoloading, is PSR-2 compliant and has been well tested through automated tests. The library is also actively used within the Dreamlabs ecosystem.

## Install

Via Composer

``` bash
$ composer require dlapps/consul-php-envvar
```

## Usage

In order to be able to interact with the library, an instance of the `ConsulEnvManager` class is required. One can easily be obtained through the dedicated builder, like so:
 
``` php
$manager = (new ConsulEnvManagerBuilder())->build();
```

The same builder can be customised:

* By setting the overwrite flag to true, even if an environment variable has been previously defined, it will still be updated with the latest from Consul.
* By setting the URL of the Consul server.

``` php
$manager = (new ConsulEnvManagerBuilder())
    ->withOverwriteEvenIfDefined(true)
    ->withConsulServer('https://consul.example.com:9123')
    ->build();
```

Once a `ConsulEnvManager` instance has been obtained, the `ConsulEnvManager::getEnvVarsFromConsul($mappings)` method can be used to provide an array of environment variable mappings to Consul KV paths. An example can be seen below:

``` php
$manager = (new ConsulEnvManagerBuilder())->build();

$manager->getEnvVarsFromConsul([
    'MYSQL_HOST' => 'dreamlabs/mysql/host',
    'MYSQL_PORT' => 'dreamlabs/mysql/port',
    'MYSQL_DB'   => 'dreamlabs/mysql/db',
    'MYSQL_USER' => 'dreamlabs/mysql/user',
    'MYSQL_PASS' => 'dreamlabs/mysql/pass',
]);
```

After running the snippet above, the currently running PHP process will have access to the `MYSQL_HOST`, `MYSQL_PORT`, `MYSQL_DB`, `MYSQL_USER` and `MYSQL_PASS` environment variables.

In order to also register the environment variables within a Symfony 3.2+ container as default values, the `ConsulEnvManager::exposeEnvironmentIntoContainer($container, $mappings)` method can be used. 

An example of `parameters.php` and `parameters.yml` can be found below:

``` php
declare(strict_types = 1);

$manager = (new \DL\ConsulPhpEnvVar\Builder\ConsulEnvManagerBuilder())->build();

$mappings = [
    'MYSQL_HOST'          => 'dreamlabs/mysql/host',
    'MYSQL_PORT'          => 'dreamlabs/mysql/port',
    'MYSQL_DB'            => 'dreamlabs/mysql/db',
    'MYSQL_USER'          => 'dreamlabs/mysql/user',
    'MYSQL_PASS'          => 'dreamlabs/mysql/pass',
];

$manager->getEnvVarsFromConsul($mappings);
$manager->exposeEnvironmentIntoContainer($container, $mappings);
```

``` yaml
parameters:
    database_host: '%env(MYSQL_HOST)%'
    database_port: '%env(MYSQL_PORT)%'
    database_name: '%env(MYSQL_DB)%'
    database_user: '%env(MYSQL_USER)%'
    database_password: '%env(MYSQL_PASS)%'
``` 

## Testing

``` bash
$ composer test
```

## PSR-2 Compatibility

``` bash
$ composer check-styles
$ composer fix-styles
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email petre [at] dreamlabs.ro instead of using the issue tracker.

## Credits

- [Petre Pătrașc][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/dlapps/consul-php-envvar.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/dlapps/consul-php-envvar/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/dlapps/consul-php-envvar.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/dlapps/consul-php-envvar.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/dlapps/consul-php-envvar.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/dlapps/consul-php-envvar
[link-travis]: https://travis-ci.org/dlapps/consul-php-envvar
[link-scrutinizer]: https://scrutinizer-ci.com/g/dlapps/consul-php-envvar/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/dlapps/consul-php-envvar
[link-downloads]: https://packagist.org/packages/dlapps/consul-php-envvar
[link-author]: https://github.com/petrepatrasc
[link-contributors]: ../../contributors
