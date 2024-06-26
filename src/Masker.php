<?php
declare(strict_types=1);

namespace WallaceMaxters\Masker;

use ArgumentCountError;

class Masker
{
    public function __construct(
        public string $numberPlaceholder = '0',
        public string $characterPlaceholder = 'A',
        public bool $enableExceptions = false
    ) {
    }

    public function __invoke(?string $value, string $mask): string
    {
        return $this->mask($value, $mask);
    }

    public function mask(?string $value, string $mask): string
    {
        if ($value === null) return '';

        $format = $this->convertToInternalFormat($mask);

        try {
            return sprintf($format, ...$this->split($value));
        } catch (ArgumentCountError) {

            if ($this->enableExceptions) {
                throw new MaskException("The value $value is not compatible with $mask");
            }

            return (string) $value;
        }
    }

    /**
     * dynamic Format masks
     *
     * @throws MaskException
     * @param string $value
     * @param array $masks
     * @return string
     */
    public function dynamic(string $value, array $masks): string
    {
        $count = mb_strlen($value);

        foreach ($masks as $mask) {

            if ($count !== $this->countPlaceholderChars($mask)) continue;

            return $this->mask($value, $mask);
        }

        if ($this->enableExceptions) {
            throw new MaskException('The value is not compatible with masks');
        }

        return $value;
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

    protected function countPlaceholderChars(string $mask): int
    {
        return array_sum(
            array_map(fn ($placeholder) => substr_count($mask, $placeholder), [
                $this->numberPlaceholder,
                $this->characterPlaceholder
            ])
        );
    }

    protected function convertToInternalFormat(string $mask): string
    {
        return strtr($mask, [
            $this->numberPlaceholder    => '%1d',
            $this->characterPlaceholder => '%1s',
            // fix sprintf
            '%'                         => '%%'
        ]);
    }

    /**
     * Convert a string to Array of chars
     *
     * @param string $value
     * @return array
     */
    protected function split(string $value): array
    {
        preg_match_all('/./u', $value, $matches);

        return $matches[0] ?? [];
    }
}