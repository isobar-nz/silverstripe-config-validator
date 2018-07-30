<?php

namespace LittleGiant\SilverStripe\ConfigValidator;

use SilverStripe\Core\Config\Config_ForClass;

/**
 * Interface OwnConfigValidator
 *
 * @package LittleGiant\SilverStripe\ConfigValidator
 */
interface OwnConfigValidator
{
    /**
     * @param Config_ForClass $config
     * @param ClassConfigValidationResult $result
     * @return void
     */
    public static function validateConfig(Config_ForClass $config, ClassConfigValidationResult $result);
}
