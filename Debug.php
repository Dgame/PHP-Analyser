<?php

require_once PHP_ANALYSER_PATH . 'Options.php';

final class Debug
{
    const VarExists          = 'Found existing Variable %s increase usage: %d';
    const VarExistsUndefined = 'Found existing but undefined Variable %s';
    const VarNew             = 'Found new Variable %s';

    const PropertyExists          = 'Found existing Property %s increase usage: %d';
    const PropertyExistsUndefined = 'Found existing but undefined property %s';
    const PropertyNew             = 'Found new Property %s';

    const PropertyStaticExists = 'Found existing static Property %s increase usage: %d';
    const PropertyStaticNew   = 'Found new static Property %s';

    const ParamNew = 'Found parameter %s';

    private $_options = 0;

    public function __construct(int $options)
    {
        $this->_options = $options;
    }

    public function log(string $id, int $line, string $msg, ...$args)
    {
        if ($this->_options & (Options::Verbose | Options::Debug)) {
            $msg = vsprintf($msg, $args);
            printf(DEBUG_PRINT_FORMAT, $id, $line, $msg);
        }
    }
}
