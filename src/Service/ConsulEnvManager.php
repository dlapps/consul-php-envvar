<?php
declare(strict_types = 1);

namespace DL\ConsulPhpEnvVar\Service;

use SensioLabs\Consul\Services\KVInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Manages environment variables through Consul.
 *
 * @package DL\ConsulPhpEnvVar\Service
 * @author  Petre Pătrașc <petre@dreamlabs.ro>
 */
class ConsulEnvManager
{
    /**
     * @var KVInterface
     */
    protected $kv;

    /**
     * @var bool
     */
    protected $overwriteEvenIfDefined = false;

    /**
     * ConsulEnvManager constructor.
     *
     * @param KVInterface   $kv
     * @param bool $overwriteEvenIfDefined
     */
    public function __construct(KVInterface $kv, bool $overwriteEvenIfDefined = false)
    {
        $this->kv                     = $kv;
        $this->overwriteEvenIfDefined = $overwriteEvenIfDefined;
    }

    /**
     * Add missing environment variables from Consul.
     *
     * @param array $mappings
     */
    public function getEnvVarsFromConsul(array $mappings)
    {
        foreach ($mappings as $environmentKey => $consulPath) {
            $keyExists = $this->keyIsDefined($environmentKey);
            if ($keyExists && !$this->overwriteEvenIfDefined) {
                continue;
            }

            $consulValue = $this->getKeyValueFromConsul($consulPath);
            $this->saveKeyValueInEnvironmentVars($environmentKey, $consulValue);
        }
    }

    /**
     * Expose a set of mappings into the container builder.
     *
     * @param ContainerBuilder $container
     * @param array            $mappings
     *
     * @return ContainerBuilder
     */
    public function exposeEnvironmentIntoContainer(ContainerBuilder $container, array $mappings): ContainerBuilder
    {
        foreach ($mappings as $environmentKey => $consulPath) {
            $container->setParameter("env({$environmentKey})", $_ENV[$environmentKey] ?? null);
        }

        return $container;
    }

    /**
     * Check if an environment key is defined.
     *
     * @param string $environmentKey
     *
     * @return bool
     */
    private function keyIsDefined(string $environmentKey): bool
    {
        return isset($_ENV[$environmentKey]);
    }

    /**
     * Get a key value from Consul.
     *
     * @param string $kvPath
     *
     * @return string
     */
    private function getKeyValueFromConsul(string $kvPath): string
    {
        return $this->kv->get($kvPath, ['raw' => true])->getBody();
    }

    /**
     * Save the value of a key in an environment variable.
     *
     * @param string $envKey
     * @param string $kvValue
     */
    private function saveKeyValueInEnvironmentVars($envKey, $kvValue)
    {
        $notHttpName = 0 !== strpos($envKey, 'HTTP_');

        putenv("$envKey=$kvValue");
        $_ENV[$envKey] = $kvValue;
        if ($notHttpName) {
            $_SERVER[$envKey] = $kvValue;
        }
    }
}
