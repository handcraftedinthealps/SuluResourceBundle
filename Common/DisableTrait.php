<?php

declare(strict_types=1);

namespace HandcraftedInTheAlps\Bundle\SuluResourceBundle\Common;

trait DisableTrait
{
    /**
     * @var bool
     */
    private $disabled = false;

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): void
    {
        $this->disabled = $disabled;
    }
}
