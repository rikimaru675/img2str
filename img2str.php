<?php
require_once 'vendor/autoload.php';
use thiagoalessio\TesseractOCR\TesseractOCR;

$dirPath = '';
if (isset($argv[1])) {
    $dirPath = $argv[1];
} else {
    echo "usage : php {$argv[0]} [directory path]\n";
    exit();
}
if (!is_dir($dirPath)) {
    echo "usage : php {$argv[0]} [directory path]\n";
    exit();
}

$date = date('Ymd');
$outFileName = "{$date}.html";
$outFilePath = $dirPath . DIRECTORY_SEPARATOR . $outFileName;
$title = $outFileName;

/*
 * 画像から文字列を抽出
 * テーブルデータとして画像と文字列を並べる
*/
$num = 1;
$trtd = '';
$normalizer = new Normalizer();
$itr = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath));
foreach ($itr as $file) {
    if (!$file->isFile()) {
        continue;
    }
    // 画像ファイルかどうかチェック
    if (!exif_imagetype($file->getPathname())) {
        continue;
    }

    $filePath = $file->getPathname();
    $fileName = $file->getFilename();

    try {
        echo "{$fileName}\n";
        // OCRを使って画像から文字列を抽出
        $text = (new TesseractOCR($filePath))
            ->lang('jpn')
            ->psm(4)
            ->run();
                // psmメソッドのパラメータ
                // 0: 文字方向および書字系の検出のみ
                // 1: 自動ページセグメンテーション（OSDありでOCR）
                // 3: 完全自動ページセグメンテーション（OSDなし）（デフォルト）
                // 4: 単一カラムの様々なサイズのテキストとみなす
                // 6: 単一カラムの均一ブロックテキストとみなす
                // 7: 画像を単一行のテキストとして扱う
                // 8: 画像を単語1つのみ含まれるものとして扱う
        // 抽出結果の文字間に不要なスペースが含まれるため、スペースを除去
        $text = $normalizer->normalize($text);
        // 特殊文字をエスケープして、改行コードを<br>に変換
        $text = nl2br(htmlspecialchars($text));
        // テーブルデータを作成
        $trtd .= <<<EOD
            <tr>
                <td>{$num}</td>
                <td><img src="{$fileName}" alt="{$fileName}"></td>
                <td>{$text}</td>
            </tr>
            EOD;
        $num++;
    } catch (Exception $e) {
        echo "{$filePath} : OCR error.({$e->getMessage()})\n";
    }
}

$html = <<<EOD
    <!DOCTYPE html>
    <html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{$title}</title>
        <style>
            img {
                width: 400px;
                height: auto;
            }
        </style>
    </head>
    <body>
        <table border="1">
        <tr>
            <th>No.</th>
            <th>画像</th>
            <th>文字列</th>
        </tr>
        {$trtd}
        </table>
    </body>
    </html>
    EOD;

file_put_contents($outFilePath, $html);


/* 文字列の中の半角スペースを正規表現を使って削除するクラス */
class Normalizer {
    private $basic_latin = "\x{0000}-\x{007F}"; // 半角英数字(ASCII文字)
    private $blocks;
    private $patterns = [];

    public function __construct() {
        $this->blocks = implode('', [           // ひらがな・全角カタカナ・半角カタカナ・漢字・全角記号
            "\x{4E00}-\x{9FFF}",                // CJK UNIFIED IDEOGRAPHS
            "\x{3040}-\x{309F}",                // HIRAGANA
            "\x{30A0}-\x{30FF}",                // KATAKANA
            "\x{3000}-\x{303F}",                // CJK SYMBOLS AND PUNCTUATION
            "\x{FF00}-\x{FFEF}",                // HALFWIDTH AND FULLWIDTH FORMS
        ]);

        $pattern1 = "/([{$this->blocks}]) ([{$this->blocks}])/u";       // マルチバイト文字とマルチバイト文字間のスペースの場合
        $pattern2 = "/([{$this->blocks}]) ([{$this->basic_latin}])/u";  // マルチバイト文字とASCII文字間のスペースの場合
        $pattern3 = "/([{$this->basic_latin}]) ([{$this->blocks}])/u";  // ASCII文字とマルチバイト文字間のスペースの場合

        $this->patterns = [$pattern1, $pattern2, $pattern3];
    }

    public function normalize($text) {
        foreach ($this->patterns as $pattern) {
            $prev_text = '';
            while ($prev_text !== $text) {
                $prev_text = $text;
                $text = preg_replace($pattern, '$1$2', $text);
            }
        }
        return $text;
    }
}
