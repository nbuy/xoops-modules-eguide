#!/usr/bin/php -q
<?php
// only command line execute this script
if (isset($_SERVER['HTTP_HOST'])) {
    exit;
}
define('ORIGIN_NAME', 'eguide');

$prog = array_shift($argv);
if (count($argv) < 1) {
    echo "usage: $prog [-v] dirname ...\n";
    echo " dirname: /^[a-zA-Z0-9_]+\$/\n";
    exit;
}

# for duplicatable (not D3, old style)
$modulePath = __DIR__;
$myprefix   = $moduleDirName = basename($modulePath);

chdir($modulePath);

// make target file lists

// force writable permission files
$writable = [];

// rewrite prefix files
$modifies = [];
foreach (find_file('templates', ORIGIN_NAME . '_*.(html|xml)') as $sfile) {
    $modifies[$sfile] = preg_replace('/\/' . preg_quote(ORIGIN_NAME) . '_/', '/{prefix}_', $sfile);
}
$modifies['sql/mysql.sql'] = 'sql/mysql_{prefix}.sql';

/**
 * @param      $dir
 * @param      $writable
 * @param      $files
 * @param bool $verb
 */
function duplicate($dir, $writable, $files, $verb = false)
{
    $base = '../' . $dir;
    $id   = preg_replace('/^\D+/', '', $dir);

    if (!empty($id)) {
        $id = (int)$id;
    }
    if (!is_dir($base)) {
        mkdir($base);
    }
    $base .= '/';
    foreach (find_file() as $name) {
        if ($verb) {
            echo "  dup: $name\n";
        }
        if (is_dir($name)) {
            if (!@mkdir($base . $name)) {
                echo "mkdir error: $name\n";
            }
        } elseif (!@link($name, $base . $name)) {
            echo "link error: $name\n";
        }
    }

    foreach ($writable as $i) {
        $f = $base . $i;
        if ($verb) {
            echo "  writable: $f\n";
        }
        if (is_dir($f)) {
            chmod($f, 0777);
        } else {
            chmod($f, 0666);
        }
    }

    foreach ($files as $file => $dest) {
        $newfile = $base . preg_replace('/{prefix}/', $dir, $dest);
        $body    = file_get_contents($file);
        if (preg_match('/\.sql$/', $file)) {
            if ($verb) {
                echo "  $file -> $newfile\n";
            }
            $body = preg_replace('/\\s' . preg_quote(ORIGIN_NAME) . '([\s_])/', " $dir$1", $body);
        } elseif (preg_match('/\.html$/', $file)) {
            $body = preg_replace('/' . preg_quote(ORIGIN_NAME) . '_/', $dir . '_', $body);
            if ($verb) {
                echo "  $file -> $newfile\n";
            }
        }
        file_put_contents($newfile, $body);
    }
}

/**
 * @param string $dir
 * @param string $pat
 * @return array
 */
function find_file($dir = '.', $pat = '')
{
    $files = [];
    $dh    = opendir($dir);
    $reg   = preg_replace(['/\./', '/\*/'], ['\.', '.*'], $pat);
    while ($file = readdir($dh)) {
        if ('.' === $file || '..' === $file) {
            continue;
        }
        if (preg_match('/~$/', $file)) {
            continue;
        }
        $path = preg_replace('/\.\//', '', "$dir/$file");
        if (is_dir($path)) {
            if ('CVS' === $file) {
                continue;
            }
            if (!$pat) {
                $files[] = $path;
            }
            $files = array_merge($files, find_file($path, $pat));
        } else {
            if ($pat && !preg_match("/^$reg$/", $file)) {
                continue;
            }
            $files[] = $path;
        }
    }
    closedir($dh);

    return $files;
}

if (!function_exists('file_put_contents')) {
    // php 4.3.11 no have?
    /**
     * @param $file
     * @param $text
     */
    function file_put_contents($file, $text)
    {
        $fp = fopen($file, 'w');
        fwrite($fp, $text);
        fclose($fp);
    }
}

$verb = false;
if ('-v' === $argv[0]) {
    array_shift($argv);
    $verb = true;
}

foreach ($argv as $dir) {
    if (preg_match('/^[a-zA-Z0-9_]+$/', $dir)) {
        echo "Duplicate: $dir\n";
        duplicate($dir, $writable, $modifies, $verb);
    } else {
        echo "Error dirname: $dir\n";
    }
}
?>
