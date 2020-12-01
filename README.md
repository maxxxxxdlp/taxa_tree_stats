# Taxa Tree Generator (Catalogue of Life)
This website generates a taxa tree compatible with Workbench and Wizzard from [Specify 6](https://github.com/specify/specify6) [Specify 7](https://github.com/specify/specify7).

## Requirements
1. PHP 7.2+ (older versions may work)
1. [PHP Zip](https://stackoverflow.com/questions/18774568/installing-php-zip-extension)
1. Any Webserver

## Installation
All of the configuration parameters you must change for the site to work are located in `./config/required.php`
Optional parameters are located in `./config/optional.php`

1. Open the `./config/required.php` file. Change the logic to properly detect correct `CONFIGURATION` and `DEVELOPMENT` constant values. The value of `DEVELOPMENT` will affect the error reporting level.
1. Set `LINK` to an address the website would be served on.
1. Set `WORKING_LOCATION` to an empty folder. This would be the destination for all the files created in the process. Make sure the webserver has **READ** and **WRITE** permissions to this folder. **Warning!** Files present in this directory may be deleted.
1. Run `http://<yourdomain>/refresh_data/` to download data.
1. Configure your webserver to point to the directory where this repository is saved.


### Optional settings
You can go over the other settings in the `./config/optional.php` file and see if there is anything you would like to adjust.

For example:
1. You can exclude internal IPs by adding them to `IPS_TO_EXCLUDE`. The data from those addresses would still be collected, but it wont be shown
1. You can set up daily CRON to the following location `http://<yourdomain>/cron/refresh_data.php`. This will automatically check for new versions of the taxa tree and download it.

## Credit for used resources
There were snippets of code/files from the following resources used:
- [Bootstrap 4.5.0](https://github.com/twbs/bootstrap)
- [jQuery 3.5.1](https://github.com/jquery/jquery)
- [Chart.js](https://github.com/chartjs/Chart.js)
- [Specify 7 icon](https://sp7demofish.specifycloud.org/static/img/fav_icon.png)
