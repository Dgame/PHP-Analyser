<?php

require_once 'config.php';
require_once 'Detect.php';
require_once 'Detection.php';

final class Detector
{
    private $_options    = 0;
    private $_detections = [];

    public function __construct(int $options)
    {
        $this->_options = $options;
    }

    public function addDetection(Token $token, string $msg, int $option)
    {
        $this->_detections[] = new Detection($token, $msg, $option);
    }

    public function detectIn(Scopes $scopes)
    {
        foreach ($this->_detections as $detection) {
            if ($this->_options & $detection->option) {
                printf(PRINT_FORMAT, $detection->msg, $detection->line);
            }
        }

        foreach ($scopes->getAllScopes() as $scope) {
            foreach ($scope->variables as $var) {
                if ($this->_options & Detect::Undefined) {
                    $this->_detectUndefined($var);
                }

                if ($this->_options & Detect::Uninitialized) {
                    $this->_detectUninitialized($var);
                }

                if ($this->_options & Detect::Unused) {
                    $this->_detectUnused($var);
                }
            }
        }
    }

    private function _detectUndefined(Variable $var)
    {
        if (!$var->defined && ($var->assignment || $var->property || $var->parameter)) {
            printf(PRINT_FORMAT, $var . ' is undefined', $var->line);
        }
    }

    private function _detectUninitialized(Variable $var)
    {
        if (!$var->initialized) {
            printf(PRINT_FORMAT, $var . ' is unintialized', $var->line);
        }
    }

    private function _detectUnused(Variable $var)
    {
        if ($var->usage == 0 && $var->defined && $var->protection != T_PUBLIC && $var->state != T_ABSTRACT) {
            printf(PRINT_FORMAT, $var . ' is unused', $var->line);
        }
    }
}
