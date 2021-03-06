# My Total Comfort

[![Travis (.org)](https://img.shields.io/travis/tenthpres/mytotalcomfort?label=tests&style=flat-square)](https://travis-ci.org/TenthPres/MyTotalComfort)
[![PHP from Travis config](https://img.shields.io/travis/php-v/tenthpres/mytotalcomfort?style=flat-square)](composer.json#L36)
[![Coveralls github](https://img.shields.io/coveralls/github/TenthPres/MyTotalComfort?style=flat-square)](https://coveralls.io/github/TenthPres/MyTotalComfort)
<!--![GitHub](https://img.shields.io/github/license/tenthpres/mytotalcomfort?style=flat-square) -->


This is a PHP library for working with [Honeywell's Total Connect Comfort interface](https://www.mytotalconnectcomfort.com) 
(TCC). It authenticates using user credentials and can provide data available through the web interface and more.    

Compatible with PHP >=5.6

Install with Composer: 
```shell
composer require tenth/my-total-comfort
composer install --no-dev
```
    
(There are notable dev dependencies that you really should only install if you want to auto-generate [documentation](https://github.com/TenthPres/MyTotalComfort/wiki).)

## Cloning and Documenting

Please clone and contribute, or report bugs should you find them.  



This repository has a script ([makedocs.php](makedocs.php)) intended to automatically generate and publish documentation changes based on doc blocks.  You can find [that documentation in the Wiki](https://github.com/TenthPres/MyTotalComfort/wiki).
As far as the repository is concerned, the documentation directory is a submodule.  To clone everything all in one shot, add the `--recursive` switch to your clone command: 

```shell
git clone --recursive https://github.com/TenthPres/MyTotalComfort.git
```
    
There's currently an issue when generating documentation in PHP 7.4 that makes the formatting very poor.  Other versions seem to work fine, and the package itself also works fine in 7.4.

## Disclaimers

The contributors to this project are not affiliated with Honeywell.

## Examples

### Log In
This Example is required before any of the other examples below will work. 

```php
require_once 'vendor/autoload.php';

$tcc = new \Tenth\MyTotalComfort("email@example.com", "password");
```

### List Conditions
Current Temperature and Set Points for all Zones in a Location. 

```php
require_once 'vendor/autoload.php';

$tcc = new \Tenth\MyTotalComfort("email@example.com", "password");

$locationId = 1234567;

echo "<table>";
echo "<tr>
    <td>id</td>
    <td>name</td>
    <td>heatSet</td>
    <td>dispTemp</td>
    <td>coolSet</td>
</tr>";

foreach ($tcc->getZonesByLocation($locationId) as $zi => $zone) {
    echo "<tr>
        <td>{$zone->id}</td>
        <td><a href='https://www.mytotalconnectcomfort.com/portal/Device/Control/{$zone->id}'>{$zone->name}</a></td>
        <td>{$zone->heatSetpoint}</td>
        <td>{$zone->dispTemperature}</td>
        <td>{$zone->coolSetpoint}</td>
    </tr>";
}

echo "</table>";
```

### Change Set Points

```php
require_once 'vendor/autoload.php';

$tcc = new \Tenth\MyTotalComfort("email@example.com", "password");

$zoneId = 1234567;

$z = $tcc->getZone($zoneId);

$z->heatSetpoint = 70;
$z->coolSetpoint = 74;
```