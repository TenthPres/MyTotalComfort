# My Total Comfort

![Travis (.org)](https://img.shields.io/travis/tenthpres/mytotalcomfort?label=tests&style=flat-square)
![PHP from Travis config](https://img.shields.io/travis/php-v/tenthpres/mytotalcomfort?style=flat-square)
![Coveralls github](https://img.shields.io/coveralls/github/tenthpres/mytotalcomfort?style=flat-square)
<!--![GitHub](https://img.shields.io/github/license/tenthpres/mytotalcomfort?style=flat-square) -->


This is a PHP library for working with [Honeywell's Total Connect Comfort interface](https://www.mytotalconnectcomfort.com) 
(TCC). It authenticates using user credentials and can provide data available through the web interface and more.    

Compatible with PHP >=5.6

Install with Composer: 

    composer require tenth/my-total-comfort
    composer install --no-dev
    
(There are notable dev dependencies that you really should only install if you want to auto-generate [documentation](https://github.com/TenthPres/MyTotalComfort/wiki).)

## Cloning and Documenting

Please clone and contribute, or report bugs should you find them.  



This repository has a script ([makedocs.php](makedocs.php)) intended to automatically generate and publish documentation changes based on doc blocks.  You can find [that documentation in the Wiki](https://github.com/TenthPres/MyTotalComfort/wiki).
As far as the repository is concerned, the documentation directory is a submodule.  To clone everything all in one shot, add the `--recursive` switch to your clone command: 

    git clone --recursive https://github.com/TenthPres/MyTotalComfort.git
    
There's currently an issue when generating documentation in PHP 7.4 that makes the formatting very poor.  Other versions seem to work fine, and the package itself also works fine in 7.4.

## Disclaimers

The contributors to this project are not affiliated with Honeywell.