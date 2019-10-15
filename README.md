# wp-checkin
A checking helper for [WordCamp Tokyo 2019](https://2019.tokyo.wordcamp.org)

## インストール

このリポジトリをクローンし、 `composer` および `npm` をインストールしてください。

```
git clone git@github.com:wct2019/wp-checkin.git
cd wp-checkin
composer install
npm install
```

- `public` 以下が公開用ディレクトリになります。

### Acknowledgement

以下のライブラリを利用しています。ありがとうございます。

- Slim
- React
- FontAwesome
- Twitter Bootstrap

## 開発

### 1. 認証ファイルを用意する

Firebaseとの連携のため、認証ファイルが必要です。 `wordcamptokyo2019app-firebase-key.json` というファイルを入手し、リポジトリのルートに入れてください。

### 2. ローカルサーバーをスタートする

- `npm start` で静的ファイルのビルドが開始されます。
- `npm run watch` で静的ファイルの監視がスタートします。
- `composer start` で `localhost:8080` にローカルサーバーが立ち上がります。これは本番データベースと接続するので、動作には注意してください。

### 3. 修正を送る

1. このリポジトリをフォークしてください。
2. `bugfix/what-you-fixed` といった形で `bugfix/修正した内容` のブランチを切り、masterブランチに対してプルリクエストを送ってください。

## ライセンス

GPL 3.0またはそれ以降。
