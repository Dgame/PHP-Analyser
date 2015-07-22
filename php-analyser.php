<?php

define('PHP_ANALYSER_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

require_once PHP_ANALYSER_PATH . 'config.php';
require_once 'Parser.php';

print DEBUG;

if (DEBUG) {
    $p = new Parser(Detect::All);
    $p->parse('test.php', Options::None);
} else {
    $commands = [
        'detect-all' => 'Detect all supported options',
        'no-init'    => 'Detects uninitialized variables',
        'undef'      => 'Detects undefined variables',
        'unused'     => 'Detects unused variables',
        'spell'      => 'Detects misspelled words',
        'verbose'    => 'Verbose output',
        'debug'      => 'Debug mode',
        'help'       => 'Print this information'
    ];

    function usage()
    {
        global $commands;

        print 'Valid options are:' . PHP_EOL;
        foreach ($commands as $cmd => $msg) {
            print "\t" . $cmd . "\t\t" . $msg . PHP_EOL;
        }
    }

    $cmd = getopt(null, array_keys($commands));

    $options = Options::None;
    $detect  = Detect::None;

    foreach ($cmd as $key => $_) {
        $index = array_search('--' . $key, $argv);
        unset($argv[$index]);

        switch ($key) {
            case 'detect-all':
                $detect = Detect::All;
            break;
            case 'no-init':
                $detect |= Detect::Uninitialized;
            break;
            case 'undef':
                $detect |= Detect::Undefined;
            break;
            case 'unused':
                $detect |= Detect::Unused;
            break;
            case 'spell':
                $detect |= Detect::PossibleMisspelling;
            break;
            case 'verbose':
                $options |= Options::Verbose;
            break;
            case 'debug':
                $options |= Options::Debug;
            break;
            case 'help':
                print usage();
                exit;
        }
    }

    array_shift($argv); // remove main.php

    if ($detect != Detect::None && !empty($argv)) {
        $p = new Parser($detect);
        foreach ($argv as $arg) {
            if (is_file($arg) && substr($arg, -4) == '.php') {
                $p->parse($arg, $options);
            }
        }
    }
}
