# img2str

## 機能
ディレクトリ内に保存された画像を読み込み、画像から文字列を抽出。\
抽出元の画像と抽出した文字列を並べて表示するHTMLファイルを出力する。

## 目的
Uber Eatsの各種表示のスクリーンショットの内容を\
Excelにまとめるために手入力していたが、データ入力の負荷が重い。\
データ入力の負荷軽減のために、本プログラムを作成。\
必要な文字列をコピー＆ペーストと誤検知の修正、体裁を整えるだけで済むようになった。

## 使用方法
PHPとTesseract-OCRをインストールした環境において、コマンドラインから下記のコマンドを実行する。\
指定したディレクトリ内に「yyyymmdd.html」のHTMLファイルが出力される。(yyyymmddは実行した年月日)
```
php img2str.php [ディレクトリパス]
```
## 動作確認環境
- OS:Windows 10 Pro (22H2)
- CPU:Intel Core i7-6700HQ
- メモリ:16GB
- XAMPP:3.3.0
- PHP:8.2.12        →XAMPPのPHPを使用
- Tesseract-OCR:5.5.0.20241111
- Composer:2.8.5    →Tesseract-OCRのラッパを使用するため

## 使用ツール類
- XAMPP\
https://www.apachefriends.org/jp/index.html

- Tesseract-OCR\
https://github.com/tesseract-ocr/tessdoc

- Tesseract-OCRベスト版学習データ\
https://github.com/tesseract-ocr/tessdata_best

- Composer\
https://getcomposer.org/

