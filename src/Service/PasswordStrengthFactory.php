<?php

declare(strict_types=1);

namespace Vendor\NeoPHP\PasswordStrengthPackage\Service;

final class PasswordStrengthFactory
{
    public function configure(): PasswordStrengthConfig
    {
        return new PasswordStrengthConfig();
    }
}