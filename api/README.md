Build it With Me API Configuration
=============

### 0. Requirements
* PHP
* MySQL Database
* A git repo
* PHP's GD Image library

***
### 1. Clone Repo
[https://bitbucket.org/madebyfold/builditwithme-webapp](https://bitbucket.org/madebyfold/builditwithme-webapp) 

Develop - for development
Master - to deploy

***
### 2. Import MySQL DB
Local: [Download Local DB](https://www.dropbox.com/s/lxpzfvvovhxs6by/bldwme_local_db_2015-10-15.sql?dl=0)

Dev : [Download Dev DB](https://www.dropbox.com/s/rz0520myiy08k66/bldwme_dev_db_2015-10-15.sql?dl=0)

 
***
### 3. Change api config

Change the settings at `config_template.php` and rename to `config.php`

For local changes for instance set the following:

```
$__serverEvironment__ = "local";
$__cdnFolder__ = "local/your_username/";	
```
This is your Amazon S3 (cdn) folder.

***
### 4. Set Apache Settings

http://stackoverflow.com/questions/7670561/how-to-get-htaccess-to-work-on-mamp

Change AllowOverride None to AllowOverride All

***
### 5. httpd.conf / httpd-vhosts.conf
```
<VirtualHost *:8888>
    DocumentRoot "path/to/gitrepo/public/"
    ServerName localhost
    ServerAlias builditwithme.dev
</VirtualHost>
```
##### Change Hosts, on a Mac type this in a terminal console
`nano /private/etc/hosts`
##### Then add the following line to the end of the file
`127.0.0.1 builditwithme.dev`

`ctrl + X` to close `nano editor`, save the file `y`.


You can now access it with [http://builditwithme.dev:8888](http://builditwithme.dev:8888) in the browser

***
### 6. Install the php GD image library

Necessary to process images.
[http://php.net/manual/en/book.image.php](http://php.net/manual/en/book.image.php)

***
### 7. Go
[http://builditwithme.dev:8888](http://builditwithme.dev:8888)