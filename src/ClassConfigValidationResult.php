<?php

namespace LittleGiant\SilverStripe\ConfigValidator;

use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\DebugView;

/**
 * Class ClassConfigValidationResult
 *
 * @package LittleGiant\SilverStripe\ConfigValidator
 */
class ClassConfigValidationResult
{
    const CALLER = 'caller';
    const MESSAGE = 'message';

    /**
     * @var string
     */
    protected $class;

    /**
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
     * @param string $configName
     * @param string $message
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
     * @return bool
     */
    public function isValid()
    {
        return empty($this->errors);
    }

    /**
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
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
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
