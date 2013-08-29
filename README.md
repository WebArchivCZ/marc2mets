Installation
============

Checkout code from git or unpack it from archive.

```sh
# download and install composer
curl -sS https://getcomposer.org/installer | php

# install dependencies
php composer.phar install --no-dev

# create directories
mkdir temp/cache log -p

# copy example configuration
cp app/config/config.local.example.neon app/config/config.local.neon

# setup at least wadmin database connection
vim app/config/config.local.neon

```

Edit `app/config/config.local.neon` and set wadmin database parameters. For additional configuration options look at `app/config/config.neon` `parameters` section.

```yaml
parameters:
	wadmin:
		host: 127.0.0.1
		dbname: ...
		user: ...
		password: ...

```

Usage
=====

```sh
./bin convert [-d|--directory directory] [-l|--list [-f|--full-path]] [-q|--quiet]
```

Default directory is current directory.
By default urls are constructed starting from `[directory/]jobs/[tld]/[2nd level domain]/[3rd level domain]` and so on.
Directory must include at least one file matching settings file pattern `Settings*.xml`.

Help
====

```sh
./bin help [convert]
```
