<?php

interface Detect
{
    const None                = 0x0;
    const Uninitialized       = 0x1;
    const Undefined           = 0x2;
    const Unused              = 0x4;
    const PossibleMisspelling = 0x8;
    const All                 = self::Uninitialized |
                                self::Undefined |
                                self::Unused |
                                self::PossibleMisspelling;
    const Standard = self::Uninitialized | self::Undefined;
}
