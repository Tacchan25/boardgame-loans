<?php
// compile_mo.php
if (!defined('ABSPATH')) exit;

function bg_loans_compile_mo($pofile, $mofile) {
    if (!file_exists($pofile)) return;
    $hash = array();
    $content = file_get_contents($pofile);

    $blocks = explode("\n\n", $content);
    foreach ($blocks as $block) {
        if (preg_match('/msgid "(.*?)"\r?\nmsgstr "(.*?)"/s', $block, $matches)) {
            $id = str_replace(array('\n', '\"'), array("\n", '"'), $matches[1]);
            $str = str_replace(array('\n', '\"'), array("\n", '"'), $matches[2]);
            if (!empty($id)) $hash[$id] = $str;
        }
    }

    $out = fopen($mofile, 'wb');
    if (!$out) return;
    fwrite($out, pack('V', 0x950412de)); // magic
    fwrite($out, pack('V', 0)); // rev
    $count = count($hash);
    fwrite($out, pack('V', $count));
    $offset_orig = 28;
    fwrite($out, pack('V', $offset_orig));
    $offset_trans = 28 + ($count * 8);
    fwrite($out, pack('V', $offset_trans));
    fwrite($out, pack('V', 0));
    fwrite($out, pack('V', $offset_trans + ($count * 8)));

    ksort($hash);

    $orig_table = '';
    $trans_table = '';
    $strings = '';
    $string_offset = $offset_trans + ($count * 8);

    foreach ($hash as $id => $str) {
        $len = strlen($id);
        $orig_table .= pack('V', $len);
        $orig_table .= pack('V', $string_offset);
        $strings .= $id . "\0";
        $string_offset += $len + 1;
    }
    foreach ($hash as $id => $str) {
        $len = strlen($str);
        $trans_table .= pack('V', $len);
        $trans_table .= pack('V', $string_offset);
        $strings .= $str . "\0";
        $string_offset += $len + 1;
    }
    fwrite($out, $orig_table);
    fwrite($out, $trans_table);
    fwrite($out, $strings);
    fclose($out);
}
