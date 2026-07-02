<?php

declare(strict_types = 1);

namespace Northrook\PHPStan\RequiresMemberRule;

use PHPStan\ShouldNotHappenException;
use Stringable;

/**
 * @internal
 * @used-by \Northrook\PHPStan\RequiresMemberRule\RequiredMember
 */
final readonly class MemberDefinition
{
    /**
     * @param string[]  $required
     * @param string[]  $missing
     * @param string[]  $declared
     * @param string[]  $unexpected
     */
    private function __construct(
        public array $required,
        public array $missing,
        public array $declared,
        public array $unexpected,
    ) {}

    public function required(string $separator = '|'): string
    {
        return \implode($separator, $this->required);
    }

    public function missing(string $separator = '|'): string
    {
        return \implode($separator, $this->missing);
    }

    public function declared(string $separator = '|'): string
    {
        return \implode($separator, $this->declared);
    }

    public function unexpected(string $separator = '|'): string
    {
        return \implode($separator, $this->unexpected);
    }

    /**
     * @param array<array-key, mixed>  $required
     * @param array<array-key, mixed>  $declared
     *
     * @return null|MemberDefinition
     * @throws ShouldNotHappenException
     */
    public static function validate(array $required, array $declared): null|MemberDefinition
    {
        // Bail early
        if ($required === []) {
            return null;
        }

        // Stringify valid values
        $required = self::prepare($required);
        $declared = self::prepare($declared);

        // Compare
        $missing    = \array_diff($required, $declared);
        $unexpected = \array_diff($declared, $required);

        $unexpected = \array_flip($unexpected);
        unset($unexpected['public'], $unexpected['protected'], $unexpected['private']);
        $unexpected = \array_flip($unexpected);

        if ($missing === [] && $unexpected === []) {
            return null;
        }

        return new MemberDefinition($required, $missing, $declared, $unexpected);
    }

    /**
     * Normalize and sort values by known definition order.
     *
     * @param array<array-key, mixed>  $array
     *
     * @return string[]
     * @throws ShouldNotHappenException
     */
    private static function prepare(array $array): array
    {
        /** @var array<string,null> $prepared */
        $prepared = [
            'static'    => null,
            'final'     => null,
            'readonly'  => null,
            'abstract'  => null,
            'public'    => null,
            'protected' => null,
            'private'   => null,
            'null'      => null,
            'bool'      => null,
            'true'      => null,
            'false'     => null,
            'string'    => null,
            'int'       => null,
            'float'     => null,
            'array'     => null,
            'object'    => null,
        ];

        foreach ($array as $key => $value) {
            $value = match (\gettype($value)) {
                'NULL'                        => 'null',
                'boolean'                     => $value ? 'true' : 'false',
                'string', 'integer', 'double' => (string) $value,
                'object' => $value instanceof Stringable ? $value->__toString() : \get_class($value),
                default                       => throw new ShouldNotHappenException(
                    self::class . ' was provided an unexpected value at key `' . $key . '`. in '
                        . \var_export($array, true),
                ),
            };

            $value = \str_replace(['?', '[]'], ['null', 'array'], \strtolower($value));

            $prepared[$value] ??= $value;
        }

        return \array_values(\array_flip(\array_filter($prepared)));
    }
}
