# DreamSpark SSO

## Introduction
This is the original source code which poweres [DreamSpark SSO](https://go.thenetw.org/dreamsparksso) service. The main purpose of the project being opensourced is to showcase how easy it is to develop applications upon Office 365 and Azure.

## Technology Used
The project is written in PHP, we use [Slim Framework](http://www.slimframework.com) as our base. The whole project is hosted on [Microsoft Azure](https://www.azure.com). We make use of [Azure Table Storage](https://azure.microsoft.com/en-us/services/storage/) (through [Azure PHP SDK](https://github.com/Azure/azure-sdk-for-php)), for user interface we use [Office UI Fabric](https://github.com/OfficeDev/Office-UI-Fabric) and [App Chrome](https://msdn.microsoft.com/en-us/office/office365/howto/javascript-controls). [Application Insights](https://azure.microsoft.com/en-us/services/application-insights/) are used for application monitoring. We also make use of [CloudFlare](https://www.cloudflare) which provides us with caching, SSL and many more.

## Setting up your own
If you would like to contribute to the development, you are probably interested in how-to run your own copy:

1. Deploy the source code to Azure or any other provider of your choice and run `composer install` (if you are using Azure Web Apps, you can use the [Composer extension](https://github.com/SyntaxC4-MSFT/ComposerExtension) to automatically handle Composer automatically when deploying from Git)
2. [Create a multi-tenant application in your Azure Active Directory](https://azure.microsoft.com/en-us/documentation/articles/resource-group-create-service-principal-portal/) and add the following permissions:
  * *Delegated Permissions*
    - Access the directory as the signed-in user
    - Read directory data
    - Sign in and read user profile
  * *Application Permissions*
    - Read directory data
    - Read and write directory data
3. Configure application through the environmental variables
4. Create a new storage account and add the [connection string](https://azure.microsoft.com/en-us/documentation/articles/web-sites-configure/#connection-strings) into the Web App (name: *Storage* and create table *organizationSettings*)
5. If you set up everything correctly, the application should work just fine.
6. In order for the applications to show in user's [My Apps](https://portal.office.com/myapps) page, you should run the `cron.php` in intervals (use WebJobs - *described below*)

### Environmental variables
| Variable Name | Description |
| ------------- | ----------- |
| Auth_appId | The Client Id of application you created in step 2. |
| Auth_appSecret | The Client Secret of application you created in step 2. |
| Auth_redirectUri | Your redirect URI which you set when creating application in step 2. |
| INSTRUMENTATION_KEY *(optional)* | The key of your application insights instance if you want to make use of it. |
| ENVIRONMENT *(optional)* | Set to `DEV` in order to see all debug messages both from PHP and Slim. |

### Setting up WebJob
In the Web App, create a new WebJob. Create a batch file, name it `run.bat` and insert the contents below, then zip the file and upload it as scheduled WebJob to Azure. In our production environment, we run this job every 12 hours.
```batch
@ECHO off
cd "D:\home\site\wwwroot"
php cron.php
```

## Application Flow
Application is designed to be granted with admin consent - upon first use so users don't have to consent to the application. After that something called *installation* happens which basically sets the tenant up in the Table Sotrage and prepares it for the first use.

Users get authenticated, authorized and then they are redirected to the DreamSpark Premium (upon successful login).

Administrators are authenticated, authorized and if then redirected to the application settings. They also have to be explicitely allowed to access DreamSpark Premium just like everyone else.

## Contributing
Feel free to contribute to this repository - just [create a pull request](https://github.com/TheNetworg/DreamSpark-SSO/pulls). If you found a bug or are having difficulties, [create a new issue](https://github.com/TheNetworg/DreamSpark-SSO/issues).

## Support, Liabilities and Disclaimer
Please note that we don't provide any guarantees for this source code. This code is provided under MIT license. If you have any questions or would like to contact us, feel free to do so at [dreamspark@edulog.in](mailto:dreamspark@edulog.in).

© [TheNetw.org s.r.o.](https://thenetw.org) 2015