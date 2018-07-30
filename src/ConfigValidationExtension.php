<?php

namespace LittleGiant\SilverStripe\ConfigValidator;

use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\Dev\CliDebugView;
use SilverStripe\Dev\DebugView;
use SilverStripe\Dev\DevBuildController;

/**
 * Class ConfigValidationExtension
 *
 * @package LittleGiant\SilverStripe\ConfigValidator
 * @property DevBuildController|ConfigValidationExtension $owner
 */
class ConfigValidationExtension extends Extension
{
    /**
     * @var ClassConfigValidationResult[]
     */
    protected $failedResults = [];

    /**
     * @param HTTPRequest $request
     * @param string $action
     */
    public function beforeCallActionHandler(HTTPRequest $request, $action)
    {
        if ($action !== 'build') return;

        foreach (ClassInfo::implementorsOf(OwnConfigValidator::class) as $classToValidate) {
            /** @var OwnConfigValidator $classToValidate */
            if (!ClassInfo::exists($classToValidate)) continue;

            $result = new ClassConfigValidationResult($classToValidate);
            $classToValidate::validateConfig(Config::forClass($classToValidate), $result);
            $this->mergeValidationResult($result);
        }

        foreach (ClassInfo::implementorsOf(ClassConfigValidator::class) as $validatorClass) {
            /** @var ClassConfigValidator $validatorClass */
            if (!ClassInfo::exists($validatorClass)) continue;

            foreach ($validatorClass::getConfigValidatedClasses() as $classToValidate) {
                if (!ClassInfo::exists($classToValidate)) continue;

                $result = new ClassConfigValidationResult($classToValidate);
                $validatorClass::validateClassConfig($classToValidate, Config::forClass($classToValidate), $result);
                $this->mergeValidationResult($result);
            }
        }

        if (!$this->isValid()) {
            $this->renderFailure($request);
            die();
        }
    }

    /**
     * @param ClassConfigValidationResult $result
     */
    protected function mergeValidationResult(ClassConfigValidationResult $result)
    {
        if ($result->isValid()) return;

        $class = $result->getClass();
        if (!isset($this->failedResults[$class])) {
            $this->failedResults[$class] = $result;
        } else {
            $this->failedResults[$class]->merge($result);
        }
    }

    /**
     * @return bool
     */
    protected function isValid()
    {
        return empty($this->failedResults);
    }

    /**
     * @param null|HTTPRequest $request
     */
    protected function renderFailure($request = null)
    {
        $view = Director::is_cli()
            ? CliDebugView::create()
            : DebugView::create();

        http_response_code(500);
        echo $view->renderHeader($request);
        echo $view->renderError('Config validation failed', E_ERROR, 'Build aborted', __FILE__, __LINE__);

        foreach ($this->failedResults as $result) {
            $result->renderErrors($view);
        }

        echo $view->renderFooter();
    }
}
