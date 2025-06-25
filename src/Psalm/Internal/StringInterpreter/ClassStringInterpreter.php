<?php

declare(strict_types=1);

namespace Psalm\Internal\StringInterpreter;

use Override;
use Psalm\Plugin\EventHandler\Event\StringInterpreterEvent;
use Psalm\Plugin\EventHandler\StringInterpreterInterface;
use Psalm\Internal\Codebase\Reflection;
use Psalm\Type\Atomic\TLiteralClassString;
use Psalm\Type\Atomic\TLiteralString;

/**
 * @internal
 */
final class ClassStringInterpreter implements StringInterpreterInterface
{
    #[Override]
    public static function getTypeFromValue(StringInterpreterEvent $event): ?TLiteralString
    {
        $value = $event->getValue();
        if ($value === '') {
            return null;
        }

        $value = ltrim($value, '\\');
        $codebase = $event->getCodebase();
        $value_lc = strtolower($value);

        if ($codebase->classlikes->doesClassLikeExist($value_lc)) {
            if (!$codebase->classlike_storage_provider->has($value)) {
                $reflection = new Reflection($codebase->classlike_storage_provider, $codebase);
                try {
                    $reflection->registerClass(new \ReflectionClass($value));
                } catch (\ReflectionException) {
                    return null;
                }
            }

            return new TLiteralClassString($value);
        }

        return null;
    }
}
