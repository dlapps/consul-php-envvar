<?php
declare(strict_types = 1);

namespace DL\ConsulPhpEnvVar\Builder;

use DL\ConsulPhpEnvVar\Enum\ManagerDefaults;
use DL\ConsulPhpEnvVar\Service\ConsulEnvManager;
use SensioLabs\Consul\ServiceFactory;

/**
 * Assists with the construction of Consul environment managers.
 *
 * @package DL\ConsulPhpEnvVar\Builder
 * @author  Petre Pătrașc <petre@dreamlabs.ro>
 */
class ConsulEnvManagerBuilder
{
    /**
     * @var string
     */
    protected $consulServer = ManagerDefaults::CONSUL_SERVER_DEFAULT;

    /**
     * @var bool
     */
    protected $overwriteEvenIfDefined = ManagerDefaults::OVERWRITE_DEFAULT;

    /**
     * Build the manager with the properties that have been defined.
     *
     * @return ConsulEnvManager
     */
    public function build(): ConsulEnvManager
    {
        $kv = (new ServiceFactory([
            'base_uri' => $this->consulServer
        ]))->get('kv');

        $manager = new ConsulEnvManager(
            $kv,
            $this->overwriteEvenIfDefined
        );

        $this->clear();

        return $manager;
    }

    /**
     * Provide the URL of a Consul server.
     *
     * @param string $consulServer
     *
     * @return ConsulEnvManagerBuilder
     */
    public function withConsulServer(string $consulServer): ConsulEnvManagerBuilder
    {
        $this->consulServer = $consulServer;

        return $this;
    }

    /**
     * Enable the flag that redefines environmental variables
     * even if they have been previously defined.
     *
     * @param bool $overwriteEvenIfDefined
     *
     * @return ConsulEnvManagerBuilder
     */
    public function withOverwriteEvenIfDefined(bool $overwriteEvenIfDefined): ConsulEnvManagerBuilder
    {
        $this->overwriteEvenIfDefined = $overwriteEvenIfDefined;

        return $this;
    }

    /**
     * Clear all of the fields to their default values.
     * Used for long-running PHP or Singletons.
     */
    private function clear()
    {
        $this->consulServer           = ManagerDefaults::CONSUL_SERVER_DEFAULT;
        $this->overwriteEvenIfDefined = ManagerDefaults::OVERWRITE_DEFAULT;
    }
}
