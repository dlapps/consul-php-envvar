<?php
declare(strict_types = 1);

namespace DL\ConsulPhpEnvVar\Enum;

/**
 * Holds defaults for defining manager settings.
 *
 * @package DL\ConsulPhpEnvVar\Enum
 * @author  Petre Pătrașc <petre@dreamlabs.ro>
 */
final class ManagerDefaults
{
    const CONSUL_SERVER_DEFAULT = 'http://127.0.0.1:8500';
    const OVERWRITE_DEFAULT = false;
}
