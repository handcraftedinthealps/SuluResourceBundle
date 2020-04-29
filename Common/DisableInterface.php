<?php

declare(strict_types=1);

namespace HandcraftedInTheAlps\Bundle\SuluResourceBundle\Common;

interface DisableInterface
{
    public function isDisabled(): bool;

    public function setDisabled(bool $disabled): void;
}
