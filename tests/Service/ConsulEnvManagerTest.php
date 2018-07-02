<?php
declare(strict_types = 1);

namespace DL\ConsulPhpEnvVar\Tests\Service;

use DL\ConsulPhpEnvVar\Service\ConsulEnvManager;
use PHPUnit\Framework\TestCase;
use SensioLabs\Consul\ConsulResponse;
use SensioLabs\Consul\Exception\ClientException;
use SensioLabs\Consul\Services\KVInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Test the behaviour of the Consul environment manager.
 *
 * @package DL\ConsulPhpEnvVar\Tests\Service
 * @author  Petre Pătrașc <petre@dreamlabs.ro>
 */
class ConsulEnvManagerTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $kv;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->kv = $this->getMockBuilder(KVInterface::class)
            ->getMock();
    }

    public function testGivenThatAnEnvironmentVariableIsNotDefinedThenItsValueWillBeRetrievedFromConsul()
    {
        $testKey  = 'TEST_ENV_1';
        $response = new ConsulResponse([], 'test_value');
        $this->kv->expects($this->once())
            ->method('get')
            ->with('test/env')
            ->willReturn($response);

        $this->assertFalse(getenv($testKey));

        $manager = new ConsulEnvManager($this->kv);
        $manager->getEnvVarsFromConsul([
            $testKey => 'test/env',
        ]);

        $this->assertEquals('test_value', getenv($testKey));
    }

    public function testGivenThatAnEnvironmentVariableIsDefinedThenItsValueWillNotBeRetrievedFromConsul()
    {
        $testKey = 'TEST_ENV_2';
        $this->kv->expects($this->never())
            ->method('get');

        putenv("{$testKey}=already-defined");
        $_ENV[$testKey] = 'already-defined';

        $manager = new ConsulEnvManager($this->kv);
        $manager->getEnvVarsFromConsul([
            $testKey => 'test/env',
        ]);

        $this->assertEquals('already-defined', getenv($testKey));
    }

    public function testGivenThatAnEnvironmentVariableIsDefinedThenItsValueWillStillBeRetrievedFromConsulIfTheOverwriteOptionIsDefined()
    {
        $testKey  = 'TEST_ENV_3';
        $response = new ConsulResponse([], 'test_value_3');
        $this->kv->expects($this->once())
            ->method('get')
            ->with('test/env3')
            ->willReturn($response);

        $this->assertFalse(getenv($testKey));
        putenv("{$testKey}=already-defined");
        $_ENV[$testKey] = 'already-defined';

        $manager = new ConsulEnvManager($this->kv, true);
        $manager->getEnvVarsFromConsul([
            $testKey => 'test/env3',
        ]);

        $this->assertEquals('test_value_3', getenv($testKey));
    }

    public function testGivenThatASetOfMappingsAreProvidedIntoTheContainerThenTheyWillBeSetAsContainerParameters()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mappings = [
            'env_key_1' => 'consul/path/1',
            'env_key_2' => 'consul/path/2',
            'env_key_3' => 'consul/3',
        ];

        $container->expects($this->exactly(count($mappings)))->method('setParameter');

        $manager = new ConsulEnvManager($this->kv, false);
        $manager->exposeEnvironmentIntoContainer($container, $mappings);
    }

    public function testItUsesDefaultValueIfKeyNotAvailableInConsul()
    {
        $testKey  = 'TEST_ENV_4';

        $this->kv->expects($this->once())
            ->method('get')
            ->will($this->throwException(new ClientException('', 404)));

        $manager = new ConsulEnvManager($this->kv, false);
        $manager->getEnvVarsFromConsul([$testKey => 'test/env4'], [$testKey => 'foo']);

        $this->assertEquals('foo', getenv($testKey));
    }

    /**
     * @expectedException \DL\ConsulPhpEnvVar\Exception\NullValueException
     */
    public function testItThrowExceptionIfKeyNotAvailableInConsulAndNotInDefaultValues()
    {
        $testKey  = 'TEST_ENV_5';

        $this->kv->expects($this->once())
            ->method('get')
            ->will($this->throwException(new ClientException('', 404)));

        $manager = new ConsulEnvManager($this->kv, false);
        $manager->getEnvVarsFromConsul([$testKey => 'test/env5']);

        $this->assertEquals('foo', getenv($testKey));
    }
}
