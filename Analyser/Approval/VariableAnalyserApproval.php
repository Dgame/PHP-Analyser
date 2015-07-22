<?php

require_once 'Approval.php';

final class VariableAnalyserApproval extends Approval
{
    private static $Properties = [
        T_PRIVATE   => true,
        T_PROTECTED => true,
        T_PUBLIC    => true,
    ];

    public function approve(Cursor $cursor, Scope $scope)
    {
        $token = $cursor->getCurrent();

        if ($token->id == '$this') {
            return $this->_approveThisProperty($cursor);
        }

        if (!Variable::Approve($token->id)) {
            return false; // don't match super globals, they are magic
        }

        // look behind
        $prev = $cursor->lookBehind();
        if (array_key_exists($prev->type, self::$Properties)) {
            return false; // Properties are already visited
        }

        // ignore static variables only if they are properties, because we already scanned them
        if ($prev->type == T_STATIC && $scope->previous && $scope->previous->token == T_CLASS) {
            return false;
        }

        return true;
    }

    private function _approveThisProperty(Cursor $cursor)
    {
        $cursor->pushPosition();
        $cursor->next(); // jump over '$this'

        $tok = $cursor->getCurrent();
        if ($tok->type != T_OBJECT_OPERATOR) {
            return true;
        }

        $cursor->next(); // jump over '->'

        $tok  = $cursor->getCurrent(); // $this->[...]
        $next = $cursor->lookAhead(); // $this->...[...]

        if ($tok->type == T_OPEN_CURLY || $tok->type == T_VARIABLE || $next->type == T_OPEN_PAREN) {
            $cursor->popPosition();
            // it's a function or runtime call
            return false;
        }

        $cursor->popPosition();

        return true;
    }
}
