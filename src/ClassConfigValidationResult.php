<?php

namespace LittleGiant\SilverStripe\ConfigValidator;

use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\DebugView;

/**
 * Validation result for a class config with an add-only API.
 *
 * @package LittleGiant\SilverStripe\ConfigValidator
 */
class ClassConfigValidationResult
{
    const CALLER = 'caller';
    const MESSAGE = 'message';

    /**
     * Class that this validation result is for.
     * @var string
     */
    protected $class;

    /**
     * Errors for the class config. Keys are config parameters, values are an array where each entry has this schema:
     * [
     *      'caller'  => [ trace to caller of addError ],
     *      'message' => error message string,
     * ]
     * @var array
     */
    protected $errors = [];

    /**
     * ClassConfigValidationResult constructor.
     * @param string $class
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    /**
     * Add an error for a config value.
     * @param string $configName Parameter name.
     * @param string $message Error message.
     * @return void
     */
    public function addError($configName, $message)
    {
        if (!isset($this->errors[$configName])) {
            $this->errors[$configName] = [];
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        $caller = $trace[1];
        $caller['line'] = $trace[0]['line'];

        $this->errors[$configName][] = [
            static::CALLER  => $caller,
            static::MESSAGE => $message,
        ];
    }

    /**
     * Whether or not $class has any config errors.
     * @return bool
     */
    public function isValid()
    {
        return empty($this->errors);
    }

    /**
     * Merge another config validation result for the same class.
     * @param ClassConfigValidationResult $other
     * @return void
     */
    public function merge(ClassConfigValidationResult $other)
    {
        if ($other->getClass() !== $this->getClass()) {
            throw new \InvalidArgumentException("ClassConfigValidationResults can only be merged if they're for the same class.");
        }

        $this->errors = array_merge_recursive($this->errors, $other->getErrors());
    }

    /**
     * Get the class being validated.
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Get any config errors for the class being validated.
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Render config errors to debug view.
     * @param DebugView $view
     * @return void
     */
    public function renderErrors(DebugView $view)
    {
        if ($this->isValid()) return;

        echo $view->renderInfo("{$this->getClass()} failed config validation", '');
        $inlinePreStyle = 'display:inline;padding:0 6px';
        $classConfig = Config::forClass($this->getClass());

        foreach ($this->getErrors() as $configName => $errors) {
            // Config variable invalid header
            echo <<<EOT
<h3><pre style="{$inlinePreStyle}">{$this->getClass()}::\${$configName}</pre> is invalid</h3>
EOT;

            // Config variable value
            echo $view->renderVariable($classConfig->get($configName), [
                'file' => $this->getClass(),
                'line' => ':$' . $configName,
            ]);

            // List of errors
            echo '<ul>';
            foreach ($errors as $error) {
                $caller = $error[static::CALLER];
                echo <<<EOT
<li>
  {$error[static::MESSAGE]}
  (<pre style="{$inlinePreStyle}">{$caller['class']}{$caller['type']}{$caller['function']}() - Line {$caller['line']}</pre>)
</li>
EOT;
            }
            echo '</ul><hr>';
        }
    }
}
