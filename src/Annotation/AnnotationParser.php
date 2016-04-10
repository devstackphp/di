<?php
/**
 * This file is part of the Stack package.
 *
 * (c) Andrzej Kostrzewa <andkos11@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stack\DI\Annotation;

/**
 * This class provides extensions to PHP's built in reflection capabilities.
 *
 * @author Andrzej Kostrzewa <andkos11@gmail.com>
 */
final class AnnotationParser
{

    const ANNOTATION_REGEX = '/(\w+)(?:\s*(?:\(\s*)?(.*?)(?:\s*\))?)??\s*(?:\n|\*\/)/';

    const NAMESPACE_REGEX = '/^([a-zA-Z0-9\\\\]+)\\\/';

    const PARAMETER_REGEX = '/(\w+)\s*=\s*(\[[^\]]*\]|"[^"]*"|[^,)]*)\s*(?:,|$)/';

    /**
     * @var bool
     */
    private static $hasNamespace = true;

    /**
     * Get annotations from document comments.
     *
     * @param $docComment
     * @return array|null
     */
    public static function getAnnotations($docComment)
    {
        $varPosition = strpos($docComment, '@var');
        if ($varPosition === false) {
            return [];
        }

        $length = 4;
        $namespace = '';
        $namespacePosition = strpos($docComment, '\\Stack\\');
        if ($namespacePosition !== false) {
            self::$hasNamespace = preg_match(
                self::NAMESPACE_REGEX,
                ltrim(substr($docComment, $varPosition + 4)),
                $matches
            );
            $length = strlen($matches[0]) + 4;
            $namespace = $matches[0];
        }

        $hasAnnotations = preg_match_all(
            self::ANNOTATION_REGEX,
            ltrim(substr($docComment, $varPosition + $length)),
            $matches,
            PREG_SET_ORDER
        );

        if (!$hasAnnotations || empty($matches)) {
            return [];
        }

        $annotations = [];
        foreach ($matches as $annotation) {
            $annotationName = $namespace . $annotation[1];
            $value = true;

            if (isset($annotation[2])) {
                $value = self::extractValue($annotation[2]);
            }

            $annotations[$annotationName] = $value;

        }

        return $annotations;
    }

    /**
     * Returns true if the namespace exist.
     * Returns false otherwise.
     *
     * @return bool
     */
    public static function hasNamespace()
    {
        return self::$hasNamespace;
    }

    /**
     * Extract parameter from document comments.
     *
     * @param $annotation
     * @return array|bool|float|int|string
     */
    private static function extractValue($annotation)
    {
        $hasParams = preg_match_all(self::PARAMETER_REGEX, $annotation, $params, PREG_SET_ORDER);
        if ($hasParams) {
            $value = [];
            foreach ($params as $param) {
                $value[$param[1]] = self::parseValue($param[2]);
            }

            return $value;
        }

        $value = trim($annotation);
        if ($value === '') {
            return true;
        }

        return self::parseValue($value);
    }

    /**
     * Parse value by type and return.
     *
     * @param $value
     * @return array|bool|float|int|string
     */
    public static function parseValue($value)
    {
        $value = trim($value);

        $isNumberOrBool = function (&$value) {
            if (is_numeric($value)) {
                $value = (float) $value;

                if ((float) $value == (int) $value) {
                    $value = (int) $value;
                }

                return true;
            }

            $isBool = function (&$value) {
                if (strtolower($value) == 'true') {
                    $value = true;

                    return true;
                }

                if (strtolower($value) == 'false') {
                    $value = false;

                    return true;
                }

                return false;
            };

            return $isBool($value);
        };

        $isArrayOrOther = function (&$value) {
            if (substr($value, 0, 1) === '[' && substr($value, -1) === ']') {
                $valuesArray = explode(',', substr($value, 1, -1));
                $value = [];
                foreach ($valuesArray as $val) {
                    $value[] = self::parseValue($val);
                }

                return true;
            }

            if (substr($value, 0, 1) == '"' && substr($value, -1) == '"' ||
                substr($value, 0, 1) == '\'' && substr($value, -1) == '\'') {
                $value = substr($value, 1, -1);
                $value = self::parseValue($value);

                return true;
            }

            return false;
        };

        if ($isArrayOrOther($value)) {
            return $value;
        }

        if ($isNumberOrBool($value)) {
            return $value;
        }

        return $value;
    }
}
