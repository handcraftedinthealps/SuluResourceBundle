<?php

declare(strict_types=1);

namespace HandcraftedInTheAlps\Bundle\SuluResourceBundle\Payload;

use Webmozart\Assert\Assert;

trait PayloadTrait
{
    /**
     * @var mixed[]
     */
    protected $payload;

    /**
     * @var bool
     */
    protected $defaultThrowIfNotExists;

    /**
     * @param mixed[] $payload
     */
    public function initializePayloadTrait(array $payload = [], bool $defaultThrowIfNotExists = true): void
    {
        $this->payload = $payload;
        $this->defaultThrowIfNotExists = $defaultThrowIfNotExists;
    }

    /**
     * @return mixed[]
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    public function keyExists(string $key): bool
    {
        return \array_key_exists($key, $this->payload);
    }

    /**
     * @return mixed
     */
    public function getValue(string $key, ?bool $throwIfNotExists = null)
    {
        if (null === $throwIfNotExists) {
            $throwIfNotExists = $this->defaultThrowIfNotExists;
        }

        if ($throwIfNotExists) {
            Assert::keyExists($this->payload, $key);
        }

        return $this->payload[$key] ?? null;
    }

    public function getBoolValue(string $key): bool
    {
        $value = $this->getValue($key, true);

        Assert::boolean($value);

        return $value;
    }

    public function getNullableBoolValue(string $key, ?bool $throwIfNotExists = null): ?bool
    {
        if (null === $throwIfNotExists) {
            $throwIfNotExists = $this->defaultThrowIfNotExists;
        }

        $value = $this->getValue($key, $throwIfNotExists);

        if (null === $value) {
            return null;
        }

        Assert::boolean($value);

        return $value;
    }

    public function getStringValue(string $key): string
    {
        $value = $this->getValue($key, true);

        Assert::string($value);

        return $value;
    }

    public function getNullableStringValue(string $key, ?bool $throwIfNotExists = null): ?string
    {
        if (null === $throwIfNotExists) {
            $throwIfNotExists = $this->defaultThrowIfNotExists;
        }

        $value = $this->getValue($key, $throwIfNotExists);

        if (null === $value) {
            return null;
        }

        Assert::string($value);

        return $value;
    }

    public function getDateTimeValueValue(string $key): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->getStringValue($key));
    }

    public function getNullableDateTimeValue(string $key, ?bool $throwIfNotExists = null): ?\DateTimeImmutable
    {
        if (null === $throwIfNotExists) {
            $throwIfNotExists = $this->defaultThrowIfNotExists;
        }

        $value = $this->getNullableStringValue($key, $throwIfNotExists);

        if (!$value) {
            return null;
        }

        return new \DateTimeImmutable($value);
    }

    public function getFloatValue(string $key): float
    {
        $value = $this->getValue($key, true);

        if (\is_int($value)) {
            $value = (float) $value;
        }

        Assert::float($value);

        return $value;
    }

    public function getNullableFloatValue(string $key, ?bool $throwIfNotExists = null): ?float
    {
        if (null === $throwIfNotExists) {
            $throwIfNotExists = $this->defaultThrowIfNotExists;
        }

        $value = $this->getValue($key, $throwIfNotExists);

        if (null === $value) {
            return null;
        }

        if (\is_int($value)) {
            $value = (float) $value;
        }

        Assert::float($value);

        return $value;
    }

    public function getIntValue(string $key): int
    {
        $value = $this->getValue($key, true);

        Assert::integer($value);

        return $value;
    }

    public function getNullableIntValue(string $key, ?bool $throwIfNotExists = null): ?int
    {
        if (null === $throwIfNotExists) {
            $throwIfNotExists = $this->defaultThrowIfNotExists;
        }

        $value = $this->getValue($key, $throwIfNotExists);

        if (null === $value) {
            return null;
        }

        Assert::integer($value);

        return $value;
    }

    /**
     * @return mixed[]
     */
    public function getArrayValue(string $key): array
    {
        $value = $this->getValue($key, true);

        Assert::isArray($value);

        return $value;
    }

    /**
     * @return mixed[]|null
     */
    public function getNullableArrayValue(string $key, ?bool $throwIfNotExists = null): ?array
    {
        if (null === $throwIfNotExists) {
            $throwIfNotExists = $this->defaultThrowIfNotExists;
        }

        $value = $this->getValue($key, $throwIfNotExists);

        if (null === $value) {
            return null;
        }

        Assert::isArray($value);

        return $value;
    }
}
