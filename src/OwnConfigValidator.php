<?php

namespace LittleGiant\SilverStripe\ConfigValidator;

use SilverStripe\Core\Config\Config_ForClass;

/**
 * Interface for classes that will validate their own configuration.
 *
 * @package LittleGiant\SilverStripe\ConfigValidator
 */
interface OwnConfigValidator
{
    /**
     * @param Config_ForClass $config Configuration for the class.
     * @param ClassConfigValidationResult $result Validation result to write errors to.
     * @return void
     */
    public static function validateConfig(Config_ForClass $config, ClassConfigValidationResult $result);
}
