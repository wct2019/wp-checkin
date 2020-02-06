# wp-checkin
A check-in helper for [WordCamp Tokyo 2019](https://2019.tokyo.wordcamp.org)

## Install

You need PHP gRPC extension since wp-checkin uses [google/cloud-firestore](https://firebase-php.readthedocs.io/en/stable/cloud-firestore.html#getting-started). Install it using [this instruction](https://github.com/grpc/grpc/tree/master/src/php).

```
sudo pecl install grpc
```

Clone `wp-checkin` repository then install `composer` and `npm`.

```
git clone git@github.com:wct2019/wp-checkin.git
cd wp-checkin
composer install
npm install
```

- Pulbic direcotry is `public`.

### Acknowledgement

wp-checkin uses the following libraries. Thanks!

- Slim
- React
- FontAwesome
- Twitter Bootstrap

## Development

### 1. Place authentication file in root

To connect Firebase, you will need an authentication file. Place `wordcamptokyo2019app-firebase-key.json` file and place it in the root of your repository.

### 2. Start a local server

- `npm start` to start building static files.
- `npm run watch` to start monitoring.
- `composer start` to start a local server at `localhost:8080`. This will connect with live database so handle with care.

### 3. Fixing Issues

1. Folk this repository.
2. Create a branch (e.g. `bugfix/what-you-fixed`) and send a pull request to `master`.

## Live Site

- Our live site is [2019.tokyo.wp-checkin.com](https://2019.tokyo.wp-checkin.com).
- The site has Basic access authentication.
- Commit to `master` branch will be automatically deployed to the live site.

## License

GPL 3.0 or later.

---
## インストール

PHPのgRPC拡張必須です。[google/cloud-firestore](https://firebase-php.readthedocs.io/en/stable/cloud-firestore.html#getting-started)を使っているためです。インストール方法は[こちら](https://github.com/grpc/grpc/tree/master/src/php)をご覧ください。

```
sudo pecl install grpc
```

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

## 本番サイト

- [2019.tokyo.wp-checkin.com](https://2019.tokyo.wp-checkin.com) です。
- Basic認証がかかっています。
- masterブランチにコミットすると、勝手にデプロイされます。

## ライセンス

GPL 3.0またはそれ以降。
