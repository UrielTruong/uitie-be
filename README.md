1. Setup to connect to sql from Azure (for mac)
- php 8.4.20
- install composer
- run cmd
	+ brew install autoconf automake libtool
	+brew tap microsoft/mssql-release https://github.com/Microsoft/homebrew-mssql-release
	+ brew install msodbcsql18 mssql-tools18
	+ sudo CXXFLAGS="-I/opt/homebrew/opt/unixodbc/include/" LDFLAGS="-L/opt/homebrew/lib/" pecl install sqlsrv
	+ sudo CXXFLAGS="-I/opt/homebrew/opt/unixodbc/include/" LDFLAGS="-L/opt/homebrew/lib/" pecl install pdo_sqlsrv

reference: https://learn.microsoft.com/en-us/sql/connect/php/installation-tutorial-linux-mac?view=sql-server-ver17

2. check file .env - paste this file to source code
3. download postman or bruno
    1. test - call API
        1. cmd - run: php artisan route:list --path=api
        2. get 2 endpoint to test
