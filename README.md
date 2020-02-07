# My Total Comfort

This is a PHP library for working with [Honeywell's Total Connect Comfort interface](https://www.mytotalconnectcomfort.com) 
(TCC). It authenticates using user credentials and can provide data available through the web interface and more.    

Compatible with PHP >=5.6 

Install with Composer: 

    composer require tenth/my-total-comfort
    composer install --no-dev
    
(There are notable dev dependencies that you really should only install if you want to auto-generate [documentation](https://github.com/TenthPres/MyTotalComfort/wiki).)

## Cloning, Contributing and Developing

Please clone and contribute, or report bugs should you find them.  

## Documentation

This repository has a script ([makedocs.php](makedocs.php)) intended to automatically generate and publish documentation changes based on doc blocks.  You can find [that documentation in the Wiki](https://github.com/TenthPres/MyTotalComfort/wiki).
As far as the repository is concerned, the documentation directory is a submodule.  To clone everything all in one shot, add the `--recursive` switch to your clone command: 

    git clone --recursive https://github.com/TenthPres/MyTotalComfort.git

## Disclaimers

The contributors to this project are not affiliated with Honeywell.