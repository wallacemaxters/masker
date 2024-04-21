<?php

namespace WallaceMaxters\Masker;

use ArgumentCountError;

class Masker
{
    public function __construct(
        public string $numberPlaceholder = '0',
        public string $characterPlaceholder = 'A',
        public bool $enableExceptions = false
    )
    {
    }

    public function mask(?string $value, string $mask): string
    {
        $format = $this->convertToInternalFormat($mask);

        try {
            return sprintf($format, ...str_split($value));
        } catch (ArgumentCountError) {

            if ($this->enableExceptions) {
                throw new MaskException("The value $value is not compatible with $mask");
            }

            return (string) $value;
        }
    }

    public function unmask(?string $value, string $mask): ?string
    {
        $result = $this->unmaskAsArray($value, $mask);

        return $result ? implode('', $result) : null;
    }

    public function unmaskAsArray(?string $value, string $mask): ?array
    {
        $result = sscanf($value,  $this->convertToInternalFormat($mask));

        if ($result === array_filter($result, fn ($v) => $v !== null)) {
            return $result;
        }

        if ($this->enableExceptions) {
            throw new UnmaskException("The value $value is not compatible with $mask");
        }

        return null;
    }

    protected function convertToInternalFormat(string $mask)
    {
        return strtr($mask, [
            $this->numberPlaceholder    => '%1d', 
            $this->characterPlaceholder => '%1s'
        ]);
    }
}
