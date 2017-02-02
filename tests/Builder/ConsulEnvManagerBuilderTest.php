<?php
declare(strict_types = 1);

namespace DL\ConsulPhpEnvVar\Tests\Builder;

use DL\ConsulPhpEnvVar\Builder\ConsulEnvManagerBuilder;
use DL\ConsulPhpEnvVar\Enum\ManagerDefaults;
use DL\ConsulPhpEnvVar\Service\ConsulEnvManager;
use PHPUnit\Framework\TestCase;

/**
 * Test the behaviour of the Consul environment manager builder.
 *
 * @package DL\ConsulPhpEnvVar\Tests\Builder
 * @author  Petre Pătrașc <petre@dreamlabs.ro>
 */
class ConsulEnvManagerBuilderTest extends TestCase
{
    /**
     * @var ConsulEnvManagerBuilder
     */
    protected $builder;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->builder = new ConsulEnvManagerBuilder;
    }

    public function testGivenThatDefaultConfigurationSettingsAreProvidedThenAManagerWithDefaultSettingsWillBeInitialised()
    {
        $manager = $this->builder->build();

        $this->assertNotNull($manager);
        $this->assertInstanceOf(ConsulEnvManager::class, $manager);
    }

    public function testGivenCustomParametersThenOnceTheManagerHasBeenInitialisedAllOfTheBuildersPropertiesWillBeReset()
    {
        $this->builder
            ->withConsulServer('https://consul.example.com')
            ->withOverwriteEvenIfDefined(true);

        $this->assertAttributeEquals('https://consul.example.com', 'consulServer', $this->builder);
        $this->assertAttributeEquals(true, 'overwriteEvenIfDefined', $this->builder);

        $this->builder->build();

        $this->assertAttributeEquals(ManagerDefaults::CONSUL_SERVER_DEFAULT, 'consulServer', $this->builder);
        $this->assertAttributeEquals(ManagerDefaults::OVERWRITE_DEFAULT, 'overwriteEvenIfDefined', $this->builder);
    }

    public function testGivenThatACustomServerUrlIsProvidedThenItWillBeUsedInTheGenerationOfTheKeyValueService()
    {
        $manager = $this->builder
            ->withConsulServer('https://consul.example.com:8123')
            ->build();

        $this->assertObjectHasAttribute('kv', $manager);
        $kv           = $this->getObjectAttribute($manager, 'kv');
        $client       = $this->getObjectAttribute($kv, 'client');
        $guzzleClient = $this->getObjectAttribute($client, 'client');
        $uri          = $this->getObjectAttribute($guzzleClient, 'config')['base_uri'];

        $scheme = $this->getObjectAttribute($uri, 'scheme');
        $host   = $this->getObjectAttribute($uri, 'host');
        $port   = $this->getObjectAttribute($uri, 'port');

        $fullUrl = "{$scheme}://{$host}";
        if (null !== $port) {
            $fullUrl .= ":{$port}";
        }
        $this->assertEquals('https://consul.example.com:8123', $fullUrl);
    }

    public function testGivenThatTheOverwriteFlagIsEnabledThenItWillBeUsedByTheManagerInstance()
    {
        $manager = $this->builder
            ->withOverwriteEvenIfDefined(true)
            ->build();

        $this->assertAttributeEquals(true, 'overwriteEvenIfDefined', $manager);
    }
}
