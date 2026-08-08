<?php

declare(strict_types=1);

namespace Vendor\NeoPHP\PasswordStrengthPackage;

use Neo\Core\Package\Abstract\AbstractPackage;

final class NeoPasswordStrengthPackage extends AbstractPackage
{
    public function getName(): string
    {
        return 'PasswordStrength';
    }

    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}