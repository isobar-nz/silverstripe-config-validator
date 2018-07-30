<?php

namespace LittleGiant\SilverStripe\ConfigValidator;

use SilverStripe\Core\Config\Config_ForClass;

/**
 * Interface for classes that will validate configuration for one or more other classes.
 *
 * @package LittleGiant\SilverStripe\ConfigValidator
 */
interface ClassConfigValidator
{
    /**
     * Get an array of class names whose configuration is validated by this class. Each class that exists will have its
     * config validated via calling validateClassConfig().
     * @see ClassConfigValidator::validateClassConfig()
     * @return string[]
     */
    public static function getConfigValidatedClasses();

    /**
     * Validate configuration for one of the classes returned by getConfigValidatedClasses().
     * @see ClassConfigValidator::getConfigValidatedClasses()
     * @param string $className Class name being validated (will be one of the classes returned by getConfigValidatedClasses()).
     * @param Config_ForClass $config Configuration for $class.
     * @param ClassConfigValidationResult $result Validation result to write errors to.
     * @return void
     */
    public static function validateClassConfig($className, Config_ForClass $config, ClassConfigValidationResult $result);
}
