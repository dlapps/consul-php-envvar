<?php
declare(strict_types = 1);

namespace DL\ConsulPhpEnvVar\Service;

use SensioLabs\Consul\Services\KV;

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
     * Get environment variables from Consul.
     *
     * @param array $mappings
     */
    public function getEnvVarsFromConsul(array $mappings)
    {
        foreach ($mappings as $envKey => $kvPath) {
            $keyExists = (false === getenv($envKey)) ? false : true;
            if ($keyExists && !$this->overwriteEvenIfDefined) {
                continue;
            }

            $kvValue = $this->kv->get($kvPath)->getBody();
            putenv("{$envKey}={$kvValue}");
        }
    }
}
