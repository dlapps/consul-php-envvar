<?php
declare(strict_types = 1);

namespace DL\ConsulPhpEnvVar\Service;

use SensioLabs\Consul\Services\KV;
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
     * @var KV
     */
    protected $kv;

    /**
     * @var bool
     */
    protected $overwriteEvenIfDefined = false;

    /**
     * ConsulEnvManager constructor.
     *
     * @param KV   $kv
     * @param bool $overwriteEvenIfDefined
     */
    public function __construct(KV $kv, bool $overwriteEvenIfDefined = false)
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
            $container->setParameter("env({$environmentKey})", getenv($environmentKey));
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
        $keyValue = getenv($environmentKey);

        if (false === $keyValue) {
            return false;
        }

        return true;
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
        putenv("{$envKey}={$kvValue}");
    }
}
