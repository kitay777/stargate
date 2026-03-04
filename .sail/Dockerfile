FROM laravelsail/php83-composer

# Node.js v20 をインストール（v22はesbuild非対応）
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get update \
    && apt-get install -y nodejs

