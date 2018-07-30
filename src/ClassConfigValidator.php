<?php

namespace LittleGiant\SilverStripe\ConfigValidator;

use SilverStripe\Core\Config\Config_ForClass;

/**
 * Interface ClassConfigValidator
 *
 * @package LittleGiant\SilverStripe\ConfigValidator
 */
interface ClassConfigValidator
{
    /**
     * @return string[]
     */
    public static function getConfigValidatedClasses();

    /**
     * @param string $className
     * @param Config_ForClass $config
     * @param ClassConfigValidationResult $result
     * @return void
     */
    public static function validateClassConfig($className, Config_ForClass $config, ClassConfigValidationResult $result);
}
